<?php
namespace Application\App;

use Application\lib\DatabaseManager;

class ChildManager {
    /** 子ども情報追加
     * @param $group_id
     * @param $user_id
     * @param $user_name
     * @param $birthday
     * @param $age
     * @param $gender
     * @param $blood_type
     * @return string
     */
    public static function add_child($group_id, $user_id, $user_name, $birthday, $age, $gender, $blood_type) {
        $db = new DatabaseManager();
        $sql = "INSERT INTO account_child (group_id, user_id, user_name, birthday, age, gender, blood_type) VALUES (:group_id, :user_id, :user_name, :birthday, :age, :gender, :blood_type)";
        $child_id = $db->insert($sql, [
            'group_id' => $group_id,
            'user_id' => $user_id,
            'user_name' => $user_name,
            'birthday' => $birthday,
            'age' => $age,
            'gender' => $gender,
            'blood_type' => $blood_type
        ]);
        return $child_id;
    }

    /** 予防接種追加
     * @param $child_id
     * @param $vaccine_name
     * @param $visit_date
     */
    public static function add_vaccination($child_id, $vaccine_name, $visit_date) {
        $db = new DatabaseManager();
        $sql = "INSERT INTO child_vaccination (child_id, vaccine_name, visit_date, add_date) VALUES (:child_id, :vaccine_name, :visit_date, :add_date)";
        $db->insert($sql, [
            'child_id' => $child_id,
            'vaccine_name' => $vaccine_name,
            'visit_date' => $visit_date,
            'add_date' => date('Y-m-d')
        ]);
    }

    public static function delete_vaccination($vaccination_id) {
        $db = new DatabaseManager();
        $sql = "DELETE FROM child_vaccination WHERE id = :vaccination_id";
        $db->execute($sql, [
            'vaccination_id' => $vaccination_id
        ]);
    }

    /** アレルギー追加
     * @param $child_id
     * @param $allergy_name
     */
    public static function add_allergy($child_id, $allergy_name) {
        $db = new DatabaseManager();
        $sql = "INSERT INTO child_allergy (child_id, allergy_name, add_date) VALUES (:child_id, :allergy_name, :add_date)";
        $db->insert($sql, [
            'child_id' => $child_id,
            'allergy_name' => $allergy_name,
            'add_date' => date('Y-m-d')
        ]);
    }

    public static function delete_allergy($allergy_id) {
        $db = new DatabaseManager();
        $sql = "DELETE FROM child_allergy WHERE id = :allergy_id";
        $db->execute($sql, [
            'allergy_id' => $allergy_id
        ]);
    }

    /** 成長記録追加
     * @param $child_id
     * @param $body_height
     * @param $body_weight
     * @param $clothes_size
     * @param $shoes_size
     * @param $add_date
     * @return string
     */
    public static function add_growth_history($child_id, $body_height, $body_weight, $clothes_size, $shoes_size, $add_date) {
        $db = new DatabaseManager();
        $sql = "INSERT INTO child_growth_history (child_id, body_height, body_weight, clothes_size, shoes_size, add_date) VALUES (:child_id, :body_height, :body_weight, :clothes_size, :shoes_size, :add_date)";
        $history_id = $db->insert($sql, [
            'child_id' => $child_id,
            'body_height' => $body_height,
            'body_weight' => $body_weight,
            'clothes_size' => $clothes_size,
            'shoes_size' => $shoes_size,
            'add_date' => $add_date
        ]);
        return $history_id;
    }

    /** 子ども一覧取得
     * @param $group_id
     * @return array
     */
    public static function get_child_list($group_id) {
        $db = new DatabaseManager();
        $sql = "SELECT
                       ac.id, ac.user_id, ac.user_name, ac.birthday, ac.age, ac.gender, ac.blood_type,
                       cgh.body_height, cgh.body_weight, cgh.clothes_size, cgh.shoes_size
                FROM account_child ac
                LEFT JOIN (
                    SELECT *
                    FROM child_growth_history as t1
                    WHERE add_date IN (
                        SELECT MAX(add_date)
                        FROM child_growth_history as t2
                        WHERE t1.child_id = t2.child_id
                        GROUP BY child_id
                    )
                ) cgh
                ON ac.id = cgh.child_id
                WHERE ac.group_id = :group_id
                AND ac.delete_flg = false";
        $data = $db->fetchAll($sql, [
            'group_id' => $group_id
        ]);
        foreach ($data as $key => $value) {
            $sql = "SELECT id, vaccine_name, visit_date FROM child_vaccination WHERE child_id = :child_id";
            $vaccination_list = $db->fetchAll($sql, [
                'child_id' => $value['id']
            ]);
            $data[$key]['vaccination'] = $vaccination_list;

            $sql = "SELECT id, allergy_name FROM child_allergy WHERE child_id = :child_id";
            $allergy_list = $db->fetchAll($sql, [
                'child_id' => $value['id']
            ]);
            $data[$key]['allergy'] = $allergy_list;

            unset($data[$key]['id']);
        }
        return $data;
    }

