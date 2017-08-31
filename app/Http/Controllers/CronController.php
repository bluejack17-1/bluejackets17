<?php

namespace App\Http\Controllers;

use App\Http\Controllers\GameHandlers\WerewolfHandler;

class CronController extends Controller {
	public static function routes($app) {
		$app->get('/cron', 'CronController@cron');
	}
	public function cron() {
		$bot = app(\App\LineBot::class)->bot();
		WerewolfHandler::cron($bot);
	}
}