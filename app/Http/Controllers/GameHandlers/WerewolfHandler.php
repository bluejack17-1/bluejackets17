<?php

namespace App\Http\Controllers\GameHandlers;
use Illuminate\Http\Request;

use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use App\LineUser;
use App\Http\Controllers\GameHandlers\Werewolf\Models\WerewolfPlayer;
use App\Http\Controllers\GameHandlers\Werewolf\Models\WerewolfGame;

class WerewolfHandler {
	public static function handle($args, $bot, $event) {
		if ($event->isUserEvent()) {
			self::userHandle($args, $bot, $event);
		}
		if (strtolower($args[0]) === 'start') {
			if (!$event->isGroupEvent() && !$event->isRoomEvent()) {
				return $bot->replyText($event->getReplyToken(), 'This game is only available in groups/rooms.');
			}
			$type = $event->isGroupEvent() ? 'group_id' : 'room_id';
			$id = $event->isGroupEvent() ? $event->getGroupId() : $event->getRoomId();
			if (WerewolfGame::where($type, $id)->first()) {
				return $bot->replyText($event->getReplyToken(), 'Game has already existed.');
			}
			$curr = (new \DateTime())->modify('+2 minutes');
			$game = new WerewolfGame();
			$game->$type = $id;
			$game->phase = 'join_phase';
			$game->day = 0;
			$game->start_time = $curr->format('Y-m-d H:i:') . '00';
			$game->save();
			return $bot->replyText($event->getReplyToken(), 'Game started! Type /werewolf join to join the game. You have until ' . $curr->format('g:iA') . ' to join the game.');
		}
		else if (strtolower($args[0]) === 'extend') {
			if (!$event->isGroupEvent() && !$event->isRoomEvent()) return;
			$type = $event->isGroupEvent() ? 'group_id' : 'room_id';
			$id = $event->isGroupEvent() ? $event->getGroupId() : $event->getRoomId();
			$game = WerewolfGame::where($type, $id)->first();
			if (!$game || $game->phase !== 'join_phase') return;
			$curr = \DateTime::createFromFormat('Y-m-d H:i:s', $game->start_time)->modify('+1 minutes');
			$game->start_time = $curr->format('Y-m-d H:i:') . '00';
			$game->save();
			return $bot->replyText($event->getReplyToken(), 'Time extended! Type /werewolf join to join the game. You have until ' . $curr->format('g:iA') . ' to join the game.');
		}
		else if (strtolower($args[0]) === 'join') {
			if (!$event->isGroupEvent() && !$event->isRoomEvent()) return;
			$type = $event->isGroupEvent() ? 'group_id' : 'room_id';
			$id = $event->isGroupEvent() ? $event->getGroupId() : $event->getRoomId();
			if ($event->getUserId()) {
				$user = LineUser::where('user_id', $event->getUserId())->first();
				$game = WerewolfGame::where($type, $id)->first();
				if (!$user) {
					return $bot->replyText($event->getReplyToken(), 'Please add me as a friend first.');
				}
				if ($user->werewolf_players()->first()) {
					return $bot->replyText($event->getReplyToken(), 'It looks like you\'re already playing.');
				}
				if (!$game) {
					return $bot->replyText($event->getReplyToken(), 'Game doesn\'t exist!');
				}
				if ($game->phase !== 'join_phase') {
					return $bot->replyText($event->getReplyToken(), 'Game has already started!');
				}
				$profile = $bot->getProfile($event->getUserId())->getJSONDecodedBody();
				if (array_key_exists('displayName', $profile)) {
					$displayName = trim($profile['displayName']);
					if ($displayName !== '') {
						$player = new WerewolfPlayer();
						$player->name = $displayName;
						$player->role = '';
						$player->alive = 1;
						$player->action = '';
						$player->line_user()->associate($user);
						$player->werewolf_game()->associate($game);
						$player->save();
						return $bot->replyText($event->getReplyToken(), $displayName . ' joined the game!');;
					}
					return $bot->replyText($event->getReplyToken(), 'Your name cannot be empty!');
				}
				return $bot->replyText($event->getReplyToken(), 'Please add me as a friend first.');
			}
			return $bot->replyText($event->getReplyToken(), 'Please add me as a friend first.');
		}
		else if (strtolower($args[0]) === 'forcestart') {
			if (!$event->isGroupEvent() && !$event->isRoomEvent()) return;
			$type = $event->isGroupEvent() ? 'group_id' : 'room_id';
			$id = $event->isGroupEvent() ? $event->getGroupId() : $event->getRoomId();
			$game = WerewolfGame::where($type, $id)->first();
			if (!$game || $game->phase !== 'join_phase') return;
			$game->start($bot);
			return;
		}
	}
	public static function userHandle($args, $bot, $event) {
		if (!$event->getUserId()) return $bot->replyText($event->getReplyToken(), 'Please add me as a friend first.');
		$user = LineUser::where('user_id', $event->getUserId())->first();
		if (!$user) return $bot->replyText($event->getReplyToken(), 'Please add me as a friend first.');
		$player = $user->werewolf_players()->first();
		if ($player) {
			if ($player->role !== '') {
			$role = '\App\Http\Controllers\GameHandlers\Werewolf\Roles\\' . $player->role . 'Role';
			return $role::handle($args, $bot, $event, $player->werewolf_game, $player);
			}
		}
	}
	public static function cron($bot) {
		$games = WerewolfGame::all();
		$curr = new \DateTime();
		foreach ($games as $game) {
			if ($game->phase === 'join_phase') {
				if (\DateTime::createFromFormat('Y-m-d H:i:s', $game->start_time)->getTimestamp() <= $curr->getTimestamp()) {
					return $game->start($bot);
				}
			}
			else {
				if (\DateTime::createFromFormat('Y-m-d H:i:s', $game->timeout)->getTimestamp() <= $curr->getTimestamp()) {
					return $game->timeout($bot);
				}
			}
		}
	}
}