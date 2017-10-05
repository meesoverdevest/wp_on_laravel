<?php

namespace meesoverdevest\wp_on_laravel\controllers;

use meesoverdevest\wp_on_laravel\models\WPPost;
use meesoverdevest\wp_on_laravel\helpers\WPAPI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WPSyncController extends Controller
{

  public function sync(Request $request) {
  	$wp = new WPAPI();
  	$wp->syncWP();

  	return response()->json(['success' => $request->get('wp_id')], 201);
  }

  public function delete(Request $request) {
  	$wp = new WPAPI();
  	$wp->deleteItems($request->get('wp_id'), $request->get('type'));

  	return response()->json(['success' => $request->get('wp_id')], 201);
  }

}

