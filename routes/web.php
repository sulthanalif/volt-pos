<?php

use App\Http\Controllers\LogoutController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Volt::route('/', 'index')->name('index');

Route::group(['middleware' => 'guest'], function () {
    Volt::route('/login', 'login')->name('login');
});

Route::group(['middleware' => ['auth', 'userAccessLog']], function () {
    Route::get('/logout', LogoutController::class)->name('logout');

    Volt::route('/dashboard', 'dashboard')->name('dashboard');

    Volt::route('/users', 'users.index')->name('users');

    Volt::route('/roles', 'settings.roles.index')->middleware('can:manage-roles')->name('roles');

    Volt::route('/permissions', 'settings.permissions.index')->middleware('can:manage-permissions')->name('permissions');

    Volt::route('/categories', 'categories.index')->middleware('can:manage-categories')->name('categories');
    Volt::route('/additions', 'additions.index')->middleware('can:manage-additions')->name('additions');
    Volt::route('/units', 'units.index')->middleware('can:manage-units')->name('units');
    Volt::route('/suppliers', 'suppliers.index')->middleware('can:manage-suppliers')->name('suppliers');
    Volt::route('/products', 'products.index')->middleware('can:manage-products')->name('products');

    Volt::route('/tables', 'tables.index')->middleware('can:manage-tables')->name('tables');

});
