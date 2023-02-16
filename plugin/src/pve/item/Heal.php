<?php
namespace pve\item;

class Heal extends PveItem {
    const NAME = '薬草';
    const ID = 1;
    const ITEM_ID = 370;
    const DESCRIPTION = '体力を小回復する。';

    public function Interact($event){
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();
        $player->sendTitle(' ', "§a§lHEAL!!>> §f20回復!!", 10, 10, 10);
        $this->plugin->playermanager->addHP($player, 20);
        $count = $item->getCount();
        $player->getInventory()->setItemInHand($item->setCount($count-1));
    }
}