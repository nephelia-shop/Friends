<?php
namespace Nepheliashop\Friends;

use pocketmine\plugin\PluginBase;
use Nepheliashop\Friends\Commands\FriendsCommand;
use Nepheliashop\Friends\Database\DatabaseManager;
use Nepheliashop\Friends\Sessions\SessionManager;

class Main extends PluginBase {

    protected function onEnable(): void
    {
        $this->saveDefaultConfig();
        DatabaseManager::getInstance()->startup($this);
        SessionManager::getInstance()->startup($this);
        $this->getServer()->getCommandMap()->register('friends', new FriendsCommand());
    }

    protected function onDisable(): void
    {
        DatabaseManager::getInstance()->getDatabase()->waitAll();
        DatabaseManager::getInstance()->getDatabase()->close();
    }

}