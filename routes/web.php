<?php

use App\Http\Controllers\HierarchyController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('dashboard', function () {
    return redirect()->route('hierarchy.index');
})->name('dashboard');

Auth::routes();

Route::group(['middleware' => 'auth'], function(){
    // Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/home', function(){
        return redirect()->route('hierarchy.index');
    })->name('home');
    Route::resource('hierarchy', HierarchyController::class);
    Route::get('hierarchy-search', [HierarchyController::class, 'search'])->name('hierarchy.search');
});

Route::get('/', [HierarchyController::class, 'main'])->name('main');
Route::get('/{id}', [HierarchyController::class, 'display'])->name('hierarchy.display');
