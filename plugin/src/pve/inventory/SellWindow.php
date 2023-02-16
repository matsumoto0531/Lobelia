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
        if($inventory instanceof GiftWindow or $inventory instanceof ShopWindow){
            $player->setImmobile(false);
            $items = $inventory->getContents();
            $sum = 0;
            foreach($items as $item){
                $price = 0;
                $t = $item->getNamedTag();
                if(($tag = $t->getTag(Weapon::TAG_WEAPON)) !== null){
                    $id = $tag->getTag(Weapon::TAG_WEAPON_ID)->getValue();
                    $price = $this->plugin->swordData[$id]['price'];
                }else if(($tag = $t->getTag(Armor::TAG_ARMOR)) !== null){
                    $id = $tag->getTag(Armor::TAG_ARMOR_ID)->getValue();
                    $price = $this->plugin->armorData[$id]['price'];
                }else if(($tag = $t->getTag(Accessory::TAG_ACCESSORY)) !== null){
                    $id = $tag->getTag(Accessory::TAG_ACCESSORY_ID)->getValue();
                    $price = $this->plugin->accessoryData[$id]['price'];
                }
                $sum += $price;
            }
            $this->plugin->playermanager->addMoney($player, $sum);
            $player->sendMessage('§eINFO§f>>箱の中身を合計'.$sum.'flで売却しました');
        }
    }
}