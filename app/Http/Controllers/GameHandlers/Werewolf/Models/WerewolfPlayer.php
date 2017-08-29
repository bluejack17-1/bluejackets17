<?php

namespace App\Http\Controllers\GameHandlers\Werewolf\Models;

use Illuminate\Database\Eloquent\Model;

class WerewolfPlayer extends Model
{
    //
    protected $fillable = ['role', 'alive', 'action', ];
    protected $hidden = [];
    
}