<?php
namespace pve\setbonus;

use pve\Type;

class Dark extends SetBonus {

    const TYPE = Type::DARK;

    public function onAttack($player, $atk){
      $name = $player->getName();
      if(!isset($this->data[$name])) return $atk;
      if($this->data[$name] >= 4){
        if(mt_rand(0, 100) <= 30){
            $this->plugin->playermanager->addHp($player, $atk/100);
        }
      }
      return parent::onAttack($player, $atk);
    }
}