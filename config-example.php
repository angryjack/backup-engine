<?php
/**
 * User: angryjack
 * Date: 28.11.18 : 16:02
 */

// подключаем автозагрузчик
require_once __DIR__ . "/src/bootstrap.php";

// токен яндекс диска
define("OAUTH", "");

// рабочая дирректория с сайтами
define("WORK_PATH", __DIR__ . "../");

// названия сервера
define("SERVER_NAME", "server");

// папка бекапов на яндекс диске
define("BACKUP_PATH", "/backups/" . SERVER_NAME);