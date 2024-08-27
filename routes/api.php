<?php

use App\Models\User;
use Pusher\PusherSecurity;
use Illuminate\Http\Request;
use App\Models\Conversatione;
use App\Models\NotifcationProject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BidController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\UserController;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\DisputeController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\BalancesController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\PaymentsController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\ChatOrderController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\ChatProjectController;
use App\Http\Controllers\IssueReportController;
use App\Http\Controllers\WithdrawalsController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TransactionsController;
use App\Http\Controllers\ConversationeController;
use App\Http\Controllers\MessageProjectController;
use App\Http\Controllers\CategoryProjectsController;
use App\Http\Controllers\IdentityVerificationController;
use App\Http\Controllers\NotificationAcceptBidController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::get('/auth/redirect', function () {
    return Socialite::driver('google')->stateless()->redirect()->getTargetUrl();
});
Route::get('/auth/google/callback/', function () {
    try {
        $user = Socialite::driver('google')->stateless()->user();
        $findUser = User::where('social_id', $user->id)->first();
        if ($findUser) {
            Auth::login($findUser);
            return response()->json($findUser);
        } else {
            $newUser = User::create([
                'firstName' => $user->getName(),
                'lastName' =>$user->getNickname(),
                'email' =>    $user->getEmail(),
                'password' => Hash::make('my-google'),
                'social_id' =>$user->getId(),
                'social_type' => 'google',
            ]);
            Auth::login($newUser);
            return response()->json($newUser);
        }
    } catch (ClientException $e) {
        return response()->json([
            'error' => 'Invalid credential Provided'
        ], 442);
    }
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('emailverify', [AuthController::class, 'emailverify']);
Route::post('forgotpassword', [AuthController::class, 'forgotpassword']);
Route::post('verify-email',[AuthController::class, 'verifyEmail']);
Route::post('/password/reset', [AuthController::class, 'reset'])
    ->middleware('guest');

    Route::get('/services' ,[ServicesController::class,'index']);
    Route::get('/users', [AuthController::class, 'index']);
    Route::get('/users/{id}', [AuthController::class, 'show']);
    Route::get('/get/{id}/rating', [RatingController::class, 'getRatingUser']);
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/categories' ,[CategoryController::class,'index']);
    Route::get('/service/{id}' ,[ServicesController::class,'show']);
    Route::get('/services/{name}/search', [ServicesController::class,'search']);
    Route::get('/bids', [BidController::class, 'index']);
Route::get('bid/{id}', [BidController::class, 'show']);
    Route::get('/getUserServices/{user_id}' ,[ServicesController::class,'getUserServices']);
Route::get('/Services-with-ratings/{id}' ,[ServicesController::class,'sellerServicesWithRatings']);
Route::get('/contracts/getServiceAsCompleted', [ContractController::class, 'getServiceTypeSales']);
Route::get('/projects/{id}', [ProjectController::class, 'show']);
Route::get('/show-user-projects/{id}', [ProjectController::class, 'freelancerShowProject']);
Route::get('/projects/{id}/status-delivery', [ProjectController::class, 'GetStatusDelivery']);
Route::post('/create-conversion', [ConversationeController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/changepassword', [AuthController::class, 'changepassword']);
Route::get('/user', [AuthController::class, 'user']);


Route::post('/users/{id}', [AuthController::class, 'update']);
Route::delete('/users/{id}',[AuthController::class, 'destroy']);
Route::post('/ban-user/{id}',[AuthController::class, 'banUser']);
Route::get('/get-user-blocked',[AuthController::class, 'getBlockedUser']);
Route::post('/user-unblocked/{id}',[AuthController::class, 'unBanUser']);
Route::post('/identity-verifications', [IdentityVerificationController::class, 'store']);
Route::get('/identity-verifications', [IdentityVerificationController::class, 'index']);
Route::post('/identity-verifications/{id}/status', [IdentityVerificationController::class, 'updateStatus']);


Route::get('/projects/pending', [ProjectController::class, 'projectPending']);

Route::get('/get-acceptedBids-Contract', [ProjectController::class, 'IndexAcceptedBids']);
Route::post('/projects', [ProjectController::class, 'store']);
Route::put('/projects/{id}/approve', [ProjectController::class, 'approveProject']);
Route::put('/projects/{id}/reject', [ProjectController::class, 'rejectProject']);
Route::post('/projects/{project}/accept-bid', [ProjectController::class, 'acceptBid']);
Route::post('/projects/{id}/request-delivery', [ProjectController::class, 'requestDelivery']);

Route::post('/projects/{id}/approve-delivery', [ProjectController::class, 'approveDelivery']);

Route::get('/get-bids-between-client-freelancer/{id}', [ProjectController::class, 'getAcceptedBids']);
Route::post('/projects/{id}/bids', [BidController::class, 'store']);
Route::put('bids/{id}', [BidController::class, 'update']);
Route::delete('bids/{id}', [BidController::class, 'destroy']);

////////////////////////ServicesController//////////////////////////

Route::get('/services/pending' ,[ServicesController::class,'getServicesPending']);

Route::post('/edite/{id}/service' ,[ServicesController::class,'update']);
Route::post('/service/{id}/status' ,[ServicesController::class,'approve']);
Route::post('/add-service' ,[ServicesController::class,'store']);

/////////////////////////CategoryController///////////////

Route::get('/services/{categories}', [CategoryController::class, 'index']);
Route::post('/store-category', [CategoryController::class, 'store']);
Route::post('/categories/{id}',  [CategoryController::class, 'update']);
Route::delete('/categories/{id}', [CategoryController::class, 'destroy'] );


Route::get('/contracts/{id}/{buyerID}/{sellerID}', [ContractController::class, 'showContractService']);
Route::post('/contracts/{contractsId}', [ContractController::class, 'update']);
Route::post('/sendOrder', [ContractController::class, 'getEventForApproveOrReject']);
//////////////////////Feedback and Ratings//////////////////
Route::get('feedback', [FeedbackController::class, 'index']);
Route::get('feedback/{id}', [FeedbackController::class, 'show']);
Route::post('feedback', [FeedbackController::class, 'store']);
Route::put('feedback/{id}', [FeedbackController::class, 'update']);
Route::delete('feedback/{id}', [FeedbackController::class, 'destroy']);
Route::get('/rating/{id}', [RatingController::class, 'getRatings']);

Route::get('contracts/{contractId}/feedbacks', [FeedbackController::class, 'getFeedbacksByContract']);
/////////////////محادثات المشاريع////////
Route::post('/conversationes/{id}/acceptBid', [ConversationeController::class, 'ConversationesacceptBid']);
Route::post('/conversationes/{conversation}/messages', [ConversationeController::class, 'addMessage']);
Route::get('/conversationes/{conversation}/messages', [ConversationeController::class, 'getMessages']);
Route::get('/getNotification', [ConversationeController::class, 'getNotification']);
Route::get('/getConversion/{id}', [ConversationeController::class, 'getConversion']);
Route::post('/notification/{id}/mark-as-read', [ConversationeController::class, 'markAsRead']);
Route::get('/conversations', [ConversationeController::class, 'Conversionindex']);
// Route::post('/store-message-contract', [ChatOrderController::class, 'store']);
// Route::get('/get-message-contract', [ChatOrderController::class, 'getMessageContract']);
Route::get('/reports', [IssueReportController::class, 'index']);
Route::get('/show/{id}/issue', [IssueReportController::class, 'show']);
Route::post('/reports/reply/{conversationId}', [IssueReportController::class, 'reply']);
Route::apiResource('disputes', DisputeController::class);
Route::post('/issue-report', [IssueReportController::class, 'store']);
// Route::delete('/conversations/{id}', [ConversationController::class, 'deleteIfEmpty']);
Route::get('/disputes',[DisputeController::class ,'index']);
Route::post('disputes/{id}/resolve', [DisputeController::class, 'resolve']);
Route::post('/pusher/auth', [ContractController::class, 'auth'])->name('pusher.auth');
Route::post('/ratings', [RatingController::class, 'storeRating']);
Route::post('/comments', [RatingController::class, 'storeComment']);


Route::get('/service/{serviceId}/ratings-comments', [ServicesController::class, 'getServiceRatingsComments']);
Route::get('/project/{projectId}/ratings-comments', [ProjectController::class, 'getProjectRatingsComments']);



// Route::get('/notification/{sellerID}', [NotificationController::class, 'show']);


// Route::post('/create/notifications', [NotificationController::class, 'store']);
Route::post('portfolio', [PortfolioController::class, 'store'])->name('portfolio.store');

// Route::post('/conversations/{conversation}/messages', [ConversationController::class, 'storeMessage']);
// Route::get('/message/{id}', [ChatController::class, 'index']);

Route::get('/contracts', [ContractController::class, 'index'])->name('contracts.index');

Route::get('/contracts/create', [ContractController::class, 'create'])->name('contracts.create');

Route::post('/contracts', [ContractController::class, 'store'])->name('contracts.store');

Route::get('/contracts/{contract}', [ContractController::class, 'show']);
Route::get('/contracts/{id}/contracts', [ContractController::class, 'showContractOrder']);

Route::get('/contracts/{contract}/edit', [ContractController::class, 'edit'])->name('contracts.edit');

Route::put('/contracts/{contract}', [ContractController::class, 'update'])->name('contracts.update');

Route::delete('/contracts/{contract}', [ContractController::class, 'destroy'])->name('contracts.destroy');
/////////////////////Payments///////////////
Route::get('/payments', [PaymentsController::class, 'index']);
Route::post('/payment/process', [PaymentsController::class, 'process']);


/////////////////Balance/////////////////

Route::get('/balance', [BalancesController::class, 'getUserBalance']);
Route::post('/balance', [BalancesController::class, 'updateBalance']);

Route::post('/transactions', [TransactionsController::class, 'store']);

Route::get('/transactions', [TransactionsController::class, 'getUserTransactions']);

Route::get('/withdrawals', [WithdrawalsController::class, 'index']);
Route::post('/withdraw', [WithdrawalsController::class, 'process']);
Route::post('/withdrawal-requests/{id}/approve', [WithdrawalsController::class, 'approve']);
Route::post('/withdrawal-requests/{id}/reject', [WithdrawalsController::class, 'reject']);


// Route::get('/categories-project/',  [CategoryProjectsController::class, 'index']);
Route::get('/chats-project-from-notification/{id}', [ConversationeController::class, 'showChatFromNotification']);
// Route::post('/messages-project', [MessageProjectController::class, 'store']);
Route::get('/get-message-notification-projects',  [NotificationAcceptBidController::class, 'index']);

});
Broadcast::routes(['middleware' => ['auth:sanctum']]);
Route::get('/filter-services/', [ServicesController::class, 'filterServices']); // Filter services by category
Route::get('/filter-services/{categoryID}', [ServicesController::class, 'filterServicesCategory']);
Route::post('/delete/{id}/service', [ServicesController::class, 'destroy']);
// Route::post('/add-service' ,[ServicesController::class,'store']);

Route::apiResource('portfolio', PortfolioController::class)->except('store');
Route::post('portfolio/{id}', [PortfolioController::class, 'update']);
Route::post('/users/{id}/update',[AuthController::class, 'UpdateUSerByAdmin']);

// Route::get('/show-user' ,[UserController::class,'index']);

Route::get('portfolio/{id}/details', [PortfolioController::class, 'showDetails'])->name('portfolio.show-details');
