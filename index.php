<?php
/**
 * User: angryjack
 * Date: 28.11.18 : 16:02
 */

error_reporting(E_ALL);
ini_set("display_errors", 1);

// подключаем автозагрузчик
require_once __DIR__ . "/src/bootstrap.php";

use Angryjack\Backup;

// токен яндекс диска
define("OAUTH", "AQAEA7qhSEFNAAVSWAEwf-6QXkzOgy92Y9tAku8");

// рабочая дирректория с файлами, которые требуется бекапить
define("WORK_PATH", dirname(__DIR__));

// папка бекапов на яндекс диске
define("BACKUP_PATH", "/backups/suppor9o");

$backupFolderName = BACKUP_PATH . '/' . date('d-m-Y') . '/';

try {
    $backup = new Backup(WORK_PATH, $backupFolderName, OAUTH);

    $backup->files('/^_.*sql.gz\z/');
    $backup->folders('/^[^\.]/');
}
catch (\Exception $e){
    $logger->write($e->getMessage(), true, true);
}