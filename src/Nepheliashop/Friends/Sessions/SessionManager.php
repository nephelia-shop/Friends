<?php
namespace Nepheliashop\Friends\Sessions;

use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use Nepheliashop\Friends\Database\DatabaseManager;

class SessionManager {
    use SingletonTrait;

    public function startup(PluginBase $plugin) : void
    {
        $plugin->getServer()->getPluginManager()->registerEvents(new SessionListener(), $plugin);
    }

    /** @var Session[] */
    protected array $sessions = [];

    public function addSession(Session $session, string $identifier) : void
    {
        $this->sessions[$identifier] = $session;
    }

    public function removeSession(string $xuid) : void
    {
        if ($this->sessions[$xuid] !== null){
            unset($this->sessions[$xuid]);
        }
    }

    public function getByXuid(string $xuid) : ?Session
    {
        return $this->sessions[$xuid] ?? null;
    }

    public function getByName(string $name): ?Session
    {
        foreach ($this->getAll() as $session) {
            if (strtolower($session->getName()) == strtolower($name)) return $session;
        }
        return null;
    }

    public function getAll(): array
    {
        return $this->sessions;
    }

    public function login(Player $player) : void
    {
        DatabaseManager::getInstance()->getDatabase()->executeInsert("players.add", ["xuid" => $player->getXuid(), "player_name" => $player->getName()], function () use ($player) {
            $session = new Session($player);
            $this->addSession($session, $player->getXuid());
        }, fn() => $player->kick("Your connection is not stable, please login"));
    }

}