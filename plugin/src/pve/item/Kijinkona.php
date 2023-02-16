<?php
namespace pve\item;

class Kijinkona extends PveItem {
    const NAME = '§c§lPOWER POWDER';
    const ID = 13;
    const ITEM_ID = 289;
    const DESCRIPTION = 'エリア内の味方全員の攻撃力が上昇する。';

    public function Interact($event){
        $player = $event->getPlayer();
        $field = $this->plugin->fieldmanager->getField($player);
        $item = $player->getInventory()->getItemInHand();
        $count = $item->getCount();
        $player->getInventory()->setItemInHand($item->setCount($count-1));
        $players = $this->plugin->fieldmanager->getPlayers($field);
        foreach($players as $p){
          $p->sendTitle(' ', "§c§lPOWER!!>> §f100上昇!!", 10, 10, 10);
          $p->sendMessage('§c§lPOWER§r>>'.$player->getName().'さんがPOWER POWDERを使用しました！');
          $this->plugin->playermanager->addAtk($p, 100, 180);
          $this->addCustomParticle("PVE:STATUSUP", $player->getPosition(), $players);
        }
    }
}