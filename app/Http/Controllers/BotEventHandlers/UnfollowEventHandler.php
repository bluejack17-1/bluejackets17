<?php

namespace App\Http\Controllers\BotEventHandlers;

use App\LineUser;

class UnfollowEventHandler {
	public static function handle($bot, $event) {
		if ($event->getUserId()) {
			$line_user = LineUser::where('user_id', $event->getUserId())->first();
			if ($line_user) {
				$line_user->delete();
			}
		}
	}
}