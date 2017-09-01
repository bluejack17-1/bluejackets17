<?php

namespace App\Http\Controllers\GameHandlers\Werewolf\Roles;

use App\Http\Controllers\GameHandlers\Werewolf\Models\WerewolfGame;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;

class SeerRole
{
	public static function first($bot, $player) {
		return $bot->pushMessage($player->line_user->user_id, new TextMessageBuilder(
			'You are the Seer. ' .
			'Each night, you may choose a player, and have their role revealed to you. ' .
			'Admit to your role wisely: If you reveal you are the seer, the wolves will try to kill you!'
		));
	}
	public static function act($bot, $game, $player) {

	}
	public static function onChangeToNight($bot, $game, $player) {
		$user = $player->line_user;
		$alive = $game->allPlayers();
		$bot->pushMessage($user->user_id, new TextMessageBuilder("Who do you want to see?\nChoose by typing /werewolf see [name]\n\n$alive"));
	}

	public static function onChangeToLynch($bot, $game, $player) {
		$user = $player->line_user;
		$alive = $game->allPlayers();
		$bot->pushMessage($user->user_id, new TextMessageBuilder("Who do you want to lynch?\nChoose by typing /werewolf lynch [name]\n\n$alive"));
	}

	public static function handle($args, $bot, $event, $game, $player) {
		if ($game->phase === 'lynch_phase') {
			if (strtolower($args[0]) === 'lynch') {
				array_shift($args);
				$name = implode(' ', $args);
				$target = $game->findAlivePlayer($name);
				if ($target) {
					$message = ($player->action === '' ? 'Choice accepted - ' : 'Choice updated - ') . $target->name;
					$player->action = 'lynch ' . $target->id;
					$player->save();
					return $bot->replyText($event->getReplyToken(), $message);
				}
			}
		}
		else if ($game->phase === 'night_phase') {
			if (strtolower($args[0]) === 'see') {
				array_shift($args);
				$name = implode(' ', $args);
				$target = $game->findAlivePlayer($name);
				if ($target) {
					$message = ($player->action === '' ? 'Choice accepted - ' : 'Choice updated - ') . $target->name;
					$player->action = 'see ' . $target->id;
					$player->save();
					return $bot->replyText($event->getReplyToken(), $message);
				}
			}

		}
		else if ($game->phase === 'day_phase') {
			
		}
	}
}