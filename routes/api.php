<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => "user"], function () {
    Route::post('login', "UserController@login");
    Route::post('register', "UserController@register");
    Route::post('logout', "UserController@logout");
    Route::post('update', "UserController@update");
    Route::post('auth', "UserController@auth");

    Route::get('/', "UserController@retrieve");
});

Route::group(['prefix' => "category"], function () {
    Route::post('create', "CategoryController@create");
    Route::post('delete', "CategoryController@delete");
    Route::get('/', "CategoryController@get");
});
Route::group(['prefix' => "coupon"], function () {
    Route::post('create', "CouponController@create");
    Route::post('generate-mass', "CouponController@generateMass");
    Route::post('delete', "CouponController@delete");
    Route::post('/', "CouponController@get");
});

Route::group(['prefix' => "course"], function () {
    Route::post('create', "CourseController@create");
    Route::post('search', "CourseController@search");

    Route::group(['prefix' => "{id}/event"], function () {
        Route::post('/', "EventController@get");
        Route::post('create', "EventController@create");
        Route::post('update', "EventController@update");
        Route::post('join', "EventController@join");
        Route::post('{eventID}', "EventController@getByID");
    });
    Route::group(['prefix' => "{id}/certificate"], function () {
        Route::post('put', "CertificateController@put");
        Route::post('update', "CertificateController@update");
        Route::post('/', "CertificateController@get");
    });

    Route::group(['prefix' => "{id}/pengajar"], function () {
        Route::post('create', "PengajarController@create");
        Route::post('update', "PengajarController@update");
        Route::post('delete', "PengajarController@delete");
        Route::post('/{pengajarID?}', "PengajarController@get");
    });

    Route::group(['prefix' => "{id}/exam"], function () {
        Route::group(['prefix' => "question"], function () {
            Route::post('store', "ExamController@storeQuestion");
            Route::post('delete', "ExamController@deleteQuestion");
        });

        Route::post('answer', "ExamController@answer");
        
        Route::post('submit-answer', "ExamController@submitAnswer");
        Route::post('sync-counter', "ExamController@syncCounter");
        Route::post('delete', "ExamController@delete");
        Route::post('/', "ExamController@getQuestions");
    });

    Route::group(['prefix' => "{id}/modul"], function () {
        Route::group(['prefix' => "{modulID}/video"], function () {
            Route::post('store', "ModulController@storeVideo");
            Route::post('delete', "ModulController@deleteVideo");
        });
        Route::group(['prefix' => "{modulID}/document"], function () {
            Route::post('store', "ModulController@storeDocument");
            Route::post('delete', "ModulController@deleteDocument");
        });
        Route::post('create', "ModulController@create");
        Route::post('{modulID}/priority/{action}', "ModulController@priority");
        Route::post('/', "ModulController@get");
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
        Route::post('enroll/complete', "CourseController@completeEnroll");
        Route::post('dashboard', "CourseController@dashboard");

        Route::group(['prefix' => "batch"], function () {
            Route::post('create', "BatchController@create");
            Route::post('/', "BatchController@get");
        });

        Route::post('/', "CourseController@getByID");
    });

    Route::post('/', "CourseController@get");
});

Route::group(['prefix' => "page"], function () {
    Route::post('home', "PageController@home");
    Route::post('search', "PageController@search");
    Route::post('category', "PageController@category");
    Route::post('my-course', "PageController@myCourse");
    Route::post('enroll', "PageController@enroll");
    Route::post('event', "PageController@event");
    Route::post('learn', "PageController@learn");
    Route::post('learn/done', "PageController@doneLearn");
    Route::get('stream/{materialID}', "PageController@stream");
    Route::post('forum/{courseID}', "PageController@forum");

    Route::group(['prefix' => "admin"], function () {
        Route::post('dashboard', "PageController@adminDashboard");
    });
});

Route::group(['prefix' => "statistic"], function () {
    Route::post('enroll', "StatisticController@enroll");
});

Route::group(['prefix' => "forum"], function () {
    Route::group(['prefix' => "thread"], function () {
        Route::post('post', "ForumController@postThread");

        Route::group(['prefix' => "{id}"], function () {
            // Route::post('upvote', "ForumController@upvoteThread");
            // Route::post('downvote', "ForumController@downvoteThread");
            Route::post('vote/{type}', "ForumController@voteThread");

            Route::post('/reply', "ForumController@getReply");
            Route::post('/reply/{replyID}/vote/{type}', "ForumController@voteReply");
            Route::post('/reply/post', "ForumController@postReply");
            Route::post('/', "ForumController@getThread");
        });
    });
    Route::group(['prefix' => "reply"], function () {
        
    });
});

Route::group(['prefix' => "presence"], function () {
    Route::post('check', "PresenceController@check");
});
Route::group(['prefix' => "contact"], function () {
    Route::get('/', "ContactController@get");
    Route::post('/', "ContactController@store");
    Route::post('delete', "ContactController@delete");
});

Route::group(['prefix' => "enroll/{enrollID}"], function () {
    Route::post('set-modul-position', "ModulController@setModulPosition");
});