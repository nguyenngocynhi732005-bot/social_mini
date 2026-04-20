<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MainController;
use App\Http\Controllers\PostInteractionController;
use App\Http\Controllers\PostUploadController;
use App\Http\Controllers\SocialConnection\SearchController;
use App\Http\Controllers\SocialConnection\FriendshipController;
use App\Http\Controllers\SocialConnection\GroupController;
use App\Http\Controllers\SocialConnection\GroupMemberController;
use App\Http\Controllers\SocialConnection\NotificationController;
use Illuminate\Support\Facades\Auth;

// ========================================================================
// THÀNH VIÊN 1 (NHI): NEWSFEED, ADMIN & POSTS
// ========================================================================
Route::get('/', 'App\Http\Controllers\MainController@index')->name('newsfeed');
Route::get('/admin/dashboard', 'App\Http\Controllers\AdminController@index')->name('admin.dashboard');
Route::post('/post/store', 'App\Http\Controllers\PostController@store')->name('post.store');
Route::delete('/post/{post}', 'App\Http\Controllers\PostController@destroy')->name('post.destroy');
Route::post('/post/upload-chunk', [PostUploadController::class, 'uploadChunk'])->name('post.upload.chunk');
Route::post('/post/upload-complete', [PostUploadController::class, 'completeUpload'])->name('post.upload.complete');
Route::post('/post/{post}/reaction', [PostInteractionController::class, 'storeReaction'])->name('post.reactions.store');
Route::post('/post/{post}/comments', [PostInteractionController::class, 'storeComment'])->name('post.comments.store');
Route::put('/comments/{comment}', [PostInteractionController::class, 'updateComment'])->name('comments.update');
Route::delete('/comments/{comment}', [PostInteractionController::class, 'destroyComment'])->name('comments.destroy');
Route::post('/post/{post}/share', [PostInteractionController::class, 'share'])->name('post.share');

// Trang Video
Route::get('/videos', 'App\Http\Controllers\MainController@videos')->name('videos');
// Trang Bạn bè
Route::get('/friends', [FriendshipController::class, 'index'])->name('friends');

// Test page
Route::get('/test-buttons', function() {
    return view('pages.test-buttons');
})->name('test.buttons');

// Thêm dòng này để xử lý đăng Story
Route::post('/story/store', [MainController::class, 'storeStory'])->name('story.store');
Route::delete('/story/{story}', [MainController::class, 'destroyStory'])->name('story.destroy');
Route::get('/stories/snapshot', [MainController::class, 'storiesSnapshot'])->name('stories.snapshot');


// ========================================================================
// THÀNH VIÊN 2: AUTHENTICATION (Đăng nhập, Đăng ký)
// ========================================================================
// Tạm thời tắt chức năng đăng nhập/đăng ký để ưu tiên luồng tạo story.
Route::redirect('/login', '/')->name('login');
Route::redirect('/register', '/')->name('register');

Route::get('/dev-login/{id}', function ($id) {
    auth()->loginUsingId($id);
    return redirect('/friends');
});

Route::redirect('/social/friends', '/friends');


// ========================================================================
// THÀNH VIÊN 3: FRIENDS & SEARCH
// ========================================================================

// Tạm thời bỏ middleware(['auth']) để dễ test
Route::prefix('social')->name('social.')->group(function () {
    
    // Search
    Route::get('/search', [SearchController::class, 'index'])->name('search.index');
    Route::get('/search/autocomplete', [SearchController::class, 'autocomplete'])->name('search.autocomplete');

    // Friendship
    Route::post('/friends/request', [FriendshipController::class, 'sendRequest'])->name('friends.request');
    Route::post('/friends/cancel', [FriendshipController::class, 'cancelRequest'])->name('friends.cancel');
    Route::post('/friends/respond', [FriendshipController::class, 'respond'])->name('friends.respond');
    Route::delete('/friends/{user}', [FriendshipController::class, 'unfriend'])->name('friends.unfriend');
    Route::post('/friends/block', [FriendshipController::class, 'block'])->name('friends.block');
    Route::delete('/friends/block/{user}', [FriendshipController::class, 'unblock'])->name('friends.unblock');

    // Groups
    Route::get('/groups', [GroupController::class, 'index'])->name('groups.index');
    Route::post('/groups', [GroupController::class, 'store'])->name('groups.store');
    Route::get('/groups/{group}', [GroupController::class, 'show'])->name('groups.show');
    Route::put('/groups/{group}', [GroupController::class, 'update'])->name('groups.update');
    Route::delete('/groups/{group}', [GroupController::class, 'destroy'])->name('groups.destroy');
    Route::post('/groups/{group}/join', [GroupController::class, 'join'])->name('groups.join');
    Route::post('/groups/{group}/leave', [GroupController::class, 'leave'])->name('groups.leave');

    // Group members
    Route::post('/groups/{group}/members', [GroupMemberController::class, 'add'])->name('groups.members.add');
    Route::put('/groups/{group}/members/{user}', [GroupMemberController::class, 'updateRole'])->name('groups.members.role');
    Route::delete('/groups/{group}/members/{user}', [GroupMemberController::class, 'remove'])->name('groups.members.remove');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');
});

// ========================================================================
// THÀNH VIÊN 4: PROFILE & SETTINGS
// ========================================================================
Route::get('/profile/{id}', 'App\Http\Controllers\ProfileController@show')->name('profile.show');


// ========================================================================
// THÀNH VIÊN 5: REALTIME CHAT
// ========================================================================
Route::get('/chat/{id}', 'App\Http\Controllers\ChatController@index')->name('chat.index');