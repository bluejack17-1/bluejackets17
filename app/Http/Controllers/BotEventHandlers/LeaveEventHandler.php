<?php

namespace App\Http\Controllers\BotEventHandlers;

class LeaveEventHandler {
	public static function handle($bot, $event) {
		if ($event->isGroupEvent()) {
		}
		else if ($event->isRoomEvent()) {
		}
	}
}