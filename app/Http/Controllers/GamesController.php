<?php

namespace App\Http\Controllers;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Game;
use Illuminate\Support\Facades\Log;

class GamesController extends Controller
{
    public function searchGame(Request $request)
    {
        $request->validate([
            "search" => "required|string",
            "page" => "sometimes|integer|min:1",
            "genres" => "sometimes|string",
            "tags" => "sometimes|string",
            "metacritic" => "sometimes|string",
            "ordering" => "sometimes|string",
        ]);

        try {
            $params = [
                "key" => env("RAWG_API_KEY"),
                "search" => $request->search,
                "page" => $request->page ?? 1,
                "page_size" => 12,
            ];

            if ($request->filled("genres")) {
                $params["genres"] = $request->genres;
            }

            if ($request->filled("tags")) {
                $params["tags"] = $request->tags;
            }

            if ($request->filled("metacritic")) {
                $params["metacritic"] = $request->metacritic;
            }

            if ($request->filled("ordering")) {
                $params["ordering"] = $request->ordering;
            }

            $results = Http::get(
                "https://api.rawg.io/api/games",
                $params,
            );

            $results->throw();

            $response = $results->json();

            if (isset($response["results"]) && $request->user()) {
                $userFavorites = Game::where("user_id", $request->user()->id)
                    ->pluck("game_id")
                    ->toArray();

                foreach ($response["results"] as &$game) {
                    $game["favorited"] = in_array($game["id"], $userFavorites);
                }
            }

            return $response;
        } catch (\Exception $e) {
            Log::error("Failed to get games from RAWG API", [
                "error" => $e->getMessage(),
            ]);

            return response()->json(
                ["error" => "Failed to get games from RAWG API"],
                500,
            );
        }
    }

    public function addToFavourites(Request $request)
    {
        $request->validate([
            "game_id" => "required|integer",
        ]);

        try {
            $userId = $request->user()->id;
            $gameId = $request->game_id;

            Log::info("addToFavourites: user_id=" . $userId . " game_id=" . $gameId);

            $favorite = Game::where("game_id", $gameId)
                ->where("user_id", $userId)
                ->first();

            Log::info("addToFavourites: existing favorite found=" . ($favorite ? "yes" : "no"));

            if ($favorite) {
                $favorite->delete();
                $message = "Game removed from favourites";
            } else {
                Game::create([
                    "game_id" => $gameId,
                    "user_id" => $userId,
                ]);
                $message = "Game added to favourites";
            }

            Log::info("addToFavourites: result=" . $message);

            return response()->json(
                ["success" => $message],
                200,
            );
        } catch (Throwable $th) {
            Log::error("Failed to update game favorite status", [
                "error" => $th->getMessage(),
                "game_id" => $request->game_id,
                "user_id" => $request->user() ? $request->user()->id : 'null',
            ]);

            return response()->json(
                [
                    "error" => "Failed to set game as favourite",
                    "errorMessage" => $th->getMessage(),
                ],
                500,
            );
        }
    }

    public function getUserFavourites(Request $request)
    {
        $request->validate([
            "search" => "sometimes|string|nullable|max:255",
            "user_id" => "sometimes|integer|exists:users,id",
        ]);
        try {
            $favResponse = [];
            $favourites = Game::where(
                "user_id",
                isset($request->user_id) ? $request->user_id : $request->user()->id,
            )->get();

            Log::info("getUserFavourites: found " . count($favourites) . " favorites for user_id=" . (isset($request->user_id) ? $request->user_id : $request->user()->id));

            foreach ($favourites as $key => $value) {
                Log::info("getUserFavourites: processing game_id=" . $value->game_id);
                $gameInfo = $value->getInfo();
                Log::info("getUserFavourites: gameInfo for game_id=" . $value->game_id . " is_array=" . (is_array($gameInfo) ? "yes" : "no") . " keys=" . (is_array($gameInfo) ? implode(",", array_keys($gameInfo)) : "N/A"));
                if (is_array($gameInfo) && isset($gameInfo["id"])) {
                    $gameInfo["favorited"] = true;
                    $favResponse[] = $gameInfo;
                } else {
                    Log::info("getUserFavourites: using fallback for game_id=" . $value->game_id);
                    $favResponse[] = [
                        "id" => $value->game_id,
                        "name" => "Unknown Game",
                        "background_image" => null,
                        "favorited" => true,
                    ];
                }
            }

            if ($request->filled("search")) {
                $searchTerm = strtolower($request->search);
                $favResponse = array_filter($favResponse, function ($game) use (
                    $searchTerm,
                ) {
                    return stripos(
                        strtolower($game["name"] ?? ""),
                        $searchTerm,
                    ) !== false;
                });
            }

            return response()->json($favResponse, 200);
        } catch (Throwable $th) {
            Log::error("Failed to get Favourited games", [
                "error" => $th->getMessage(),
            ]);

            return response()->json(
                [
                    "error" => "Failed to get favorited games",
                    "errorMessage" => $th->getMessage(),
                ],
                500,
            );
        }
    }

    public function delUserFavourite(Request $request)
    {
        $request->validate([
            "game_id" => "required|integer",
        ]);
        try {
            Game::where("game_id", $request->game_id)->delete();

            return response()->json(
                ["success" => "Game removed from favourites"],
                200,
            );
        } catch (\Throwable $th) {
            Log::error("Failed to remove game from favorite", [
                "error" => $th->getMessage(),
            ]);

            return response()->json(
                ["error" => "Failed to remove game from favourites"],
                500,
            );
        }
    }

