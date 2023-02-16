<?php
namespace pve;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerInteractEvent;

use pve\dungeon\DungeonManager;

class DungeonTP implements Listener{

    public function __construct($plugin){
        $this->plugin = $plugin;
        $this->data = [];
        $plugin->server->getPluginManager()->registerEvents($this, $plugin);
    }

    public function onInteract(PlayerInteractEvent $event){
      $player = $event->getPlayer();
      $name = $player->getName();
      if(isset($this->data[$name])){
        $pos = $event->getBlock()->getPosition();
        if($pos->y > 22){
          $player->sendMessage('ここにはおけません');
          return false;
        }
        $data = (int)$pos->y * (10**8) + (int)$pos->x * (10**4) + (int) $pos->z;
        $this->plugin->dungeonData[$data] = $this->data[$name];
        $player->sendMessage(DungeonManager::getDungeon($this->data[$name])->name."へのTPを設置しました。");
        unset($this->data[$name]);
      }
    }

    public function set($player, $field){
        $name = $player->getName();
        $this->data[$name] = $field;
    }
}