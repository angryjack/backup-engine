<?php
/**
 * User: angryjack
 * Date: 05.12.18 : 15:43
 */

namespace Angryjack\BackupEngine;

use InvalidArgumentException;
use Yandex\Disk\DiskClient;

/**
 * Class YandexDisk
 * @package Angryjack
 */
class YandexDisk extends DiskClient
{
    /**
     * Создаем папку на яндекс диске, если папка существует - возвращаем false
     * @param string $path
     * @return bool
     */
    public function createDirectory($path = '')
    {
        if (empty($path)) {
            throw new InvalidArgumentException('Вы не указали папку на Яндекс Диске.');
        }
        $folders = explode('/', $path);

        $currentDir = '/';
        for ($i = 1; $i < count($folders) - 1; $i++) {
            $currentDir .= $folders[$i] . '/';
            $directories = $this->directoryContents(dirname($currentDir));

            if (! array_search($currentDir, array_column($directories, 'href'))) {
                parent::createDirectory($currentDir);
            }
        }

        return true;
    }
}
