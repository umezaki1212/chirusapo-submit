<?php
namespace Application\Controllers;

use Application\App\ChildManager;
use Application\app\GroupManager;
use Application\app\TokenManager;
use Application\lib\Error;
use Application\lib\GoogleCloudStorage as GCS;
use Application\lib\Validation;
use Slim\Http\Request;
use Slim\Http\Response;

class ChildController {
    public static function add_child(Request $request, Response $response) {
        $param = array_escape($request->getParsedBody());
        $file = $request->getUploadedFiles();

        $token = isset($param['token']) ? $param['token'] : null;
        $group_id = isset($param['group_id']) ? $param['group_id'] : null;
        $user_id = isset($param['user_id']) ? $param['user_id'] : null;
        $user_name = isset($param['user_name']) ? $param['user_name'] : null;
        $birthday = isset($param['birthday']) ? $param['birthday'] : null;
        $age = isset($param['age']) ? $param['age'] : null;
        $gender = isset($param['gender']) ? $param['gender'] : null;
        $blood_type = isset($param['blood_type']) ? $param['blood_type'] : null;
        $body_height = isset($param['body_height']) ? $param['body_height'] : null;
        $body_weight = isset($param['body_weight']) ? $param['body_weight'] : null;
        $clothes_size = isset($param['clothes_size']) ? $param['clothes_size'] : null;
        $shoes_size = isset($param['shoes_size']) ? $param['shoes_size'] : null;
        $vaccination = isset($param['vaccination']) ? $param['vaccination'] : null;
        $allergy = isset($param['allergy']) ? $param['allergy'] : null;

        $error = [];

        if (is_nulls(
            $token, $group_id, $user_id, $user_name, $birthday, $age, $gender, $blood_type,
            $body_height, $body_weight, $clothes_size, $shoes_size)) {
            $result = [
                'status' => 400,
                'message' => [
                    Error::$REQUIRED_PARAM
                ],
                'data' => null
            ];
        } else {
            $parents_user_id = TokenManager::get_user_id($token);

            if (!$parents_user_id) {
                $result = [
                    'status' => 400,
                    'message' => [
                        Error::$UNKNOWN_TOKEN
                    ],
                    'data' => null
                ];
            } else {
                $inner_group_id = GroupManager::get_group_id($group_id);

                if (!$inner_group_id) {
                    $result = [
                        'status' => 400,
                        'message' => [
                            Error::$UNKNOWN_GROUP
                        ],
                        'data' => null
                    ];
                } else {
                    $belong_group = GroupManager::already_belong_group($inner_group_id, $parents_user_id);

                    if (!$belong_group) {
                        $result = [
                            'status' => 400,
                            'message' => [
                                Error::$UNREADY_BELONG_GROUP
                            ],
                            'data' => null
                        ];
                    } else {
                        $validation_user_id = Validation::fire($user_id, Validation::$USER_ID);
                        $validation_user_name = Validation::fire($user_name, Validation::$USER_NAME);
                        $validation_birthday = Validation::fire($birthday, Validation::$BIRTHDAY);
                        $validation_age = Validation::fire($age, Validation::$AGE);
                        $validation_gender = Validation::fire($gender, Validation::$GENDER);
                        $validation_blood_type = Validation::fire($blood_type, Validation::$BLOOD_TYPE);
                        $validation_body_height = Validation::fire($body_height, Validation::$BODY_HEIGHT);
                        $validation_body_weight = Validation::fire($body_weight, Validation::$BODY_WEIGHT);
                        $validation_clothes_size = Validation::fire($clothes_size, Validation::$CLOTHES_SIZE);
                        $validation_shoes_size = Validation::fire($shoes_size, Validation::$SHOES_SIZE);

                        $validation_vaccination = true;
                        if (!is_null($vaccination)) {
                            foreach ($vaccination as $value) {
                                if (array_key_exists('vaccine_name', $value) && array_key_exists('visit_date', $value)) {
                                    if (!Validation::fire($value['vaccine_name'], Validation::$VACCINATION)) $validation_vaccination = false;
                                    if (!Validation::fire($value['visit_date'], Validation::$DATE)) $validation_vaccination = false;
                                } else {
                                    $validation_vaccination = false;
                                }
                            }
                        }

                        $validation_allergy = true;
                        if (!is_null($allergy)) {
                            foreach ($allergy as $value) {
                                if (!Validation::fire($value, Validation::$ALLERGY)) $validation_allergy = false;
                            }
                        }

                        if (
                            !$validation_user_id ||
                            !$validation_user_name ||
                            !$validation_birthday ||
                            !$validation_age ||
                            !$validation_gender ||
                            !$validation_blood_type ||
                            !$validation_body_height ||
                            !$validation_body_weight ||
                            !$validation_clothes_size ||
                            !$validation_shoes_size ||
                            !$validation_vaccination ||
                            !$validation_allergy
                        ) {
                            if (!$validation_user_id) $error[] = Error::$VALIDATION_USER_ID;
                            if (!$validation_user_name) $error[] = Error::$VALIDATION_USER_NAME;
                            if (!$validation_birthday) $error[] = Error::$VALIDATION_BIRTHDAY;
                            if (!$validation_age) $error[] = Error::$VALIDATION_AGE;
                            if (!$validation_gender) $error[] = Error::$VALIDATION_GENDER;
                            if (!$validation_blood_type) $error[] = Error::$VALIDATION_BLOOD_TYPE;
                            if (!$validation_body_height) $error[] = Error::$VALIDATION_BODY_HEIGHT;
                            if (!$validation_body_weight) $error[] = Error::$VALIDATION_BODY_WEIGHT;
                            if (!$validation_clothes_size) $error[] = Error::$VALIDATION_CLOTHES_SIZE;
                            if (!$validation_shoes_size) $error[] = Error::$VALIDATION_SHOES_SIZE;
                            if (!$validation_vaccination) $error[] = Error::$VALIDATION_VACCINATION;
                            if (!$validation_allergy) $error[] = Error::$VALIDATION_ALLERGY;

                            $result = [
                                'status' => 400,
                                'message' => $error,
                                'data' => null
                            ];
                        } else {
                            $already_user_id = ChildManager::already_user_id($user_id);

                            if ($already_user_id) {
                                $result = [
                                    'status' => 400,
                                    'message' => [
                                        Error::$ALREADY_USER_ID
                                    ],
                                    'data' => null
                                ];
                            } else {
                                $child_id = ChildManager::add_child($inner_group_id, $user_id, $user_name, $birthday, $age, $gender, $blood_type);
                                if (!is_null($vaccination)) {
                                    foreach ($vaccination as $value) {
                                        ChildManager::add_vaccination($child_id, $value['vaccine_name'], $value['visit_date']);
                                    }
                                }
                                if (!is_null($allergy)) {
                                    foreach ($allergy as $value) {
                                        ChildManager::add_allergy($child_id, $value);
                                    }
                                }
                                ChildManager::add_growth_history($child_id, $body_height, $body_weight, $clothes_size, $shoes_size, date('Y-m-d'));
                                $child_info = ChildManager::get_child($child_id);

                                $result = [
                                    'status' => 200,
                                    'message' => null,
                                    'data' => [
                                        'child_info' => $child_info
                                    ]
                                ];
                            }
                        }
                    }
                }
            }
        }

        return $response->withJson($result);
    }

