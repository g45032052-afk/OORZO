<?php

use Illuminate\Support\Facades\Route;

use Modules\AI\app\Http\Controllers\Api\ProductAutoFillController;
use Modules\AI\app\Http\Controllers\Api\V1\AiChatController;
use Modules\AI\app\Http\Middleware\AiChatEnabled;
use Modules\AI\app\Http\Middleware\AiChatUsageLimit;

/*
| Customer-facing AI chat assistant (food-only). Optional auth via
| apiGuestCheck (logged-in customers or guest_id), gated on the admin
| `ai_chat_status` toggle (AiChatEnabled → 503 when off).
|
| The routes are NAMED because rateLimiter keys its bucket on the route name;
| unnamed routes all share one empty-named bucket, so a limit set here would be
| spent by traffic to an unrelated endpoint.
|
| send() is the only route that costs money - it is the one that reaches the
| provider - so it carries both guards. The others read or delete rows the
| caller already owns and only need the burst limiter.
*/
Route::group(['prefix' => 'customer/ai-chat', 'as' => 'api.v1.customer.ai-chat.', 'middleware' => ['apiGuestCheck', AiChatEnabled::class, 'rateLimiter']], function () {
    Route::post('send', [AiChatController::class, 'send'])->name('send')->middleware(AiChatUsageLimit::class);
    Route::get('conversations', [AiChatController::class, 'conversations'])->name('conversations');
    Route::get('messages', [AiChatController::class, 'messages'])->name('messages');
    Route::delete('conversations/{id}', [AiChatController::class, 'deleteConversation'])->name('conversations.delete');
});

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

Route::group(['prefix' => 'ai', 'as' => 'ai.','middleware'=>['vendor.api']], function () {
    // Route::get('generate-food-data', [ProductAutoFillController::class, 'getData']);
    Route::get('generate-title-and-description', [ProductAutoFillController::class, 'getTitleAndDescription']);
    Route::get('generate-other-data', [ProductAutoFillController::class, 'getOtherData']);
    Route::get('generate-variation-data', [ProductAutoFillController::class, 'getVariationData']);
    Route::get('generate-title-suggestions', [ProductAutoFillController::class, 'generateTitleSuggestions']);
    Route::post('generate-form-image', [ProductAutoFillController::class, 'analyzeImageAutoFill']);
});
