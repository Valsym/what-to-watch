<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Film extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_ON_MODERATION = 'moderate';
    public const STATUS_READY = 'ready';
}
