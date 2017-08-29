<?php

namespace App\Http\Controllers;

class CronController extends Controller {
	public static function routes($app) {
		$app->get('/cron', 'CronController@cron');
	}
	public function cron() {
		
	}
}