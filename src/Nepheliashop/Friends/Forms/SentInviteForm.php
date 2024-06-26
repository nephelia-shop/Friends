<?php
namespace Nepheliashop\Friends\Forms;

use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\player\Player;
use Nepheliashop\Friends\Sessions\SessionManager;

class SentInviteForm {

    public static function send(Player $player) : void
    {
        $session = SessionManager::getInstance()->getByXuid($player->getXuid());
        $form = new MenuForm("Friends", "§7- §fList of invitations you have sent", array_map(function (string $sent){
            return new MenuOption($sent);
        }, $session->getSent()), function(Player $player, int $selectedOption) use ($session): void{
            $sends = $session->getSent();
            $sends = array_values($sends);
            self::manageReceivedInvite($player, $sends[$selectedOption]);
        });

        $player->sendForm($form);
    }

    public static function manageReceivedInvite(Player $player, string $sent) : void
    {
        $form = new MenuForm($sent, "", [
            new MenuOption("Cancel"),
            new MenuOption("Back")
        ], function(Player $player, int $selectedOption) use ($sent): void{
            match ($selectedOption){
                0 => $player->chat("/friends cancel $sent"),
                default => self::send($player)
            };
        });
        $player->sendForm($form);
    }

}