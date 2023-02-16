<?php
namespace pve\item;

class Healll extends PveItem {
    const NAME = '§l§a上薬草';
    const ID = 7;
    const ITEM_ID = 370;
    const DESCRIPTION = '体力を大回復する。';

    public function Interact($event){
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();
        $player->sendTitle(' ', "§a§lHEAL!!>> §f100回復!!", 10, 10, 10);
        $this->plugin->playermanager->addHP($player, 100);
        $count = $item->getCount();
        $player->getInventory()->setItemInHand($item->setCount($count-1));
    }
}