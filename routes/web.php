<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\MainController;
use App\Http\Controllers\PostInteractionController;
use App\Http\Controllers\PostUploadController;


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
Route::get('/friends', 'App\Http\Controllers\MainController@friends')->name('friends');

// Test page
Route::get('/test-buttons', function () {
    return View::make('pages.test-buttons');
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


// ========================================================================
// THÀNH VIÊN 3: FRIENDS & SEARCH
// ========================================================================
Route::get('/search', 'App\Http\Controllers\FriendshipController@search')->name('search.friends');


// ========================================================================
// THÀNH VIÊN 4: PROFILE & SETTINGS
// ========================================================================
Route::get('/profile/{id}', 'App\Http\Controllers\ProfileController@show')->name('profile.show');


// ========================================================================
// THÀNH VIÊN 5: REALTIME CHAT
// ========================================================================
use App\Http\Controllers\ChatRealtime\MessageController;

// Route để mở trang giao diện test
Route::get('/chat/test', function () {
    return View::make('chat-realtime.test');
})->name('chat.test');

// Nhóm các Route liên quan đến Chat
Route::prefix('chat')->name('chat.')->group(function () {

    // 0. Lấy danh sách hội thoại để render sidebar chat
    Route::get('/conversations', [MessageController::class, 'conversations'])->name('conversations.index');

    // 1. Lấy tin nhắn (GET) - Bạn đã có
    Route::get('/conversations/{conversation}/messages', [MessageController::class, 'index'])->name('messages.index');

    // 2. GỬI TIN NHẮN (POST) - BẠN ĐANG THIẾU DÒNG NÀY
    Route::post('/conversations/{conversation}/messages', [MessageController::class, 'store'])->name('messages.store');

    // 3. THU HỒI TIN NHẮN
    Route::delete('/conversations/{conversation}/messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');

    // 4. ĐỔI/XÓA ẢNH NỀN CUỘC TRÒ CHUYỆN
    Route::post('/conversations/{conversation}/background', [MessageController::class, 'updateBackground'])->name('background.update');
});
