<?php
namespace Application\Controllers;

use Application\App\CalendarManager;
use Application\app\GroupManager;
use Application\app\TokenManager;
use Application\lib\Error;
use Application\lib\Validation;
use Slim\Http\Request;
use Slim\Http\Response;

class CalendarController {
    public static function get_calendar(Request $request, Response $response) {
        $param = array_escape($request->getQueryParams());

        $token = isset($param['token']) ? $param['token'] : null;
        $group_id = isset($param['group_id']) ? $param['group_id'] : null;

        $error = [];

        if (is_nulls($token, $group_id)) {
            $result = [
                'status' => 400,
                'message' => [
                    Error::$REQUIRED_PARAM
                ],
                'data' => null
            ];
        } else {
            $user_id = TokenManager::get_user_id($token);
            $inner_group_id = GroupManager::get_group_id($group_id);

            if (!$user_id || !$inner_group_id) {
                if (!$user_id) $error[] =  Error::$UNKNOWN_TOKEN;
                if (!$inner_group_id) $error[] = Error::$UNKNOWN_GROUP;

                $result = [
                    'status' => 400,
                    'message' => $error,
                    'data' => null
                ];
            } else {
                $belong_group = GroupManager::already_belong_group($inner_group_id, $user_id);

                if (!$belong_group) {
                    $result = [
                        'status' => 400,
                        'message' => [
                            Error::$UNREADY_BELONG_GROUP
                        ],
                        'data' => null
                    ];
                } else {
                    $calendar_list = CalendarManager::list_calendar($inner_group_id);

                    $result = [
                        'status' => 200,
                        'message' => null,
                        'data' => [
                            'calendar_list' => $calendar_list
                        ]
                    ];
                }
            }
        }

        return $response->withJson($result);
    }

    public static function search_calendar(Request $request, Response $response) {
        $param = array_escape($request->getQueryParams());

        $token = isset($param['token']) ? $param['token'] : null;
        $group_id = isset($param['group_id']) ? $param['group_id'] : null;
        $begin_date  = isset($param['begin_date']) ? $param['begin_date'] : null;
        $end_date = isset($param['end_date']) ? $param['end_date'] : null;

        $error = [];

        if (is_nulls($token, $group_id, $begin_date, $end_date)) {
            $result = [
                'status' => 400,
                'message' => [
                    Error::$REQUIRED_PARAM
                ],
                'data' => null
            ];
        } else {
            $user_id = TokenManager::get_user_id($token);
            $inner_group_id = GroupManager::get_group_id($group_id);

            if (!$user_id || !$inner_group_id) {
                if (!$user_id) $error[] =  Error::$UNKNOWN_TOKEN;
                if (!$inner_group_id) $error[] = Error::$UNKNOWN_GROUP;

                $result = [
                    'status' => 400,
                    'message' => $error,
                    'data' => null
                ];
            } else {
                $belong_group = GroupManager::already_belong_group($inner_group_id, $user_id);

                if (!$belong_group) {
                    $result = [
                        'status' => 400,
                        'message' => [
                            Error::$UNREADY_BELONG_GROUP
                        ],
                        'data' => null
                    ];
                } else {
                    $validation_begin_date = Validation::fire($begin_date, Validation::$DATE);
                    $validation_end_date = Validation::fire($end_date, Validation::$DATE);

                    if (!$validation_begin_date || !$validation_end_date) {
                        if (!$validation_begin_date) $error[] = Error::$VALIDATION_BEGIN_DATE;
                        if (!$validation_end_date) $error[] = Error::$VALIDATION_END_DATE;

                        $result = [
                            'status' => 400,
                            'message' => $error,
                            'data' => null
                        ];
                    } else {
                        $calendar_list = CalendarManager::search_calendar($inner_group_id, $begin_date, $end_date);

                        $result = [
                            'status' => 200,
                            'message' => null,
                            'data' => [
                                'calendar_list' => $calendar_list
                            ]
                        ];
                    }
                }
            }
        }

        return $response->withJson($result);
    }

