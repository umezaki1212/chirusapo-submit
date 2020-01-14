<?php
use Application\Controllers\AlbumController;
use Application\Controllers\CalendarController;
use Application\Controllers\ChildController;
use Application\Controllers\ChildFaceController;
use Application\Controllers\ChildFriendController;
use Application\Controllers\ChildFriendFaceController;
use Application\Controllers\ChildGrowthDiaryCommentController;
use Application\Controllers\ChildGrowthDiaryController;
use Application\Controllers\ChildGrowthHistoryController;
use Application\Controllers\IndexController;
use Application\Controllers\AccountController;
use Application\controllers\MasterController;
use Application\Controllers\ModelController;
use Application\Controllers\RemoveBgController;
use Application\controllers\TimelineController;
use Application\controllers\TimelineCommentController;
use Application\controllers\TokenController;
use Application\controllers\GroupController;
use Slim\App;
use Slim\Container;
use Slim\Http\Response;

// autoload
require_once __DIR__.'/../vendor/autoload.php';
// controller
require_once __DIR__.'/../src/classes/controllers/IndexController.php';
require_once __DIR__.'/../src/classes/controllers/MasterController.php';
require_once __DIR__.'/../src/classes/controllers/AccountController.php';
require_once __DIR__.'/../src/classes/controllers/TokenController.php';
require_once __DIR__.'/../src/classes/controllers/GroupController.php';
require_once __DIR__.'/../src/classes/controllers/TimelineController.php';
require_once __DIR__.'/../src/classes/controllers/TimelineCommentController.php';
require_once __DIR__.'/../src/classes/controllers/ChildController.php';
require_once __DIR__.'/../src/classes/controllers/ChildFaceController.php';
require_once __DIR__.'/../src/classes/controllers/ChildFriendController.php';
require_once __DIR__.'/../src/classes/controllers/ChildFriendFaceController.php';
require_once __DIR__.'/../src/classes/controllers/ChildGrowthHistoryController.php';
require_once __DIR__.'/../src/classes/controllers/ChildGrowthDiaryController.php';
require_once __DIR__.'/../src/classes/controllers/ChildGrowthDiaryCommentController.php';
require_once __DIR__.'/../src/classes/controllers/AlbumController.php';
require_once __DIR__.'/../src/classes/controllers/CalendarController.php';
require_once __DIR__.'/../src/classes/controllers/ModelController.php';
require_once __DIR__.'/../src/classes/controllers/RemoveBgController.php';
// manager
require_once __DIR__.'/../src/classes/app/MasterManager.php';
require_once __DIR__.'/../src/classes/app/AccountManager.php';
require_once __DIR__.'/../src/classes/app/TokenManager.php';
require_once __DIR__.'/../src/classes/app/GroupManager.php';
require_once __DIR__.'/../src/classes/app/TimelineManager.php';
require_once __DIR__.'/../src/classes/app/ChildManager.php';
require_once __DIR__.'/../src/classes/app/ChildFaceManager.php';
require_once __DIR__.'/../src/classes/app/ChildFriendManager.php';
require_once __DIR__.'/../src/classes/app/ChildFriendFaceManager.php';
require_once __DIR__.'/../src/classes/app/ChildDiaryManager.php';
require_once __DIR__.'/../src/classes/app/AlbumManager.php';
require_once __DIR__.'/../src/classes/app/CalendarManager.php';
require_once __DIR__.'/../src/classes/app/ModelManager.php';
// lib
require_once __DIR__.'/../src/classes/lib/DatabaseManager.php';
require_once __DIR__.'/../src/classes/lib/Error.php';
require_once __DIR__.'/../src/classes/lib/Validation.php';
require_once __DIR__.'/../src/classes/lib/Mailer.php';
require_once __DIR__.'/../src/classes/lib/GoogleCloudStorage.php';
require_once __DIR__.'/../src/classes/lib/FFMpegManager.php';
require_once __DIR__.'/../src/classes/lib/RemoveBg.php';
require_once __DIR__.'/../src/classes/lib/BotDataSheet.php';
// etc.
require_once __DIR__.'/../src/classes/app/functions.php';

$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];

$c = new Container($configuration);

$app = new App($c);

unset($app->getContainer()['notFoundHandler']);
$app->getContainer()['notFoundHandler'] = function ($c) {
    return function () use ($c) {
        $response = new Response(404);
        $result = [
            'status' => 400,
            'message' => [
                'Slim Framework Error! 404 Not Found Handler.'
            ],
            'data' => null
        ];

        return $response->withJson($result);
    };
};

$c['notAllowedHandler'] = function ($c) {
    return function () use ($c) {
        $response = new Response(405);
        $result = [
            'status' => 400,
            'message' => [
                'Slim Framework Error! 405 Not Allowed Handler.'
            ],
            'data' => null
        ];

        return $response->withJson($result);
    };
};

