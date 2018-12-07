<?php
/**
 * User: angryjack
 * Date: 06.12.18 : 16:40
 */

namespace Angryjack;

use Angryjack\Exception\BackupException;

/**
 * Class Backup
 * @package Angryjack
 */
class Backup
{
    /**
     * @var YandexDisk
     */
    private $_disk;

    /**
     * @var Logger
     */
    private $_logger;

    /**
     * @var FileManager
     */
    private $_fileManager;

    /**
     * Рабочая директория (из которой сохраняются файлы)
     * @var string
     */
    private $_workPath = WORK_PATH;

    /**
     * Директория куда сохраняются файлы (на Яндекс Диске)
     * @var string
     */
    private $_backupPath = BACKUP_PATH;

    /**
     * Токен OAUTH для доступа к Яндекс диску
     * @var string
     */
    private $_oauth = OAUTH;

    /**
     * Backup constructor.
     * @param $workPath
     * @param $backupPath
     * @param $oauth
     * @throws Exception\LoggerException
     */
    public function __construct($workPath, $backupPath, $oauth)
    {
        if (!empty($workPath)) {
            $this->_workPath = $workPath;
        }

        if (!empty($backupPath)) {
            $this->_backupPath = $backupPath;
        }

        if (!empty($oauth)) {
            $this->_oauth = $oauth;
        }

        $this->_disk = new YandexDisk($this->_oauth);
        $this->_logger = new Logger();
        $this->_fileManager = new FileManager();

        $this->_logger->write("Создаю директорию: $this->_backupPath на яндекс диске.");
        $this->_disk->createDirectory($this->_backupPath);
    }

    /**
     * Резервная копия файлов
     * @param string $pattern отсортировать файлы - регулярное выражение (какие файлы сохранять)
     * @param bool $deleteAfterBackup
     * @throws Exception\FileManagerException
     * @throws Exception\LoggerException
     */
    public function files($pattern = '', $deleteAfterBackup = false)
    {
        $files = $this->_fileManager->getFilesFromPath($this->_workPath);

        if (! empty($pattern)) {
            $files = preg_grep($pattern, $files);
        }

        foreach ($files as $file) {
            $fileName = $this->_workPath . '/' . $file;

            $this->_logger->start("Выгружаю файл: $file на яндекс диск.");
            $this->_disk->uploadFile(
                $this->_backupPath,
                array(
                    'path' => $fileName,
                    'size' => filesize($fileName),
                    'name' => $file
                )
            );
            $this->_logger->stop("Файл: $file загружен.");

            if ($deleteAfterBackup) {
                $this->_logger->write("Удаление файла $file");
                $this->_fileManager->deleteFile($fileName);
            }
        }
    }

    /**
     * Резервная копия папок
     * @param string $pattern - отсортировать папки  - регулярное выражение (какие папки сохранять)
     * @param bool $deleteAfterBackup
     * @throws Exception\FileManagerException
     * @throws Exception\LoggerException
     */
    public function folders($pattern = '', $deleteAfterBackup = true)
    {
        $folders = $this->_fileManager->getFoldersFromPath($this->_workPath);

        if (! empty($pattern)) {
            $folders = preg_grep($pattern, $folders);
        }

        foreach ($folders as $folder) {

            $archiveName = $folder . '.zip';
            $fileName = $this->_workPath . '/ ' . $archiveName;

            $this->_logger->start("Создаю архив: $archiveName");
            HZip::zipDir($this->_workPath . '/' . $folder, $fileName);
            $this->_logger->stop("Архив: $archiveName создан.");

            $this->_logger->start("Выгружаю архив: $archiveName на яндекс диск.");
            $res = $this->_disk->uploadFile(
                $this->_backupPath,
                array(
                    'path' => $fileName,
                    'size' => filesize($fileName),
                    'name' => $archiveName
                )
            );
            $this->_logger->stop("Архив: $archiveName загружен.");

            if ($deleteAfterBackup) {
                $this->_logger->write("Удаление архива $archiveName");
                $this->_fileManager->deleteFile($fileName);
            }
        }
    }
}