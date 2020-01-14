<?php
namespace Application\app;

use Application\lib\DatabaseManager;

class ModelManager {
    public static function get_model($group_id) {
        $db = new DatabaseManager();
        $sql = "SELECT file_name FROM model_child WHERE group_id = :group_id";
        $model_child = $db->fetchAll($sql, [
            'group_id' => $group_id
        ]);
        foreach ($model_child as $key => $value) {
            $model_child[$key] = 'https://storage.googleapis.com/chirusapo/model/child/'.$value['file_name'];
        }
        $sql = "SELECT file_name FROM model_clothes WHERE group_id = :group_id";
        $model_clothes = $db->fetchAll($sql, [
            'group_id' => $group_id
        ]);
        foreach ($model_clothes as $key => $value) {
            $model_clothes[$key] = 'https://storage.googleapis.com/chirusapo/model/clothes/'.$value['file_name'];
        }
        return [
            'model_child' => $model_child,
            'model_clothes' => $model_clothes
        ];
    }

    public static function add_child_model($group_id, $file_name) {
        $db = new DatabaseManager();
        $sql = "INSERT INTO model_child (group_id, file_name) VALUES (:group_id, :file_name)";
        $db->insert($sql, [
            'group_id' => $group_id,
            'file_name' => $file_name
        ]);
    }

    public static function add_clothes_model($group_id, $file_name) {
        $db = new DatabaseManager();
        $sql = "INSERT INTO model_clothes (group_id, file_name) VALUES (:group_id, :file_name)";
        $db->insert($sql, [
            'group_id' => $group_id,
            'file_name' => $file_name
        ]);
    }
}