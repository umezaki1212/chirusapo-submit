<?php
namespace Application\App;

use Application\lib\DatabaseManager;

class ChildFriendManager {
    public static function list_friend($child_id) {
        $db = new DatabaseManager();
        $sql = "SELECT id, user_name, birthday, gender, memo, icon FROM child_friend WHERE child_id = :child_id";
        $data = $db->fetchAll($sql, [
            'child_id' => $child_id
        ]);
        foreach ($data as $key => $value) {
            $data[$key]['icon'] = empty($value['icon']) ? null : 'https://storage.googleapis.com/chirusapo/child-friend-icon/'.$value['icon'];
        }
        return $data;
    }

    public static function get_friend($friend_id) {
        $db = new DatabaseManager();
        $sql = "SELECT id, user_name, birthday, gender, memo, icon FROM child_friend WHERE id = :friend_id";
        $data = $db->fetch($sql, [
            'friend_id' => $friend_id
        ]);
        $data['icon'] = empty($data['icon']) ? null : 'https://storage.googleapis.com/chirusapo/child-friend-icon/'.$data['icon'];
        return $data;
    }

    public static function add_friend($child_id, $user_name, $birthday, $gender, $memo, $icon = null) {
        $db = new DatabaseManager();
        if (is_null($icon)) {
            $sql = "INSERT INTO child_friend (child_id, user_name, birthday, gender, memo) VALUES (:child_id, :user_name, :birthday, :gender, :memo)";
            $inner_id = $db->insert($sql, [
                'child_id' => $child_id,
                'user_name' => $user_name,
                'birthday' => $birthday,
                'gender' => $gender,
                'memo' => $memo
            ]);
        } else {
            $sql = "INSERT INTO child_friend (child_id, user_name, birthday, gender, memo, icon) VALUES (:child_id, :user_name, :birthday, :gender, :memo, :icon)";
            $inner_id = $db->insert($sql, [
                'child_id' => $child_id,
                'user_name' => $user_name,
                'birthday' => $birthday,
                'gender' => $gender,
                'memo' => $memo,
                'icon' => $icon
            ]);
        }
        return $inner_id;
    }

    public static function edit_user_name($friend_id, $user_name) {
        $db = new DatabaseManager();
        $sql = "UPDATE child_friend SET user_name = :user_name WHERE id = :friend_id";
        $db->execute($sql, [
            'user_name' => $user_name,
            'friend_id' => $friend_id
        ]);
    }

    public static function edit_birthday($friend_id, $birthday) {
        $db = new DatabaseManager();
        $sql = "UPDATE child_friend SET birthday = :birthday WHERE id = :friend_id";
        $db->execute($sql, [
            'birthday' => $birthday,
            'friend_id' => $friend_id
        ]);
    }

    public static function edit_gender($friend_id, $gender) {
        $db = new DatabaseManager();
        $sql = "UPDATE child_friend SET gender = :gender WHERE id = :friend_id";
        $db->execute($sql, [
            'gender' => $gender,
            'friend_id' => $friend_id
        ]);
    }

    public static function edit_memo($friend_id, $memo) {
        $db = new DatabaseManager();
        $sql = "UPDATE child_friend SET memo = :memo WHERE id = :friend_id";
        $db->execute($sql, [
            'memo' => $memo,
            'friend_id' => $friend_id
        ]);
    }

    public static function edit_icon($friend_id, $icon) {
        $db = new DatabaseManager();
        $sql = "UPDATE child_friend SET icon = :icon WHERE id = :friend_id";
        $db->execute($sql, [
            'icon' => $icon,
            'friend_id' => $friend_id
        ]);
    }

    public static function already_friend_id($friend_id) {
        $db = new DatabaseManager();
        $sql = "SELECT count(*) FROM child_friend WHERE id = :friend_id";
        $result = $db->fetch($sql, [
            'friend_id' => $friend_id
        ]);
        return $result == 0 ? false : true;
    }

    public static function friend_id_to_inner_child_id($friend_id) {
        if (!self::already_friend_id($friend_id)) return false;
        $db = new DatabaseManager();
        $sql = "SELECT child_id FROM child_friend WHERE id = :friend_id";
        return $db->fetchColumn($sql, [
            'friend_id' => $friend_id
        ]);
    }

    public static function delete_friend($friend_id) {
        $db = new DatabaseManager();
        $sql = "DELETE FROM child_friend WHERE id = :friend_id";
        $db->execute($sql, [
            'friend_id' => $friend_id
        ]);
    }

    public static function autofill_child_info($child_id) {
        $db = new DatabaseManager();
        $sql = "SELECT user_name, birthday, gender FROM account_child WHERE id = :child_id";
        $data = $db->fetch($sql, [
            'child_id' => $child_id
        ]);
        return $data;
    }
}