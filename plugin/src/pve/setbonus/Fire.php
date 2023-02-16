<?php
namespace pve\setbonus;

use pve\Type;

class Fire extends SetBonus {

    const TYPE = Type::FIRE;

    public function onAttack($player, $atk){
      $name = $player->getName();
      if(!isset($this->data[$name])) return $atk;
      if($this->data[$name] >= 4) return $atk*1.5*1.2;
      return parent::onAttack($player, $atk);
    }

    public function onD($player, $atk){
        $name = $player->getName();
        if(!isset($this->data[$name])) return $atk;
        if($this->data[$name] >= 4) return $atk*1.5;
        return $atk;
    }
}