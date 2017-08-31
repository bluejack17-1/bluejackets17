<?php

namespace App\Http\Controllers\GameHandlers\Werewolf\Models;

use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\GameHandlers\Werewolf\Models\WerewolfPlayer;
use App\Http\Controllers\GameHandlers\Werewolf\Role;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;

class WerewolfGame extends Model
{
    //
    protected $fillable = ['group_id', 'room_id', 'phase', 'day', 'start_time', 'timeout', ];
    protected $hidden = [];
    
    public function werewolf_players() {
        return $this->hasMany(WerewolfPlayer::class, 'werewolf_game_id', 'id');
    }

    public function start($bot) {
        $id = $this->group_id !== '' ? $this->group_id : $this->room_id;
        $bot->pushMessage($id, new TextMessageBuilder('Game has started! Please wait while I assign roles and update the database.'));
        $players = $this->werewolf_players()->inRandomOrder()->get();
        Role::assign($bot, $players);
        $this->phase = 'lynch_phase';
        $this->save();
        $this->next($bot);
    }

    public function next($bot) {
        $id = $this->group_id !== '' ? $this->group_id : $this->room_id;
        $curr = (new \DateTime())->modify('+2 minutes');
        $event = 'unknownEvent';
        if ($this->phase === 'lynch_phase') {
            $this->phase = 'night_phase';
            $this->timeout = $curr->format('Y-m-d H:i:') . '00';
            $this->save();
            $event = 'onChangeToNight';
            $bot->pushMessage($id, new TextMessageBuilder(
				'Night has fallen. Everyone heads to bed, weary after another stressful day. Night players, you have until ' . $curr->format('g:iA') . ' to use your actions!'
			));
        }
        else if ($this->phase === 'night_phase') {
			$this->phase = 'day_phase';
			$this->timeout = $curr->format('Y-m-d H:i:') . '00';
			$this->day = intval($this->day) + 1;
			$this->save();
			$event = 'onChangeToDay';
			$bot->pushMessage($id, new TextMessageBuilder(
				'It is now daytime. All of you have until ' . $curr->format('g:iA') . ' to make your accusations, defenses, or just talk.'
			));
		}
		else if ($this->phase === 'day_phase') {
			$this->phase = 'lynch_phase';
			$this->timeout = $curr->format('Y-m-d H:i:') . '00';
			$this->save();
			$event = 'onChangeToLynch';
			$bot->pushMessage($id, new TextMessageBuilder(
				'Dusk draws near, and the villagers gather to decide who they are lynching this evening... All of you have until ' . $curr->format('g:iA') . ' to vote.'
			));
        }
        
        $players = $this->werewolf_players;
        foreach ($players as $player) {
            $bot->pushMessage($player->line_user->user_id, new TextMessageBuilder($role));
            if (method_exists($role, $event)) {
                $role::$event($bot, $this, $player);
            }
        }
    }

    public function findPlayer($game, $name) {
        $found = $this->werewolf_players()->where('name', $name)->first();
        if ($found) return $found;
        
    }
}