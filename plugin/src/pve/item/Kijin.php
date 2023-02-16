<?php
namespace pve\item;

class Kijin extends PveItem {
    const NAME = '§c力薬';
    const ID = 10;
    const ITEM_ID = 371;
    const DESCRIPTION = '攻撃力が少し上昇する。';

    public function Interact($event){
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();
        $player->sendTitle(' ', "§c§lPOWER!!>> §f3上昇!!", 10, 10, 10);
        $this->plugin->playermanager->addAtk($player, 3, 60);
        $field = $this->plugin->fieldmanager->getField($player);
        $players = $this->plugin->fieldmanager->getPlayers($field);
        $this->addCustomParticle("PVE:STATUSUP", $player->getPosition(), $players);
        $count = $item->getCount();
        $player->getInventory()->setItemInHand($item->setCount($count-1));
    }
}