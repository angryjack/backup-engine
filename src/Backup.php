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
    private $disk;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var FileManager
     */
    private $fileManager;

    /**
     * Рабочая директория (из которой сохраняются файлы)
     * @var string
     */
    private $workPath;

    /**
     * Директория куда сохраняются файлы (на Яндекс Диске)
     * @var string
     */
    private $backupPath;

    /**
     * Токен OAUTH для доступа к Яндекс диску
     * @var string
     */
    private $oauth;

    /**
     * Backup constructor.
     * @param $workPath
     * @param $backupPath
     * @param $oauth
     * @throws Exception\BackupException
     * @throws Exception\LoggerException
     */
    public function __construct($workPath, $backupPath, $oauth)
    {
        if (empty($workPath)) {
            throw new BackupException('Вы не указали рабочую директорию.');
        }

        if (empty($backupPath)) {
            throw new BackupException('Вы не указали директорию на Яндекс Диске');
        }

        if (empty($oauth)) {
            throw new BackupException('Вы не указали токен Яндекс Диска');
        }

        $this->workPath = $workPath;
        $this->backupPath = $backupPath;
        $this->oauth = $oauth;

        $this->disk = new YandexDisk($this->oauth);
        $this->logger = new Logger();
        $this->fileManager = new FileManager();

        $this->logger->write("Создаю директорию: $this->backupPath на яндекс диске.");
        $this->disk->createDirectory($this->backupPath);
    }

    /**
     * Резервная копия файлов
     * @param string $pattern отсортировать файлы - регулярное выражение (какие файлы сохранять)
     * @param bool $deleteAfterBackup
     * @throws Exception\FileManagerException
     * @throws Exception\LoggerException
     * @return bool
     */
    public function files($pattern = '', $deleteAfterBackup = false)
    {
        $files = $this->fileManager->getFilesFromPath($this->workPath);

        if (! empty($pattern)) {
            $files = preg_grep($pattern, $files);
        }

        foreach ($files as $file) {
            $fileName = $this->workPath . '/' . $file;

            $this->logger->start("Выгружаю файл: $file на яндекс диск.");
            $this->disk->uploadFile(
                $this->backupPath,
                array(
                    'path' => $fileName,
                    'size' => filesize($fileName),
                    'name' => $file
                )
            );
            $this->logger->stop("Файл: $file загружен.");

            if ($deleteAfterBackup) {
                $this->logger->write("Удаление файла $file");
                $this->fileManager->deleteFile($fileName);
            }
        }

        return true;
    }

    /**
     * Резервная копия папок
     * @param string $pattern - отсортировать папки  - регулярное выражение (какие папки сохранять)
     * @param bool $deleteAfterBackup
     * @throws Exception\FileManagerException
     * @throws Exception\LoggerException
     * @return bool
     */
    public function folders($pattern = '', $deleteAfterBackup = true)
    {
        $folders = $this->fileManager->getFoldersFromPath($this->workPath);

        if (! empty($pattern)) {
            $folders = preg_grep($pattern, $folders);
        }

        foreach ($folders as $folder) {
            $archiveName = $folder . '.zip';
            $fileName = $this->workPath . '/ ' . $archiveName;

            $this->logger->start("Создаю архив: $archiveName");
            HZip::zipDir($this->workPath . '/' . $folder, $fileName);
            $this->logger->stop("Архив: $archiveName создан.");

            $this->logger->start("Выгружаю архив: $archiveName на яндекс диск.");
            $this->disk->uploadFile(
                $this->backupPath,
                array(
                    'path' => $fileName,
                    'size' => filesize($fileName),
                    'name' => $archiveName
                )
            );
            $this->logger->stop("Архив: $archiveName загружен.");

            if ($deleteAfterBackup) {
                $this->logger->write("Удаление архива $archiveName");
                $this->fileManager->deleteFile($fileName);
            }
        }

        return true;
    }
}
