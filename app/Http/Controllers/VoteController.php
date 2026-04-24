<?php

namespace App\Http\Controllers;

use App\Models\Lobby;
use App\Models\Vote;
use App\Models\User;
use App\Models\Game;
use App\Models\UserStatistic;
use App\Models\UserGameVote;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Stmt\TryCatch;

class VoteController extends Controller
{
    private function getCurrentLobby($user)
    {
        $lobbies = Cache::get("lobbies", []);
        return collect($lobbies)->first(
            fn($lobby) => in_array($user->id, $lobby->getUsers()),
        );
    }

    private function getLobbyGames($lobby, $existingGames = [])
    {
        return collect($lobby->getUsers())
            ->map(function ($userId) {
                $user = User::find($userId);
                return $user->getFavoritedGames();
            })
            ->flatten(1)
            ->unique("game_id")
            ->mapWithKeys(function ($gameData) use ($existingGames) {
                $gameId = $gameData->game_id;

                if (isset($existingGames[$gameId])) {
                    return [
                        $gameId => [
                            "name" => $existingGames[$gameId]["name"] ?? "Unknown Game",
                            "background_image" => $existingGames[$gameId]["background_image"] ?? null,
                        ]
                    ];
                }

                $game = new Game([
                    "user_id" => $gameData->user_id,
                    "game_id" => $gameId,
                ]);
                $game->id = $gameData->id;
                $game->timestamps = false;

                $info = $game->getInfo();
                return [
                    $gameId => [
                        "name" => $info["name"] ?? "Unknown Game",
                        "background_image" => $info["background_image"] ?? null,
                    ]
                ];
            })
            ->toArray();
    }

    private function getOrCreateVote($lobby)
    {
        if (!$lobby->getLobbyState()) {
            return null;
        }

        $voteId = "vote_" . $lobby->getId();
        $vote = Cache::get($voteId);

        if (!$vote) {
            $games = $this->getLobbyGames($lobby);
            $vote = new Vote($lobby, $games);
            Cache::put($voteId, $vote);
            return $vote;
        }

        $currentGames = $this->getLobbyGames($lobby, $vote->getGames());
        $vote->syncNewGames($currentGames);
        Cache::put($voteId, $vote);

        return $vote;
    }

    public function postVote(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(
                    ["success" => false, "error" => "User not authenticated"],
                    401,
                );
            }

            $validated = $request->validate([
                "game_id" => "required|integer",
                "vote" => "required|integer|in:1,-1",
            ]);

            $userLobby = $this->getCurrentLobby($user);

            if (!$userLobby) {
                return response()->json(
                    ["success" => false, "error" => "You are not in any lobby"],
                    400,
                );
            }

            $voteId = "vote_" . $userLobby->getId();
            $lock = Cache::lock("lock_" . $voteId, 10);

            try {
                $lock->block(5);

                $vote = $this->getOrCreateVote($userLobby);

                if (!$vote) {
                    return response()->json(
                        [
                            "success" => false,
                            "error" => "Voting has not started yet",
                        ],
                        400,
                    );
                }

                $playerVotes = $vote->getPlayerVotes();
                if (
                    isset($playerVotes[$user->id][$validated["game_id"]]) &&
                    $playerVotes[$user->id][$validated["game_id"]] !== 0
                ) {
                    return response()->json(
                        [
                            "success" => false,
                            "error" => "You have already voted on this game",
                        ],
                        400,
                    );
                }

                $vote->voteGame(
                    $validated["game_id"],
                    $user->id,
                    $validated["vote"],
                );

                Cache::put($voteId, $vote);
            } catch (\Illuminate\Contracts\Cache\LockTimeoutException $e) {
                return response()->json(
                    [
                        "success" => false,
                        "error" => "Server busy. Please try again.",
                    ],
                    503,
                );
            } finally {
                $lock->release();
            }

            $statistic = UserStatistic::getOrCreateForUser($user->id);
            $statistic->incrementGamesVotedOn();

            $games = $vote->getGames();
            $gameData = $games[$validated["game_id"]];

            $userGameVote = UserGameVote::firstOrCreate(
                ['user_id' => $user->id, 'game_id' => $validated["game_id"]],
                ['upvotes' => 0, 'downvotes' => 0, 'game_name' => $gameData["name"]]
            );

            if ($userGameVote->game_name !== $gameData["name"]) {
                $userGameVote->update(['game_name' => $gameData["name"]]);
            }

            if ($validated["vote"] == 1) {
                $userGameVote->increment('upvotes');
            } else {
                $userGameVote->increment('downvotes');
            }

