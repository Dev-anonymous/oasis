<?php

use App\Http\Controllers\api\PayementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 'API APP';
});

Route::get('/payment-callback/{cb_code?}', [PayementController::class, 'payCallBack'])->name('payment.callback.web');
