<?php

namespace App\Http\Controllers\BotEventHandlers;

use LINE\LINEBot\Event\MessageEvent\TextMessage;
use App\Http\Controllers\BotEventHandlers\MessageEventHandlers\TextMessageEventHandler;

class MessageEventHandler {
	public static function handle($bot, $event) {
		if ($event instanceof TextMessage) {
			try {
				return TextMessageEventHandler::handle($bot, $event);
			}
			catch (\Exception $e) {
				return $bot->replyText($event->getReplyToken(), 'Error: ' . $e->getMessage());
			}
		}
	}
}