            return response()->json(
                [
                    "success" => true,
                    "message" => "Vote recorded successfully",
                    "game_id" => $validated["game_id"],
                    "game_name" => $gameData["name"],
                    "user_vote" => $validated["vote"],
                    "new_total_votes" => $gameData["votes"],
                    "new_upvotes" => $gameData["upvotes"],
                    "new_downvotes" => $gameData["downvotes"],
                ],
                200,
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(
                [
                    "success" => false,
                    "error" => "Validation error",
                    "errorMessages" => $e->errors(),
                ],
                422,
            );
        } catch (\Exception $e) {
            \Log::error("Error voting: " . $e->getMessage());
            return response()->json(
                [
                    "success" => false,
                    "error" => "Failed to vote. Please try again.",
                    "errorDetails" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function deleteEmptyAndOldLobbies()
    {
        $lobbies = Cache::get("lobbies", []);

        $thirtyMinutesAgo = now()->subMinutes(30);

        $updatedLobbies = [];
        $deletedCount = 0;

        foreach ($lobbies as $id => $lobby) {
            if (
                $lobby->getUserCount() === 0 &&
                $lobby->created_at < $thirtyMinutesAgo
            ) {
                $deletedCount++;
            } else {
                $updatedLobbies[$id] = $lobby;
            }
        }

        Cache::put("lobbies", $updatedLobbies);
    }

    public function voteResult(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(
                    ["success" => false, "error" => "User not authenticated"],
                    401,
                );
            }

            $userLobby = $this->getCurrentLobby($user);

            if (!$userLobby) {
                return response()->json(
                    ["success" => false, "error" => "You are not in any lobby"],
                    400,
                );
            }

            $lobby = $userLobby;

            if (!$lobby->getLobbyState()) {
                return response()->json(
                    [
                        "success" => false,
                        "error" => "Voting has not started yet",
                    ],
                    401,
                );
            }

            $voteId = "vote_" . $lobby->getId();
            $lock = Cache::lock("lock_" . $voteId, 10);

            try {
                $lock->block(5);
                $vote = $this->getOrCreateVote($lobby);
            } catch (\Illuminate\Contracts\Cache\LockTimeoutException $e) {
                return response()->json(
                    [
                        "success" => false,
                        "error" => "Server busy. Please try again.",
                    ],
                    503,
                );
            } finally {
                $lock->release();
            }

            if (!$vote) {
                return response()->json(
                    ["success" => false, "error" => "No voting session found"],
                    400,
                );
            }

            $isVotingComplete = $vote->isVotingComplete($lobby);

            if (!$isVotingComplete) {
                $progress = $vote->getRemainingPlayersProgress($lobby);
                return response()->json(
                    array_merge([
                        "success" => true,
                        "voting_finished" => false,
                    ], $progress),
                    200
                );
            }

            $results = $vote->getVoteResults();
            $favorites = $vote->getVotedGames();

            $sortedGames = collect($results["games"])
                ->sortByDesc("votes")
                ->toArray();

            return response()->json(
                [
                    "success" => true,
                    "voting_finished" => true,
                    "lobby_id" => $lobby->getId(),
                    "games" => $sortedGames,
                    "players_favorite_games" => $favorites,
                    "total_votes_cast" => $results["total_votes"],
                    "total_players" => count($lobby->getUsers()),
                    "player_votes" => $results["player_votes"],
                ],
                200,
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(
                [
                    "success" => false,
                    "error" => "Validation error",
                    "errorMessages" => $e->errors(),
                ],
                422,
            );
        } catch (\Exception $e) {
            \Log::error("Error getting vote results: " . $e->getMessage());
            return response()->json(
                [
                    "success" => false,
                    "error" => "Failed to get vote results. Please try again.",
                ],
                500,
            );
        }
    }

    public function startVoting(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(
                    ["success" => false, "error" => "User not authenticated"],
                    401,
                );
            }

            $lobby = $this->getCurrentLobby($user);

            if (!$lobby) {
                return response()->json(
                    ["success" => false, "error" => "You are not in any lobby"],
                    400,
                );
            }

            if ($lobby->getLobbyState()) {
                return response()->json(
                    [
                        "success" => false,
                        "error" => "Voting has already started",
                    ],
                    400,
                );
            }

            $games = $this->getLobbyGames($lobby);

            if (empty($games)) {
                $lobbies = Cache::get("lobbies", []);
                unset($lobbies[$lobby->getId()]);
                Cache::put("lobbies", $lobbies);

                return response()->json(
                    [
                        "success" => false,
                        "error" => "No games to vote on. Lobby has been ended.",
                    ],
                    400,
                );
            }

            $lobby->startLobby($user);

            $lobbies = Cache::get("lobbies", []);
            $lobbyId = $lobby->getId();
            $lobbies[$lobbyId] = $lobby;
            Cache::put("lobbies", $lobbies);

            return response()->json(
                [
                    "success" => true,
                    "message" => "Voting started successfully",
                ],
                200,
            );
        } catch (\Exception $e) {
            \Log::error("Error starting voting: " . $e->getMessage());
            return response()->json(
                [
                    "success" => false,
                    "error" => "Failed to start voting. Please try again.",
                    "errorMessages" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function getVoteGames(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(
                    ["success" => false, "error" => "User not authenticated"],
                    401,
                );
            }

            $currentLobby = $this->getCurrentLobby($user);

            if (!$currentLobby) {
                return response()->json(
                    ["success" => false, "error" => "You are not in any lobby"],
                    400,
                );
            }

            if (!$currentLobby->getLobbyState()) {
                return response()->json(
                    [
                        "success" => false,
                        "error" => "Voting has not started yet",
                    ],
                    400,
                );
            }

            $games = collect($currentLobby->getUsers())
                ->map(function ($userId) {
                    $user = User::find($userId);
                    $userGames = $user->getFavoritedGames();

                    $gamesWithDetails = collect($userGames)
                        ->map(function ($gameData) {
                            $game = new Game([
                                "user_id" => $gameData->user_id,
                                "game_id" => $gameData->game_id,
                            ]);
                            $game->id = $gameData->id;
                            $game->timestamps = false;

                            return [
                                "id" => $gameData->id,
                                "user_id" => $gameData->user_id,
                                "game_id" => $gameData->game_id,
                                "info" => $game->getInfo(),
                            ];
                        })
                        ->filter()
                        ->toArray();

                    return $gamesWithDetails;
                })
                ->flatten(1)
                ->filter()
                ->unique("game_id")
                ->values()
                ->toArray();

            if (empty($games)) {
                $lobbies = Cache::get("lobbies", []);
                unset($lobbies[$currentLobby->getId()]);
                Cache::put("lobbies", $lobbies);

                return response()->json(
                    [
                        "success" => false,
                        "error" => "No games to vote on. Lobby has been ended.",
                    ],
                    400,
                );
            }

            $voteId = "vote_" . $currentLobby->getId();
            $lock = Cache::lock("lock_" . $voteId, 10);
            $voteSession = null;

            try {
                $lock->block(5);
                $voteSession = $this->getOrCreateVote($currentLobby);
            } catch (\Illuminate\Contracts\Cache\LockTimeoutException $e) {
                return response()->json(
                    [
                        "success" => false,
                        "error" => "Server busy. Please try again.",
                    ],
                    503,
                );
            } finally {
                $lock->release();
            }

            if ($voteSession) {
                $playerVotes = $voteSession->getPlayerVotes();
                $userVotes = $playerVotes[$user->id] ?? [];

                $games = array_filter($games, function($g) use ($userVotes) {
                    return !isset($userVotes[$g['game_id']]) || $userVotes[$g['game_id']] === 0;
                });
                $games = array_values($games);
            }

            return response()->json(
                [
                    "success" => true,
                    "games" => $games,
                ],
                200,
            );
        } catch (\Exception $e) {
            \Log::error("Error getting current game: " . $e->getMessage());
            return response()->json(
                [
                    "success" => false,
                    "error" => "Failed to get current game. Please try again.",
                ],
                500,
            );
        }
    }

    private function getWinner($games)
    {
        if (empty($games)) {
            return null;
        }

        $winner = array_reduce($games, function ($carry, $game) {
            return !$carry || $game["votes"] > $carry["votes"] ? $game : $carry;
        });

        return [
            "game_id" => array_search($winner, $games),
            "game_name" => $winner["name"],
            "total_votes" => $winner["votes"],
        ];
    }

    public function resetVoting(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(
                    ["success" => false, "error" => "User not authenticated"],
                    401,
                );
            }

            $lobby = $this->getCurrentLobby($user);

            if (!$lobby) {
                return response()->json(
                    ["success" => false, "error" => "You are not in any lobby"],
                    400,
                );
            }

            $voteId = "vote_" . $lobby->getId();
            Cache::forget($voteId);

            $lobby->state = false;

            $lobbies = Cache::get("lobbies", []);
            $lobbyId = $lobby->getId();
            $lobbies[$lobbyId] = $lobby;
            Cache::put("lobbies", $lobbies);

            return response()->json(
                [
                    "success" => true,
                    "message" => "Voting reset successfully. You can start a new vote.",
                ],
                200,
            );
        } catch (\Exception $e) {
            \Log::error("Error resetting voting: " . $e->getMessage());
            return response()->json(
                [
                    "success" => false,
                    "error" => "Failed to reset voting. Please try again.",
                    "errorMessages" => $e->getMessage(),
                ],
                500,
            );
        }
    }
}