    public function getFilters(Request $request)
    {
        try {
            $key = env("RAWG_API_KEY");
            
            $genresResponse = Http::get("https://api.rawg.io/api/genres", [
                "key" => $key,
            ]);
            $genresResponse->throw();
            $genresData = $genresResponse->json();
            
            $tagsResponse = Http::get("https://api.rawg.io/api/tags", [
                "key" => $key,
                "page_size" => 50,
            ]);
            $tagsResponse->throw();
            $tagsData = $tagsResponse->json();
            
            $genres = collect($genresData["results"] ?? [])->map(fn($g) => [
                "id" => $g["id"],
                "name" => $g["name"],
            ])->toArray();
            
            $tags = collect($tagsData["results"] ?? [])->map(fn($t) => [
                "id" => $t["id"],
                "name" => $t["name"],
            ])->toArray();

            return response()->json([
                "genres" => $genres,
                "tags" => $tags,
            ], 200);
        } catch (\Exception $e) {
            Log::error("Failed to get filters from RAWG API", [
                "error" => $e->getMessage(),
            ]);

            return response()->json([
                "genres" => [],
                "tags" => [],
            ], 200);
        }
    }

    public function getGameDetails(Request $request)
    {
        $request->validate([
            "game_id" => "required|integer",
        ]);

        try {
            $response = Http::get(
                "https://api.rawg.io/api/games/{$request->game_id}?key=" .
                    env("RAWG_API_KEY"),
            );

            $response->throw();

            $data = $response->json();

            return response()->json([
                "name" => $data["name"] ?? null,
                "background_image" => $data["background_image"] ?? null,
                "released" => $data["released"] ?? null,
                "platforms" => $data["platforms"] ?? [],
                "developers" => $data["developers"] ?? [],
                "publishers" => $data["publishers"] ?? [],
                "genres" => $data["genres"] ?? [],
            ], 200);
        } catch (\Throwable $th) {
            Log::error("Failed to get game details from RAWG API", [
                "error" => $th->getMessage(),
            ]);

            return response()->json(
                ["error" => "Failed to get game details"],
                500,
            );
        }
    }

    public function getRecommendations(Request $request)
    {
        try {
            $key = env("RAWG_API_KEY");

            $userFavoriteIds = Game::where("user_id", $request->user()->id)
                ->pluck("game_id")
                ->toArray();

            $recommended = [];
            $seen = [];

            $lastFavorited = Game::where("user_id", $request->user()->id)
                ->orderBy("created_at", "desc")
                ->first();

            if ($lastFavorited) {
                $gameInfo = Http::get(
                    "https://api.rawg.io/api/games/{$lastFavorited->game_id}",
                    ["key" => $key]
                );

                if ($gameInfo->successful()) {
                    $genres = collect($gameInfo->json()["genres"] ?? [])
                        ->pluck("slug")
                        ->toArray();

                    if (!empty($genres)) {
                        $pageStart = rand(1, 5);

                        $searchResponse = Http::get(
                            "https://api.rawg.io/api/games",
                            [
                                "key" => $key,
                                "genres" => implode(",", $genres),
                                "metacritic" => "80,100",
                                "page_size" => 12,
                                "page" => $pageStart,
                                "ordering" => "-metacritic",
                            ]
                        );

                        if ($searchResponse->successful()) {
                            foreach ($searchResponse->json()["results"] ?? [] as $game) {
                                if (isset($seen[$game["id"]])) {
                                    continue;
                                }
                                $seen[$game["id"]] = true;
                                $game["favorited"] = in_array($game["id"], $userFavoriteIds);
                                if (!$game["favorited"]) {
                                    $recommended[] = $game;
                                }
                            }
                        }
                    }
                }
            }

            if (empty($recommended) && !empty($userFavoriteIds)) {
                $sampleIds = $userFavoriteIds;
                shuffle($sampleIds);
                $sampleIds = array_slice($sampleIds, 0, min(3, count($sampleIds)));

                foreach ($sampleIds as $gameId) {
                    $response = Http::get(
                        "https://api.rawg.io/api/games/{$gameId}/suggested",
                        ["key" => $key, "page_size" => 6]
                    );

                    if (!$response->successful()) {
                        continue;
                    }

                    foreach ($response->json()["results"] ?? [] as $game) {
                        if (isset($seen[$game["id"]])) {
                            continue;
                        }
                        $seen[$game["id"]] = true;
                        $game["favorited"] = in_array($game["id"], $userFavoriteIds);
                        if (!$game["favorited"]) {
                            $recommended[] = $game;
                        }
                    }
                }
            }

            if (empty($recommended)) {
                $pageStart = rand(1, 5);
                $fallback = Http::get("https://api.rawg.io/api/games", [
                    "key"       => $key,
                    "page_size" => 12,
                    "page"      => $pageStart,
                    "ordering"  => "-metacritic",
                    "metacritic" => "80,100",
                ]);
                $fallback->throw();

                foreach ($fallback->json()["results"] ?? [] as $game) {
                    $game["favorited"] = in_array($game["id"], $userFavoriteIds);
                    if (!$game["favorited"]) {
                        $recommended[] = $game;
                    }
                }
            }

            return response()->json(["results" => array_slice($recommended, 0, 12)], 200);

        } catch (\Exception $e) {
            Log::error("Failed to get recommendations from RAWG API", [
                "error" => $e->getMessage(),
            ]);

            return response()->json(
                ["error" => "Failed to get game recommendations"],
                500,
            );
        }
    }
}
