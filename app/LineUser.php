<?php

namespace App;

use App\Http\Controllers\GameHandlers\Werewolf\Models\WerewolfPlayer;
use Illuminate\Database\Eloquent\Model;

class LineUser extends Model
{
    //
    protected $fillable = ['user_id', ];
    protected $hidden = [];

    public function werewolf_players() {
        return $this->hasMany(WerewolfPlayer::class, 'line_user_id', 'id');
    }
}