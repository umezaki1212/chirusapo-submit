<?php
namespace Application\Controllers;

use Application\App\ChildManager;
use Application\app\GroupManager;
use Application\app\TokenManager;
use Application\lib\Error;
use Application\lib\Validation;
use Slim\Http\Request;
use Slim\Http\Response;

class ChildGrowthHistoryController {
    public static function add_history(Request $request, Response $response) {
        $param = array_escape($request->getParsedBody());

        $token = isset($param['token']) ? $param['token'] : null;
        $child_id = isset($param['child_id']) ? $param['child_id'] : null;
        $body_height = isset($param['body_height']) ? $param['body_height'] : null;
        $body_weight = isset($param['body_weight']) ? $param['body_weight'] : null;
        $clothes_size = isset($param['clothes_size']) ? $param['clothes_size'] : null;
        $shoes_size = isset($param['shoes_size']) ? $param['shoes_size'] : null;
        $add_date = isset($param['add_date']) ? $param['add_date'] : null;

        $error = [];

        if (is_nulls($token, $child_id, $body_height, $body_weight, $clothes_size, $shoes_size, $add_date)) {
            $result = [
                'status' => 400,
                'message' => [
                    Error::$REQUIRED_PARAM
                ],
                'data' => null
            ];
        } else {
            $user_id = TokenManager::get_user_id($token);
            $inner_group_id = ChildManager::child_id_to_group_id($child_id);

            if (!$user_id || !$inner_group_id) {
                if (!$user_id) $error[] = Error::$UNKNOWN_TOKEN;
                if (!$inner_group_id) $error[] = Error::$UNKNOWN_CHILD;

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
                    $validation_body_height = Validation::fire($body_height, Validation::$BODY_HEIGHT);
                    $validation_body_weight = Validation::fire($body_weight, Validation::$BODY_WEIGHT);
                    $validation_clothes_size = Validation::fire($clothes_size, Validation::$CLOTHES_SIZE);
                    $validation_shoes_size = Validation::fire($shoes_size, Validation::$SHOES_SIZE);
                    $validation_add_date = Validation::fire($add_date, Validation::$DATE);

                    if (
                        !$validation_body_height ||
                        !$validation_body_weight ||
                        !$validation_clothes_size ||
                        !$validation_shoes_size ||
                        !$validation_add_date
                    ) {
                        if (!$validation_body_height) $error[] = Error::$VALIDATION_BODY_HEIGHT;
                        if (!$validation_body_weight) $error[] = Error::$VALIDATION_BODY_WEIGHT;
                        if (!$validation_clothes_size) $error[] = Error::$VALIDATION_CLOTHES_SIZE;
                        if (!$validation_shoes_size) $error[] = Error::$VALIDATION_SHOES_SIZE;
                        if (!$validation_add_date) $error[] = Error::$VALIDATION_ADD_DATE;

                        $result = [
                            'status' => 400,
                            'message' => $error,
                            'data' => null
                        ];
                    } else {
                        $inner_child_id = ChildManager::child_id_to_inner_child_id($child_id);

                        if (!ChildManager::already_record_growth_history($inner_child_id, $add_date)) {
                            $result = [
                                'status' => 400,
                                'message' => [
                                    Error::$ALREADY_RECORD
                                ],
                                'data' => null
                            ];
                        } else {
                            ChildManager::add_growth_history($inner_child_id, $body_height, $body_weight, $clothes_size, $shoes_size, $add_date);
                            $history_data = ChildManager::get_growth_history($inner_child_id);

                            $result = [
                                'status' => 200,
                                'message' => null,
                                'data' => [
                                    'history_data' => $history_data
                                ]
                            ];
                        }
                    }
                }
            }
        }

        return $response->withJson($result);
    }

    public static function list_history(Request $request, Response $response) {
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
                if (!$user_id) $error[] = Error::$UNKNOWN_TOKEN;
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
                    $history_list = ChildManager::list_growth_history($inner_group_id);

                    $result = [
                        'status' => 200,
                        'message' => null,
                        'data' => [
                            'history_list' => $history_list
                        ]
                    ];
                }
            }
        }

        return $response->withJson($result);
    }

    public static function get_history(Request $request, Response $response) {
        $param = array_escape($request->getQueryParams());

        $token = isset($param['token']) ? $param['token'] : null;
        $child_id = isset($param['child_id']) ? $param['child_id'] : null;

        $error = [];

        if (is_nulls($token, $child_id)) {
            $result = [
                'status' => 400,
                'message' => [
                    Error::$REQUIRED_PARAM
                ],
                'data' => null
            ];
        } else {
            $user_id = TokenManager::get_user_id($token);
            $group_id = ChildManager::child_id_to_group_id($child_id);

            if (!$user_id || !$group_id) {
                if (!$user_id) $error[] = Error::$UNKNOWN_TOKEN;
                if (!$group_id) $error[] = Error::$UNKNOWN_CHILD;

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
                    $inner_child_id = ChildManager::child_id_to_inner_child_id($child_id);
                    $history_data = ChildManager::get_growth_history($inner_child_id);

                    $result = [
                        'status' => 200,
                        'message' => null,
                        'data' => [
                            'history_data' => $history_data
                        ]
                    ];
                }
            }
        }

        return $response->withJson($result);
    }
}