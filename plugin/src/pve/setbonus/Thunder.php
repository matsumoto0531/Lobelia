<?php
namespace pve\setbonus;

use entity\Attribute;
use pve\Type;

class Thunder extends SetBonus {

    const TYPE = Type::THUNDER;

    public function onSet($player){
        parent::onSet($player);
        $name = $player->getName();
        if($this->data[$name] >= 4){
          $this->plugin->playermanager->setCritPer($player, 1);
        }
    }

    public function onReset($player){
        $name = $player->getName();
        if($this->data[$name] === 4){
            $this->plugin->playermanager->setCritPer($player, -1);
        }
        parent::onReset($player);
    }
}