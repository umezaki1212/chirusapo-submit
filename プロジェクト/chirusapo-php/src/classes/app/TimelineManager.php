<?php
namespace Application\app;

use Application\lib\DatabaseManager;

class TimelineManager {
    public static function get_timeline($group_id) {
        $db = new DatabaseManager();
        $sql = "SELECT vgt.id, vgt.group_id, vgt.user_id, vgt.user_name, vgt.icon_file_name,
                       vgt.content_type, vgt.content, vgt.image01, vgt.image02, vgt.image03, vgt.image04,
                       vgt.movie01_thumbnail, vgt.movie01_content, vgt.post_time
                FROM view_group_timeline vgt
                INNER JOIN group_user gu
                ON gu.group_id = vgt.group_id
                AND gu.user_id = vgt.inner_user_id
                WHERE vgt.group_id = :group_id
                ORDER BY vgt.id DESC";
        $data = $db->fetchAll($sql, [
            'group_id' => $group_id
        ]);
        $result = [];
        foreach ($data as $post) {
            $result[] = self::switch_content_type($post);
        }
        return $result;
    }

    public static function get_post($timeline_id) {
        $db = new DatabaseManager();
        $sql = "SELECT vgt.id, vgt.user_id, vgt.user_name, vgt.icon_file_name,
                       vgt.content_type, vgt.content, vgt.image01, vgt.image02, vgt.image03, vgt.image04,
                       vgt.movie01_thumbnail, vgt.movie01_content, vgt.post_time
                FROM view_group_timeline vgt
                WHERE id = :timeline_id";
        $data = $db->fetch($sql, [
            'timeline_id' => $timeline_id
        ]);
        return self::switch_content_type($data);
    }

    public static function delete_timeline($timeline_id) {
        $db = new DatabaseManager();
        $sql = "UPDATE group_timeline SET delete_flg = true WHERE id = :timeline_id";
        $db->execute($sql, [
            'timeline_id' => $timeline_id
        ]);
    }

    public static function post_timeline($group_id, $user_id, $content_type, $date) {
        $db = new DatabaseManager();
        $sql = "INSERT INTO group_timeline (group_id, user_id, content_type, post_time) VALUES (:group_id, :user_id, :content_type, :post_time)";
        $timeline_id = $db->insert($sql, [
            'group_id' => $group_id,
            'user_id' => $user_id,
            'content_type' => $content_type,
            'post_time' => $date
                // date('Y-m-d H:i:s')
        ]);
        return $timeline_id;
    }

    public static function post_timeline_text($timeline_id, $content) {
        $db = new DatabaseManager();
        $sql = "INSERT INTO group_timeline_text (timeline_id, content) VALUES (:timeline_id, :content)";
        $db->insert($sql, [
            'timeline_id' => $timeline_id,
            'content' => $content
        ]);
    }

    public static function post_timeline_image($timeline_id, $order_id, $file_name) {
        $db = new DatabaseManager();
        $sql = "INSERT INTO group_timeline_image (timeline_id, order_id, file_name) VALUES (:timeline_id, :order_id, :file_name)";
        $db->insert($sql, [
            'timeline_id' => $timeline_id,
            'order_id' => $order_id,
            'file_name' => $file_name
        ]);
    }

