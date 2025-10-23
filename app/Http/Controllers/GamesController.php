<?php

namespace App\Http\Controllers;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Game;
use Illuminate\Support\Facades\Log;

class gamesController extends Controller
{
    public function searchGame(Request $request)
    {
        $request->validate([
            "search" => "required|string",
        ]);

        try {
            $results = Http::get(
                "https://api.rawg.io/api/games?key={key}&search={search}&page={page}&page_size=12",
                [
                    "key" => env("RAWG_API_KEY"),
                    "search" => $request->search,
                    "page" => $request->page,
                ],
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

    //metode games kontrolierim kas atzīmē spēli kā mīļāko lietotājam
    public function addToFavourites(Request $request)
    {
        //validē lietotāju atsūtīto spēles id
        $request->validate([
            "game_id" => "required|integer",
        ]);

        //loģikas daļa kas atzīmē spēli kā mīļāko lietotājam
        try {
            //parbauda vai lietotājs jau ir šo spēli atzīmējis kā mīļāko
            if (
                Game::where("game_id", $request->game_id)
                    ->where("user_id", $request->user()->id)
                    ->exists()
            ) {
                //Izdzēš ierakstu par mīļāko spēli
                Game::where("game_id", $request->game_id)
                    ->where("user_id", $request->user()->id)
                    ->delete();
            }
            //Ja lietotājs nav šī spēle atzīmēta kā mīļāko izveido jaunu ierakstu
            else {
                Game::create([
                    "game_id" => $request->game_id,
                    "user_id" => $request->user()->id,
                ]);
            }
            //Veiksmīgi spēle atzīmēta kā mīļāko
            return response()->json(
                ["success" => "Game added to favourites"],
                200,
            );
        } catch (Throwable $th) {
            //Izvada kļūdu par spēļu API ja tāda uzradusies
            Log::error("Failed to get games from RAWG API", [
                "error" => $th->getMessage(),
            ]);

            //Izvada kļūdu ziņojumu ja ir sastopta kļūda
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
            "search" => "sometimes|max:255",
        ]);
        try {
            $favResponse = [];
            if (!isset($request->user_id)) {
                $favourites = Game::where(
                    "user_id",
                    $request->user()->id,
                )->get();
                foreach ($favourites as $key => $value) {
                    $gameInfo = $value->getInfo();
                    if (is_array($gameInfo)) {
                        $gameInfo["favorited"] = true;
                        $favResponse[] = $gameInfo;
                    }
                }
            } else {
                $favourites = Game::where("user_id", $request->user_id)->get();
                foreach ($favourites as $key => $value) {
                    $gameInfo = $value->getInfo();
                    if (is_array($gameInfo)) {
                        $gameInfo["favorited"] = true;
                        $favResponse[] = $gameInfo;
                    }
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

    public function delUserFavourit(Request $request)
    {
        $request->validate([
            "game_id" => "required",
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
}
