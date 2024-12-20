<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'match_id'];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
