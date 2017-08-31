<?php

namespace App\Http\Controllers;

class RouteController extends Controller {
	public static function routes($app) {
		LineBotController::routes($app);
		CronController::routes($app);
	}
}