    public static function get_child($child_id) {
        $db = new DatabaseManager();
        $sql = "SELECT
                       ac.id, ac.user_id, ac.user_name, ac.birthday, ac.age, ac.gender, ac.blood_type,
                       cgh.body_height, cgh.body_weight, cgh.clothes_size, cgh.shoes_size
                FROM account_child ac
                LEFT JOIN (
                    SELECT *
                    FROM child_growth_history as t1
                    WHERE add_date IN (
                        SELECT MAX(add_date)
                        FROM child_growth_history as t2
                        WHERE t1.child_id = t2.child_id
                        GROUP BY child_id
                    )
                ) cgh
                ON ac.id = cgh.child_id
                WHERE ac.id = :child_id
                AND ac.delete_flg = false";
        $data = $db->fetch($sql, [
            'child_id' => $child_id
        ]);

        $sql = "SELECT id, vaccine_name, visit_date FROM child_vaccination WHERE child_id = :child_id";
        $vaccination_list = $db->fetchAll($sql, [
            'child_id' => $data['id']
        ]);
        $data['vaccination'] = $vaccination_list;

        $sql = "SELECT id, allergy_name FROM child_allergy WHERE child_id = :child_id";
        $allergy_list = $db->fetchAll($sql, [
            'child_id' => $data['id']
        ]);
        $data['allergy'] = $allergy_list;

        unset($data['id']);

        return $data;
    }

    public static function delete_child($child_id) {
        $db = new DatabaseManager();
        $sql = "UPDATE account_child SET delete_flg = true WHERE id = :child_id";
        $db->execute($sql, [
            'child_id' => $child_id
        ]);
    }

    /** 子どもIDが存在しているか返す
     * true -> 存在する
     * false -> 存在しない
     * @param $user_id
     * @return bool
     */
    public static function already_user_id($user_id) {
        $db = new DatabaseManager();
        $sql = "SELECT count(*) FROM account_child WHERE user_id = :user_id";
        $count = $db->fetchColumn($sql, [
            'user_id' => $user_id
        ]);
        return $count == 0 ? false : true;
    }

    /** 子どもIDが存在しているか返す・削除済みの場合はfalse
     * @param $user_id
     * @return bool
     */
    public static function already_delete_user_id($user_id) {
        $db = new DatabaseManager();
        $sql = "SELECT count(*) FROM account_child WHERE user_id = :user_id AND delete_flg = false;";
        $count = $db->fetchColumn($sql, [
            'user_id' => $user_id
        ]);
        return $count == 0 ? false : true;
    }

    /** 子どもIDからグループIDを返す
     * @param $child_id
     * @return bool|int
     */
    public static function child_id_to_group_id($child_id) {
        if (!self::already_delete_user_id($child_id)) return false;
        $db = new DatabaseManager();
        $sql = "SELECT group_id FROM account_child WHERE user_id = :user_id";
        $id = $db->fetchColumn($sql, [
            'user_id' => $child_id
        ]);
        return $id;
    }

    public static function child_id_to_inner_child_id($child_id) {
        if (!self::already_delete_user_id($child_id)) return false;
        $db = new DatabaseManager();
        $sql = "SELECT id FROM account_child WHERE user_id = :user_id";
        $id = $db->fetchColumn($sql, [
            'user_id' => $child_id
        ]);
        return $id;
    }

