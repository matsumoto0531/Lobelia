<?php
namespace pve\setbonus;

use entity\Attribute;
use pve\Type;

class Light extends SetBonus {

    const TYPE = Type::LIGHT;

    public function onSet($player){
        parent::onSet($player);
        $name = $player->getName();
        if($this->data[$name] >= 4){
          $def = $this->plugin->playermanager->getDef($player);
          $this->hosei[$name] = $def * 0.2;
          $this->plugin->playermanager->setDef($player, $this->hosei[$name]);
        }
    }

    public function onReset($player){
        $name = $player->getName();
        if($this->data[$name] === 4){
          $def = $this->plugin->playermanager->getDef($player);
          $this->plugin->playermanager->setDef($player, $this->hosei[$name] * -1);
        }
        parent::onReset($player);
    }

    public function onHeal($player, $amount){
        $name = $player->getName();
        if(!isset($this->data[$name])) return $amount;
        if($this->data[$name] >= 4){
           return $amount * 1.5;
        }
        return $amount;
    }
}