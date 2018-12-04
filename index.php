<?php

require_once 'config.php';

use Yandex\Disk\DiskClient;
use Angryjack\FileManager;
use Angryjack\HZip;

$backupFolderName = BACKUP_PATH . '/' . date('d-m-Y') . '/';
try{
    $disk = new DiskClient(OAUTH);

    // получаем все папки из указанной папки бекапов на яндекс диске
    $directories = $disk->directoryContents(dirname($backupFolderName));
    // проверяем существует ли папка, которую мы пытаемся создать
    // если не существует - создаем
    if(! array_search($backupFolderName, array_column($directories, 'href'))) {
        logger("Создаю директорию: $backupFolderName на яндекс диске.");
        $dirContent = $disk->createDirectory($backupFolderName);
    }

    $fileManager = new FileManager();
    $folders = $fileManager->getFoldersFromPath(WORK_PATH);

    //сортируем папки по имени
    asort($folders);

    foreach ($folders as $folder) {
        echo $folder . "\n";
        if (substr($folder, 0, 1) == '.') {
            logger("Системная папка: $folder. Пропускаю...");
            continue;
        }
        $fileName = WORK_PATH . '/' . $folder . '.zip';
        $archiveName = $folder . '.zip';
        logger("Создаю архив: $archiveName");
        $start = microtime(true);
        HZip::zipDir(WORK_PATH . '/' . $folder, $fileName);
        $time = round(microtime(true) - $start);
        logger("Архив: $archiveName создан. Операция заняла $time секунд.");

        logger("Выгружаю архив: $archiveName на яндекс диск.");
        $start = microtime(true);
        $res = $disk->uploadFile(
            $backupFolderName,
            array(
                'path' => $fileName,
                'size' => filesize($fileName),
                'name' => $archiveName
            )
        );
        $time = round(microtime(true) - $start);
        logger("Архив: $archiveName загружен. Операция заняла $time секунд.");
        //удаление созданного архива
        logger("Удаление архива $archiveName");
        $fileManager->deleteFile($fileName);
    }

}
catch (\Exception $e){
    logger($e->getMessage(), 'error');
}

function logger($message, $type = 'normal'){
    if($type == 'normal')
        $logFile = 'runtime.log';
    else
        $logFile = 'error.log';
    $message = '[' . date('d.M.Y H:i:s') . '] ' . $message . "\n";
    echo $message;
    file_put_contents($logFile, $message . "\n", FILE_APPEND | LOCK_EX);
}