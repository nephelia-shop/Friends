<?php
namespace Nepheliashop\Friends\Sessions;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;

class SessionListener implements Listener {

    public function onJoin(PlayerJoinEvent $event) : void
    {
        $player = $event->getPlayer();
        SessionManager::getInstance()->login($player);
    }

    public function onQuit(PlayerQuitEvent $event) : void
    {
        $player = $event->getPlayer();
        SessionManager::getInstance()->removeSession($player->getXuid());
    }

}