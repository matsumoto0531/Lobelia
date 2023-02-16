<?php
namespace pve;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;

class ChestLock implements Listener {
  
  public function __construct($plugin){
    $this->plugin = $plugin;
    $this->q = [];
  }

  public function onJoin(PlayerJoinEvent $event){
    $player = $event->getPlayer();
    $name = $player->getName();
    $this->q[$name] = false;
  }

  public function onInteract(PlayerInteractEvent $event){
    $player = $event->getPlayer();
    $name = $player->getName();
    $block = $event->getBlock();
    $pos = $block->getPosition();
    if($pos->y > 9) return false;
    $data = (int)$pos->x * (10**5) + (int)$pos->y * (10**4) + (int) $pos->z;
    if($block->getId() === 146){
      if(isset($this->plugin->chestData[$data])){
        if($this->plugin->chestData[$data] !== $this->plugin->playerData[$name]['guild']){
          $event->setCancelled(true);
          $player->sendMessage('§eChest§f>>このチェストは'.$guild.'のものです。');
        }
      }else{
        if($this->q[$name]){
          $this->plugin->chestData[$data] = $this->plugin->playerData[$name]['guild'];
          $player->sendMessage('§eChest§f>>チェストをロックしました。');
          $this->q[$name] = false;
        }
      }
    }
  }

  public function setQ($player){
    $this->q[$name] = true;
  }

}