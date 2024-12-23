<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function participants()
    {
        return $this->belongsToMany(User::class, 'participants')
                ->withPivot('name', 'match_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
