<?php

// do shell script "open http://www.webpagehere.com"
Route::group(['namespace' => 'meesoverdevest\wp_on_laravel\controllers'], function(){
	Route::post('wp/sync', 'WPSyncController@sync');
	Route::post('wp/delete', 'WPSyncController@delete');
});

