<?php
namespace Nepheliashop\Friends\Forms;

use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\player\Player;
use Nepheliashop\Friends\Sessions\SessionManager;

class ReceivedInviteForm {

    public static function send(Player $player) : void
    {
        $session = SessionManager::getInstance()->getByXuid($player->getXuid());
        $form = new MenuForm("Friends", "§7- §fList of invitations you have received", array_map(function (string $received){
            return new MenuOption($received);
        }, $session->getReceived()), function(Player $player, int $selectedOption) use ($session): void{
            $received = $session->getReceived();
            $received = array_values($received);
            self::manageReceivedInvite($player, $received[$selectedOption]);
        });

        $player->sendForm($form);
    }

    public static function manageReceivedInvite(Player $player, string $received) : void
    {
        $form = new MenuForm($received, "", [
            new MenuOption("Accept"),
            new MenuOption("Deny"),
            new MenuOption("Back")
        ], function(Player $player, int $selectedOption) use ($received): void{
            match ($selectedOption){
                0 => $player->chat("/friends accept $received"),
                1 => $player->chat("/friends deny $received"),
                default => self::send($player)
            };
        });
        $player->sendForm($form);
    }

}