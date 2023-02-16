<?php
namespace pve\weaponskill;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;

use pve\WeaponSkillManager;
use pve\SkillManager;
use pve\weapon\Weapon;

class WeaponSkillListener implements Listener {
    
    public function __construct($plugin){
      $this->plugin = $plugin;
      $plugin->server->getPluginManager()->registerEvents($this, $plugin);
    }

    public function onInteract(PlayerInteractEvent $event){
        $player = $event->getPlayer();
        $action = $event->getAction();
        $name = $player->getName(); 
        if($action !== 3) return false;
        $item = $event->getPlayer()->getInventory()->getItemInHand();
        $tag = $item->getNamedTagEntry(Weapon::TAG_WEAPON);
        if(!isset($tag)) return false;
        if($player->isImmobile()) return false;
        if($player->isSprinting()){
            $id = $this->plugin->playerData[$name]['skills'][1];
        }elseif($player->isSneaking()){
            $id = $this->plugin->playerData[$name]['skills'][2];
        }else{
            $id = $this->plugin->playerData[$name]['skills'][0];
        }
        if($id === -1) return false;
        $class = WeaponSkillManager::getSkill($id);
        $class->Interact($item, $player);
    }

    public function onJoin(PlayerJoinEvent $event){
        $player = $event->getplayer();
        $name = $player->getName();
        
    }

}