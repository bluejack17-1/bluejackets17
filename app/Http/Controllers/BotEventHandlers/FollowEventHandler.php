<?php

namespace App\Http\Controllers\BotEventHandlers;

use App\LineUser;

class FollowEventHandler {
	public static function handle($bot, $event) {
		if ($event->getUserId()) {
			if (LineUser::where('user_id', $event->getUserId())->first()) {
				return;
			}
			$line_user = new LineUser();
			$line_user->user_id = $event->getUserId();
			$line_user->save();
		}
	}
}