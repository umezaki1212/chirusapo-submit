<?php
namespace Application\controllers;

use Application\app\GroupManager;
use Application\app\TimelineManager;
use Application\app\TokenManager;
use Application\lib\Error;
use Application\lib\FFMpegManager;
use Application\lib\GoogleCloudStorage as GCS;
use Application\lib\Validation;
use DateTime;
use Slim\Http\Request;
use Slim\Http\Response;

require_once __DIR__.'/../app/GroupManager.php';
require_once __DIR__.'/../app/TimelineManager.php';
require_once __DIR__.'/../app/TokenManager.php';
require_once __DIR__.'/../lib/Error.php';
require_once __DIR__.'/../lib/FFMpegManager.php';
require_once __DIR__.'/../lib/GoogleCloudStorage.php';
require_once __DIR__.'/../lib/Validation.php';

class TimelineController {
    public static function get_timeline(Request $request, Response $response) {
        $param = array_escape($request->getQueryParams());

        $token = isset($param['token']) ? $param['token'] : null;
        $group_id = isset($param['group_id']) ? $param['group_id'] : null;

        $error = [];

        if (is_null($token) || is_null($group_id)) {
            $result = [
                'status' => 400,
                'message' => [
                    Error::$REQUIRED_PARAM
                ],
                'data' => null
            ];
        } else {
            $validation_group_id = Validation::fire($group_id, Validation::$GROUP_ID);

            if (!$validation_group_id) {
                $result = [
                    'status' => 400,
                    'message' => [
                        Error::$VALIDATION_GROUP_ID
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
                        $timeline_data = TimelineManager::get_timeline($inner_group_id);

                        $result = [
                            'status' => 200,
                            'message' => null,
                            'data' => [
                                'timeline_data' => $timeline_data
                            ]
                        ];
                    }
                }
            }
        }

        return $response->withJson($result);
    }

