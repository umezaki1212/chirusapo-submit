<?php
namespace Application\app;

use Application\lib\DatabaseManager;
use Application\lib\Mailer;

require_once __DIR__.'/../lib/DatabaseManager.php';
require_once __DIR__.'/../lib/Mailer.php';
require_once 'TokenManager.php';

class AccountManager {
    /** アカウント登録
     * @param $user_id
     * @param $user_name
     * @param $email
     * @param $password
     * @param $gender
     * @param $birthday
     * @return string
     */
    public static function sign_up($user_id, $user_name, $email, $password, $gender, $birthday) {
        $db = new DatabaseManager();
        $sql = "INSERT INTO account_user (user_id, user_name, email, password, gender, birthday) VALUES (:user_id, :user_name, :email, :password, :gender, :birthday)";
        $id = $db->insert($sql, [
            'user_id' => $user_id,
            'user_name' => $user_name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'gender' => $gender,
            'birthday' => $birthday
        ]);
        return $id;
    }

    /** ログイン
     * @param $user_id
     * @param $password
     * @return bool
     */
    public static function sign_in($user_id, $password) {
        $db = new DatabaseManager();
        $sql = "SELECT id, password FROM account_user WHERE resign_flg = false AND user_id = :user_id OR email = :email";
        $data = $db->fetch($sql, [
            'user_id' => $user_id,
            'email' => $user_id
        ]);
        if (!empty($data)) {
            if (password_verify($password, $data['password'])) {
                return $data['id'];
            }
        }
        return false;
    }

    /** 既にユーザーIDが登録されているか返す（退会済みのユーザーは含まない）
     * @param $user_id
     * @return bool
     */
    public static function already_user_id($user_id) {
        $db = new DatabaseManager();
        $sql = "SELECT count(*) FROM account_user WHERE user_id = :user_id";
        $data = $db->fetchColumn($sql, [
            'user_id' => $user_id
        ]);
        return $data == 0 ? true : false;
    }

    /** 既にユーザーIDが登録されているか返す（退会済みのユーザーも含む）
     * @param $user_id
     * @return bool
     */
    public static function already_user_id_delete($user_id) {
        $db = new DatabaseManager();
        $sql = "SELECT count(*) FROM account_user WHERE user_id = :user_id AND resign_flg = false";
        $data = $db->fetchColumn($sql, [
            'user_id' => $user_id
        ]);
        return $data == 0 ? false : true;
    }

    /** 既にメールアドレスが登録されているか返す
     * @param $email
     * @return bool
     */
    public static function already_email($email) {
        $db = new DatabaseManager();
        $sql = "SELECT count(*) FROM account_user WHERE email = :email";
        $data = $db->fetchColumn($sql, [
            'email' => $email
        ]);
        return $data == 0 ? true : false;
    }

    /** 既にユーザーIDかメールアドレスが登録されているか返す
     * @param $user_id
     * @return bool|int
     */
    public static function already_user_id_or_email($user_id) {
        $db = new DatabaseManager();
        $sql = "SELECT id FROM account_user WHERE user_id = :user_id OR email = :email";
        $id = $db->fetchColumn($sql, [
            'user_id' => $user_id,
            'email' => $user_id
        ]);
        return $id ? $id : false;
    }

    /** パスワードをリセットする
     * @param $user_id
     * @return Mailer
     */
    public static function password_reset($user_id) {
        $db = new DatabaseManager();
        $sql = "SELECT email FROM account_user WHERE id = :id";
        $email = $db->fetchColumn($sql, [
            'id' => $user_id
        ]);
        $password = random(8);
        $sql = "UPDATE account_user SET password = :password WHERE id = :id";
        $db->execute($sql, [
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'id' => $user_id
        ]);

        $subject = 'パスワード再発行';
        $body = <<<EOF
パスワードをリセットしました
仮パスワードを使用してログインを行いパスワードを再設定してください

仮パスワード：$password
EOF;

        return new Mailer($email, $subject, $body);
    }

    /** パスワードを変更する
     * @param $user_id
     * @param $new_password
     * @param $old_password
     * @return bool
     */
    public static function password_change($user_id, $new_password, $old_password) {
        $db = new DatabaseManager();
        $sql = "SELECT password FROM account_user WHERE id = :user_id";
        $now_password = $db->fetchColumn($sql, [
            'user_id' => $user_id
        ]);
        if (password_verify($old_password, $now_password)) {
            $sql = "UPDATE account_user SET password = :password WHERE id = :user_id";
            $db->execute($sql, [
                'password' => password_hash($new_password, PASSWORD_DEFAULT),
                'user_id' => $user_id
            ]);
            return true;
        } else {
            return false;
        }
    }