    public static function add_calendar(Request $request, Response $response) {
        $param = array_escape($request->getParsedBody());

        $token = isset($param['token']) ? $param['token'] : null;
        $group_id = isset($param['group_id']) ? $param['group_id'] : null;
        $title = isset($param['title']) ? $param['title'] : null;
        $content = isset($param['content']) ? $param['content'] : null;
        $date = isset($param['date']) ? $param['date'] : null;
        $remind_flg = isset($param['remind_flg']) ? $param['remind_flg'] : null;

        $error = [];

        if (is_nulls($token, $group_id, $title, $content, $date, $remind_flg)) {
            $result = [
                'status' => 400,
                'message' => [
                    Error::$REQUIRED_PARAM
                ],
                'data' => null
            ];
        } else {
            $user_id = TokenManager::get_user_id($token);
            $inner_group_id = GroupManager::get_group_id($group_id);

            if (!$user_id || !$inner_group_id) {
                if (!$user_id) $error[] =  Error::$UNKNOWN_TOKEN;
                if (!$inner_group_id) $error[] = Error::$UNKNOWN_GROUP;

                $result = [
                    'status' => 400,
                    'message' => $error,
                    'data' => null
                ];
            } else {
                $belong_group = GroupManager::already_belong_group($inner_group_id, $user_id);

                if (!$belong_group) {
                    $result = [
                        'status' => 400,
                        'message' => [
                            Error::$UNREADY_BELONG_GROUP
                        ],
                        'data' => null
                    ];
                } else {
                    $validation_title = Validation::fire($title, Validation::$CALENDAR_TITLE);
                    $validation_content = Validation::fire($content, Validation::$CALENDAR_CONTENT);
                    $validation_date = Validation::fire($date, Validation::$DATE);
                    $validation_remind_flg = Validation::fire($remind_flg, Validation::$CALENDAR_REMIND_FLG);

                    if (!$validation_title || !$validation_content || !$validation_date || !$validation_remind_flg) {
                        if (!$validation_title) $error[] = Error::$VALIDATION_TITLE;
                        if (!$validation_content) $error[] = Error::$VALIDATION_CONTENT;
                        if (!$validation_date) $error[] = Error::$VALIDATION_DATE;
                        if (!$validation_remind_flg) $error[] = Error::$VALIDATION_REMIND_FLG;

                        $result = [
                            'status' => 400,
                            'message' => $error,
                            'data' => null
                        ];
                    } else {
                        $calendar_id = CalendarManager::add_calendar($inner_group_id, $user_id, $title, $content, $date, $remind_flg);
                        $calendar_info = CalendarManager::get_calendar($calendar_id);

                        $result = [
                            'status' => 200,
                            'message' => null,
                            'data' => [
                                'calendar_info' => $calendar_info
                            ]
                        ];
                    }
                }
            }
        }

        return $response->withJson($result);
    }

    public static function edit_calendar(Request $request, Response $response) {
        $param = array_escape($request->getParsedBody());

        $token = isset($param['token']) ? $param['token'] : null;
        $calendar_id = isset($param['calendar_id']) ? $param['calendar_id'] : null;
        $title = isset($param['title']) ? $param['title'] : null;
        $content = isset($param['content']) ? $param['content'] : null;
        $date = isset($param['date']) ? $param['date'] : null;
        $remind_flg = isset($param['remind_flg']) ? $param['remind_flg'] : null;

        $error = [];

        if (is_nulls($token, $calendar_id, $title, $content, $date, $remind_flg)) {
            $result = [
                'status' => 400,
                'message' => [
                    Error::$REQUIRED_PARAM
                ],
                'data' => null
            ];
        } else {
            $user_id = TokenManager::get_user_id($token);

            if (!$user_id) {
                $result = [
                    'status' => 400,
                    'message' => [
                        Error::$UNKNOWN_TOKEN
                    ],
                    'data' => null
                ];
            } else {
                if (!CalendarManager::have_calendar_id($calendar_id, $user_id)) {
                    $result = [
                        'status' => 400,
                        'message' => [
                            Error::$UNAUTHORIZED_OPERATION
                        ],
                        'data' => null
                    ];
                } else {
                    $validation_title = Validation::fire($title, Validation::$CALENDAR_TITLE);
                    $validation_content = Validation::fire($content, Validation::$CALENDAR_CONTENT);
                    $validation_date = Validation::fire($date, Validation::$DATE);
                    $validation_remind_flg = Validation::fire($remind_flg, Validation::$CALENDAR_REMIND_FLG);

                    if (!$validation_title || !$validation_content || !$validation_date || !$validation_remind_flg) {
                        if (!$validation_title) $error[] = Error::$VALIDATION_TITLE;
                        if (!$validation_content) $error[] = Error::$VALIDATION_CONTENT;
                        if (!$validation_date) $error[] = Error::$VALIDATION_DATE;
                        if (!$validation_remind_flg) $error[] = Error::$VALIDATION_REMIND_FLG;

                        $result = [
                            'status' => 400,
                            'message' => $error,
                            'data' => null
                        ];
                    } else {
                        CalendarManager::edit_calendar($calendar_id, $title, $content, $date, $remind_flg);
                        $calendar_info = CalendarManager::get_calendar($calendar_id);

                        $result = [
                            'status' => 200,
                            'message' => null,
                            'data' => [
                                'calendar_info' => $calendar_info
                            ]
                        ];
                    }
                }
            }
        }

        return $response->withJson($result);
    }

    public static function delete_calendar(Request $request, Response $response) {
        $param = array_escape($request->getParsedBody());

        $token = isset($param['token']) ? $param['token'] : null;
        $calendar_id = isset($param['calendar_id']) ? $param['calendar_id'] : null;

        if (is_nulls($token, $calendar_id)) {
            $result = [
                'status' => 400,
                'message' => [
                    Error::$REQUIRED_PARAM
                ],
                'data' => null
            ];
        } else {
            $user_id = TokenManager::get_user_id($token);

            if (!$user_id) {
                $result = [
                    'status' => 400,
                    'message' => [
                        Error::$UNKNOWN_TOKEN
                    ],
                    'data' => null
                ];
            } else {
                if (!CalendarManager::have_calendar_id($calendar_id, $user_id)) {
                    $result = [
                        'status' => 400,
                        'message' => [
                            Error::$UNAUTHORIZED_OPERATION
                        ],
                        'data' => null
                    ];
                } else {
                    CalendarManager::delete_calendar($calendar_id);

                    $result = [
                        'status' => 200,
                        'message' => null,
                        'data' => null
                    ];
                }
            }
        }

        return $response->withJson($result);
    }
}