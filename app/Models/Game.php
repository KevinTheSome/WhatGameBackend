<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Game extends Model
{
    protected $table = "games";

    protected $fillable = ["user_id", "game_id"];

    /**
     * Get the user who favorited this game
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getInfo()
    {
        try {
            $results = Http::get(
                "https://api.rawg.io/api/games/{$this->game_id}?key=" .
                    env("RAWG_API_KEY"),
            );

            if ($results->failed()) {
                return [
                    "id" => $this->game_id,
                    "name" => "Unknown Game",
                    "background_image" => null,
                ];
            }

            return $results->json();
        } catch (Exception $e) {
            Log::error("Failed to get game info from RAWG API", [
                "game_id" => $this->game_id,
                "error" => $e->getMessage(),
            ]);

            return [
                "id" => $this->game_id,
                "name" => "Unknown Game",
                "background_image" => null,
            ];
        }
    }
}
