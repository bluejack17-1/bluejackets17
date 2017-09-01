<?php

namespace App\Http\Controllers\GameHandlers\Werewolf\Models;

use Illuminate\Database\Eloquent\Model;
use App\LineUser;
use App\Http\Controllers\GameHandlers\Werewolf\Models\WerewolfGame;

class WerewolfPlayer extends Model
{
    //
    protected $fillable = ['name', 'role', 'alive', 'action', 'extra', ];
    protected $hidden = [];
    
    public function werewolf_game() {
        return $this->belongsTo(WerewolfGame::class, 'werewolf_game_id', 'id');
    }

    public function line_user() {
        return $this->belongsTo(LineUser::class, 'line_user_id', 'id');
    }
}