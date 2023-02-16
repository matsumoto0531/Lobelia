<?php
namespace pve\item;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;

class ItemListener implements Listener {

    public function __construct($plugin){
        $this->plugin = $plugin;
        $this->cooltime = [];
        $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
    }

    public function onInteract(PlayerInteractEvent $event){
        $player = $event->getPlayer();
        $name = $player->getName();
        if(!array_key_exists($name, $this->cooltime))
          $this->cooltime[$name] = 0;
		$time = microtime(true);
        $num = $time - $this->cooltime[$name];
        if($num < 0.25){
			return false;
        }
        $this->cooltime[$name] = $time;
        $item = $player->getInventory()->getItemInHand();
        if(PveItem::isItem($item)){
          $id = PveItem::gettingId($item);
          if(isset($id))
            ItemManager::getItem($id)->Interact($event);
        }
    }
}