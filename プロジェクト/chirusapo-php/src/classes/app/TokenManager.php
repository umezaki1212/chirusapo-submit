<?php
namespace Application\app;

use Application\lib\DatabaseManager;

require_once __DIR__.'/../lib/DatabaseManager.php';
require_once 'functions.php';

class TokenManager {
    /**
     * @param $token
     * @return bool
     */
    public static function verify_token($token) {
        $db = new DatabaseManager();
        $sql = "SELECT count(*) FROM account_user_token WHERE token = :token AND expiration_date > :expiration_date";
        $count = $db->fetchColumn($sql, [
            'token' => $token,
            'expiration_date' => date('Y-m-d H:i:s')
        ]);
        return $count == 0 ? false : true;
    }

    public static function already_token($token) {
        return self::count_token($token) == 0 ? false : true;
    }

    /**
     * @param $token
     * @return bool|int
     */
    public static function get_user_id($token) {
        if (!self::verify_token($token)) {
            return false;
        }
        $db = new DatabaseManager();
        $sql = "SELECT user_id FROM account_user_token WHERE token = :token";
        $user_id = $db->fetchColumn($sql, [
            'token' => $token
        ]);
        return $user_id;
    }

    /**
     * @param $user_id
     * @return string
     */
    public static function add_token($user_id) {
        do {
            $token = random(30);
        } while (self::count_token($token) > 0);
        $db = new DatabaseManager();
        $sql = "INSERT INTO account_user_token (user_id, token, expiration_date) VALUES (:user_id, :token, :expiration_date)";
        $db->insert($sql, [
            'user_id' => $user_id,
            'token' => $token,
            'expiration_date' => date('Y-m-d H:i:s', strtotime('+1 month'))
        ]);
        return $token;
    }

    /**
     * @param $token
     * @return int
     */
    public static function count_token($token) {
        $db = new DatabaseManager();
        $sql = "SELECT count(*) FROM account_user_token WHERE token = :token";
        $count = $db->fetchColumn($sql, [
            'token' => $token
        ]);
        return $count;
    }

    public static function delete_token($token) {
        $db = new DatabaseManager();
        $sql = "DELETE FROM account_user_token WHERE token = :token";
        $db->execute($sql, [
            'token' => $token
        ]);
    }

    public static function delete_user_id_token($user_id) {
        $db = new DatabaseManager();
        $sql = "DELETE FROM account_user_token WHERE user_id = :user_id";
        $db->execute($sql, [
            'user_id' => $user_id
        ]);
    }
}