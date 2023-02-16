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

use pve\ItemManager;
use pve\PveItem;
use pve\weapon\Weapon;
use pve\armor\Armor;
use pve\accessory\Accessory;


class SellWindow implements Listener{
    public function __construct($plugin){
        $this->plugin = $plugin;
    }

    public function onClose(InventoryCloseEvent $event){
        $player = $event->getPlayer();
        $inventory = $event->getInventory();
        if($inventory instanceof ForgeInventory){
            $player->setImmobile(false);
            $items = $inventory->getContents();
            $tag = $item->getNamedTag(Armor::TAG_ARMOR);
            $flag = 'armor';
            if(!isset($tag)){
                $tag = $item->getNamedTag(Weapon::TAG_WEAPON);
                $flag = 'sword';
            }
            $rare = $tag->getTag(Weapon::TAG_RARE)->getValue();
            $lv = $tag->getTag(Weapon::TAG_LEVEL)->getValue();
            if($lv > 16){
                $player->sendMessage('§eINFO§f>>それ以上強化できません');
                $items = $inv->getContents();
                foreach($items as $item){
                    $player->getInventory()->addItem($item);
                }
                return false;
            }
            $amount = 0;
            foreach($items as $item){
                $tag = $item->getNamedTag(PveItem::TAG_ITEM);
                if(isset($tag)){
                    $id = $tag->getTag(PveItem::TAG_ID)->getValue();  
                    if(ItemManager::getItem($id)->isOre()){
                        $inv->removeItem($item);
                        $amount += (ItemManager::getItem($id)->getPurity()) * ($item->getCount());
                        continue;
                    }
                }
                $inv->removeItem($item);
                $player->getInventory()->addItem($item);
            }
            $money = (3 / (550 * $rare)) * self::MONEY[$rare-1] * $amount;
            $player->sendMessage('§7§l消費フロル§f§r: '.$money.'Fl');
            $player->sendMessage('§e§l獲得経験値§f§r: '.$amount.'exp');
            if(!$this->plugin->playermanager->hasMoney($player, $money)){
                $player->sendMessage('§eINFO§f>>お金が足りません。');
                foreach($inv->getContents() as $item){
                    $player->getInventory()->addItem($item);
                }
                return false;
            }
            $this->plugin->playermanager->takeMoney($player, $money);
            $this->Str($player, $amount);
        }  
    }
}