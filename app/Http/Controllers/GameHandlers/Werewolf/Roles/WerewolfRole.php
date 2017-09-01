<?php

namespace App\Http\Controllers\GameHandlers\Werewolf\Roles;

use App\Http\Controllers\GameHandlers\Werewolf\Models\WerewolfGame;
use App\Http\Controllers\GameHandlers\Werewolf\Models\WerewolfPlayer;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;

class WerewolfRole
{
	public static function first($bot, $player) {
		return $bot->pushMessage($player->line_user->user_id, new TextMessageBuilder(
			'You are the Werewolf. ' .
			'You get to have fun! ' . 
			'Each day, you will attempt to deceive your fellow players. ' . 
			'However, each night, you will get to pick a player to kill! '
		));
	}
	public static function act($bot, $game, $player) {
		$action = explode(' ', $player->action);
		$phase = $game->phase;
		if ($phase === 'night_phase') {
			if ($action[0] === 'eat') {
				$target = WerewolfPlayer::where('alive', 1)->where('id', $action[1])->first();
				if ($target) {
					$target->alive = '0';
					$target->save();
					return $bot->pushMessage($game->getId(), new TextMessageBuilder(
						'As everyone rises in the morning, they smell a terrible stink. ' . 
						'They follow the smell to find ' . $target->name . ' shredded apart in the pig pen, head missing. ' . 
						'NOMNOMNOMNOM. ' . $target->name . ' was a ' . $target->role . '.'
					));
				}
			}
		}
	}
	public static function onChangeToNight($bot, $game, $player) {
		$user = $player->line_user;
		$alive = $game->allPlayers();
		$bot->pushMessage($user->user_id, new TextMessageBuilder("Who do you want to eat?\nChoose by typing /werewolf eat [name]\n\n$alive"));
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
			if (strtolower($args[0]) === 'eat') {
				array_shift($args);
				$name = implode(' ', $args);
				$target = $game->findAlivePlayer($name);
				if ($target) {
					$message = ($player->action === '' ? 'Choice accepted - ' : 'Choice updated - ') . $target->name;
					$player->action = 'eat ' . $target->id;
					$player->save();
					return $bot->replyText($event->getReplyToken(), $message);
				}
			}
		}
		else if ($game->phase === 'day_phase') {
			
		}
	}
}