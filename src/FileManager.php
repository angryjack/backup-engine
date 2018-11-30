<?php
/**
 * User: angryjack
 * Date: 28.11.18 : 17:15
 */

namespace Angryjack;

use DirectoryIterator;

/**
 * Класс для удобной работы с файлами на сервере
 * @package Angryjack
 */
class FileManager
{
    protected $path;

    public function __construct($path = '')
    {
        $this->path = $path;
    }

    /**
     * Возвращаем все файлы и папки по указанному пути за исключением точек
     * @param $directory
     * @return array массив с файлами и папками
     */
    public function scandir($path)
    {
        return array_diff(scandir($path), array('..', '.'));
    }

    /**
     * Возвращает массив с названием папок по указанному пути
     * @param $path
     * @return array
     */
    public function getFoldersFromPath($path)
    {
        // '.' for current
        $folders = array();
        foreach (new DirectoryIterator($path) as $file) {
            if ($file->isDot()) continue;

            if ($file->isDir()) {
                array_push($folders, $file->getFilename());
            }
        }
        return $folders;
    }

    /**
     * Проверяем папку на установленную систему Joomla
     * @param $path
     * @return bool
     */
    public function checkForJoomla($path)
    {
        return file_exists($path . "/public_html/configuration.php");
    }

    /**
     * Удаление файла
     * @param $path
     * @return bool
     */
    public function deleteFile($path)
    {
        return unlink($path);
    }

    /**
     * Удаление папки
     * @param $path
     * @return bool
     */
    public function deleteDir($path){
        return rmdir($path);
    }
}