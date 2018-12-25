<?php
/**
 * User: angryjack
 * Date: 05.12.18 : 10:45
 */

namespace Angryjack\BackupEngine;

use Angryjack\BackupEngine\Exception\LoggerException;

/**
 * Class Logger
 * @package Angryjack
 */
class Logger
{
    /**
     * Файл
     * @var string
     */
    protected $logFile = 'runtime.log';

    /**
     * @var string
     */
    protected $errorFile = 'error.log';

    /**
     * @var string
     */
    protected $flags;

    /**
     * @var int
     */
    public $timer;

    /**
     * Logger constructor.
     */
    public function __construct()
    {
        $this->flags = FILE_APPEND | LOCK_EX;
    }

    /**
     * Логирование начала операции с таймером
     * @param $message
     * @param bool $echo
     * @throws LoggerException
     */
    public function start($message, $echo = true)
    {
        $this->write($message, $echo);

        $this->timer = microtime(true);
    }

    /**
     * Логирование окончания операции с выводом продолжительности в секундах
     * @param $message
     * @param bool $echo
     * @throws LoggerException
     */
    public function stop($message, $echo = true)
    {
        $length = round(microtime(true) - $this->timer);
        $this->timer = null;
        $message .= " Операция заняла " . $this->timeFormat($length);
        $this->write($message, $echo);
    }

    /**
     * @param $name
     * @throws LoggerException
     */
    public function setLogFile($name)
    {
        if (empty($name)) {
            throw new LoggerException('Не указан лог файла.');
        }
        $this->logFile = $name;
    }

    /**
     * @param $name
     * @throws LoggerException
     */
    public function setErrorFile($name)
    {
        if (empty($name)) {
            throw new LoggerException('Не указан лог файла.');
        }
        $this->errorFile = $name;
    }

    /**
     * @param $message
     * @param bool $echo
     * @param bool $error
     * @throws LoggerException
     */
    public function write($message, $echo = true, $error = false)
    {
        if (empty($message)) {
            throw new LoggerException('Передана пустая строка.');
        }

        $message = '['. date('d.M.Y H:i:s') . '] ' . $message . "\n";

        if ($echo) {
            echo $message;
        }

        $logFile = $this->logFile;
        if ($error) {
            $logFile = $this->errorFile;
        }

        file_put_contents($logFile, $message, $this->flags);
    }

    /**
     * @param $seconds
     * @return string
     */
    protected function timeFormat($seconds)
    {
        $message = '';
        if ($seconds < 1) {
            $message =  'менее секунды.';
        } elseif ($seconds >= 1 && $seconds < 60) {
            $message =  $seconds . ' сек';
        } elseif ($seconds > 60) {
            // only in >= php7 //$message = floor($seconds / 60) . ' мин ' . ($seconds % 60) ?? $seconds % 60 . ' сек';
            if ($seconds % 60 === 0) {
                $message = $seconds / 60 . ' мин';
            } else {
                $message = floor($seconds / 60) . ' мин ' . $seconds % 60 . ' сек';
            }
        }

        return $message;
    }
}
