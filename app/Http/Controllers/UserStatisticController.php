<?php

namespace App\Http\Controllers;

use App\Models\UserStatistic;
use App\Models\UserGameVote;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class UserStatisticController extends Controller
{
    public function getStatistics(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json(
                    [
                        "success" => false,
                        "error" => "User not authenticated",
                    ],
                    401,
                );
            }

            $statistic = UserStatistic::getOrCreateForUser($user->id);

            $mostLiked = UserGameVote::where('user_id', $user->id)
                ->where('upvotes', '>', 0)
                ->orderByDesc('upvotes')
                ->first();

            $mostDisliked = UserGameVote::where('user_id', $user->id)
                ->where('downvotes', '>', 0)
                ->orderByDesc('downvotes')
                ->first();

            return response()->json(
                [
                    "success" => true,
                    "statistics" => [
                        "account_age_in_days" => $statistic->getAccountAgeInDays(),
                        "lobbies_created" => $statistic->lobbies_created,
                        "lobbies_joined" => $statistic->lobbies_joined,
                        "games_voted_on" => $statistic->games_voted_on,
                        "last_login" => $statistic->last_login?->toIso8601String(),
                        "most_liked_game" => $mostLiked ? [
                                "name" => $mostLiked->game_name,
                                "count" => $mostLiked->upvotes
                            ] : null,
                        "most_disliked_game" => $mostDisliked ? [
                                "name" => $mostDisliked->game_name,
                                "count" => $mostDisliked->downvotes
                            ] : null,
                    ],
                ],
                200,
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "error" => $e,
                ],
                500,
            );
        }
    }

    public function recordLogin(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json(
                    [
                        "success" => false,
                        "error" => "User not authenticated",
                    ],
                    401,
                );
            }

            $statistic = UserStatistic::getOrCreateForUser($user->id);
            $statistic->updateLastLogin();

            return response()->json(
                [
                    "success" => true,
                ],
                200,
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success" => false,
                    "error" => "Failed to record login",
                ],
                500,
            );
        }
    }
}