    public static function list_child(Request $request, Response $response) {
        $param = array_escape($request->getQueryParams());

        $token = isset($param['token']) ? $param['token'] : null;
        $group_id = isset($param['group_id']) ? $param['group_id'] : null;

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

            if (!$user_id) {
                $result = [
                    'status' => 400,
                    'message' => [
                        Error::$UNKNOWN_TOKEN
                    ],
                    'data' => null
                ];
            } else {
                $inner_group_id = GroupManager::get_group_id($group_id);

                if (!$inner_group_id) {
                    $result = [
                        'status' => 400,
                        'message' => [
                            Error::$UNKNOWN_GROUP
                        ],
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
                        $child_list = ChildManager::get_child_list($inner_group_id);

                        $result = [
                            'status' => 200,
                            'message' => null,
                            'data' => [
                                'child_list' => $child_list
                            ]
                        ];
                    }
                }
            }
        }

        return $response->withJson($result);
    }

    public static function get_child(Request $request, Response $response) {
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
                    $inner_child_id = ChildManager::child_id_to_inner_child_id($child_id);
                    $child_info = ChildManager::get_child($inner_child_id);

                    $result = [
                        'status' => 200,
                        'message' => null,
                        'data' => [
                            'child_info' => $child_info
                        ]
                    ];
                }
            }
        }

        return $response->withJson($result);
    }

    public static function edit_child(Request $request, Response $response) {
        $param = array_escape($request->getParsedBody());
        $file = $request->getUploadedFiles();

        $token = isset($param['token']) ? $param['token'] : null;
        $child_id = isset($param['child_id']) ? $param['child_id'] : null;
        $vaccination_delete = isset($param['vaccination_delete']) ? $param['vaccination_delete'] : null;
        $allergy_delete = isset($param['allergy_delete']) ? $param['allergy_delete'] : null;
        $vaccination_new = isset($param['vaccination_new']) ? $param['vaccination_new'] : null;
        $allergy_new = isset($param['allergy_new']) ? $param['allergy_new'] : null;

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

            if (!$user_id) {
                $result = [
                    'status' => 400,
                    'message' => [
                        Error::$UNKNOWN_TOKEN
                    ],
                    'data' => null
                ];
            } else {
                $group_id = ChildManager::child_id_to_group_id($child_id);

                if (!$group_id) {
                    $result = [
                        'status' => 400,
                        'message' => [
                            Error::$UNKNOWN_CHILD
                        ],
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

                        $validation_vaccination = true;
                        if (!is_null($vaccination_new)) {
                            foreach ($vaccination_new as $value) {
                                if (array_key_exists('vaccine_name', $value) && array_key_exists('visit_date', $value)) {
                                    if (!Validation::fire($value['vaccine_name'], Validation::$VACCINATION)) $validation_vaccination = false;
                                    if (!Validation::fire($value['visit_date'], Validation::$DATE)) $validation_vaccination = false;
                                } else {
                                    $validation_vaccination = false;
                                }
                            }
                        }

                        $validation_allergy = true;
                        if (!is_null($allergy_new)) {
                            foreach ($allergy_new as $value) {
                                if (!Validation::fire($value, Validation::$ALLERGY)) $validation_allergy = false;
                            }
                        }

                        $unauthorized_operation = true;
                        if (!is_null($vaccination_delete)) {
                            foreach ($vaccination_delete as $value) {
                                if (!ChildManager::vaccination_id_have_child($inner_child_id, $value)) $unauthorized_operation = false;
                            }
                        }

                        if (!is_null($allergy_delete)) {
                            foreach ($allergy_delete as $value) {
                                if (!ChildManager::allergy_id_have_child($inner_child_id, $value)) $unauthorized_operation = false;
                            }
                        }

                        if (!$validation_vaccination || !$validation_allergy || !$unauthorized_operation) {
                            if (!$validation_vaccination) $error[] = Error::$VALIDATION_VACCINATION;
                            if (!$validation_allergy) $error[] = Error::$VALIDATION_ALLERGY;
                            if (!$unauthorized_operation) $error[] = Error::$UNAUTHORIZED_OPERATION;

                            $result = [
                                'status' => 400,
                                'message' => $error,
                                'data' => null
                            ];
                        } else {
                            if (!is_null($vaccination_new)) {
                                foreach ($vaccination_new as $value) {
                                    ChildManager::add_vaccination($inner_child_id, $value['vaccine_name'], $value['visit_date']);
                                }
                            }

                            if (!is_null($allergy_new)) {
                                foreach ($allergy_new as $value) {
                                    ChildManager::add_allergy($inner_child_id, $value);
                                }
                            }

                            if (!is_null($vaccination_delete)) {
                                foreach ($vaccination_delete as $value) {
                                    ChildManager::delete_vaccination($value);
                                }
                            }

                            if (!is_null($allergy_delete)) {
                                foreach ($allergy_delete as $value) {
                                    ChildManager::delete_allergy($value);
                                }
                            }

                            $child_info = ChildManager::get_child($inner_child_id);

                            $result = [
                                'status' => 200,
                                'message' => null,
                                'data' => [
                                    'child_info' => $child_info
                                ]
                            ];
                        }
                    }
                }
            }
        }

        return $response->withJson($result);
    }

    public static function delete_child(Request $request, Response $response) {
        $param = array_escape($request->getParsedBody());

        $token = isset($param['token']) ? $param['token'] : null;
        $child_id = isset($param['child_id']) ? $param['child_id'] : null;

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

            if (!$user_id) {
                $result = [
                    'status' => 400,
                    'message' => [
                        Error::$UNKNOWN_TOKEN
                    ],
                    'data' => null
                ];
            } else {
                $group_id = ChildManager::child_id_to_group_id($child_id);

                if (!$group_id) {
                    $result = [
                        'status' => 400,
                        'message' => [
                            Error::$UNKNOWN_CHILD
                        ],
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
                        ChildManager::delete_child($inner_child_id);

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