<?php

namespace App\Models;

use App\MessageSender;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    const UPDATED_AT = null;

    use HasFactory;

    public static function compose()
    {
        return new MessageSender;
    }
}
