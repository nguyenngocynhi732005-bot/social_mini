<?php

use Illuminate\Support\Facades\Route;
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

Route::get('/', [MainController::class, 'index'])->name('newsfeed');

Route::redirect('/admin/dashboard', '/')->name('admin.dashboard');

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

Route::post('/story/store', [MainController::class, 'storeStory'])->name('story.store');
Route::delete('/story/{story}', [MainController::class, 'destroyStory'])->name('story.destroy');
Route::get('/stories/snapshot', [MainController::class, 'storiesSnapshot'])->name('stories.snapshot');

Route::redirect('/login', '/')->name('login');
Route::redirect('/register', '/')->name('register');

Route::get('/search', [FriendshipController::class, 'index'])->name('search.friends');

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
})->name('chat.index');

Route::prefix('social')->name('social.')->group(function () {
    Route::get('/friends', [FriendshipController::class, 'index'])->name('friends.index');
    Route::post('/friends/request', [FriendshipController::class, 'sendRequest'])->name('friends.request');
    Route::post('/friends/cancel', [FriendshipController::class, 'cancelRequest'])->name('friends.cancel');
    Route::post('/friends/respond', [FriendshipController::class, 'respond'])->name('friends.respond');
    Route::delete('/friends/{user}', [FriendshipController::class, 'unfriend'])->name('friends.unfriend');
    Route::post('/friends/block', [FriendshipController::class, 'block'])->name('friends.block');
    Route::delete('/friends/unblock/{user}', [FriendshipController::class, 'unblock'])->name('friends.unblock');

    Route::get('/search', [SearchController::class, 'index'])->name('search.index');
    Route::get('/search/autocomplete', [SearchController::class, 'autocomplete'])->name('search.autocomplete');

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
