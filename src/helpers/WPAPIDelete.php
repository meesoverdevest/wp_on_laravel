<?php

namespace meesoverdevest\wp_on_laravel\helpers;

use meesoverdevest\wp_on_laravel\models\WPPost;
use meesoverdevest\wp_on_laravel\models\WPCategory;
use Carbon\Carbon;

// use Illuminate\Support\Facades\Log;

class WPAPIDelete {

	private $type;

	function __contract($type) {
		$this->type = $type;
	}

	public function deleteCategory($wp_id) {
		$found = WPCategory::where('wp_id', $wp_id)->first();

		$found->posts()->detach();

		$found->delete();
	}

	public function deletePost($wp_id) {
		$found = WPPost::where('wp_id', $wp_id)->first();

		$found->categories()->detach();

		// TODO: Detach Tags

		$found->delete();
	}
}
