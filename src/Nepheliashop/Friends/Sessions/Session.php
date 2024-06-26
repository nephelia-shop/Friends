<?php
namespace Nepheliashop\Friends\Sessions;

use Error;
use pocketmine\player\Player;
use Nepheliashop\Friends\Database\DatabaseManager;

class Session {

    protected string $name;

    public const LIMIT = 10;

    private array $friends;
    private array $sent;
    private array $received;
    private int $limit = self::LIMIT;


    public function __construct(public Player $player)
    {
        $this->load();
        $this->name = $player->getName();
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function load() : void
    {
        DatabaseManager::getInstance()->getDatabase()->executeSelect("players.get", ["player_name" => $this->player->getName()], function (array $rows){
            $data = $rows[0];
            $this->friends = $data['friends_list'] === null ? [] : json_decode($data['friends_list'], true);
            $this->received = $data['invitations_received'] === null ? [] : json_decode($data['invitations_received'], true);
            $this->sent = $data['invitations_sent'] === null ? [] : json_decode($data['invitations_sent'], true);
            $this->limit = ($data['limit'] === null or $data['limit'] === 1) ? self::LIMIT : (int)$data['limit'];
        }, fn() => throw new Error("Failed to load " . $this->player->getName() . "' session"));
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFriends(): array
    {
        return $this->friends;
    }

    public function getSent(): array
    {
        return $this->sent;
    }

    public function getReceived(): array
    {
        return $this->received;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function isFriendWith(string $friend) : bool
    {
        return in_array($friend, $this->friends);
    }

    public function addReceivedInvite(string $name) : void
    {
        $this->received[] = $name;
        DatabaseManager::getInstance()->addReceivedInvite($this->name, $this->received);
    }

    public function hasReceivedFrom(string $name) : bool
    {
        return in_array($name, $this->received);
    }

    public function removeReceivedInvite(string $name) : void
    {
        $index = array_search($name, $this->received);
        if ($index !== false){
            unset($this->received[$index]);
        }
        DatabaseManager::getInstance()->removeReceivedInvite($this->name, $this->received);
    }

    public function addSendInvite(string $name) : void
    {
        $this->sent[] = $name;
        DatabaseManager::getInstance()->addSendInvite($this->name, $this->sent);
    }

    public function removeSendInvite(string $name) : void
    {
        $index = array_search($name, $this->sent);
        if ($index !== false){
            unset($this->sent[$index]);
        }
        DatabaseManager::getInstance()->removeSendInvite($this->name, $this->sent);
    }

    public function addFriend(string $friend) : void
    {
        $this->friends[] = $friend;
        DatabaseManager::getInstance()->addFriend($this->name, $this->friends);
    }

    public function removeFriend(string $friend) : void
    {
        $index = array_search($friend, $this->friends);
        if ($index !== false){
            unset($this->friends[$index]);
        }
        DatabaseManager::getInstance()->removeFriend($this->name, $this->friends);
    }

    public function hasInvited(string $name) : bool{
        return in_array($name, $this->sent);
    }

}