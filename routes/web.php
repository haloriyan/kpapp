<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('info', function () {
    phpinfo();
});
Route::get('command', function () {
    return view('command');
});

Route::group(['prefix' => "export"], function () {
    Route::get('enroll', "ExportController@enroll");
    Route::get('user', "ExportController@user");
    Route::get('certificate/{enrollID}', "ExportController@certificate");
    Route::get('certificate/{courseID}/preview', "ExportController@certificatePreview");
    Route::get('coupon', "ExportController@coupon");
});

Route::get('document/{path}', "ExportController@viewDocument")->middleware('NoCors');
