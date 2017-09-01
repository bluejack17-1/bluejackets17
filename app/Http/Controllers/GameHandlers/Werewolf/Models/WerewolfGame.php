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

    /**
     * Called after a timeout occurs from cronjobs and the game is in join_phase
     * 
     * @param LINEBot $bot
     */
    public function start($bot) {
        $id = $this->getId();
        if (count($this->werewolf_players) < 5) {
            $this->delete();
            return $bot->pushMessage($id, new TextMessageBuilder('Game cancelled, not enough players!'));
        }
        $bot->pushMessage($id, new TextMessageBuilder('Game has started! Please wait while I assign roles and update the database.'));
        $players = $this->werewolf_players()->inRandomOrder()->get();
        Role::assign($bot, $players);
        $this->phase = 'lynch_phase';
        $this->save();
        $this->next($bot);
    }

    /**
     * Called after a timeout occurs from cronjobs
     *
     * @param LINEBot $bot
     */
    public function timeout($bot) {
        $this->story($bot);
        $this->resetAct();
        $this->next($bot);
    }

    /**
     * Advances a phase
     *
     * @param LINEBot $bot
     */
    public function next($bot) {
        if (!$this->validateGame($bot)) return;
        $id = $this->getId();
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
            $bot->pushMessage($id, new TextMessageBuilder($this->allPlayers()));
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
        
        $players = $this->werewolf_players()->where('alive', 1)->get();
        foreach ($players as $player) {
            $role = '\App\Http\Controllers\GameHandlers\Werewolf\Roles\\' . $player->role . 'Role';
            // $bot->pushMessage($player->line_user->user_id, new TextMessageBuilder($role));
            if (method_exists($role, $event)) {
                $role::$event($bot, $this, $player);
            }
        }
    }

    /**
     * Generate story for each passing phases
     *
     * @param LINEBot $bot
     */
    public function story($bot) {
        $priorities = ['Werewolf', 'Seer', 'Villager'];
        $players = $this->werewolf_players;
        foreach ($priorities as $priority) {
            foreach ($players as $player) {
                if ($player->role === $priority) {
                    $role = '\App\Http\Controllers\GameHandlers\Werewolf\Roles\\' . $player->role . 'Role';
                    if (WerewolfPlayer::where('alive', 1)->where('id', $player->id)->first()) {
                        $role::act($bot, $this, $player);
                    }
                }
            }
        }
    }

    /**
     * Resets player actions
     */
    public function resetAct() {
        $players = $this->werewolf_players;
        foreach ($players as $player) {
            $player->action = '';
            $player->save();
        }
    }

    /**
     * Check win/lose condition
     * Returns true when game is still valid (no one wins/loses yet)
     *
     * @param LINEBot $bot
     * @return boolean
     */
    public function validateGame($bot) {
        $players = $this->werewolf_players()->get();
        $count = [];
        foreach ($players as $player) {
            $alignment = Role::getAlignment($player->role);
            if (array_key_exists($alignment, $count)) {
                $count[$alignment]++;
            }
            else {
                $count[$alignment] = 1;
            }
        }
        $win = false;
        $message = '';
        if (!array_key_exists('werewolf', $count) || !array_key_exists('town', $count)) {
            return false;
        }
        if ($count['werewolf'] >= $count['town']) {
            $win = true;
            $message = 'The werewolf has won!';
        }
        else if ($count['werewolf'] == 0) {
            $win = true;
            $message = 'The villager has won!';
        }
        if ($win) {
            $this->delete();
            $bot->pushMessage($this->getId(), new TextMessageBuilder($message));
        }
        return !$win;
    }

    /**
     * Find Alive Player in game
     * Returns WerewolfPlayer if found, or null
     *
     * @param string $name
     * @return WerewolfPlayer
     */
    public function findAlivePlayer($name) {
        $found = $this->werewolf_players()->where('alive', 1)->where('name', $name)->first();
        if ($found) return $found;
        return $this->werewolf_players()->where('alive', 1)->where('name', 'like', "%$name%")->first();
    }

    /**
     * Generate a string for all players in game
     *
     * @return string
     */
    public function allPlayers() {
        $players = $this->werewolf_players()->get();
        $message = 'Players:';
        $count = 1;
        foreach ($players as $player) {
            $message .= "\n" . $player->name . ': ' . ($player->alive === '1' ? '🙂 Alive' : '💀 Dead - the ' . $player->role);
            $count++;
        }
        return $message;
    }

    /**
     * Get Game ID (for Bot replies)
     *
     * @return string
     */
    public function getId() {
        return $this->group_id !== '' ? $this->group_id : $this->room_id;
    }
}