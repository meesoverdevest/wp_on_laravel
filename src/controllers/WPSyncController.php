<?php

namespace meesoverdevest\wp_on_laravel\controllers;

use meesoverdevest\wp_on_laravel\models\WPPost;
use meesoverdevest\wp_on_laravel\helpers\WPAPI;

use App\Http\Controllers\Controller;


class WPSyncController extends Controller
{

	// public function __construct() {
 //    $this->checkWP();
 //  }

  public function index()
  {

    $posts = WPPost::all();
    // dd($posts);

  }

  private function checkWP() {
		
  }

  public function sync() {
  	$wp = new WPAPI();
  	$wp->syncWP();
  }

}

