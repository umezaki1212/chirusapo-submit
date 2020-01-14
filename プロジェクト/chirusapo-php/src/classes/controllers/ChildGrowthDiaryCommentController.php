<?php
namespace Application\Controllers;

use Application\App\ChildDiaryManager;
use Application\app\TokenManager;
use Application\lib\Error;
use Application\lib\Validation;
use Slim\Http\Request;
use Slim\Http\Response;

class ChildGrowthDiaryCommentController {
    public static function get_comment(Request $request, Response $response) {
        $param = array_escape($request->getQueryParams());

        $token = isset($param['token']) ? $param['token'] : null;
        $diary_id = isset($param['diary_id']) ? $param['diary_id'] : null;

        $error = [];

        if (is_nulls($token, $diary_id)) {
            $result = [
                'status' => 400,
                'message' => [
                    Error::$REQUIRED_PARAM
                ],
                'data' => null
            ];
        } else {
            $user_id = TokenManager::get_user_id($token);
            $already_diary = ChildDiaryManager::have_diary_id($diary_id);

            if (!$diary_id || !$already_diary) {
                if (!$user_id) $error[] = Error::$UNKNOWN_TOKEN;
                if (!$already_diary) $error[] = Error::$UNKNOWN_POST;

                $result = [
                    'status' => 400,
                    'message' => $error,
                    'data' => null
                ];
            } else {
                if (!ChildDiaryManager::have_post_comment_permission($diary_id, $user_id)) {
                    $result = [
                        'status' => 400,
                        'message' => [
                            Error::$UNAUTHORIZED_OPERATION
                        ],
                        'data' => null
                    ];
                } else {
                    $comment_data = ChildDiaryManager::get_comment($diary_id);

                    $result = [
                        'status' => 200,
                        'message' => null,
                        'data' => [
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
        $diary_id = isset($param['diary_id']) ? $param['diary_id'] : null;
        $comment = isset($param['comment']) ? $param['comment'] : null;

        $error = [];

        if (is_nulls($token, $diary_id, $comment)) {
            $result = [
                'status' => 400,
                'message' => [
                    Error::$REQUIRED_PARAM
                ],
                'data' => null
            ];
        } else {
            $user_id = TokenManager::get_user_id($token);
            $already_diary = ChildDiaryManager::have_diary_id($diary_id);

            if (!$user_id || !$already_diary) {
                if (!$user_id) $error[] = Error::$UNKNOWN_TOKEN;
                if (!$already_diary) $error[] = Error::$UNKNOWN_POST;

                $result = [
                    'status' => 400,
                    'message' => $error,
                    'data' => null
                ];
            } else {
                if (!ChildDiaryManager::have_post_comment_permission($diary_id, $user_id)) {
                    $result = [
                        'status' => 400,
                        'message' => [
                            Error::$UNAUTHORIZED_OPERATION
                        ],
                        'data' => null
                    ];
                } else {
                    $validation_comment = Validation::fire($comment, Validation::$DIARY_POST_COMMENT);

                    if (!$validation_comment) {
                        $result = [
                            'status' => 400,
                            'message' => [
                                Error::$VALIDATION_DIARY_POST_COMMENT
                            ],
                            'data' => null
                        ];
                    } else {
                        ChildDiaryManager::post_comment($diary_id, $user_id, $comment);
                        $comment_data = ChildDiaryManager::get_comment($diary_id);

                        $result = [
                            'status' => 200,
                            'message' => null,
                            'data' => [
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

        $error = [];

        if (is_nulls($token, $comment_id)) {
            $result = [
                'status' => 400,
                'message' => [
                    Error::$REQUIRED_PARAM
                ],
                'data' => null
            ];
        } else {
            $user_id = TokenManager::get_user_id($token);
            $already_comment = ChildDiaryManager::have_comment_id($comment_id);

            if (!$user_id || !$already_comment) {
                if (!$user_id) $error[] = Error::$UNKNOWN_TOKEN;
                if (!$already_comment) $error[] = Error::$UNKNOWN_COMMENT;

                $result = [
                    'status' => 400,
                    'message' => $error,
                    'data' => null
                ];
            } else {
                if (!ChildDiaryManager::have_delete_comment_permission($comment_id, $user_id)) {
                    $result = [
                        'status' => 400,
                        'message' => [
                            Error::$UNAUTHORIZED_OPERATION
                        ],
                        'data' => null
                    ];
                } else {
                    ChildDiaryManager::delete_comment($comment_id);

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