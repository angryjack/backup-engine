<?php

require_once 'config.php';

use Yandex\Disk\DiskClient;
use Angryjack\FileManager;
//use Angryjack\SqlDumper;
use Angryjack\HZip;

// Проходимся по каждой папке в корне сервера
// Если найдена Joomla в папке делаем дамп базы данных
// Формируем zip архив папки
// Выгружаем архив на яндекс диск
// Удаляем архив и переходим к следующей папке

$backupFolderName = BACKUP_PATH . '/' . date('d-m-Y') . '/';

$disk = new DiskClient(OAUTH);
//todo сделать возможность создание вложенных каталогов
$dirContent = $disk->createDirectory($backupFolderName);

$fileManager = new FileManager();
$folders = $fileManager->getFoldersFromPath(WORK_PATH);

foreach ($folders as $folder) {

    $fileName = WORK_PATH . $folder . '.zip';

    HZip::zipDir(WORK_PATH . $folder, $fileName);

    $newName = $folder . '.zip';

    $res = $disk->uploadFile(
        $backupFolderName,
        array(
            'path' => $fileName,
            'size' => filesize($fileName),
            'name' => $newName
        )
    );

    //удаление созданного архива
    $fileManager->deleteFile($fileName);
}