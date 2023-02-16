<?php
namespace pve\setbonus;

use entity\Attribute;
use pve\Type;

class Ice extends SetBonus {

    const TYPE = Type::ICE;

    public function onSet($player){
        parent::onSet($player);
        $name = $player->getName();
        if($this->data[$name] >= 4){
          $this->plugin->playermanager->setSharp($player, 100);
        }
    }

    public function onReset($player){
        $name = $player->getName();
        if($this->data[$name] === 4){
            $this->plugin->playermanager->setSharp($player, -100);
        }
        parent::onReset($player);
    }
}