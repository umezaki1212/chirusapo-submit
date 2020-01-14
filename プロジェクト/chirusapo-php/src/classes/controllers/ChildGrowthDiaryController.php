<?php
namespace Application\Controllers;

use Application\App\ChildDiaryManager;
use Application\App\ChildManager;
use Application\app\GroupManager;
use Application\app\TokenManager;
use Application\lib\Error;
use Application\lib\FFMpegManager;
use Application\lib\GoogleCloudStorage as GCS;
use Application\lib\Validation;
use DateTime;
use Slim\Http\Request;
use Slim\Http\Response;

class ChildGrowthDiaryController {
    public static function get_diary(Request $request, Response $response) {
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
                    $post_data = ChildDiaryManager::list_diary($inner_child_id);

                    $result = [
                        'status' => 200,
                        'message' => null,
                        'data' => [
                            'post_data' => $post_data
                        ]
                    ];
                }
            }
        }

        return $response->withJson($result);
    }

    public static function post_diary(Request $request, Response $response) {
        $param = array_escape($request->getParsedBody());
        $file = $request->getUploadedFiles();

        $token = isset($param['token']) ? $param['token'] : null;
        $child_id = isset($param['child_id']) ? $param['child_id'] : null;
        $text = isset($param['text']) ? $param['text'] : null;
        $image01 = isset($file['image01']) ? $file['image01'] : null;
        $image02 = isset($file['image02']) ? $file['image02'] : null;
        $image03 = isset($file['image03']) ? $file['image03'] : null;
        $image04 = isset($file['image04']) ? $file['image04'] : null;
        $movie01 = isset($file['movie01']) ? $file['movie01'] : null;

        $error = [];

        $date = new DateTime();

        $db_date = $date->format('Y-m-d H:i:s');
        $file_date = $date->format('Ymd-His');

        if (is_nulls($token, $child_id, $text)) {
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
                    if (!is_null($image01) && !is_null($movie01)) {
                        $result = [
                            'status' => 400,
                            'message' => [
                                Error::$DUPLICATE_MEDIA_FILE
                            ],
                            'data' => null
                        ];
                    } else {
                        $validation_text = Validation::fire($text, Validation::$DIARY_POST_CONTENT);

                        $image01_file_name =
                        $image02_file_name =
                        $image03_file_name =
                        $image04_file_name =
                        $movie01_file_name =
                        $movie01_thumbnail =
                        $movie01_thumbnail_name = true;

                        $post_flg = true;

                        if (!is_null($image01)) {
                            if (GCS::allow_extension($image01)) {
                                $image01_file_name = GCS::upload($image01, 'growth/diary/image', $file_date);

                                if (!$image01_file_name) {
                                    $error[] = Error::$UPLOAD_FAILED;
                                    $post_flg = false;
                                }
                            } else {
                                $error[] = Error::$ALLOW_EXTENSION;
                                $post_flg = false;
                            }

                            if (!is_null($image02)) {
                                if (GCS::allow_extension($image02)) {
                                    $image02_file_name = GCS::upload($image02, 'growth/diary/image', $file_date);

                                    if (!$image02_file_name) {
                                        $error[] = Error::$UPLOAD_FAILED;
                                        $post_flg = false;
                                    }
                                } else {
                                    $error[] = Error::$ALLOW_EXTENSION;
                                    $post_flg = false;
                                }

                                if (!is_null($image03)) {
                                    if (GCS::allow_extension($image03)) {
                                        $image03_file_name = GCS::upload($image03, 'growth/diary/image', $file_date);

                                        if (!$image03_file_name) {
                                            $error[] = Error::$UPLOAD_FAILED;
                                            $post_flg = false;
                                        }
                                    } else {
                                        $error[] = Error::$ALLOW_EXTENSION;
                                        $post_flg = false;
                                    }

                                    if (!is_null($image04)) {
                                        if (GCS::allow_extension($image04)) {
                                            $image04_file_name = GCS::upload($image04, 'growth/diary/image', $file_date);

                                            if (!$image04_file_name) {
                                                $error[] = Error::$UPLOAD_FAILED;
                                                $post_flg = false;
                                            }
                                        } else {
                                            $error[] = Error::$ALLOW_EXTENSION;
                                            $post_flg = false;
                                        }
                                    }
                                }
                            }
                        }

                        if (!is_null($movie01)) {
                            if (GCS::allow_extension($movie01)) {
                                $movie01_thumbnail = FFMpegManager::generate_thumbnail($movie01);
                                if (!$movie01_thumbnail) {
                                    $error[] = Error::$GENERATE_THUMBNAIL;
                                    $post_flg = false;
                                } else {
                                    $movie01_file_name = GCS::upload($movie01, 'growth/diary/movie/content', $file_date);
                                    $movie01_thumbnail_name = GCS::upload_tmp($movie01_thumbnail, 'growth/diary/movie/thumbnail', $file_date);

                                    if (!$movie01_file_name || !$movie01_thumbnail_name) {
                                        $error[] = Error::$UPLOAD_FAILED;
                                        $post_flg = false;
                                    }
                                }
                            } else {
                                $error[] = Error::$ALLOW_EXTENSION;
                                $post_flg = false;
                            }
                        }

                        if (!$validation_text) $error[] = Error::$VALIDATION_DIARY_POST_CONTENT;

                        if (!$post_flg) {
                            $result = [
                                'status' => 400,
                                'message' => $error,
                                'data' => null
                            ];
                        } else {
                            if (!is_null($text) && !is_null($image01)) {
                                $content_type = 'text_image';
                            } else if (!is_null($text) && !is_null($movie01)) {
                                $content_type = 'text_movie';
                            } else {
                                $content_type = 'text';
                            }

                            $inner_child_id = ChildManager::child_id_to_inner_child_id($child_id);
                            $diary_id = ChildDiaryManager::post_diary($inner_child_id, $user_id, $content_type, $text, $db_date);

                            if (!is_null($image01) && $image01_file_name) {
                                ChildDiaryManager::post_diary_image($diary_id, 1, $image01_file_name);

                                if (!is_null($image02) && $image02_file_name) {
                                    ChildDiaryManager::post_diary_image($diary_id, 2, $image02_file_name);

                                    if (!is_null($image03) && $image03_file_name) {
                                        ChildDiaryManager::post_diary_image($diary_id, 3, $image03_file_name);

                                        if (!is_null($image04) && $image04_file_name) {
                                            ChildDiaryManager::post_diary_image($diary_id, 4, $image04_file_name);
                                        }
                                    }
                                }
                            }

                            if (!is_null($movie01) && $movie01_file_name) {
                                ChildDiaryManager::post_diary_movie($diary_id, $movie01_thumbnail_name, $movie01_file_name);
                            }

                            $post_data = ChildDiaryManager::get_diary($diary_id);

                            $result = [
                                'status' => 200,
                                'message' => null,
                                'data' => [
                                    'post_data' => $post_data
                                ]
                            ];
                        }
                    }
                }
            }
        }

        return $response->withJson($result);
    }

    public static function delete_diary(Request $request, Response $response) {
        $param = array_escape($request->getParsedBody());

        $token = isset($param['token']) ? $param['token'] : null;
        $diary_id = isset($param['diary_id']) ? $param['diary_id'] : null;

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

            if (!$user_id) {
                $result = [
                    'status' => 400,
                    'message' => [
                        Error::$UNKNOWN_TOKEN
                    ],
                    'data' => null
                ];
            } else {
                if (!ChildDiaryManager::have_diary_id($diary_id)) {
                    $result = [
                        'status' => 400,
                        'message' => [
                            Error::$UNKNOWN_POST
                        ],
                        'data' => null
                    ];
                } else {
                    if (!ChildDiaryManager::have_diary_id_from_user_id($diary_id, $user_id)) {
                        $result = [
                            'status' => 400,
                            'message' => [
                                Error::$UNAUTHORIZED_OPERATION
                            ],
                            'data' => null
                        ];
                    } else {
                        ChildDiaryManager::delete_diary($diary_id);

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