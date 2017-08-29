<?php

namespace App\Http\Controllers\BotEventHandlers\MessageEventHandlers;

use App\LineUser;
use App\Http\Controllers\GameHandlers\WerewolfHandler;

class TextMessageEventHandler {
	public static function handle($bot, $event) {
		// return $bot->replyText($event->getReplyToken(), $event->getText());
		$args = explode(' ', $event->getText());
		if (strtolower($args[0]) === '/register') {
			if (!$event->isUserEvent()) {
				return $bot->replyText($event->getReplyToken(), 'You can only do this by personally chatting me.');
			}
			if ($event->getUserId()) {
				$profile = $bot->getProfile($event->getUserId())->getJSONDecodedBody();
				if (array_key_exists('displayName', $profile)) {
					$displayName = trim($profile['displayName']);
					if ($displayName !== '') {
						if (LineUser::where('user_id', $event->getUserId())->first()) {
							return $bot->replyText($event->getReplyToken(), 'Oops, you\'re already registered!');
						}
						$line_user = new LineUser();
						$line_user->user_id = $event->getUserId();
						$line_user->save();
						return $bot->replyText($event->getReplyToken(), 'Okay, you\'re registered!');;
					}
					return $bot->replyText($event->getReplyToken(), 'Your name cannot be empty!');
				}
				return $bot->replyText($event->getReplyToken(), 'Please add me as a friend first.');
			}
			return $bot->replyText($event->getReplyToken(), 'Please add me as a friend first.');
		}
		else if (strtolower($args[0]) === '/werewolf') {
			array_shift($args);
			return WerewolfHandler::handle($args, $bot, $event);
		}
	}
}