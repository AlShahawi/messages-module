<?php

namespace App\Models;

use App\MessageSender;
use App\QueryBuilders\MessageQueryBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $casts = ['read_at' => 'datetime'];

    public static function compose()
    {
        return new MessageSender;
    }

    public function sender()
    {
        return $this->belongsTo(User::class);
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function newEloquentBuilder($query)
    {
        return new MessageQueryBuilder($query);
    }
}
