<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserGameVote extends Model
{
    protected $fillable = [
        'user_id',
        'game_id',
        'game_name',
        'upvotes',
        'downvotes',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
