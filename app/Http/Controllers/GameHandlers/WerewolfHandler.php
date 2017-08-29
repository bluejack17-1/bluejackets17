<?php

namespace App\Http\Controllers\GameHandlers;

use App\Http\Controllers\GameHandlers\Werewolf\Models\WerewolfPlayer;
use App\Http\Controllers\GameHandlers\Werewolf\Models\WerewolfGame;

class WerewolfHandler {
	public static function handle($args, $bot, $event) {
		if (strtolower($args[0]) === 'start') {
			if (!$event->isGroupEvent() && !$event->isRoomEvent()) {
				return $bot->replyText($event->getReplyToken(), 'This game is only available in groups/rooms.');
			}
			$type = $event->isGroupEvent() ? 'group_id' : 'room_id';
			$id = $event->isGroupEvent() ? $event->getGroupId() : $event->getRoomId();
			if (WerewolfGame::where($type, $id)->first()) {
				return $bot->replyText($event->getReplyToken(), 'Game has already existed.');
			}
			$game = new WerewolfGame();
			$game->$type = $id;
			$game->phase = 'join_phase';
			$game->day = 0;
			$game->save();
			return $bot->replyText($event->getReplyToken(), 'Game started! Type /werewolf join to join the game.');
		}
	}
}