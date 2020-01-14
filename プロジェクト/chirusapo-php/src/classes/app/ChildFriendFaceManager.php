<?php
namespace Application\App;

use Application\lib\DatabaseManager;

class ChildFriendFaceManager {
    /** 顔情報を取得
     * @param $friend_id
     * @return array
     */
    public static function get_face($friend_id) {
        $db = new DatabaseManager();
        $sql = "SELECT id, file_name FROM child_friend_face WHERE friend_id = :friend_id";
        $data = $db->fetchAll($sql, [
            'friend_id' => $friend_id
        ]);
        foreach ($data as $key => $value) {
            $data[$key]['file_name'] = 'https://storage.googleapis.com/chirusapo/face-recognition/friend/'.$value['file_name'];
        }
        return $data;
    }

    /** 顔情報を登録
     * @param $friend_id
     * @param $file_name
     * @return string
     */
    public static function add_face($friend_id, $file_name) {
        $db = new DatabaseManager();
        $sql = "INSERT INTO child_friend_face (friend_id, file_name) VALUES (:friend_id, :file_name)";
        return $db->insert($sql, [
            'friend_id' => $friend_id,
            'file_name' => $file_name
        ]);
    }

    /** 顔情報を削除
     * @param $file_name
     */
    public static function delete_face($file_name) {
        $db = new DatabaseManager();
        $sql = "DELETE FROM child_friend_face WHERE file_name = :file_name";
        $db->execute($sql, [
            'file_name' => $file_name
        ]);
    }

    /** ファイル名が存在するか
     * @param $file_name
     * @return bool
     */
    public static function already_file_name($file_name) {
        $db = new DatabaseManager();
        $sql = "SELECT count(*) FROM child_friend_face WHERE file_name = :file_name";
        $count = $db->fetchColumn($sql, [
            'file_name' => $file_name
        ]);
        return $count == 0 ? false : true;
    }

    /** ファイル名から子どもIDを取得
     * @param $file_name
     * @return bool|int
     */
    public static function file_name_to_child_id($file_name) {
        if (!self::already_file_name($file_name)) return false;
        $db = new DatabaseManager();
        $sql = "SELECT friend_id FROM child_friend_face WHERE file_name = :file_name";
        return $db->fetchColumn($sql, [
            'file_name' => $file_name
        ]);
    }
}