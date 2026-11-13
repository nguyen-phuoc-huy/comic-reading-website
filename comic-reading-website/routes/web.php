<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ComicController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\ChapterDownloadController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\GoogleController;

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ComicController as AdminComicController;
use App\Http\Controllers\Admin\ChapterController as AdminChapterController;
use App\Http\Controllers\Admin\ChapterImageController as AdminChapterImageController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\AuthorController as AdminAuthorController;
use App\Http\Controllers\Admin\CommentController as AdminCommentController;
use App\Http\Controllers\Admin\ImportController as AdminImportController;
use App\Http\Controllers\Admin\UserController as AdminUserController;

/*
|--------------------------------------------------------------------------
| Trả ảnh trong storage/app/public
| (dùng khi website chạy không qua thư mục public, nên storage:link không có tác dụng)
| Nhớ đặt 'serve' => false cho ổ đĩa 'local' trong config/filesystems.php
|--------------------------------------------------------------------------
*/
Route::get('storage/{path}', function (string $path) {
    abort_if(str_contains($path, '..'), 404);

    $disk = Storage::disk('public');
    abort_unless($disk->exists($path), 404);

    return response()->file($disk->path($path), [
        'Cache-Control' => 'public, max-age=0, must-revalidate',
    ]);
})->where('path', '.*')->name('storage.serve');

/*
|--------------------------------------------------------------------------
| Routes dành cho người đọc (công khai)
|--------------------------------------------------------------------------
*/

// Trang chủ
Route::get('/', [HomeController::class, 'index'])->name('home');

// Laravel UI chuyển hướng về /home sau khi đăng nhập/đăng ký: đưa về trang chủ.
// Dùng redirect()->route() (không dùng Route::redirect) để địa chỉ có kèm thư mục con của WAMP.
Route::get('/home', function () {
    // Quản trị viên vào thẳng trang quản trị, người dùng thường về trang chủ
    return auth()->check() && auth()->user()->role === 'admin'
        ? redirect()->route('admin.dashboard')
        : redirect()->route('home');
});

// Danh sách truyện (lọc theo trạng thái, sắp xếp...)
Route::get('/truyen', [ComicController::class, 'index'])->name('comics.index');

// Chi tiết truyện
Route::get('/truyen/{comic:slug}', [ComicController::class, 'show'])->name('comics.show');

// Đọc chương (Laravel tự giới hạn chapter thuộc comic nhờ scoped binding)
Route::get('/truyen/{comic:slug}/chap-{chapter:number}', [ChapterController::class, 'show'])
    ->where('chapter', '[0-9]+(\.[0-9]+)?')
    ->name('chapters.show');

// Tải chương về dạng PDF (giới hạn 10 lần / phút để tránh bị lạm dụng)
Route::get('/truyen/{comic:slug}/chap-{chapter:number}/tai-pdf', ChapterDownloadController::class)
    ->where('chapter', '[0-9]+(\.[0-9]+)?')
    ->middleware('throttle:10,1')
    ->name('chapters.download');

// Thể loại
Route::get('/the-loai', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/the-loai/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');

// Tìm kiếm
Route::get('/tim-kiem', [SearchController::class, 'index'])->name('search');

/*
|--------------------------------------------------------------------------
| Routes cần đăng nhập
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Hồ sơ cá nhân: xem, cập nhật tên/email, đổi mật khẩu
    Route::get('/ho-so', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/ho-so', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/ho-so/mat-khau', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Theo dõi truyện
    Route::get('/theo-doi', [FollowController::class, 'index'])->name('follows.index');
    Route::post('/truyen/{comic}/follow', [FollowController::class, 'toggle'])->name('follow.toggle');

    // Lịch sử đọc
    Route::get('/lich-su', [HistoryController::class, 'index'])->name('history.index');
    Route::delete('/lich-su/{history}', [HistoryController::class, 'destroy'])->name('history.destroy');
    Route::delete('/lich-su', [HistoryController::class, 'clear'])->name('history.clear');

    // Bình luận
    Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
});

/*
|--------------------------------------------------------------------------
| Routes dành cho Admin
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin'])
    ->group(function () {

        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Truyện
        Route::resource('comics', AdminComicController::class)->except(['show']);

        // Chương (lồng trong truyện, shallow: edit/update/destroy không cần comic id)
        Route::resource('comics.chapters', AdminChapterController::class)
            ->except(['show'])
            ->shallow();

        // Quản lý ảnh của chương: kéo thả thứ tự, đổi tên, thay ảnh, xoay, đánh số lại
        Route::patch('chapters/{chapter}/images/reorder', [AdminChapterImageController::class, 'reorder'])->name('chapters.images.reorder');
        Route::post('chapters/{chapter}/images/renumber', [AdminChapterImageController::class, 'renumber'])->name('chapters.images.renumber');
        Route::put('chapters/{chapter}/images/{image}/rename', [AdminChapterImageController::class, 'rename'])->name('chapters.images.rename');
        Route::post('chapters/{chapter}/images/{image}/replace', [AdminChapterImageController::class, 'replace'])->name('chapters.images.replace');
        Route::post('chapters/{chapter}/images/{image}/rotate', [AdminChapterImageController::class, 'rotate'])->name('chapters.images.rotate');

        // Chuẩn hóa tên file + thư mục ảnh của một truyện
        Route::post('comics/{comic}/organize', [AdminComicController::class, 'organize'])->name('comics.organize');

        // Xóa 1 ảnh trong chương
        Route::delete('chapters/{chapter}/images/{image}', [AdminChapterController::class, 'destroyImage'])
            ->name('chapters.images.destroy');

        // Thể loại, tác giả
        Route::resource('categories', AdminCategoryController::class)->except(['show']);
        Route::resource('authors', AdminAuthorController::class)->except(['show']);

        // Quản lý bình luận
        Route::get('comments', [AdminCommentController::class, 'index'])->name('comments.index');
        Route::delete('comments/{comment}', [AdminCommentController::class, 'destroy'])->name('comments.destroy');

        // Quản lý người dùng
        Route::resource('users', AdminUserController::class)->except(['show']);

        // Nhập dữ liệu từ Excel
        Route::get('imports', [AdminImportController::class, 'index'])->name('imports.index');
        Route::post('imports/{type}', [AdminImportController::class, 'store'])->name('imports.store');
        Route::get('imports/{type}/template', [AdminImportController::class, 'template'])->name('imports.template');
    });

/*
|--------------------------------------------------------------------------
| Routes đăng nhập / đăng ký / quên mật khẩu (Laravel UI)
|--------------------------------------------------------------------------
*/
// Đăng nhập bằng Google
Route::middleware('guest')->group(function () {
    Route::get('auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
    Route::get('auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');
});

Auth::routes();