<?php
namespace Application\App;

use Application\Lib\BotDataSheet;
use Application\lib\DatabaseManager;

class CalendarManager {
    public static function add_calendar($group_id, $user_id, $title, $content, $date, $remind_flg) {
        $db = new DatabaseManager();
        $sql = "INSERT INTO calendar_data (group_id, user_id, title, content, date, year, month, day, remind_flg)
                    VALUES (:group_id, :user_id, :title, :content, :date, :year, :month, :day, :remind_flg)";
        $split_date = preg_split('/-/', $date);
        $calendar_id = $db->insert($sql, [
            'group_id' => $group_id,
            'user_id' => $user_id,
            'title' => $title,
            'content' => $content,
            'date' => $date,
            'year' => $split_date[0],
            'month' => $split_date[1],
            'day' => $split_date[2],
            'remind_flg' => $remind_flg
        ]);
        if ($remind_flg) {
            $search_sql = "SELECT line_token FROM account_user WHERE id = :user_id";
            $line_id = $db->fetchColumn($search_sql, [
                'user_id' => $user_id
            ]);
            if (!empty($line_id)) {
                BotDataSheet::insert($calendar_id, $date, $line_id, $title, $content);
            }
        }
        return $calendar_id;
    }

    public static function list_calendar($group_id) {
        $db = new DatabaseManager();
        $sql = "SELECT ca.id, ac.user_id, ac.user_name, ca.title, ca.content, ca.date, ca.year, ca.month, ca.day, ca.remind_flg
                FROM calendar_data ca
                LEFT JOIN account_user ac ON ac.id = ca.user_id
                WHERE group_id = :group_id
                ORDER BY year DESC, month, day";
        $data = $db->fetchAll($sql, [
            'group_id' => $group_id
        ]);
        $result = [];
        foreach ($data as $item) {
            $result[] = [
                'id' => $item['id'],
                'user_id' => $item['user_id'],
                'user_name' => $item['user_name'],
                'title' => $item['title'],
                'content' => $item['content'],
                'date' => $item['date'],
                'year' => $item['year'],
                'month' => $item['month'],
                'day' => $item['day'],
                'remind_flg' => $item['remind_flg'] ? true : false
            ];
        }
        return $result;
    }

    public static function get_calendar($calendar_id) {
        $db = new DatabaseManager();
        $sql = "SELECT ca.id, ac.user_id, ac.user_name, ca.title, ca.content, ca.date, ca.year, ca.month, ca.day, ca.remind_flg
                FROM calendar_data ca
                LEFT JOIN account_user ac ON ac.id = ca.user_id
                WHERE ca.id = :calendar_id
                ORDER BY year DESC, month, day";
        $data = $db->fetch($sql, [
            'calendar_id' => $calendar_id
        ]);
        return [
            'id' => $data['id'],
            'user_id' => $data['user_id'],
            'user_name' => $data['user_name'],
            'title' => $data['title'],
            'content' => $data['content'],
            'date' => $data['date'],
            'year' => $data['year'],
            'month' => $data['month'],
            'day' => $data['day'],
            'remind_flg' => $data['remind_flg'] ? true : false
        ];
    }

    public static function search_calendar($group_id, $begin_date, $end_date) {
        $db = new DatabaseManager();
        $sql = "SELECT ca.id, ac.user_id, ca.title, ca.content, ca.date, ca.year, ca.month, ca.day, ca.remind_flg
                FROM calendar_data ca
                LEFT JOIN account_user ac ON ac.id = ca.user_id
                WHERE group_id = :group_id
                AND date BETWEEN :begin_date AND :end_date";
        $data = $db->fetchAll($sql, [
            'group_id' => $group_id,
            'begin_date' => $begin_date,
            'end_date' => $end_date
        ]);
        $result = [];
        foreach ($data as $item) {
            $result[$item['year']][$item['month']][$item['day']][] = [
                'id' => $item['id'],
                'user_id' => $item['user_id'],
                'title' => $item['title'],
                'content' => $item['content'],
                'date' => $item['date'],
                'remind_flg' => $item['remind_flg'] ? true : false
            ];
        }
        return $result;
    }

    public static function delete_calendar($calendar_id) {
        $db = new DatabaseManager();
        $sql = "DELETE FROM calendar_data WHERE id = :calendar_id";
        $db->execute($sql, [
            'calendar_id' => $calendar_id
        ]);
    }

    public static function already_calendar_id($calendar_id) {
        $db = new DatabaseManager();
        $sql = "SELECT count(*) FROM calendar_data WHERE id = :calendar_id";
        $count = $db->fetchColumn($sql, [
            'calendar_id' => $calendar_id
        ]);
        return $count == 0 ? false : true;
    }

    public static function calendar_id_to_group_id($calendar_id) {
        if (!self::already_calendar_id($calendar_id)) {
            return false;
        }
        $db = new DatabaseManager();
        $sql = "SELECT group_id FROM calendar_data WHERE id = :calendar_id";
        $group_id = $db->fetchColumn($sql, [
            'calendar_id' => $calendar_id
        ]);
        return $group_id;
    }

    public static function have_calendar_id($calendar_id, $user_id) {
        $db = new DatabaseManager();
        $sql = "SELECT count(*) FROM calendar_data WHERE id = :calendar_id AND user_id = :user_id";
        $count = $db->fetchColumn($sql, [
            'calendar_id' => $calendar_id,
            'user_id' => $user_id
        ]);
        return $count == 0 ? false : true;
    }

    public static function edit_calendar($calendar_id, $title, $content, $date, $remind_flg) {
        $db = new DatabaseManager();
        $sql = "UPDATE calendar_data SET title = :title, content = :content, date = :date, 
                         year = :year, month = :month, day = :day, remind_flg = :remind_flg WHERE id = :calendar_id";
        $split_date = preg_split('/-/', $date);
        $db->execute($sql, [
            'calendar_id' => $calendar_id,
            'title' => $title,
            'content' => $content,
            'date' => $date,
            'year' => $split_date[0],
            'month' => $split_date[1],
            'day' => $split_date[2],
            'remind_flg' => $remind_flg
        ]);
    }
}