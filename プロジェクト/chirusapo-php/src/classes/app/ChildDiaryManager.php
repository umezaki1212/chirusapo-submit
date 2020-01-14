<?php
namespace Application\App;

use Application\lib\DatabaseManager;

class ChildDiaryManager {
    public static function post_diary($child_id, $user_id, $content_type, $text_content, $add_date) {
        $db = new DatabaseManager();
        $sql = "INSERT INTO child_growth_diary (child_id, user_id, content_type, text_content, date)
                    VALUES (:child_id, :user_id, :content_type, :text_content, :add_date)";
        $diary_id = $db->insert($sql, [
            'child_id' => $child_id,
            'user_id' => $user_id,
            'content_type' => $content_type,
            'text_content' => $text_content,
            'add_date' => $add_date
        ]);
        return $diary_id;
    }

    public static function post_diary_image($diary_id, $order_id, $file_name) {
        $db = new DatabaseManager();
        $sql = "INSERT INTO child_growth_diary_image (diary_id, order_id, file_name)
                    VALUES (:diary_id, :order_id, :file_name)";
        $db->insert($sql, [
            'diary_id' => $diary_id,
            'order_id' => $order_id,
            'file_name' => $file_name
        ]);
    }

    public static function post_diary_movie($diary_id, $thumbnail_name, $file_name) {
        $db = new DatabaseManager();
        $sql = "INSERT INTO child_growth_diary_movie (diary_id, file_thumbnail, file_name)
                    VALUES (:diary_id, :file_thumbnail, :file_name)";
        $db->insert($sql, [
            'diary_id' => $diary_id,
            'file_thumbnail' => $thumbnail_name,
            'file_name' => $file_name
        ]);
    }

    public static function list_diary($child_id) {
        $db = new DatabaseManager();
        $sql = "SELECT id, user_id, user_name, icon_file_name, content_type,
                        content, image01, image02, image03, image04,
                        movie01_thumbnail, movie01_content, post_time
                FROM view_child_growth_diary
                WHERE child_id = :child_id";
        $data = $db->fetchAll($sql, [
            'child_id' => $child_id
        ]);
        foreach ($data as $key => $value) {
            $data[$key] = self::switch_content_type($value);
        }
        return $data;
    }

    public static function delete_diary($diary_id) {
        $db = new DatabaseManager();
        $sql = "UPDATE child_growth_diary SET delete_flg = true WHERE id = :diary_id";
        $db->execute($sql, [
            'diary_id' => $diary_id
        ]);
    }

    public static function have_diary_id($diary_id) {
        $db = new DatabaseManager();
        $sql = "SELECT count(*) FROM child_growth_diary WHERE id = :diary_id AND delete_flg = false";
        $count = $db->fetchColumn($sql, [
            'diary_id' => $diary_id
        ]);
        return $count == 0 ? false : true;
    }

    public static function have_comment_id($comment_id) {
        $db = new DatabaseManager();
        $sql = "SELECT count(*) FROM child_growth_diary_comment WHERE id = :comment_id";
        $count = $db->fetchColumn($sql, [
            'comment_id' => $comment_id
        ]);
        return $count == 0 ? false : true;
    }

    public static function have_diary_id_from_user_id($diary_id, $user_id) {
        $db = new DatabaseManager();
        $sql = "SELECT count(*) FROM child_growth_diary WHERE id = :diary_id AND user_id = :user_id";
        $count = $db->fetchColumn($sql, [
            'diary_id' => $diary_id,
            'user_id' => $user_id
        ]);
        return $count == 0 ? false : true;
    }

    public static function get_diary($diary_id) {
        $db = new DatabaseManager();
        $sql = "SELECT id, user_id, user_name, icon_file_name, content_type,
                        content, image01, image02, image03, image04,
                        movie01_thumbnail, movie01_content, post_time
                FROM view_child_growth_diary
                WHERE id = :diary_id";
        $data = $db->fetch($sql, [
            'diary_id' => $diary_id
        ]);
        return self::switch_content_type($data);
    }

