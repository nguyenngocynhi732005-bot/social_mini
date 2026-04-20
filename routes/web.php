<?php

use Illuminate\Support\Facades\Route;
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
Route::get('/chat/{id}', 'App\Http\Controllers\ChatController@index')->name('chat.index');