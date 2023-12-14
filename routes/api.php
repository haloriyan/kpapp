<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => "user"], function () {
    Route::post('login', "UserController@login");
    Route::post('register', "UserController@register");
    Route::post('logout', "UserController@logout");

    Route::get('/', "UserController@retrieve");
});

Route::group(['prefix' => "category"], function () {
    Route::post('create', "CategoryController@create");
    Route::post('delete', "CategoryController@delete");
    Route::get('/', "CategoryController@get");
});
Route::group(['prefix' => "coupon"], function () {
    Route::post('create', "CouponController@create");
    Route::post('delete', "CouponController@delete");
    Route::post('/', "CouponController@get");
});

Route::group(['prefix' => "course"], function () {
    Route::post('create', "CourseController@create");
    Route::post('search', "CourseController@search");

    Route::group(['prefix' => "{id}/media"], function () {
        Route::post('store', "MediaController@store");
        Route::post('delete', "MediaController@delete");
        Route::get('/', "MediaController@getByCourse");
    });

    Route::group(['prefix' => "{id}/material"], function () {
        Route::post('store', "MaterialController@store");
        Route::post('delete', "MaterialController@delete");
        Route::get('/', "MaterialController@getByCourse");
    });
    
    Route::group(['prefix' => "{id}"], function () {
        Route::post('update', "CourseController@update");
        Route::post('delete', "CourseController@delete");
        Route::post('enroll', "CourseController@enroll");
        Route::post('dashboard', "CourseController@dashboard");
        Route::post('/', "CourseController@getByID");
    });

    Route::post('/', "CourseController@get");
});

Route::group(['prefix' => "page"], function () {
    Route::post('home', "PageController@home");
    Route::post('category', "PageController@category");
    Route::post('my-course', "PageController@myCourse");
    Route::post('learn', "PageController@learn");
    Route::get('stream/{materialID}', "PageController@stream");
});