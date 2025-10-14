<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Queue\SerializesModels;

class Lobby
{
    public string $id = "";
    private array $users = [];
    public string $name = "";
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        "state" => "boolean",
    ];

    public bool $state = false;
    public string $filter = "public";
    public int $maxPlayers = 2;
    private ?User $creator = null;
    private ?array $friendsList = null;
    public $created_at = null;

    use SerializesModels;

    public function __construct(
        string $name = "",
        string $filter = "public",
        int $maxPlayers = 2,
        ?User $creator = null,
    ) {
        $this->id = uniqid("lobby_");
        $this->name = $name;
        $this->filter = $filter;
        $this->maxPlayers = $maxPlayers;
        $this->creator = $creator;
        $this->created_at = now();
        $this->state = false;

        // If creator is provided and lobby is friends-only, preload friends list
        if ($this->creator && $this->filter === "friends") {
            $this->loadFriendsList();
        }

        if ($this->creator) {
            $this->addUser($this->creator->id);
        }
    }

    /**
     * Set the creator user object and preload friends if needed
     */
    public function setCreator(User $creator): void
    {
        $this->creator = $creator;
        if ($this->filter === "friends") {
            $this->loadFriendsList();
        }
    }

    /**
     * Load and extract friend IDs from the creator's friends list
     */
    private function loadFriendsList(): void
    {
        if (!$this->creator) {
            $this->friendsList = [];
            return;
        }

        $friendRelations = $this->creator->getUsersFriends($this->creator);
        $friendIds = [];

        foreach ($friendRelations as $friendRelation) {
            // Extract the friend's ID based on whether creator is sender or receiver
            if (
                isset($friendRelation["sender_id"]) &&
                $friendRelation["sender_id"] == $this->creator->id
            ) {
                $friendIds[] = (string) $friendRelation["receiver_id"];
            } elseif (
                isset($friendRelation["receiver_id"]) &&
                $friendRelation["receiver_id"] == $this->creator->id
            ) {
                $friendIds[] = (string) $friendRelation["sender_id"];
            }
        }

        $this->friendsList = array_unique($friendIds);
    }

    public function addUser(string $userId): bool
    {
        // If user is already in the lobby, return false
        if (in_array($userId, $this->users)) {
            return false;
        }

        // If lobby is friends-only and user is not the creator, check if they're friends
        if (
            $this->filter === "friends" &&
            (string) $userId !== (string) $this->creator->id
        ) {
            // If we don't have the friends list yet but have the creator, try to load it
            if ($this->friendsList === null && $this->creator) {
                $this->loadFriendsList();
            }

            // Check if user is in friends list
            if (
                $this->friendsList === null ||
                !in_array((string) $userId, $this->friendsList, true)
            ) {
                return false; // User is not in the creator's friends list
            }
        }

        $this->users[] = $userId;
        return true;
    }

    public function canUserJoin(string $userId): bool
    {
        // Check if user is already in the lobby
        if (in_array($userId, $this->users)) {
            return false;
        }

        // Check if lobby is full
        if (count($this->users) >= $this->maxPlayers) {
            return false;
        }

        // If lobby is friends-only, check if user is in the creator's friends list
        if (
            $this->filter === "friends" &&
            (string) $userId !== (string) $this->creator->id
        ) {
            // If we don't have the friends list yet but have the creator, try to load it
            if ($this->friendsList === null && $this->creator) {
                $this->loadFriendsList();
            }

            return $this->friendsList !== null &&
                in_array((string) $userId, $this->friendsList, true);
        }

        return true;
    }

    public function removeUser(string $userId): bool
    {
        $key = array_search($userId, $this->users);
        if ($key !== false) {
            unset($this->users[$key]);
            $this->users = array_values($this->users); // Reindex array
            return true;
        }
        return false;
    }

    public function getUsers(): array
    {
        return $this->users;
    }

    public function getUserCount(): int
    {
        return count($this->users);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCreatorId(): string
    {
        return $this->creator->id;
    }

    public function getLobbyState(): bool
    {
        return $this->state;
    }

    public function startLobby(User $user): bool
    {
        if ($user->id !== $this->creator->id) {
            return false;
        }

        // Only allow starting if the lobby is not already started
        if ($this->state === true) {
            return false;
        }

        $this->state = true;
        return true;
    }

    public function toArray(): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "users" => $this->users,
            "user_count" => count($this->users),
            "state" => $this->state === true, // Ensure boolean type
            "filter" => $this->filter,
            "max_players" => $this->maxPlayers,
            "creator_id" => $this->creator ? $this->creator->id : null,
        ];
    }
}
