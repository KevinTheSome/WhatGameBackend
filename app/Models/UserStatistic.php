<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserStatistic extends Model
{
    protected $fillable = [
        'user_id',
        'account_created_at',
        'lobbies_created',
        'lobbies_joined',
        'games_voted_on',
        'last_login',
    ];

    protected $casts = [
        'account_created_at' => 'datetime',
        'lobbies_created' => 'integer',
        'lobbies_joined' => 'integer',
        'games_voted_on' => 'integer',
        'last_login' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getOrCreateForUser(int $userId): self
    {
        $statistic = self::where('user_id', $userId)->first();

        if (!$statistic) {
            $statistic = self::create([
                'user_id' => $userId,
                'account_created_at' => now(),
                'lobbies_created' => 0,
                'lobbies_joined' => 0,
                'games_voted_on' => 0,
                'last_login' => now(),
            ]);
        }

        return $statistic;
    }

    public function getAccountAgeInDays(): int
    {
        if (!$this->account_created_at) {
            return 0;
        }
        return $this->account_created_at->diffInDays(now());
    }

    public function incrementLobbiesCreated(): void
    {
        $this->increment('lobbies_created');
    }

    public function incrementLobbiesJoined(): void
    {
        $this->increment('lobbies_joined');
    }

    public function incrementGamesVotedOn(int $count = 1): void
    {
        $this->increment('games_voted_on', $count);
    }

    public function updateLastLogin(): void
    {
        $this->update(['last_login' => now()]);
    }
}