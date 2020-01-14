<?php
namespace Application\controllers;

use Application\app\GroupManager;
use Application\app\TimelineManager;
use Application\app\TokenManager;
use Application\lib\Error;
use Application\lib\Validation;
use Slim\Http\Request;
use Slim\Http\Response;

class TimelineCommentController {
    public static function get_comment(Request $request, Response $response) {
        $param = array_escape($request->getQueryParams());

        $token = isset($param['token']) ? $param['token'] : null;
        $timeline_id = isset($param['timeline_id']) ? $param['timeline_id'] : null;

        $error = [];

        if (is_null($token) || is_null($timeline_id)) {
            $result = [
                'status' => 400,
                'message' => [
                    Error::$REQUIRED_PARAM
                ],
                'data' => null
            ];
        } else {
            $user_id = TokenManager::get_user_id($token);
            $group_id = TimelineManager::get_timeline_group_id($timeline_id);

            if (!$user_id || !$group_id) {
                if (!$user_id) $error[] = Error::$UNKNOWN_TOKEN;
                if (!$group_id) $error[] = Error::$UNKNOWN_POST;

                $result = [
                    'status' => 400,
                    'message' => $error,
                    'data' => null
                ];
            } else {
                $belong_group = GroupManager::already_belong_group($group_id, $user_id);

                if (!$belong_group) {
                    $result = [
                        'status' => 400,
                        'message' => [
                            Error::$UNREADY_BELONG_GROUP
                        ],
                        'data' => null
                    ];
                } else {
                    $timeline_data = TimelineManager::get_post($timeline_id);
                    $comment_data = TimelineManager::get_comment($timeline_id);

                    $result = [
                        'status' => 200,
                        'message' => null,
                        'data' => [
                            'timeline_data' => $timeline_data,
                            'comment_data' => $comment_data
                        ]
                    ];
                }
            }
        }

        return $response->withJson($result);
    }

    public static function post_comment(Request $request, Response $response) {
        $param = array_escape($request->getParsedBody());

        $token = isset($param['token']) ? $param['token'] : null;
        $timeline_id = isset($param['timeline_id']) ? $param['timeline_id'] : null;
        $comment = isset($param['comment']) ? $param['comment'] : null;

        $error = [];

        if (is_null($token) || is_null($timeline_id) || is_null($comment)) {
            $result = [
                'status' => 400,
                'message' => [
                    Error::$REQUIRED_PARAM
                ],
                'data' => null
            ];
        } else {
            $user_id = TokenManager::get_user_id($token);
            $group_id = TimelineManager::get_timeline_group_id($timeline_id);

            if (!$user_id || !$group_id) {
                if (!$user_id) $error[] = Error::$UNKNOWN_TOKEN;
                if (!$group_id) $error[] = Error::$UNKNOWN_POST;

                $result = [
                    'status' => 400,
                    'message' => $error,
                    'data' => null
                ];
            } else {
                $belong_group = GroupManager::already_belong_group($group_id, $user_id);

                if (!$belong_group) {
                    $result = [
                        'status' => 400,
                        'message' => [
                            Error::$UNREADY_BELONG_GROUP
                        ],
                        'data' => null
                    ];
                } else {
                    $validation_comment = Validation::fire($comment, Validation::$TIMELINE_POST_COMMENT);

                    if (!$validation_comment) {
                        $result = [
                            'status' => 400,
                            'message' => [
                                Error::$VALIDATION_TIMELINE_POST_COMMENT
                            ],
                            'data' => null
                        ];
                    } else {
                        TimelineManager::post_comment($timeline_id, $user_id, $comment);

                        $timeline_data = TimelineManager::get_post($timeline_id);
                        $comment_data = TimelineManager::get_comment($timeline_id);

                        $result = [
                            'status' => 200,
                            'message' => null,
                            'data' => [
                                'timeline_data' => $timeline_data,
                                'comment_data' => $comment_data
                            ]
                        ];
                    }
                }
            }
        }

        return $response->withJson($result);
    }

    public static function delete_comment(Request $request, Response $response) {
        $param = array_escape($request->getParsedBody());

        $token = isset($param['token']) ? $param['token'] : null;
        $comment_id = isset($param['comment_id']) ? $param['comment_id'] : null;

        if (is_null($token) || is_null($comment_id)) {
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
                $already_comment = TimelineManager::get_comment_timeline_id($comment_id);

                if (!$already_comment) {
                    $result = [
                        'status' => 400,
                        'message' => [
                            Error::$UNKNOWN_COMMENT
                        ],
                        'data' => null
                    ];
                } else {
                    $have_comment = TimelineManager::have_user_id_comment($comment_id, $user_id);

                    if (!$have_comment) {
                        $result = [
                            'status' => 400,
                            'message' => [
                                Error::$UNAUTHORIZED_OPERATION
                            ],
                            'data' => null
                        ];
                    } else {
                        TimelineManager::delete_comment($comment_id);

                        $result = [
                            'status' => 200,
                            'message' => null,
                            'data' => null
                        ];
                    }
                }
            }
        }

        return $response->withJson($result);
    }
}