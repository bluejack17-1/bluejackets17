<?php

namespace App\Http\Controllers\GameHandlers\Werewolf;

use LINE\LINEBot\MessageBuilder\TextMessageBuilder;

class Role
{
	public static function assign($bot, $players) {
		$roles = [
			[
				'name'  => 'Seer',
				'count' => 0,
				'ratio' => 1,
			],
			[
				'name'  => 'Villager',
				'count' => 4,
				'ratio' => 4,
			],
			[
				'name'  => 'Werewolf',
				'count' => 0,
				'ratio' => 1,
			],
		];

		foreach ($players as $player) {
			$reshuffle = true;
			foreach ($roles as $role) {
				if ($role['count'] < $role['ratio']) {
					$reshuffle = false;
				}
			}
			if ($reshuffle) {
				foreach ($roles as &$role) {
					$role['count'] = 0;
				}
			}

			while (true) {
				# WTF PHP's Pass by Value/Reference is weird asf
				$idx = rand() % count($roles);
				$role = $roles[$idx];
				if ($role['count'] < $role['ratio']) {
					$roles[$idx]['count']++;
					$player->role = $role['name'];
					$player->save();
					$role_class = '\App\Http\Controllers\GameHandlers\Werewolf\Roles\\' . $role['name'] . 'Role';
					$role_class::first($bot, $player);
					break;
				}
			}
		}
	}

	public static function getAlignment($role) {
		$roles = [
			'Villager' => 'town',
			'Seer'     => 'town',
			'Werewolf' => 'werewolf',
		];
		return array_key_exists($role, $roles) ? $roles[$role] : null;
	}
}