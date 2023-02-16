<?php
namespace pve\inventory;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;


class AccessoryInventory implements Listener{
    public function __construct($plugin){
        $this->plugin = $plugin;
        $this->contents = unserialize(file_get_contents($this->plugin->getDataFolder() . "accessory.dat"));
        $this->souko = unserialize(file_get_contents($this->plugin->getDataFolder() . "souko.dat"));
    }

    public function onPacketReceive(DataPacketReceiveEvent $event){
        $pk = $event->getPacket();
        $player = $event->getOrigin()->getPlayer();
        if(is_null($player)) return false;
        if($pk instanceof ModalFormResponsePacket){
            $data = json_decode($pk->formData, true);
            if(!isset($data)) return false;
            switch($pk->formId){
                case 777:
                  $this->receiveChooseForm($data, $player);
                  break;
            }
        }
    }

    public function receiveChooseForm($data, $player){
        if($data == 0) $player->setCurrentWindow(new ACustomInventory());
        else $player->setCurrentWindow(new WereHouse());
    }

    public function onClose(InventoryCloseEvent $event){
        $player = $event->getPlayer();
        $inventory = $event->getInventory();
        if($inventory instanceof ACustomInventory){
            $this->contents[$player->getName()] = $inventory->getContents();
        }elseif($inventory instanceof WereHouse){
            $this->souko[$player->getName()] = $inventory->getContents();
        }
    }

    public function onOpen(InventoryOpenEvent $event){
        $player = $event->getPlayer();
        $inventory = $event->getInventory();
        if($inventory instanceof ACustomInventory){
            $name = $player->getName();
            if(!isset($this->contents[$name])) return false;
            $inventory->setContents($this->contents[$name]);
        }elseif($inventory instanceof WereHouse){
            $name = $player->getName();
            if(!isset($this->souko[$name])) return false;
            $inventory->setContents($this->souko[$name]);
        }
    }
}