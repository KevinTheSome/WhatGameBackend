<?php

namespace App\Models;

use App\Models\User;
use App\Models\Game;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Vote
{
    private const VOTING_TIMEOUT_MINUTES = 5;

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

        foreach ($games as $gameId => $gameData) {
            $name = is_array($gameData) ? ($gameData["name"] ?? "Unknown Game") : $gameData;
            $bg = is_array($gameData) ? ($gameData["background_image"] ?? null) : null;

            $this->games[$gameId] = [
                "name" => $name,
                "background_image" => $bg,
                "votes" => 0,
                "upvotes" => 0,
                "downvotes" => 0,
            ];
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

        $votingStartedAt = $this->created_at ?? now();
        $timeoutSeconds = self::VOTING_TIMEOUT_MINUTES * 60;
        $hasTimedOut = $votingStartedAt && now()->diffInSeconds($votingStartedAt) > $timeoutSeconds;

        $allGames = array_keys($this->games);
        if (empty($allGames)) {
            Log::info("No games in voting session for lobby {$currentLobby->getId()}, concluding voting");
            return true;
        }

        foreach ($this->playerVotes as $playerId => $votes) {
            if (!in_array($playerId, $currentUsers)) {
                continue;
            }

            $playerVotedCount = 0;
            $playerTotalGames = count($allGames);
            foreach ($allGames as $gameId) {
                if (isset($votes[$gameId]) && $votes[$gameId] !== 0) {
                    $playerVotedCount++;
                }
            }

            if ($playerVotedCount === 0) {
                if (!$hasTimedOut) {
                    Log::warning("Player {$playerId} has not voted on any games in lobby {$currentLobby->getId()}");
                    return false;
                }
                Log::info("Player {$playerId} timeout with no votes, concluding anyway");
                continue;
            }

            if ($playerVotedCount < $playerTotalGames && !$hasTimedOut) {
                return false;
            }

            if ($playerVotedCount < $playerTotalGames && $hasTimedOut) {
                Log::info("Player {$playerId} timeout with partial votes ({$playerVotedCount}/{$playerTotalGames}), concluding anyway");
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

    public function syncNewGames($newGames)
    {
        $lobbyPlayers = $this->lobby->getUsers();

        foreach ($newGames as $gameId => $gameData) {
            if (!isset($this->games[$gameId])) {
                $name = is_array($gameData) ? ($gameData["name"] ?? "Unknown Game") : $gameData;
                $bg = is_array($gameData) ? ($gameData["background_image"] ?? null) : null;

                $this->games[$gameId] = [
                    "name" => $name,
                    "background_image" => $bg,
                    "votes" => 0,
                    "upvotes" => 0,
                    "downvotes" => 0,
                ];

                foreach ($lobbyPlayers as $playerId) {
                    if (!isset($this->playerVotes[$playerId][$gameId])) {
                        $this->playerVotes[$playerId][$gameId] = 0;
                    }
                }
            }
        }

        foreach ($lobbyPlayers as $playerId) {
            if (!isset($this->playerVotes[$playerId])) {
                $this->playerVotes[$playerId] = [];
                foreach (array_keys($this->games) as $gameId) {
                    $this->playerVotes[$playerId][$gameId] = 0;
                }
            } else {
                foreach (array_keys($this->games) as $gameId) {
                    if (!isset($this->playerVotes[$playerId][$gameId])) {
                        $this->playerVotes[$playerId][$gameId] = 0;
                    }
                }
            }
        }
    }
}