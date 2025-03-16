<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'image_path', 'team_id'];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function positions()
    {
        return $this->belongsToMany(Position::class);
    }
}
