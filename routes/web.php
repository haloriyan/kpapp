<?php

use Illuminate\Support\Facades\Route;

Route::get('info', function () {
    phpinfo();
});
Route::get('/', function () {
    return bcrypt('inikatasandi');
});
