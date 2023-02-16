<?php
namespace pve\inventory;
use pve\inventory\inventoryui\CustomInventory;
use pocketmine\player\Player;
use pocketmine\item\Item;
use pve\accessory\Accessory;
use pve\PlayerManager;

class ACustomInventory extends CustomInventory {
    public function __construct() {
        parent::__construct(6, "Accessory");
    }

    public function click(Player $player, int $slot, Item $sourceItem, Item $targetItem): bool {
        $tag = $targetItem->getNamedTag()->getTag(Accessory::TAG_ACCESSORY);
        if(!isset($tag) && $targetItem->getId() !== 0) return true;
        if(isset($tag)){
            $id = $tag->getTag(Accessory::TAG_ACCESSORY_ID)->getValue();
            $items = $this->getContents();
            foreach($items as $item){
                $ttag = $item->getNamedTag()->getTag(Accessory::TAG_ACCESSORY);
                if(!isset($ttag) && $item->getId() !== 0) continue;
                $id2 = $ttag->getTag(Accessory::TAG_ACCESSORY_ID)->getValue();
                if($id2 == $id){
                    $player->sendMessage("§eINFO§f>>同名アクセサリは装備できません。");
                    return true;
                }
            }
        }
        $class = PlayerManager::$class;
        $class->setAcc($player, $targetItem);
        $class->resetAcc($player, $sourceItem);
        return false;
    }
}