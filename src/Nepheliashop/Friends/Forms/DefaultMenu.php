<?php
namespace Nepheliashop\Friends\Forms;

use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\player\Player;
use Nepheliashop\Friends\Sessions\SessionManager;

class DefaultMenu extends MenuForm {

    public function __construct(Player $player)
    {
        $session = SessionManager::getInstance()->getByXuid($player->getXuid());
        if ($session === null){
            $player->kick("Your connection is not stable, please login");
            return;
        }
        $x = count($session->getFriends());
        $y = count(array_filter($session->getFriends(), fn(string $friend) => $player->getServer()->getPlayerExact($friend) instanceof Player));
        $z = count(array_filter($session->getFriends(), fn(string $friend) => $player->getServer()->getPlayerExact($friend) === null));

        $text = "§7Friends : ($x)\n";
        $text .= "§aOnline friends : ($y)\n";
        $text .= "§cOffline friends : ($z)\n";

        $listOption = new MenuOption("Friends list");
        $addOption = new MenuOption("Add a new friend");
        $myInvite = new MenuOption("Manage invitations\nyou received");
        $otherInvitation = new MenuOption("Manage invitations\nyou sent");
        $leave = new MenuOption("Quit");

        parent::__construct("Friends", $text, [$listOption, $addOption, $myInvite, $otherInvitation, $leave], function (Player $player, int $selectedOption) : void{
            $this->submit($player, $selectedOption);
        });
    }

    protected function submit(Player $player, int $selectedOption) : void
    {
        switch ($selectedOption){
            case 0:
                FriendsListMenu::send($player);
                break;
            case 1:
                AddFriendMenu::send($player);
                break;
            case 2:
                ReceivedInviteForm::send($player);
                break;
            case 3:
                SentInviteForm::send($player);
                break;
        }
    }

}