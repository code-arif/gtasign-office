<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{

    protected $fillable = ['first_user_id','second_user_id'];

    public function first_user()
    {
        return $this->belongsTo(User::class, 'first_user_id');
    }

    public function second_user()
    {
        return $this->belongsTo(User::class, 'second_user_id');
    }

    public function chats(){
       return  $this->hasMany(Chat::class, 'room_id', 'id');
    }



}
