<?php

namespace App\Http\Controllers;

use App\Http\Controllers\GameHandlers\WerewolfHandler;
use App\Http\Controllers\GameHandlers\Werewolf\Models\WerewolfPlayer;

class WerewolfController extends Controller {
	// Routes for werewolf game
	public static function routes($app) {
		$app->get('/werewolf/lynch/{player_id}/{target_id}', 'WerewolfController@lynch');
		$app->get('/werewolf/eat/{player_id}/{target_id}', 'WerewolfController@eat');
	}
	public function lynch($player_id, $target_id) {
		$player = WerewolfPlayer::where('alive', 1)->where('id', $player_id)->first();
		$target = WerewolfPlayer::where('alive', 1)->where('id', $target_id)->first();
		if ($player && $target) {

		}
		return view('exit');
	}
	public function eat() {
		return view('exit');
	}
}