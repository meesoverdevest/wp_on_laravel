<?php

// do shell script "open http://www.webpagehere.com"
Route::group(['namespace' => 'meesoverdevest\wp_on_laravel\controllers'], function(){
	Route::get('wp/index', 'WPSyncController@index');
	Route::get('wp/sync', 'WPSyncController@sync');
});
