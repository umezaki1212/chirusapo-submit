<?php
namespace Application\lib;

class Validation {
    // Account
    public static $USER_ID = 'user_id';
    public static $USER_NAME = 'user_name';
    public static $EMAIL = 'email';
    public static $PASSWORD = 'password';
    public static $BIRTHDAY = 'birthday';
    public static $GENDER = 'gender';
    public static $USER_ID_OR_EMAIL = 'user_id_or_email';
    public static $LINE_ID = 'line_id';
    public static $INTRODUCTION = 'introduction';
    // Group
    public static $GROUP_ID = 'group_id';
    public static $GROUP_NAME = 'group_name';
    public static $PIN_CODE = 'pin_code';
    // Timeline
    public static $TIMELINE_POST_CONTENT = 'timeline_post_content';
    public static $TIMELINE_POST_COMMENT = 'timeline_post_comment';

    public static $BODY_HEIGHT = 'body_height';
    public static $BODY_WEIGHT = 'body_weight';
    public static $AGE = 'age';
    public static $BLOOD_TYPE = 'blood_type';
    public static $CLOTHES_SIZE = 'clothes_size';
    public static $SHOES_SIZE = 'shoes_size';
    public static $VACCINATION = 'vaccination';
    public static $ALLERGY = 'allergy';
    public static $DATE = 'date';

    public static $CALENDAR_TITLE = 'calendar_title';
    public static $CALENDAR_CONTENT = 'calendar_content';
    public static $CALENDAR_REMIND_FLG = 'calendar_remind_flg';
    public static $DIARY_POST_CONTENT = 'diary_post_content';
    public static $DIARY_POST_COMMENT = 'diary_post_comment';

    public static $FRIEND_MEMO = 'friend_memo';

    public static function fire($value, string $rule) {
        $regex = '';
        switch ($rule) {
            case self::$USER_ID:
                $regex = '/^[a-zA-Z0-9-_]{4,30}$/';
                break;
            case self::$USER_NAME:
                $regex = '/^.{2,30}$/';
                break;
            case self::$EMAIL:
                $regex = '/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/';
                break;
            case self::$GROUP_ID:
            case self::$PASSWORD:
                $regex = '/^[a-zA-Z0-9-_]{5,30}$/';
                break;
            case self::$BIRTHDAY:
            case self::$DATE:
                $regex = '/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/';
                break;
            case self::$GENDER:
                $regex = '/^[0-2]$/';
                break;
            case self::$USER_ID_OR_EMAIL:
                $regex = '/^[a-zA-Z0-9-_.@+]{4,}$/';
                break;
            case self::$LINE_ID:
                $regex = '/^[a-zA-Z0-9-_.]{4,20}$/';
                break;
            case self::$INTRODUCTION:
                return mb_strlen($value) <= 250 ? true : false;
                break;
            case self::$CALENDAR_TITLE:
            case self::$GROUP_NAME:
                $regex = '/^.{1,30}$/';
                break;
            case self::$PIN_CODE:
                $regex = '/^[0-9]{4}$/';
                break;
            case self::$TIMELINE_POST_CONTENT:
            case self::$TIMELINE_POST_COMMENT:
                $regex = '/^.{1,250}$/';
                break;
            case self::$BODY_HEIGHT:
                $regex = '/^[1-9][0-9]|1[0-9]{2}|200$/';
                break;
            case self::$BODY_WEIGHT:
                $regex = '/^([1-9]|[1-9][0-9]|1[0-4][0-9]|150)(\.[0-9]+)?$/';
                break;
            case self::$AGE:
                $regex = '/^[0-9]|[1-9][0-9]$/';
                break;
            case self::$BLOOD_TYPE:
                $regex = '/^[0-4]$/';
                break;
            case self::$CLOTHES_SIZE:
                $regex = '/^[5-9][0-9]|1[0-5][0-9]|160$/';
                break;
            case self::$SHOES_SIZE:
                $regex = '/^([5-9]|[1-2][0-9]|30)(\.[0-9]+)?$/';
                break;
            case self::$VACCINATION:
            case self::$ALLERGY:
                $regex = '/^.{1,100}$/';
                break;
            case self::$CALENDAR_CONTENT:
            case self::$FRIEND_MEMO:
                return mb_strlen($value) <= 200 ? true : false;
                break;
            case self::$CALENDAR_REMIND_FLG:
                $regex = '/^[0-1]$/';
                break;
            case self::$DIARY_POST_CONTENT:
            case self::$DIARY_POST_COMMENT:
                return mb_strlen($value) <= 250 ? true : false;
                break;
        }

        return (preg_match($regex, $value)) ? true : false;
    }
}