    /** 子どもIDがグループIDのものか調べる
     * @param $child_id
     * @param $group_id
     * @return bool
     */
    public static function child_id_have_group($child_id, $group_id) {
        $db = new DatabaseManager();
        $sql = "SELECT count(*) FROM account_child WHERE user_id = :child_id AND group_id = :group_id";
        $count = $db->fetchColumn($sql, [
            'child_id' => $child_id,
            'group_id' => $group_id
        ]);
        return $count == 0 ? false : true;
    }

    /** 予防接種IDが子どもIDのものか返す
     * @param $user_id
     * @param $vaccination_id
     * @return bool
     */
    public static function vaccination_id_have_child($user_id, $vaccination_id) {
        $db = new DatabaseManager();
        $sql = "SELECT count(*) FROM child_vaccination WHERE id = :vaccination_id AND child_id = :user_id";
        $count = $db->fetchColumn($sql, [
            'vaccination_id' => $vaccination_id,
            'user_id' => $user_id
        ]);
        return $count == 0 ? false : true;
    }

    /** アレルギーIDが子どもIDのものか返す
     * @param $user_id
     * @param $allergy_id
     * @return bool
     */
    public static function allergy_id_have_child($user_id, $allergy_id) {
        $db = new DatabaseManager();
        $sql = "SELECT count(*) FROM child_allergy WHERE id = :allergy_id AND child_id = :user_id";
        $count = $db->fetchColumn($sql, [
            'allergy_id' => $allergy_id,
            'user_id' => $user_id
        ]);
        return $count == 0 ? false : true;
    }

    public static function get_growth_history($child_id) {
        $db = new DatabaseManager();
        $sql = "SELECT body_height, body_weight, clothes_size, shoes_size, add_date
                FROM child_growth_history cgh
                LEFT JOIN account_child ac ON ac.id = cgh.child_id
                WHERE cgh.child_id = :child_id
                ORDER BY add_date DESC";
        $data = $db->fetchAll($sql, [
            'child_id' => $child_id
        ]);
        $result = [];
        foreach ($data as $value) {
            $result[$value['add_date']] = [
                'body_height' => $value['body_height'],
                'body_weight' => $value['body_weight'],
                'clothes_size' => $value['clothes_size'],
                'shoes_size' => $value['shoes_size']
            ];
        }
        return $result;
    }

    public static function list_growth_history($group_id) {
        $db = new DatabaseManager();
        $sql = "SELECT ac.user_id, body_height, body_weight, clothes_size, shoes_size, add_date
                FROM child_growth_history cgh
                LEFT JOIN account_child ac ON ac.id = cgh.child_id
                WHERE ac.group_id = :group_id
                ORDER BY ac.id, add_date DESC";
        $data = $db->fetchAll($sql, [
            'group_id' => $group_id
        ]);
        $result = [];
        foreach ($data as $value) {
            $result[$value['user_id']][$value['add_date']] = [
                'body_height' => $value['body_height'],
                'body_weight' => $value['body_weight'],
                'clothes_size' => $value['clothes_size'],
                'shoes_size' => $value['shoes_size']
            ];
        }
        return $result;
    }

    public static function have_child_list($user_id) {
        $db = new DatabaseManager();
        $sql = "SELECT user_id, user_name, age
                FROM account_child ac
                WHERE ac.group_id IN (SELECT group_id FROM group_user WHERE user_id = :user_id)
                AND ac.delete_flg = false";
        $data = $db->fetchAll($sql, [
            'user_id' => $user_id
        ]);
        return $data;
    }

    public static function already_record_growth_history($child_id, $add_date) {
        $db = new DatabaseManager();
        $sql = "SELECT count(*) FROM child_growth_history
                WHERE child_id = :child_id AND add_date = :add_date";
        $result = $db->fetchColumn($sql, [
            'child_id' => $child_id,
            'add_date' => $add_date
        ]);
        return $result == 0 ? true : false;
    }

    /** 子どものグループIDに所属しているか判定
     * @param $user_id
     * @param $child_id
     * @return bool
     */
    public static function have_child($user_id, $child_id) {
        $db = new DatabaseManager();
        $sql = "SELECT count(*)
                FROM group_user
                WHERE user_id = :user_id
                AND group_id = (SELECT group_id FROM account_child WHERE id = :child_id)";
        $result = $db->fetchColumn($sql, [
            'user_id' => $user_id,
            'child_id' => $child_id
        ]);
        return $result == 0 ? false : true;
    }
}