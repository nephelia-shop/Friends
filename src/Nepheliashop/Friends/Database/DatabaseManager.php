<?php
namespace Nepheliashop\Friends\Database;

use Generator;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use SOFe\AwaitGenerator\Await;

class DatabaseManager {
    use SingletonTrait;

    private DataConnector $database;

    public function startup(PluginBase $plugin) : void
    {

        $this->database = libasynql::create($plugin, $plugin->getConfig()->get("database"), [
            "mysql" => "mysql.sql",
            "sqlite" => 'friends.sqlite'
        ]);
        $this->database->executeGeneric("players.init");
    }

    public function getDatabase(): DataConnector
    {
        return $this->database;
    }

    public function update(string $name, string $change, string $select, string $key, array|string $target, string $type) : void
    {
        if (is_array($target)){
            $this->database->executeChange("players.".$change, ["ret" => json_encode($target), "name" => $name]);
        } else {
            Await::f2c(function () use ($change, $target, $type, $key, $name, $select) {
                $rows = (array)yield from self::getData("players.".$select, ['name' => $name]);
                $data = $rows[0];
                $ret = $data[$key] === null ? [] : json_decode($data[$key], true);
                if ($type === QueryDbEnum::REMOVE){
                    if ($ret !== []){
                        if (in_array($target, $ret)){
                            unset($ret[array_search($target, $ret)]);
                        }
                    }
                } else {
                    $ret[] = $target;
                }
                $this->database->executeChange("players.".$change, ["ret" => json_encode($ret), "name" => $name]);
            });
        }
    }

    protected function getData(string $query, array $args) : Generator
    {
        $this->database->executeSelect($query, $args, yield, yield Await::REJECT);
        return yield Await::ONCE;
    }

    public function removeReceivedInvite(string $name, array|string $target) : void
    {
        $this->update($name, QueryDbEnum::SET_RECEIVED, QueryDbEnum::GET_RECEIVED, QueryDbEnum::INVITATIONS_RECEIVED, $target, QueryDbEnum::REMOVE);
    }
    
    public function removeSendInvite(string $name, array|string $target) : void
    {
        $this->update($name, QueryDbEnum::SET_SENT, QueryDbEnum::GET_SENT, QueryDbEnum::INVITATIONS_SENT, $target, QueryDbEnum::REMOVE);
    }
    
    public function addReceivedInvite(string $name, array|string $target) : void
    {
        $this->update($name, QueryDbEnum::SET_RECEIVED, QueryDbEnum::GET_RECEIVED, QueryDbEnum::INVITATIONS_RECEIVED, $target, QueryDbEnum::ADD);
    }
    
    public function addSendInvite(string $name, array|string $target) : void
    {
        $this->update($name, QueryDbEnum::SET_SENT, QueryDbEnum::GET_SENT, QueryDbEnum::INVITATIONS_SENT, $target, QueryDbEnum::ADD);
    }
    
    public function addFriend(string $name, array|string $target) : void
    {
        $this->update($name, QueryDbEnum::SET_FRIENDS, QueryDbEnum::GET_FRIENDS, QueryDbEnum::FRIENDS_LIST, $target, QueryDbEnum::ADD);
    }
    
    public function removeFriend(string $name, array|string $target) : void
    {
        $this->update($name, QueryDbEnum::SET_FRIENDS, QueryDbEnum::GET_FRIENDS, QueryDbEnum::FRIENDS_LIST, $target, QueryDbEnum::REMOVE);
    }

}