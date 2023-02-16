<?php
namespace pve\item;

class Kouka extends PveItem {
    const NAME = '§6守薬';
    const ID = 11;
    const ITEM_ID = 372;
    const DESCRIPTION = '攻撃力が少し上昇する。';

    public function Interact($event){
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();
        $player->sendTitle(' ', "§6§lDEF!!>> §f30上昇!!", 10, 10, 10);
        $this->plugin->playermanager->addDef($player, 30, 60);
        $field = $this->plugin->fieldmanager->getField($player);
        $players = $this->plugin->fieldmanager->getPlayers($field);
        $this->addCustomParticle("PVE:STATUSUP", $player->getPosition(), $players);
        $count = $item->getCount();
        $player->getInventory()->setItemInHand($item->setCount($count-1));
    }
}