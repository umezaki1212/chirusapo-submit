<?php
namespace Classes\app;

use Application\lib\DatabaseManager;

require_once __DIR__.'/../lib/DatabaseManager.php';

class MasterManager {
    public static function get_vaccination() {
        $db = new DatabaseManager();
        $sql = "SELECT vaccination_name FROM master_vaccination ORDER BY id";
        $data = $db->fetchAll($sql, []);
        $array = [];
        foreach ($data as $row) {
            $array[] = $row['vaccination_name'];
        }
        return $array;
    }

    public static function get_allergy() {
        $db = new DatabaseManager();
        $sql = "SELECT allergy_name FROM master_allergy ORDER BY id";
        $data = $db->fetchAll($sql, []);
        $array = [];
        foreach ($data as $row) {
            $array[] = $row['allergy_name'];
        }
        return $array;
    }
}