$app->get('/', IndexController::class.':index');

$app->get('/start/master-download', MasterController::class.':master_download');

$app->post('/account/signup', AccountController::class.':sign_up');
$app->post('/account/signin', AccountController::class.':sign_in');
$app->post('/account/signout', AccountController::class.':sign_out');
$app->post('/account/resign', AccountController::class.':resign');
$app->post('/account/password-reset', AccountController::class.':password_reset');
$app->post('/account/password-change', AccountController::class.':password_change');
$app->post('/account/edit', AccountController::class.':account_edit');
$app->get('/account/member-user-info', AccountController::class.':member_user_info');
$app->post('/account/line-cooperation', AccountController::class.':line_cooperation');

$app->post('/token/verify-token', TokenController::class.':verify_token');

$app->post('/group/create', GroupController::class.':group_create');
$app->post('/group/join', GroupController::class.':group_join');
$app->post('/group/edit', GroupController::class.':group_edit');
$app->get('/group/belong-group', GroupController::class.':belong_group');
$app->get('/group/belong-member', GroupController::class.':belong_member');
$app->post('/group/withdrawal', GroupController::class.':group_withdrawal');
$app->post('/group/withdrawal-force', GroupController::class.':group_withdrawal_force');
$app->post('/group/delete', GroupController::class.':group_delete');

$app->get('/timeline/get', TimelineController::class.':get_timeline');
$app->post('/timeline/post', TimelineController::class.':post_timeline');
$app->post('/timeline/delete', TimelineController::class.':delete_timeline');
$app->post('/timeline/comment/post', TimelineCommentController::class.':post_comment');
$app->get('/timeline/comment/get', TimelineCommentController::class.':get_comment');
$app->post('/timeline/comment/delete', TimelineCommentController::class.':delete_comment');

$app->get('/child/list', ChildController::class.':list_child');
$app->get('/child/get', ChildController::class.':get_child');
$app->post('/child/add', ChildController::class.':add_child');
$app->post('/child/edit', ChildController::class.':edit_child');
$app->post('/child/delete', ChildController::class.':delete_child');

$app->get('/child/growth/history/list', ChildGrowthHistoryController::class.':list_history');
$app->get('/child/growth/history/get', ChildGrowthHistoryController::class.':get_history');
$app->post('/child/growth/history/add', ChildGrowthHistoryController::class.':add_history');

$app->get('/child/growth/diary/get', ChildGrowthDiaryController::class.':get_diary');
$app->post('/child/growth/diary/post', ChildGrowthDiaryController::class.':post_diary');
$app->post('/child/growth/diary/delete', ChildGrowthDiaryController::class.':delete_diary');
$app->get('/child/growth/diary/comment/get', ChildGrowthDiaryCommentController::class.':get_comment');
$app->post('/child/growth/diary/comment/post', ChildGrowthDiaryCommentController::class.':post_comment');
$app->post('/child/growth/diary/comment/delete', ChildGrowthDiaryCommentController::class.':delete_comment');

$app->get('/child/face/get', ChildFaceController::class.':get_face');
$app->post('/child/face/add', ChildFaceController::class.':add_face');
$app->post('/child/face/delete', ChildFaceController::class.':delete_face');

$app->get('/child/friend/get', ChildFriendController::class.':get_friend');
$app->post('/child/friend/add', ChildFriendController::class.':add_friend');
$app->post('/child/friend/edit', ChildFriendController::class.':edit_friend');
$app->post('/child/friend/delete', ChildFriendController::class.':delete_friend');
$app->get('/child/friend/autofill', ChildFriendController::class.':autofill_friend');

$app->get('/child/friend/face/get', ChildFriendFaceController::class.':get_face');
$app->post('/child/friend/face/add', ChildFriendFaceController::class.':add_face');
$app->post('/child/friend/face/delete', ChildFriendFaceController::class.':delete_face');

$app->get('/album/get', AlbumController::class.':get_album');
$app->post('/album/upload', AlbumController::class.':upload_album');

$app->get('/calendar/get', CalendarController::class.':get_calendar');
$app->get('/calendar/search', CalendarController::class.':search_calendar');
$app->post('/calendar/add', CalendarController::class.':add_calendar');
$app->post('/calendar/edit', CalendarController::class.':edit_calendar');
$app->post('/calendar/delete', CalendarController::class.':delete_calendar');

$app->get('/model/get', ModelController::class.':get_model');
$app->post('/model/add/child', ModelController::class.':add_child');
$app->post('/model/add/clothes', ModelController::class.':add_clothes');
$app->post('/model/add/child/remove', ModelController::class.':add_child_remove');
$app->post('/model/add/clothes/remove', ModelController::class.':add_clothes_remove');

$app->post('/external-api/remove.bg/remove', RemoveBgController::class.':bg_remove');

try {
    $app->run();
} catch (Throwable $e) {
    echo $e;
}
