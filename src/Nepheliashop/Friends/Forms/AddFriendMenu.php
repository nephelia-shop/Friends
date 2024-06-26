<?php
namespace Nepheliashop\Friends\Forms;

use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Input;
use pocketmine\player\Player;

class AddFriendMenu {

    public static function send(Player $player) : void
    {
        $form = new CustomForm('Friends', [
            new Input('target', 'Enter your new friend name', $player->getName(), '')
        ], function(Player $player, CustomFormResponse $response) : void{
            $target = $response->getString("target");
            $player->chat("/friends add $target");
        });
        $player->sendForm($form);
    }

}