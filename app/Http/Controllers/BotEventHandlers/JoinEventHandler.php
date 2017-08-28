<?php

namespace App\Http\Controllers\BotEventHandlers;

class JoinEventHandler {
	public static function handle($bot, $event) {
		if ($event->isGroupEvent()) {
		}
		else if ($event->isRoomEvent()) {
		}
	}
}