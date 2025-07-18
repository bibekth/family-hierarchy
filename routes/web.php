<?php

use App\Http\Controllers\HierarchyController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('dashboard', function () {
    return redirect()->route('hierarchy.index');
})->name('dashboard');

Auth::routes();

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect('/home');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.resend');

Route::group(['middleware' => ['auth','verified']], function(){
    Route::get('/home', function(){
        return redirect()->route('hierarchy.index');
    })->name('home');
    Route::resource('hierarchy', HierarchyController::class);
    Route::get('hierarchy-search', [HierarchyController::class, 'search'])->name('hierarchy.search');
});

Route::get('/', [HierarchyController::class, 'main'])->name('main');
Route::get('/{id}', [HierarchyController::class, 'display'])->name('hierarchy.display');
