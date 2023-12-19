<?php

use Illuminate\Support\Facades\Route;

Route::get('info', function () {
    phpinfo();
});
Route::get('/', function () {
    return bcrypt('123456');
});

Route::group(['prefix' => "export"], function () {
    Route::get('enroll', "ExportController@enroll");
    Route::get('user', "ExportController@user");
    Route::get('certificate/{enrollID}', "ExportController@certificate");
    Route::get('certificate/{courseID}/preview', "ExportController@certificatePreview");
});
