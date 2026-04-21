<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ChatRealtime\MessageController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostInteractionController;
use App\Http\Controllers\PostUploadController;
use App\Http\Controllers\SocialConnection\FriendshipController;
use App\Http\Controllers\SocialConnection\GroupController;
use App\Http\Controllers\SocialConnection\GroupMemberController;
use App\Http\Controllers\SocialConnection\GroupPostController;
use App\Http\Controllers\SocialConnection\NotificationController;
use App\Http\Controllers\SocialConnection\SearchController;
use App\ProfilePersonalization\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

Route::get('/newsfeed', [MainController::class, 'index'])->middleware('auth')->name('newsfeed');
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('newsfeed')
        : redirect()->route('login');
});
Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');

Route::post('/post/store', [PostController::class, 'store'])->name('post.store');
Route::delete('/post/{post}', [PostController::class, 'destroy'])->name('post.destroy');
Route::post('/post/upload-chunk', [PostUploadController::class, 'uploadChunk'])->name('post.upload.chunk');
Route::post('/post/upload-complete', [PostUploadController::class, 'completeUpload'])->name('post.upload.complete');
Route::post('/post/{post}/reaction', [PostInteractionController::class, 'storeReaction'])->name('post.reactions.store');
Route::post('/post/{post}/comments', [PostInteractionController::class, 'storeComment'])->name('post.comments.store');
Route::put('/comments/{comment}', [PostInteractionController::class, 'updateComment'])->name('comments.update');
Route::delete('/comments/{comment}', [PostInteractionController::class, 'destroyComment'])->name('comments.destroy');
Route::post('/post/{post}/share', [PostInteractionController::class, 'share'])->name('post.share');
Route::patch('/api/posts/{post}/privacy', [PostController::class, 'updatePrivacy'])->name('post.privacy.update');

Route::get('/videos', [MainController::class, 'videos'])->name('videos');
Route::get('/friends', [MainController::class, 'friends'])->name('friends');

Route::get('/test-buttons', function () {
    return View::make('pages.test-buttons');
})->name('test.buttons');

Route::post('/story/store', [MainController::class, 'storeStory'])->name('story.store');
Route::delete('/story/{story}', [MainController::class, 'destroyStory'])->name('story.destroy');
Route::get('/stories/snapshot', [MainController::class, 'storiesSnapshot'])->name('stories.snapshot');

Route::get('/search', [SearchController::class, 'index'])->name('search.friends');
Route::get('/social/search', [SearchController::class, 'index'])->name('social.search.index');

Route::get('/profile', [ProfileController::class, 'index'])->name('profile.personalization');
Route::get('/profile/{id}', [ProfileController::class, 'show'])->whereNumber('id')->name('profile.show');
Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.personalization.avatar');
Route::post('/profile/update-images', [ProfileController::class, 'updateImages'])->name('profile.personalization.update-images');
Route::post('/api/profile/update-account', [ProfileController::class, 'updateAccount'])->name('profile.personalization.update-account');
Route::post('/api/profile/update-intro', [ProfileController::class, 'updateIntro'])->name('profile.personalization.update-intro');
Route::post('/api/profile/update-details', [ProfileController::class, 'updateDetails'])->name('api.profile.update-details');
Route::get('/profile/activity-log', [ProfileController::class, 'activityLog'])->name('profile.personalization.activity-log');
Route::post('/profile/logout', [ProfileController::class, 'logout'])->name('profile.personalization.logout');

Route::get('/chat/{id}', function ($id) {
    return redirect()->route('newsfeed');
})->whereNumber('id')->name('chat.index');

Route::prefix('social')->name('social.')->group(function () {
    Route::get('/friends', [FriendshipController::class, 'index'])->name('friends.index');
    Route::post('/friends/request', [FriendshipController::class, 'sendRequest'])->name('friends.request');
    Route::post('/friends/cancel', [FriendshipController::class, 'cancelRequest'])->name('friends.cancel');
    Route::post('/friends/respond', [FriendshipController::class, 'respond'])->name('friends.respond');
    Route::delete('/friends/{user}', [FriendshipController::class, 'unfriend'])->name('friends.unfriend');
    Route::post('/friends/block', [FriendshipController::class, 'block'])->name('friends.block');
    Route::delete('/friends/unblock/{user}', [FriendshipController::class, 'unblock'])->name('friends.unblock');

    Route::get('/search/autocomplete', [SearchController::class, 'autocomplete'])->name('search.autocomplete');
    Route::get('/search', [SearchController::class, 'index'])->name('search.index');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    Route::get('/groups', [GroupController::class, 'index'])->name('groups.index');
    Route::post('/groups', [GroupController::class, 'store'])->name('groups.store');
    Route::get('/groups/{group}', [GroupController::class, 'show'])->name('groups.show');
    Route::post('/groups/{group}/join', [GroupController::class, 'join'])->name('groups.join');
    Route::post('/groups/{group}/leave', [GroupController::class, 'leave'])->name('groups.leave');
    Route::put('/groups/{group}', [GroupController::class, 'update'])->name('groups.update');
    Route::delete('/groups/{group}', [GroupController::class, 'destroy'])->name('groups.destroy');

    Route::post('/groups/{group}/members', [GroupMemberController::class, 'add'])->name('groups.members.add');
    Route::patch('/groups/{group}/members/{user}', [GroupMemberController::class, 'updateRole'])->name('groups.members.update');
    Route::delete('/groups/{group}/members/{user}', [GroupMemberController::class, 'remove'])->name('groups.members.remove');

    Route::post('/groups/{group}/posts', [GroupPostController::class, 'store'])->name('groups.posts.store');
});

Route::get('/chat/test', function () {
    return View::make('chat-realtime.test');
})->middleware('auth')->name('chat.test');

Route::prefix('chat')->name('chat.')->middleware('auth')->group(function () {
    Route::get('/conversations', [MessageController::class, 'conversations'])->name('conversations.index');
    Route::get('/conversations/{conversation}/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::post('/conversations/{conversation}/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::delete('/conversations/{conversation}/messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');
    Route::post('/conversations/{conversation}/read', [MessageController::class, 'markAsRead'])->name('conversations.read');
    Route::post('/conversations/{conversation}/typing', [MessageController::class, 'typingStatus'])->name('conversations.typing');
    Route::get('/conversations/{conversation}/typing/latest', [MessageController::class, 'latestTypingStatus'])->name('conversations.typing.latest');
    Route::post('/conversations/{conversation}/background', [MessageController::class, 'updateBackground'])->name('background.update');
    Route::post('/conversations/{conversation}/call-signal', [MessageController::class, 'signalCall'])->name('calls.signal');
    Route::get('/conversations/{conversation}/call-signal/latest', [MessageController::class, 'latestCallSignal'])->name('calls.latest');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

require __DIR__ . '/auth.php';
