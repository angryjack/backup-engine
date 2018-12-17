<?php
/**
 * User: angryjack
 * Date: 06.12.18 : 16:40
 */

namespace Angryjack;

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
    public function __construct($workPath = '', $backupPath = '', $oauth = '')
    {
        $this->workPath = $workPath;
        $this->backupPath = $backupPath;
        $this->oauth = $oauth;
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
        $this->prepareForBackup();

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
        $this->prepareForBackup();

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

    /**
     * Подготавливаем скрипт резервного копирования (создаем папки, проверяем наличие переменных)
     * @throws Exception\LoggerException
     */
    protected function prepareForBackup()
    {
        if (empty($this->oauth)) {
            throw new \InvalidArgumentException('Не указан OAUTH токен!');
        }

        if (empty($this->workPath)) {
            throw new \InvalidArgumentException('Не указана рабочая директория!');
        }

        if (empty($this->backupPath)) {
            throw new \InvalidArgumentException('Не указана директория резервного копирования на Яндекс Диске');
        }

        $this->disk = new YandexDisk($this->oauth);
        $this->logger = new Logger();
        $this->fileManager = new FileManager();

        $this->logger->write("Создаю директорию: $this->backupPath на яндекс диске.");
        $this->disk->createDirectory($this->backupPath);
    }

    /**
     * Устанавливаем рабочую директорию
     * @param $path
     */
    public function setWorkPath($path)
    {
        $this->workPath = $path;
    }

    /**
     * Устанавливаем директорию для резервного копирования на ЯД
     * @param $path
     */
    public function setBackupPath($path)
    {
        $this->backupPath = $path;
    }

    /**
     * Устанавливаем токен
     * @param $token
     */
    public function setOauth($token)
    {
        $this->oauth = $token;
    }
}
