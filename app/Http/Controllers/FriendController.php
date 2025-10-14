<?php

namespace App\Http\Controllers;

use App\Models\Friend;
use App\Models\User;
use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Pail\ValueObjects\Origin\Console;
use Symfony\Component\Console\Output\ConsoleOutput;

class FriendController extends Controller
{
    public function peopleSearch(Request $request)
    {
        $request->validate([
            "search" => "sometimes|max:255",
        ]);

        try {
            $sent_requests = Friend::where("sender_id", $request->user()->id)
                ->pluck("receiver_id")
                ->toArray();
            $received_requests = Friend::where(
                "receiver_id",
                $request->user()->id,
            )
                ->pluck("sender_id")
                ->toArray();
            $friends = array_merge($sent_requests, $received_requests);

            $usersQuery = User::where(
                "id",
                "!=",
                $request->user()->id,
            )->whereNotIn("id", $friends);

            if ($request->has("search")) {
                $usersQuery->where(
                    "name",
                    "like",
                    "%" . $request->search . "%",
                );
            }

            if (!$request->has("search")) {
                $usersQuery->limit(20);
            }

            $users = $usersQuery->get();

            return response()->json($users, 200);
        } catch (Throwable $th) {
            Log::error("Failed to get users", [
                "error" => $th->getMessage(),
            ]);

            return response()->json(
                [
                    "error" => "Failed to get users",
                    "errorMessage" => $th->getMessage(),
                ],
                500,
            );
        }
    }

    public function addFriend(Request $request)
    {
        $request->validate([
            "friend_id" => "required|exists:users,id",
        ]);

        try {
            if (
                Friend::where("sender_id", $request->user()->id)
                    ->where("receiver_id", $request->friend_id)
                    ->exists()
            ) {
                return response()->json(
                    ["error" => "Friend request already sent"],
                    400,
                );
            }
            if ($request->friend_id == $request->user()->id) {
                return response()->json(
                    ["error" => 'You can\'t be your own friends'],
                    400,
                );
            }

            Friend::create([
                "sender_id" => $request->user()->id,
                "receiver_id" => $request->friend_id,
                "accepted" => false,
            ]);

            return response()->json(["success" => "Friend request sent"], 200);
        } catch (Throwable $th) {
            Log::error("Failed to add friend", [
                "error" => $th->getMessage(),
            ]);
            return response()->json(
                [
                    "error" => "Failed to add friend",
                    "errorMessage" => $th->getMessage(),
                ],
                500,
            );
        }
    }

    public function acceptFriend(Request $request)
    {
        $request->validate([
            "friend_id" => "required|exists:friends,id",
        ]);

        try {
            $friendRecord = Friend::where("id", $request->friend_id)->first();

            if ($friendRecord->accepted) {
                return response()->json(
                    ["error" => "You already accepted this friend request"],
                    400,
                );
            }

            if ($friendRecord->receiver_id != $request->user()->id) {
                return response()->json(
                    ["error" => 'You can\'t accept this friend request'],
                    400,
                );
            }

            $friendRecord->update(["accepted" => true]);

            return response()->json(
                ["success" => "Friend request accepted"],
                200,
            );
        } catch (Throwable $th) {
            Log::error("Failed to accept friend", [
                "error" => $th->getMessage(),
            ]);

            return response()->json(
                [
                    "error" => "Failed to accept friend",
                    "errorMessage" => $th->getMessage(),
                ],
                500,
            );
        }
    }

    public function getFriends(Request $request)
    {
        $request->validate([
            "search" => "sometimes",
        ]);

        try {
            $sent_friends_query = Friend::where(
                "sender_id",
                $request->user()->id,
            )
                ->where("accepted", true)
                ->join("users", "users.id", "=", "friends.receiver_id")
                ->select("friends.*", "users.name");

            $received_friends_query = Friend::where(
                "receiver_id",
                $request->user()->id,
            )
                ->where("accepted", true)
                ->join("users", "users.id", "=", "friends.sender_id")
                ->select("friends.*", "users.name");

            if ($request->has("search") && $request->search != "") {
                $searchTerm = "%" . $request->search . "%";
                $sent_friends_query->where("users.name", "like", $searchTerm);
                $received_friends_query->where(
                    "users.name",
                    "like",
                    $searchTerm,
                );
            }

            $sent_friends = $sent_friends_query->get();
            $received_friends = $received_friends_query->get();

            $friends = array_merge(
                $sent_friends->toArray(),
                $received_friends->toArray(),
            );
            return response()->json($friends, 200);
        } catch (Throwable $th) {
            Log::error("Failed to get friend", [
                "error" => $th->getMessage(),
            ]);

            return response()->json(
                [
                    "error" => "Failed to get friends",
                    "errorMessage" => $th->getMessage(),
                ],
                500,
            );
        }
    }

    public function getPending(Request $request)
    {
        $request->validate([
            "search" => "sometimes",
        ]);
        try {
            $sent_query = Friend::where("sender_id", $request->user()->id)
                ->where("accepted", false)
                ->join("users", "users.id", "=", "friends.receiver_id")
                ->select("friends.*", "users.name");

            $received_query = Friend::where("receiver_id", $request->user()->id)
                ->where("accepted", false)
                ->join("users", "users.id", "=", "friends.sender_id")
                ->select("friends.*", "users.name");

            if ($request->has("search") && $request->search != "") {
                $searchTerm = "%" . $request->search . "%";
                $sent_query->where("users.name", "like", $searchTerm);
                $received_query->where("users.name", "like", $searchTerm);
            }

            $sent = $sent_query->get();
            $received = $received_query->get();

            $friends = array_merge($sent->toArray(), $received->toArray());

            return response()->json($friends, 200);
        } catch (Throwable $th) {
            Log::error("Failed to get friend", [
                "error" => $th->getMessage(),
            ]);

            return response()->json(
                [
                    "error" => "Failed to get friends",
                    "errorMessage" => $th->getMessage(),
                ],
                500,
            );
        }
    }

    public function removeFriend(Request $request)
    {
        $request->validate([
            "friend_id" => "required|exists:friends,id",
        ]);

        try {
            Friend::where("id", $request->friend_id)->delete();

            return response()->json(["success" => "Friend removed"], 200);
        } catch (Throwable $th) {
            Log::error("Failed to remove friend", [
                "error" => $th->getMessage(),
            ]);

            return response()->json(
                ["error" => "Failed to remove friend"],
                500,
            );
        }
    }
}
