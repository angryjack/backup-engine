<?php
/**
 * User: angryjack
 * Date: 29.11.18 : 15:36
 */

namespace Angryjack;

use mysqli;

//todo добавить создание уникальных ключей, связей и тд

/**
 * Класс для создания резервной копии базы данных средствами php
 * В основу класса лег код с https://stackoverflow.com/a/31531996
 * @package Angryjack
 */
class SqlDumper
{
    private $host, $dbUser, $password, $dbName;

    /**
     * SqlDumper constructor.
     * @param $host - хост
     * @param $dbUser - пользователь
     * @param $password - пароль
     * @param $dbName - имя базы данных
     */
    public function __construct($host, $dbUser, $password, $dbName)
    {
        $this->host = $host;
        $this->dbUser = $dbUser;
        $this->password = $password;
        $this->dbName = $dbName;
    }

    /**
     * Получения всех таблиц и их значений из БД
     * @param bool $tables
     * @return string
     */
    public function exportDatabase($tables = false)
    {
        $mysqli = new mysqli($this->host, $this->dbUser, $this->password, $this->dbName);
        $mysqli->select_db($this->dbName);
        $mysqli->query("SET NAMES 'utf8'");

        $queryTables = $mysqli->query('SHOW TABLES');
        while ($row = $queryTables->fetch_row()) {
            $target_tables[] = $row[0];
        }
        if ($tables !== false) {
            $target_tables = array_intersect($target_tables, $tables);
        }
        foreach ($target_tables as $table) {
            $result = $mysqli->query('SELECT * FROM ' . $table);
            $fields_amount = $result->field_count;
            $rows_num = $mysqli->affected_rows;
            $res = $mysqli->query('SHOW CREATE TABLE ' . $table);
            $TableMLine = $res->fetch_row();
            $content = (!isset($content) ? '' : $content) . "\n\n" . $TableMLine[1] . ";\n\n";

            for ($i = 0, $st_counter = 0; $i < $fields_amount; $i++, $st_counter = 0) {
                while ($row = $result->fetch_row()) { //when started (and every after 100 command cycle):
                    if ($st_counter % 100 == 0 || $st_counter == 0) {
                        $content .= "\nINSERT INTO " . $table . " VALUES";
                    }
                    $content .= "\n(";
                    for ($j = 0; $j < $fields_amount; $j++) {
                        $row[$j] = str_replace("\n", "\\n", addslashes($row[$j]));
                        if (isset($row[$j])) {
                            $content .= '"' . $row[$j] . '"';
                        } else {
                            $content .= '""';
                        }
                        if ($j < ($fields_amount - 1)) {
                            $content .= ',';
                        }
                    }
                    $content .= ")";
                    //every after 100 command cycle [or at last line] ....p.s. but should be inserted 1 cycle eariler
                    if ((($st_counter + 1) % 100 == 0 && $st_counter != 0) || $st_counter + 1 == $rows_num) {
                        $content .= ";";
                    } else {
                        $content .= ",";
                    }
                    $st_counter = $st_counter + 1;
                }
            }
            $content .= "\n\n\n";
        }

        return $content;
    }

    /**
     * Отдача базы данных на скачивание
     * @param $content
     * @param $backupName
     */
    public function downloadSqlDump($content, $backupName)
    {
        $backupName = $backupName ? $backupName : $this->dbName . ".sql";
        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"" . $backupName . "\"");
        echo $content;
        exit;
    }

    /**
     * Создания дапма БД на аккаунте
     * @param $content
     * @param bool $backupName
     * @return bool|int
     */
    public function createSqlDump($content, $backupName = false)
    {
        $backupName = $backupName ? $backupName : $this->dbName . ".sql";
        return file_put_contents($backupName, $content);
    }

}