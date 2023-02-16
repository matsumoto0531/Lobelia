<?php
namespace pve\item;

class Koukakona extends PveItem {
    const NAME = '§6§lDEF POWDER';
    const ID = 14;
    const ITEM_ID = 371;
    const DESCRIPTION = 'エリア内の味方全員の防御力が上昇する。';

    public function Interact($event){
        $player = $event->getPlayer();
        $field = $this->plugin->fieldmanager->getField($player);
        $item = $player->getInventory()->getItemInHand();
        $count = $item->getCount();
        $player->getInventory()->setItemInHand($item->setCount($count-1));
        $players = $this->plugin->fieldmanager->getPlayers($field);
        foreach($players as $p){
          $p->sendTitle(' ', "§6§lDEF!!>> §f100上昇!!", 10, 10, 10);
          $p->sendMessage('§6§lDEF§r>>'.$player->getName().'さんがDEF POWDERを使用しました！');
          $this->plugin->playermanager->addDef($p, 5, 180);
          $this->addCustomParticle("PVE:STATUSUP", $p->getPosition(), $players);
        }
    }
}