<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\ProductsController;
use App\Http\Controllers\Web\UsersController;
use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Auth;

Route::get('register', [UsersController::class, 'register'])->name('register');
Route::post('register', [UsersController::class, 'doRegister'])->name('do_register');
Route::get('login', [UsersController::class, 'login'])->name('login');
Route::post('login', [UsersController::class, 'doLogin'])->name('do_login');

// Social Login Routes
Route::get('auth/{provider}/redirect', [\App\Http\Controllers\Auth\SocialiteController::class, 'redirect'])->name('socialite.redirect');
Route::get('auth/{provider}/callback', [\App\Http\Controllers\Auth\SocialiteController::class, 'callback'])->name('socialite.callback');
Route::get('logout', [UsersController::class, 'doLogout'])->name('do_logout');
Route::get('users', [UsersController::class, 'list'])->name('users');
Route::middleware(['auth'])->group(function () {
    Route::get('profile/{user?}', [UsersController::class, 'profile'])->name('profile');
    Route::get('users/edit/{user?}', [UsersController::class, 'edit'])->name('users_edit');
    Route::post('users/save/{user}', [UsersController::class, 'save'])->name('users_save');
    Route::post('users/delete/{user}', [UsersController::class, 'delete'])->name('users_delete');
    Route::get('users/edit_password/{user?}', [UsersController::class, 'editPassword'])->name('edit_password');
    Route::post('users/save_password/{user}', [UsersController::class, 'savePassword'])->name('save_password');
});

Route::get('products', [ProductsController::class, 'list'])->name('products_list');
Route::get('products/edit/{product?}', [ProductsController::class, 'edit'])->name('products_edit');
Route::post('products/save/{product?}', [ProductsController::class, 'save'])->name('products.save');
Route::get('products/delete/{product}', [ProductsController::class, 'delete'])->name('products_delete');
Route::post('products/{product}/buy', [ProductsController::class, 'buy'])->name('products.buy');
Route::put('products/{product}/update-quantity', [ProductsController::class, 'updateQuantity'])->name('products.update_quantity');
Route::post('/products/return/{user_id}/{product_id}', [ProductsController::class, 'returnProduct'])->name('return_product');

/*
Route::middleware(['auth', 'role:Admin'])->group(function () {
    Route::get('/employees/create', [UsersController::class, 'createEmployee'])->name('employees.create');
    Route::post('/employees/store', [UsersController::class, 'storeEmployee'])->name('employees.store');
});
*/

Route::middleware(['auth'])->group(function () {
    // Route for listing customers
    Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');

    // Route to show a form to add credit to a customer
    Route::get('customers/{customer}/add-credit', [CustomerController::class, 'showAddCreditForm'])->name('customers.addCreditForm');
    Route::post('customers/{customer}/add-credit', [CustomerController::class, 'addCredit'])->name('customers.addCredit');
    Route::post('customers/{customer}/reset-credit', [CustomerController::class, 'resetCredit'])->name('customers.resetCredit');

    // Route for creating a new customer
    Route::get('customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');

    // Route for editing a customer
    Route::get('customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');

    // Route for deleting a customer
    Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/multable', function (Request $request) {
    $j = $request->number??5;
    $msg = $request->msg;
    return view('multable', compact("j", "msg"));
});

Route::get('/even', function () {
    return view('even');
});

Route::get('/prime', function () {
    return view('prime');
});

Route::get('/test', function () {
    return view('test');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Credit Management Routes
Route::middleware(['auth'])->group(function () {
    Route::post('/users/{user}/charge-credit', [App\Http\Controllers\Web\CreditController::class, 'chargeCredit'])
        ->name('users.charge-credit');
    Route::post('/users/{user}/reset-credit', [App\Http\Controllers\Web\CreditController::class, 'resetCredit'])
        ->name('users.reset-credit');
    Route::get('/credit/insufficient', [App\Http\Controllers\Web\CreditController::class, 'insufficientCredit'])
        ->name('credit.insufficient');
    Route::get('/credit/history', [App\Http\Controllers\Web\CreditController::class, 'transactionHistory'])
        ->name('credit.history');
});

Route::get('verify', [UsersController::class, 'verify'])->name('verify');

Route::get('auth/google', [UsersController::class, 'redirectToGoogle'])->name('redirectToGoogle');
Route::get('auth/google/callback', [UsersController::class, 'handleGoogleCallback'])->name('handleGoogleCallback');

Route::get('auth/facebook', [UsersController::class, 'redirectToFacebook'])->name('redirectToFacebook');
Route::get('auth/facebook/callback', [UsersController::class, 'handleFacebookCallback'])->name('handleFacebookCallback');