    /** 退会する
     * @param $user_id
     */
    public static function resign($user_id) {
        $db = new DatabaseManager();
        $sql = "UPDATE account_user SET resign_flg = true WHERE id = :user_id";
        $db->execute($sql, [
            'user_id' => $user_id
        ]);
        TokenManager::delete_user_id_token($user_id);
    }

    /** ユーザー情報を返す
     * @param $user_id
     * @return array
     */
    public static function user_info($user_id) {
        $db = new DatabaseManager();
        $sql = "SELECT user_id, user_name, email, introduction, icon_file_name, line_id FROM account_user WHERE id = :user_id";
        $data = $db->fetch($sql, [
            'user_id' => $user_id
        ]);
        return [
            'user_id' => $data['user_id'],
            'user_name' => $data['user_name'],
            'email' => $data['email'],
            'introduction' => !empty($data['introduction']) ? $data['introduction'] : null,
            'user_icon' => !empty($data['icon_file_name']) ? 'https://storage.googleapis.com/chirusapo/user-icon/'.$data['icon_file_name'] : null,
            'line_id' => !empty($data['line_id']) ? $data['line_id'] : null
        ];
    }

    public static function member_user_info($user_id) {
        $db = new DatabaseManager();
        $sql = "SELECT user_id, user_name, birthday, gender, introduction, icon_file_name, line_id FROM account_user WHERE id = :user_id";
        $data = $db->fetch($sql, [
            'user_id' => $user_id
        ]);
        return [
            'user_id' => $data['user_id'],
            'user_name' => $data['user_name'],
            'birthday' => $data['birthday'],
            'gender' => $data['gender'],
            'introduction' => !empty($data['introduction']) ? $data['introduction'] : null,
            'user_icon' => !empty($data['icon_file_name']) ? 'https://storage.googleapis.com/chirusapo/user-icon/'.$data['icon_file_name'] : null,
            'line_id' => !empty($data['line_id']) ? $data['line_id'] : null
        ];
    }

    public static function update_user_name($user_id, $user_name) {
        $db = new DatabaseManager();
        $sql = "UPDATE account_user SET user_name = :user_name WHERE id = :user_id";
        $db->execute($sql, [
            'user_name' => $user_name,
            'user_id' => $user_id
        ]);
    }

    public static function update_line_id($user_id, $line_id) {
        if (empty($line_id)) {
            self::delete_line_id($user_id);
            return;
        }
        $db = new DatabaseManager();
        $sql = "UPDATE account_user SET line_id = :line_id WHERE id = :user_id";
        $db->execute($sql, [
            'line_id' => $line_id,
            'user_id' => $user_id
        ]);
    }

    public static function delete_line_id($user_id) {
        $db = new DatabaseManager();
        $sql = "UPDATE account_user SET line_id = null WHERE id = :user_id";
        $db->execute($sql, [
            'user_id' => $user_id
        ]);
    }

    public static function update_introduction($user_id, $introduction) {
        if (empty($introduction)) {
            self::delete_introduction($user_id);
            return;
        }
        $db = new DatabaseManager();
        $sql = "UPDATE account_user SET introduction = :introduction WHERE id = :user_id";
        $db->execute($sql, [
            'introduction' => $introduction,
            'user_id' => $user_id
        ]);
    }

    public static function delete_introduction($user_id) {
        $db = new DatabaseManager();
        $sql = "UPDATE account_user SET introduction = null WHERE id = :user_id";
        $db->execute($sql, [
            'user_id' => $user_id
        ]);
    }

    public static function update_user_icon($user_id, $user_icon) {
        $db = new DatabaseManager();
        $sql = "UPDATE account_user SET icon_file_name = :user_icon WHERE id = :user_id";
        $db->execute($sql, [
            'user_icon' => $user_icon,
            'user_id' => $user_id
        ]);
    }

    public static function get_user_id($user_id) {
        if (!self::already_user_id_delete($user_id)) return false;
        $db = new DatabaseManager();
        $sql = "SELECT id FROM account_user WHERE user_id = :user_id";
        $inner_user_id = $db->fetchColumn($sql, [
            'user_id' => $user_id
        ]);
        return $inner_user_id;
    }

    public static function line_cooperation($user_id, $line_id) {
        $db = new DatabaseManager();
        $sql = "UPDATE account_user SET line_token = :line_id WHERE id = :user_id";
        $db->execute($sql, [
            'line_id' => $line_id,
            'user_id' => $user_id
        ]);
    }
}