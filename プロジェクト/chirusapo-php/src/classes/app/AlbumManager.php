<?php
namespace Application\App;

use Application\lib\DatabaseManager;

class AlbumManager {
    public static function get_album($group_id) {
        $db = new DatabaseManager();
        $sql = "SELECT file_name FROM album_data WHERE group_id = :group_id";
        $data = $db->fetchAll($sql, [
            'group_id' => $group_id
        ]);
        foreach ($data as $key => $value) {
            $data[$key] = 'https://storage.googleapis.com/chirusapo/album/'.$value['file_name'];
        }
        return $data;
    }

    public static function add_album($group_id, $file_name) {
        $db = new DatabaseManager();
        $sql = "INSERT INTO album_data (group_id, file_name, upload_time) VALUES (:group_id, :file_name, :upload_time)";
        $db->insert($sql, [
            'group_id' => $group_id,
            'file_name' => $file_name,
            'upload_time' => date('Y-m-d H:i:s')
        ]);
    }
}