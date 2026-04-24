<?php

namespace App\Models;

use App\Models\User;
use App\Models\Game;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class Vote
{
    private $id;
    private $lobby;
    private $games = [];
    private $playerVotes;
    private $created_at;

    public function __construct($lobby, $games)
    {
        if (!is_object($lobby) || !method_exists($lobby, "getId")) {
            throw new \InvalidArgumentException("Invalid lobby object");
        }

        if (!is_array($games) || empty($games)) {
            throw new \InvalidArgumentException(
                "Games must be a non-empty array",
            );
        }

        $this->id = uniqid("vote_");
        $this->lobby = $lobby;
        $this->created_at = now();
        $this->games = [];

        foreach ($games as $gameId => $gameName) {
            $this->games[$gameId] = [
                "name" => $gameName,
                "votes" => 0,
                "upvotes" => 0,
                "downvotes" => 0,
            ];

            try {
                $response = Http::get(
                    "https://api.rawg.io/api/games/{$gameId}?key=" .
                        env("RAWG_API_KEY"),
                );
                $response->throw();
                $data = $response->json();
                $this->games[$gameId]["background_image"] =
                    $data["background_image"] ?? null;
            } catch (\Exception $e) {
                $this->games[$gameId]["background_image"] = null;
            }
        }

        $this->playerVotes = [];
        $this->initializePlayerVotes();
    }

    private function initializePlayerVotes()
    {
        $lobbyPlayers = $this->lobby->getUsers();

        foreach ($lobbyPlayers as $playerId) {
            $this->playerVotes[$playerId] = [];
            foreach (array_keys($this->games) as $gameId) {
                $this->playerVotes[$playerId][$gameId] = 0;
            }
        }
    }

    public function voteGame($gameId, $userId, $vote)
    {
        if ($vote !== 1 && $vote !== -1) {
            throw new \InvalidArgumentException(
                "Vote must be 1 (upvote) or -1 (downvote)",
            );
        }

        if (!isset($this->games[$gameId])) {
            throw new \InvalidArgumentException("Invalid game ID");
        }

        if (!isset($this->playerVotes[$userId])) {
            throw new \InvalidArgumentException("User is not in this lobby");
        }

        if (
            isset($this->playerVotes[$userId][$gameId]) &&
            $this->playerVotes[$userId][$gameId] !== 0
        ) {
            $previousVote = $this->playerVotes[$userId][$gameId];
            $this->games[$gameId]["votes"] -= $previousVote;
            if ($previousVote > 0) {
                $this->games[$gameId]["upvotes"]--;
            } else {
                $this->games[$gameId]["downvotes"]--;
            }
        }

        $this->playerVotes[$userId][$gameId] = $vote;
        $this->games[$gameId]["votes"] += $vote;
        if ($vote > 0) {
            $this->games[$gameId]["upvotes"]++;
        } else {
            $this->games[$gameId]["downvotes"]++;
        }

        return true;
    }

    public function getGames()
    {
        return $this->games;
    }

    public function getPlayerVotes()
    {
        return $this->playerVotes;
    }

    public function getPlayerFavoriteGames()
    {
        $favorites = [];

        foreach ($this->playerVotes as $playerId => $votes) {
            $playerFavorites = [];
            foreach ($votes as $gameId => $vote) {
                if ($vote > 0) {
                    $playerFavorites[] = $gameId;
                }
            }
            $favorites[$playerId] = $playerFavorites;
        }

        return $favorites;
    }

    public function getVotedGames()
    {
        $lobbyPlayers = $this->lobby->getUsers();
        $favorites = [];

        foreach ($lobbyPlayers as $playerId) {
            $playerFavorites = [];
            if (isset($this->playerVotes[$playerId])) {
                foreach ($this->playerVotes[$playerId] as $gameId => $vote) {
                    if ($vote > 0) {
                        $playerFavorites[] = $gameId;
                    }
                }
            }
            $favorites[$playerId] = $playerFavorites;
        }

        return $favorites;
    }

    public function getVoteResults()
    {
        return [
            "games" => $this->games,
            "player_votes" => $this->playerVotes,
            "total_votes" => array_sum(array_column($this->games, "votes")),
        ];
    }

    public function isVotingComplete($currentLobby)
    {
        $currentUsers = $currentLobby->getUsers();

        foreach ($this->playerVotes as $playerId => $votes) {
            if (!in_array($playerId, $currentUsers)) {
                continue;
            }
            foreach ($votes as $gameId => $vote) {
                if (!isset($this->games[$gameId])) {
                    continue;
                }
                if ($vote === 0) {
                    return false;
                }
            }
        }
        return true;
    }

    public function getRemainingPlayersProgress($currentLobby)
    {
        $currentUsers = $currentLobby->getUsers();
        $remainingPlayerIds = [];

        foreach ($this->playerVotes as $playerId => $votes) {
            if (!in_array($playerId, $currentUsers)) {
                continue;
            }
            foreach ($votes as $gameId => $vote) {
                if (!isset($this->games[$gameId])) {
                    continue;
                }
                if ($vote === 0) {
                    $remainingPlayerIds[] = $playerId;
                    break;
                }
            }
        }

        $remainingNames = User::whereIn('id', $remainingPlayerIds)->pluck('name')->toArray();

        return [
            'remaining_count' => count($remainingPlayerIds),
            'remaining_names' => $remainingNames,
            'total_players' => count($currentUsers),
        ];
    }

    public function toArray(): array
    {
        return [
            "id" => $this->id,
            "lobby_id" => $this->lobby->getId(),
            "games" => $this->games,
            "player_votes" => $this->playerVotes,
            "created_at" => $this->created_at,
        ];
    }
}