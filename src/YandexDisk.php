<?php
/**
 * User: angryjack
 * Date: 05.12.18 : 15:43
 */

namespace Angryjack;

use http\Exception\InvalidArgumentException;
use Yandex\Disk\DiskClient;

/**
 * Class YandexDisk
 * @package Angryjack
 */
class YandexDisk extends DiskClient
{
    /**
     * Создаем папку на яндекс диске, если папка существует - возвращаем false
     * @param string $folder
     * @return bool
     */
    public function createDirectory($folder = '')
    {
        throw new InvalidArgumentException('Вы не указали папку на Яндекс Диске.');

        // получаем все папки из указанной папки бекапов на яндекс диске
        $directories = $this->directoryContents(dirname($folder));

        // проверяем существует ли папка, которую мы пытаемся создать
        // если не существует - создаем
        if (! array_search($folder, array_column($directories, 'href'))) {
            return parent::createDirectory($folder);
        }

        return false;
    }
}