    public static function post_timeline_movie($timeline_id, $file_thumbnail, $file_name) {
        $db = new DatabaseManager();
        $sql = "INSERT INTO group_timeline_movie (timeline_id, file_thumbnail, file_name) VALUES (:timeline_id, :file_thumbnail, :file_name)";
        $db->insert($sql, [
            'timeline_id' => $timeline_id,
            'file_thumbnail' => $file_thumbnail,
            'file_name' => $file_name
        ]);
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
                    'image01' => !empty($data['image01']) ? 'https://storage.googleapis.com/chirusapo/timeline/image/'.$data['image01'] : null,
                    'image02' => !empty($data['image02']) ? 'https://storage.googleapis.com/chirusapo/timeline/image/'.$data['image02'] : null,
                    'image03' => !empty($data['image03']) ? 'https://storage.googleapis.com/chirusapo/timeline/image/'.$data['image03'] : null,
                    'image04' => !empty($data['image04']) ? 'https://storage.googleapis.com/chirusapo/timeline/image/'.$data['image04'] : null,
                    'post_time' => $data['post_time']
                ];
                break;
            case 'image':
                $result = [
                    'id' => $data['id'],
                    'user_id' => $data['user_id'],
                    'user_name' => $data['user_name'],
                    'user_icon' => !empty($data['icon_file_name']) ? 'https://storage.googleapis.com/chirusapo/user-icon/'.$data['icon_file_name'] : null,
                    'content_type' => $data['content_type'],
                    'image01' => !empty($data['image01']) ? 'https://storage.googleapis.com/chirusapo/timeline/image/'.$data['image01'] : null,
                    'image02' => !empty($data['image02']) ? 'https://storage.googleapis.com/chirusapo/timeline/image/'.$data['image02'] : null,
                    'image03' => !empty($data['image03']) ? 'https://storage.googleapis.com/chirusapo/timeline/image/'.$data['image03'] : null,
                    'image04' => !empty($data['image04']) ? 'https://storage.googleapis.com/chirusapo/timeline/image/'.$data['image04'] : null,
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
                    'movie01_thumbnail' => !empty($data['movie01_thumbnail']) ? 'https://storage.googleapis.com/chirusapo/timeline/movie/thumbnail/'.$data['movie01_thumbnail'] : null,
                    'movie01_content' => !empty($data['movie01_content']) ? 'https://storage.googleapis.com/chirusapo/timeline/movie/content/'.$data['movie01_content'] : null,
                    'post_time' => $data['post_time']
                ];
                break;
            case 'movie':
                $result = [
                    'id' => $data['id'],
                    'user_id' => $data['user_id'],
                    'user_name' => $data['user_name'],
                    'user_icon' => !empty($data['icon_file_name']) ? 'https://storage.googleapis.com/chirusapo/user-icon/'.$data['icon_file_name'] : null,
                    'content_type' => $data['content_type'],
                    'movie01_thumbnail' => !empty($data['movie01_thumbnail']) ? 'https://storage.googleapis.com/chirusapo/timeline/movie/thumbnail/'.$data['movie01_thumbnail'] : null,
                    'movie01_content' => !empty($data['movie01_content']) ? 'https://storage.googleapis.com/chirusapo/timeline/movie/content/'.$data['movie01_content'] : null,
                    'post_time' => $data['post_time']
                ];
                break;
        }
        return $result;
    }

    public static function post_comment($timeline_id, $user_id, $comment) {
        $db = new DatabaseManager();
        $sql = "INSERT INTO group_timeline_comment (timeline_id, user_id, comment, post_time) VALUES (:timeline_id, :user_id, :comment, :post_time)";
        $comment_id = $db->insert($sql, [
            'timeline_id' => $timeline_id,
            'user_id' => $user_id,
            'comment' => $comment,
            'post_time' => date('Y-m-d H:i:s')
        ]);
        return $comment_id;
    }

    public static function delete_comment($comment_id) {
        $db = new DatabaseManager();
        $sql = "DELETE FROM group_timeline_comment WHERE id = :comment_id";
        $db->execute($sql, [
            'comment_id' => $comment_id
        ]);
    }

    public static function have_user_id_comment($comment_id, $user_id) {
        $db = new DatabaseManager();
        $sql = "SELECT count(*) FROM group_timeline_comment WHERE id = :comment_id AND user_id = :user_id";
        $count = $db->fetchColumn($sql, [
            'comment_id' => $comment_id,
            'user_id' => $user_id
        ]);
        return $count == 0 ? false : true;
    }

    public static function have_user_id_timeline($timeline_id, $user_id) {
        $db = new DatabaseManager();
        $sql = "SELECT count(*) FROM group_timeline WHERE id = :timeline_id AND user_id = :user_id";
        $count = $db->fetchColumn($sql, [
            'timeline_id' => $timeline_id,
            'user_id' => $user_id
        ]);
        return $count == 0 ? false : true;
    }

    public static function get_comment($timeline_id) {
        $db = new DatabaseManager();
        $sql = "SELECT gtc.id, au.user_id, au.user_name, au.icon_file_name, gtc.comment, gtc.post_time
                FROM group_timeline_comment gtc
                INNER JOIN account_user au ON au.id = gtc.user_id
                WHERE gtc.timeline_id = :timeline_id";
        $data = $db->fetchAll($sql, [
            'timeline_id' => $timeline_id
        ]);
        $result = [];
        foreach ($data as $comment) {
            $result[] = [
                'id' => $comment['id'],
                'user_id' => $comment['user_id'],
                'user_name' => $comment['user_name'],
                'user_icon' => !empty($comment['icon_file_name']) ? 'https://storage.googleapis.com/chirusapo/user-icon/'.$data['icon_file_name'] : null,
                'comment' => $comment['comment'],
                'post_time' => $comment['post_time']
            ];
        }
        return $result;
    }

    public static function get_timeline_group_id($timeline_id) {
        $db = new DatabaseManager();
        $sql = "SELECT group_id FROM group_timeline WHERE id = :timeline_id AND delete_flg = false";
        $group_id = $db->fetchColumn($sql, [
            'timeline_id' => $timeline_id
        ]);
        return !empty($group_id) ? $group_id : false;
    }

    public static function get_comment_timeline_id($comment_id) {
        $db = new DatabaseManager();
        $sql = "SELECT timeline_id FROM group_timeline_comment WHERE id = :comment_id";
        $timeline_id = $db->fetchColumn($sql, [
            'comment_id' => $comment_id
        ]);
        return !empty($timeline_id) ? $timeline_id : false;
    }
}