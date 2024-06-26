<?php
namespace Nepheliashop\Friends\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use Nepheliashop\Friends\Database\DatabaseManager;
use Nepheliashop\Friends\Forms\DefaultMenu;
use Nepheliashop\Friends\Sessions\SessionManager;

class FriendsCommand extends Command {

    public function __construct()
    {
        parent::__construct("friends", "Open friends menu", self::help(), ["friend"]);
        $this->setPermission("nepheliashop.permissions.command.friends");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : void
    {
        if (count($args) === 0){
            if ($sender instanceof Player){
                $sender->sendForm(new DefaultMenu($sender));
            } else {
                $sender->sendMessage($this->getUsage());
            }
            return;
        }
        if(!$sender instanceof Player){
            $sender->sendMessage("§cRun command in game");
            return;
        }
        $session = SessionManager::getInstance()->getByXuid($sender->getXuid());
        if ($session == null) {
            $sender->kick("Your connection is not stable, please login");
            return;
        }
        switch (array_shift($args)){
            case "invite":
            case "add":
                $targetName = implode("", $args);
                if ($targetName !== ""){
                    if (($target = $sender->getServer()->getPlayerByPrefix($targetName)) !== null){
                        if (!$session->isFriendWith($target->getName())){
                            if (!$session->hasInvited($target->getName())){
                                $targetSession = SessionManager::getInstance()->getByXuid($target->getXuid());
                                if ($targetSession === null){
                                    $sender->sendMessage("§c{$target->getName()} is offline");
                                    return;
                                }
                                $session->addSendInvite($target->getName());
                                $targetSession->addReceivedInvite($sender->getName());
                                $sender->sendMessage("§aYou have sent a friend invitation to {$target->getName()}");
                                $target->sendMessage("You have received a friend invitation from {$sender->getName()}, type :");
                                $target->sendMessage("§7/friend accept {$sender->getName()} : §fTo accept the invitation.");
                                $target->sendMessage("§7/friend deny {$sender->getName()} : §fTo deny the invitation.");
                            } else $sender->sendMessage("§cYou have already sent a friend request to {$target->getName()}");
                        } else $sender->sendMessage("§cYou are already friend with {$target->getName()}.");
                    } else $sender->sendMessage("§c$targetName is not online.");
                } else $sender->sendMessage("§cUsage : /friends add (player)");
                break;
            case "cancel":
                $targetName = implode("", $args);
                if ($targetName !== ""){
                    if ($session->hasInvited($targetName)){
                        $session->removeSendInvite($targetName);
                        $targetSession = SessionManager::getInstance()->getByName($targetName);
                        if ($targetSession !== null){
                            $targetSession->removeReceivedInvite($sender->getName());
                        } else {
                            DatabaseManager::getInstance()->removeReceivedInvite($targetName, $sender->getName());
                        }
                        $sender->sendMessage("§6You have cancel your friend invitation");
                    } else $sender->sendMessage("§cYou did not send a friend invitation to $targetName");
                } else $sender->sendMessage("§cUsage : /friends cancel (player)");
                break;
            case "accept":
                $targetName = implode("", $args);
                if ($targetName !== ""){
                    if ($session->hasReceivedFrom($targetName)){
                        $session->removeReceivedInvite($targetName);
                        $session->addFriend($targetName);
                        $targetSession = SessionManager::getInstance()->getByName($targetName);
                        if ($targetSession !== null){
                            $targetSession->removeSendInvite($sender->getName());
                            $targetSession->addFriend($sender->getName());
                        } else {
                            DatabaseManager::getInstance()->removeSendInvite($targetName, $sender->getName());
                            DatabaseManager::getInstance()->addFriend($targetName, $sender->getName());
                        }
                        $sender->sendMessage("§aYou are now friend with $targetName");
                        if (($target = $sender->getServer()->getPlayerExact($targetName)) !== null){
                            $target->sendMessage("§a{$sender->getName()} has accepted your friend invitation");
                        }
                    } else $sender->sendMessage("§cYou didn't receive invitation from $targetName");
                } else $sender->sendMessage("§cUsage : /friends accept (player)");
                break;
            case "deny":
            case "reject":
                $targetName = implode("", $args);
                if ($targetName !== ""){
                    if ($session->hasReceivedFrom($targetName)){
                        $session->removeReceivedInvite($targetName);
                        $targetSession = SessionManager::getInstance()->getByName($targetName);
                        if ($targetSession !== null){
                            $targetSession->removeSendInvite($sender->getName());
                        } else {
                            DatabaseManager::getInstance()->removeSendInvite($targetName, $sender->getName());
                        }
                        $sender->sendMessage("§6You have reject $targetName friend's request");
                        if (($target = $sender->getServer()->getPlayerExact($targetName)) !== null){
                            $target->sendMessage("§6{$sender->getName()} has rejected your friend invitation");
                        }
                    } else $sender->sendMessage("§cYou didn't receive invitation from $targetName");
                } else $sender->sendMessage("§cUsage : /friends deny (player)");
                break;
            case "remove":
                $targetName = implode("", $args);
                if ($targetName !== ""){
                    if ($session->isFriendWith($targetName)){
                        $session->removeFriend($targetName);
                        $targetSession = SessionManager::getInstance()->getByName($targetName);
                        if ($targetSession !== null){
                            $targetSession->removeFriend($sender->getName());
                        } else {
                            DatabaseManager::getInstance()->removeFriend($targetName, $sender->getName());
                        }
                        $sender->sendMessage("§aYou have remove $targetName from your friends list.");
                    } else $sender->sendMessage("§cYou are not friend with $targetName.");
                } else $sender->sendMessage("§cUsage : /friends remove (player)");
                break;
            case "tp":
            case "join":
                $targetName = implode("", $args);
                if ($targetName !== ""){
                    if ($session->isFriendWith($targetName)){
                        if (($target = $sender->getServer()->getPlayerExact($targetName)) !== null){
                            $sender->teleport($target->getPosition());
                            $sender->sendMessage("§aYou have join {$target->getName()}");
                        } else $sender->sendMessage("§c$targetName is not online.");
                    } else $sender->sendMessage("§cYou are not friend with $targetName.");
                } else $sender->sendMessage("§cUsage : /friends tp (player)");
                break;
            case "list":
                if (empty($session->getFriends()) or $session->getFriends() === []){
                    $sender->sendMessage("§cYou don't have any friends yet, type /friends invite (player) to send a friend request");
                    return;
                }
                $message = "§7- §fList of your friends :\n";
                foreach ($session->getFriends() as $friend){
                    $status = $sender->getServer()->getPlayerExact($friend) === null ? "§c(Offline)" : "§a(Online)";
                    $message .= "§7- §f$friend $status\n";
                }
                $sender->sendMessage($message);
                break;
            case "info":
                $sender->sendForm(new DefaultMenu($sender));
                break;
            case "help":
            default:
                $sender->sendMessage($this->getUsage());
            break;
        }
    }

    public static function help() : string
    {
        $message = "§7------------------------ §fFriends §7--------------------------------\n";
        $message .= "§7/friend help §f: Show you the help message\n";
        $message .= "§7/friend invite/add (player) §f: Send a friend request to a player\n";
        $message .= "§7/friend cancel (player) §f: Cancel a friend invitation you sent\n";
        $message .= "§7/friend accept (player) §f: Accept a friend request from a player\n";
        $message .= "§7/friend deny/reject (player) §f: Deny a friend request from a player\n";
        $message .= "§7/friend remove (player) §f: Remove a friend from your list\n";
        $message .= "§7/friend tp/join (player) §f: Teleport to your friend\n";
        $message .= "§7/friend list §f: Show your friend list\n";
        $message .= "§7/friend info §f: Open the default menu \n";
        return $message;
    }

}