<?php

namespace App\Http\Controllers\GameHandlers\Werewolf\Models;

use Illuminate\Database\Eloquent\Model;

class WerewolfGame extends Model
{
    //
    protected $fillable = ['group_id', 'room_id', 'phase', 'day', ];
    protected $hidden = [];
    
}