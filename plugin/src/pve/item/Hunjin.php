<?php
namespace pve\item;

use pocketmine\network\mcpe\protocol\types\ParticleIds;

class Hunjin extends PveItem {
    const NAME = '§a癒しの粉';
    const ID = 8;
    const ITEM_ID = 295;
    const DESCRIPTION = 'フィールド内の味方全員の体力を回復する';

    public function Interact($event){
        $player = $event->getPlayer();
        $field = $this->plugin->fieldmanager->getField($player);
        $item = $player->getInventory()->getItemInHand();
        $count = $item->getCount();
        $player->getInventory()->setItemInHand($item->setCount($count-1));
        $players = $this->plugin->fieldmanager->getPlayers($field);
        foreach($players as $p){
          $p->sendTitle(' ', "§a§lHEAL!!>> §f100回復!!", 10, 10, 10);
          $p->sendMessage('§a§lHEAL§r>>'.$player->getName().'さんが癒しの粉を使用しました！');
          $this->plugin->playermanager->addHP($p, 100);
          $this->addParticle($field, $p->getPosition(), ParticleIds::HEART, 2, 7);
        }
    }
}