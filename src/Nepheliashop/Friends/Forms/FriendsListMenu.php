<?php
namespace Nepheliashop\Friends\Forms;

use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\player\Player;
use Nepheliashop\Friends\Sessions\SessionManager;

class FriendsListMenu {

    public static function send(Player $player) : void
    {
        $session = SessionManager::getInstance()->getByXuid($player->getXuid());
        $form = new MenuForm("Friends", '§7- §fList of your friends', array_map(function (string $friend){
            return new MenuOption($friend);
        }, $session->getFriends()), function(Player $player, int $selectedOption) use ($session): void{
            $friends = $session->getFriends();
            $friends = array_values($friends);
            self::manageFriend($player, $friends[$selectedOption]);
        });

        $player->sendForm($form);
    }

    public static function manageFriend(Player $player, string $friend) : void
    {
        $form = new MenuForm($friend, "", [
            new MenuOption("Remove"),
            new MenuOption("Join"),
            new MenuOption("Back")
        ], function(Player $player, int $selectedOption) use ($friend): void{
            match ($selectedOption){
                0 => $player->chat("/friends remove $friend"),
                1 => $player->chat("/friends join $friend"),
                default => self::send($player)
            };
        });
        $player->sendForm($form);
    }

}