    public static function post_timeline(Request $request, Response $response) {
        $param = array_escape($request->getParsedBody());
        $file = $request->getUploadedFiles();

        $token = isset($param['token']) ? $param['token'] : null;
        $group_id = isset($param['group_id']) ? $param['group_id'] : null;
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

        if (is_null($token) || is_null($group_id)) {
            $result = [
                'status' => 400,
                'message' => [
                    Error::$REQUIRED_PARAM
                ],
                'data' => null
            ];
        } else if (is_null($text) && is_null($image01) && is_null($movie01)) {
            $result = [
                'status' => 400,
                'message' => [
                    Error::$NOT_FIND_POST_CONTENT
                ],
                'data' => null
            ];
        } else if (!is_null($image01) && !is_null($movie01)) {
            $result = [
                'status' => 400,
                'message' => [
                    Error::$DUPLICATE_MEDIA_FILE
                ],
                'data' => null
            ];
        } else {
            $validation_group_id = Validation::fire($group_id, Validation::$GROUP_ID);
            $validation_content = is_null($text) ? true : Validation::fire($text, Validation::$TIMELINE_POST_CONTENT);

            $image01_file_name =
            $image02_file_name =
            $image03_file_name =
            $image04_file_name =
            $movie01_file_name =
            $movie01_thumbnail =
            $movie01_thumbnail_name = true;

            $post_flg = true;

            if (!is_null($image01)) {
                if (!GCS::allow_extension($image01)) {
                    $error[] = Error::$ALLOW_EXTENSION;
                    $post_flg = false;
                }

                if (!is_null($image02)) {
                    if (!GCS::allow_extension($image02)) {
                        $error[] = Error::$ALLOW_EXTENSION;
                        $post_flg = false;
                    }

                    if (!is_null($image03)) {
                        if (!GCS::allow_extension($image03)) {
                            $error[] = Error::$ALLOW_EXTENSION;
                            $post_flg = false;
                        }

                        if (!is_null($image04)) {
                            if (!GCS::allow_extension($image04)) {
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
                    }
                } else {
                    $error[] = Error::$ALLOW_EXTENSION;
                    $post_flg = false;
                }
            }

            if (!$validation_group_id || !$validation_content) {
                if (!$validation_group_id) $error[] = Error::$VALIDATION_GROUP_ID;
                if (!$validation_content) $error[] = Error::$VALIDATION_TIMELINE_POST_CONTENT;

                $result = [
                    'status' => 400,
                    'message' => $error,
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
                        if (!is_null($text) && !is_null($image01)) {
                            $content_type = 'text_image';
                        } else if (!is_null($text) && !is_null($movie01)) {
                            $content_type = 'text_movie';
                        } else if (is_null($text) && !is_null($image01)) {
                            $content_type = 'image';
                        } else if (is_null($text) && !is_null($movie01)) {
                            $content_type = 'movie';
                        } else {
                            $content_type = 'text';
                        }

                        if (!is_null($image01)) {
                            $image01_file_name = GCS::upload($image01, 'timeline/image', $file_date);

                            if (!$image01_file_name) {
                                $error[] = Error::$UPLOAD_FAILED;
                                $post_flg = false;
                            }

                            if (!is_null($image02)) {
                                $image02_file_name = GCS::upload($image02, 'timeline/image', $file_date);

                                if (!$image02_file_name) {
                                    $error[] = Error::$UPLOAD_FAILED;
                                    $post_flg = false;
                                }

                                if (!is_null($image03)) {
                                    $image03_file_name = GCS::upload($image03, 'timeline/image', $file_date);

                                    if (!$image03_file_name) {
                                        $error[] = Error::$UPLOAD_FAILED;
                                        $post_flg = false;
                                    }

                                    if (!is_null($image04)) {
                                        $image04_file_name = GCS::upload($image04, 'timeline/image', $file_date);

                                        if (!$image04_file_name) {
                                            $error[] = Error::$UPLOAD_FAILED;
                                            $post_flg = false;
                                        }
                                    }
                                }
                            }
                        }

                        if (!is_null($movie01) && $movie01_thumbnail) {
                            $movie01_file_name = GCS::upload($movie01, 'timeline/movie/content', $file_date);
                            $movie01_thumbnail_name = GCS::upload_tmp($movie01_thumbnail, 'timeline/movie/thumbnail', $file_date);

                            if (!$movie01_file_name || !$movie01_thumbnail_name) {
                                $error[] = Error::$UPLOAD_FAILED;
                                $post_flg = false;
                            }
                        }

                        if ($post_flg) {
                            $timeline_id = TimelineManager::post_timeline($inner_group_id, $user_id, $content_type, $db_date);

                            if (!is_null($text)) {
                                TimelineManager::post_timeline_text($timeline_id, $text);
                            }

                            if (!is_null($image01) && $image01_file_name) {
                                TimelineManager::post_timeline_image($timeline_id, 1, $image01_file_name);

                                if (!is_null($image02) && $image02_file_name) {
                                    TimelineManager::post_timeline_image($timeline_id, 2, $image02_file_name);

                                    if (!is_null($image03) && $image03_file_name) {
                                        TimelineManager::post_timeline_image($timeline_id, 3, $image03_file_name);

                                        if (!is_null($image04) && $image04_file_name) {
                                            TimelineManager::post_timeline_image($timeline_id, 4, $image04_file_name);
                                        }
                                    }
                                }
                            }

                            if (!is_null($movie01) && $movie01_file_name) {
                                TimelineManager::post_timeline_movie($timeline_id, $movie01_thumbnail_name, $movie01_file_name);
                            }

                            $post_data = TimelineManager::get_post($timeline_id);

                            $result = [
                                'status' => 200,
                                'message' => null,
                                'data' => [
                                    'post_data' => $post_data
                                ]
                            ];
                        } else {
                            $result = [
                                'status' => 400,
                                'message' => $error,
                                'data' => null
                            ];
                        }
                    }
                }
            }
        }

        return $response->withJson($result);
    }

    public static function delete_timeline(Request $request, Response $response) {
        $param = array_escape($request->getParsedBody());

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
                    $have_timeline = TimelineManager::have_user_id_timeline($timeline_id, $user_id);

                    if (!$have_timeline) {
                        $result = [
                            'status' => 400,
                            'message' => [
                                Error::$UNAUTHORIZED_OPERATION
                            ],
                            'data' => null
                        ];
                    } else {
                        TimelineManager::delete_timeline($timeline_id);

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