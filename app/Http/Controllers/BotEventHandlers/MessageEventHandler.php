<?php

namespace App\Http\Controllers\BotEventHandlers;

use LINE\LINEBot\Event\MessageEvent\TextMessage;
// use App\Http\Controllers\BotEventHandlers\MessageEventHandlers\TextMessageEventHandler;

class MessageEventHandler {
	public static function handle($bot, $event) {
		if ($event instanceof TextMessage) {
			// TextMessageEventHandler::handle($bot, $event);
		}
	}
}