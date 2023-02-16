<?php
namespace pve\item;

class Heall extends PveItem {
    const NAME = '§a中薬草';
    const ID = 6;
    const ITEM_ID = 370;
    const DESCRIPTION = '体力を中回復する。';

    public function Interact($event){
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();
        $player->sendTitle(' ', "§a§lHEAL!!>> §f40回復!!", 10, 10, 10);
        $this->plugin->playermanager->addHP($player, 50);
        $count = $item->getCount();
        $player->getInventory()->setItemInHand($item->setCount($count-1));
    }
}