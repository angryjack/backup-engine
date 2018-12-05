<?php

require_once 'config.php';

use Angryjack\FileManager;
use Angryjack\HZip;
use Angryjack\YandexDisk;
use Angryjack\Logger;

$backupFolderName = BACKUP_PATH . '/' . date('d-m-Y') . '/';
try{
    $disk = new YandexDisk(OAUTH);
    $logger = new Logger();
    $fileManager = new FileManager();

    $logger->write("Создаю директорию: $backupFolderName на яндекс диске.");
    $disk->createDirectory($backupFolderName);

    $folders = $fileManager->getFoldersFromPath(WORK_PATH);

    foreach ($folders as $folder) {
        if (substr($folder, 0, 1) == '.') {
            $logger->write("Системная папка: $folder. Пропускаю...");
            continue;
        }
        $archiveName = $folder . '.zip';
        $fileName = WORK_PATH . '/ '. $archiveName;

        $logger->start("Создаю архив: $archiveName");
        HZip::zipDir(WORK_PATH . '/' . $folder, $fileName);
        $logger->stop("Архив: $archiveName создан.");

        $logger->start("Выгружаю архив: $archiveName на яндекс диск.");
        $res = $disk->uploadFile(
            $backupFolderName,
            array(
                'path' => $fileName,
                'size' => filesize($fileName),
                'name' => $archiveName
            )
        );
        $logger->stop("Архив: $archiveName загружен.");
        //удаление созданного архива
        $logger->write("Удаление архива $archiveName");
        $fileManager->deleteFile($fileName);
    }

}
catch (\Exception $e){
    $logger->write($e->getMessage(), true, true);
}

