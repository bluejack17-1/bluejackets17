<?php

namespace App\Http\Controllers\GameHandlers\Werewolf\Roles;

use App\Http\Controllers\GameHandlers\Werewolf\Models\WerewolfGame;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;

class VillagerRole
{
	public static function onChangeToLynch($bot, $game, &$player) {
		$user = $player->line_user;
		$bot->pushMessage($user->user_id, new TextMessageBuilder('Who do you want to lynch?'));
	}

	public static function handle($bot, $game, $player) {
		
	}
}