    private static function switch_content_type($data) {
        $result = [];
        switch ($data['content_type']) {
            case 'text':
                $result = [
                    'id' => $data['id'],
                    'user_id' => $data['user_id'],
                    'user_name' => $data['user_name'],
                    'user_icon' => !empty($data['icon_file_name']) ? 'https://storage.googleapis.com/chirusapo/user-icon/'.$data['icon_file_name'] : null,
                    'content_type' => $data['content_type'],
                    'text' => $data['content'],
                    'post_time' => $data['post_time']
                ];
                break;
            case 'text_image':
                $result = [
                    'id' => $data['id'],
                    'user_id' => $data['user_id'],
                    'user_name' => $data['user_name'],
                    'user_icon' => !empty($data['icon_file_name']) ? 'https://storage.googleapis.com/chirusapo/user-icon/'.$data['icon_file_name'] : null,
                    'content_type' => $data['content_type'],
                    'text' => $data['content'],
                    'image01' => !empty($data['image01']) ? 'https://storage.googleapis.com/chirusapo/growth/diary/image/'.$data['image01'] : null,
                    'image02' => !empty($data['image02']) ? 'https://storage.googleapis.com/chirusapo/growth/diary/image/'.$data['image02'] : null,
                    'image03' => !empty($data['image03']) ? 'https://storage.googleapis.com/chirusapo/growth/diary/image/'.$data['image03'] : null,
                    'image04' => !empty($data['image04']) ? 'https://storage.googleapis.com/chirusapo/growth/diary/image/'.$data['image04'] : null,
                    'post_time' => $data['post_time']
                ];
                break;
            case 'text_movie':
                $result = [
                    'id' => $data['id'],
                    'user_id' => $data['user_id'],
                    'user_name' => $data['user_name'],
                    'user_icon' => !empty($data['icon_file_name']) ? 'https://storage.googleapis.com/chirusapo/user-icon/'.$data['icon_file_name'] : null,
                    'content_type' => $data['content_type'],
                    'text' => $data['content'],
                    'movie01_thumbnail' => !empty($data['movie01_thumbnail']) ? 'https://storage.googleapis.com/chirusapo/growth/diary/movie/thumbnail/'.$data['movie01_thumbnail'] : null,
                    'movie01_content' => !empty($data['movie01_content']) ? 'https://storage.googleapis.com/chirusapo/growth/diary/movie/content/'.$data['movie01_content'] : null,
                    'post_time' => $data['post_time']
                ];
                break;
        }
        return $result;
    }

    public static function get_comment($diary_id) {
        $db = new DatabaseManager();
        $sql = "SELECT cgdc.id, au.user_id, au.user_name, au.icon_file_name, cgdc.comment, cgdc.post_time
                FROM child_growth_diary_comment cgdc 
                INNER JOIN account_user au ON au.id = cgdc.user_id
                WHERE cgdc.diary_id = :diary_id";
        $data = $db->fetchAll($sql, [
            'diary_id' => $diary_id
        ]);
        foreach ($data as $key => $value) {
            $data[$key]['user_icon'] = !empty($comment['icon_file_name']) ? 'https://storage.googleapis.com/chirusapo/user-icon/'.$data['icon_file_name'] : null;
        }
        return $data;
    }

    public static function post_comment($diary_id, $user_id, $comment) {
        $db = new DatabaseManager();
        $sql = "INSERT INTO child_growth_diary_comment (diary_id, user_id, comment, post_time) 
                VALUES (:diary_id, :user_id, :comment, :post_time)";
        $comment_id = $db->insert($sql, [
            'diary_id' => $diary_id,
            'user_id' => $user_id,
            'comment' => $comment,
            'post_time' => date('Y-m-d H:i:s')
        ]);
        return $comment_id;
    }

    public static function delete_comment($comment_id) {
        $db = new DatabaseManager();
        $sql = "DELETE FROM child_growth_diary_comment WHERE id = :comment_id";
        $db->execute($sql, [
            'comment_id' => $comment_id
        ]);
    }

    public static function have_post_comment_permission($diary_id, $user_id) {
        $db = new DatabaseManager();
        $sql = "SELECT if(count(*) = 0, false, true) result
                FROM group_user
                WHERE user_id = :user_id
                  AND group_id IN (
                    SELECT group_id
                    FROM account_child
                    WHERE id IN (
                        SELECT child_id
                        FROM child_growth_diary
                        WHERE id = :diary_id
                    )
                )";
        $result = $db->fetchColumn($sql, [
            'user_id' => $user_id,
            'diary_id' => $diary_id
        ]);
        return boolval($result);
    }

    public static function have_delete_comment_permission($comment_id, $user_id) {
        $db = new DatabaseManager();
        $sql = "SELECT * FROM child_growth_diary_comment
                WHERE id = :comment_id AND user_id = :user_id";
        $result = $db->fetchColumn($sql, [
            'comment_id' => $comment_id,
            'user_id' => $user_id
        ]);
        return boolval($result);
    }
}