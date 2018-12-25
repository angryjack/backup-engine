<?php
/**
 * User: angryjack
 * Date: 28.11.18 : 17:15
 */

namespace Angryjack\BackupEngine;

use Angryjack\BackupEngine\Exception\FileManagerException;
use DirectoryIterator;

/**
 * Класс для удобной работы с файлами на сервере
 * @package Angryjack
 */
class FileManager
{
    /**
     * @var string
     */
    protected $path;

    /**
     * FileManager constructor.
     * @param string $path
     */
    public function __construct($path = '')
    {
        $this->path = $path;
    }

    /**
     * Устанавливаем рабочий путь
     * @param $path
     */
    public function setWorkPath($path)
    {
        $this->path = $path;
    }

    /**
     * Возвращаем все файлы и папки по указанному пути за исключением точек
     * @param $path
     * @throws FileManagerException
     * @return array массив с файлами и папками
     */
    public function scandir($path = '')
    {
        if (empty($path)) {
            if (empty($this->path)) {
                throw new FileManagerException('Вы не указали рабочую директорию.');
            }
            $path = $this->path;
        }

        if (! is_dir($path)) {
            throw new FileManagerException("Данная директория: $path не существует.");
        }

        return array_diff(scandir($path), array('..', '.'));
    }

    /**
     * Возвращает отсортированный массив с названием папок по указанному пути
     * @param $path
     * @throws FileManagerException
     * @return array
     */
    public function getFoldersFromPath($path = '')
    {
        if (empty($path)) {
            if (empty($this->path)) {
                throw new FileManagerException('Вы не указали рабочую директорию.');
            }
            $path = $this->path;
        }

        if (! is_dir($path)) {
            throw new FileManagerException("Данная директория: $path не существует.");
        }

        $folders = array();
        foreach (new DirectoryIterator($path) as $file) {
            if ($file->isDot()) {
                continue;
            }

            if ($file->isDir()) {
                array_push($folders, $file->getFilename());
            }
        }
        asort($folders);

        return $folders;
    }

    /**
     * Возвращает отсортированный массив с названием файлов по указанному пути
     * @param string $path
     * @return array
     * @throws FileManagerException
     */
    public function getFilesFromPath($path = '')
    {
        if (empty($path)) {
            if (empty($this->path)) {
                throw new FileManagerException('Вы не указали рабочую директорию.');
            }
            $path = $this->path;
        }

        if (! is_dir($path)) {
            throw new FileManagerException("Данная директория: $path не существует.");
        }

        $files = array();
        foreach (new DirectoryIterator($path) as $file) {
            if ($file->isDot()) {
                continue;
            }

            if ($file->isFile()) {
                array_push($files, $file->getFilename());
            }
        }
        asort($files);

        return $files;
    }


    /**
     * Удаление файла
     * @param $path
     * @throws FileManagerException
     * @return bool
     */
    public function deleteFile($path = '')
    {
        if (! file_exists($path)) {
            throw new FileManagerException("Файл $path не найден.");
        }

        if (! is_writable($path)) {
            throw new FileManagerException("Нет прав на удаление файла: $path");
        }

        if (! is_file($path)) {
            throw new FileManagerException("$path должен быть файлом.");
        }

        return unlink($path);
    }

    /**
     * Создание новой дирректории
     * @param string $path
     * @return bool
     * @throws FileManagerException
     */
    public function makeDir($path = '')
    {
        if (empty($path)) {
            if (empty($this->path)) {
                throw new FileManagerException('Вы не указали рабочую директорию.');
            }
            $path = $this->path;
        }

        $parent = dirname($path);
        if (! is_writable($parent)) {
            throw new FileManagerException("Нет прав на запись: $path");
        }

        return mkdir($path);
    }

    /**
     * Удаление папки
     * @param $path
     * @throws FileManagerException
     * @return bool
     */
    public function deleteDir($path = '')
    {
        if (! file_exists($path)) {
            throw new FileManagerException("Папка $path не найдена.");
        }

        if (! is_writable($path)) {
            throw new FileManagerException("Нет прав на удаление папки: $path");
        }

        return rmdir($path);
    }
}
