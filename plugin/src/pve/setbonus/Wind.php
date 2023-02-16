<?php
namespace pve\setbonus;

use pocketmine\entity\Attribute;
use pve\Type;

class Wind extends SetBonus {

    const TYPE = Type::WIND;

    public function onSet($player){
        parent::onSet($player);
        $name = $player->getName();
        if($this->data[$name] >= 4){
          $attr = $player->getAttributeMap()->get(Attribute::MOVEMENT_SPEED);
          $attr->setValue($attr->getValue() * 1.3, false, true);
          $this->plugin->playermanager->setCrit($player, 30);
        }
    }

    public function onReset($player){
        $name = $player->getName();
        if($this->data[$name] === 4){
            $attr = $player->getAttributeMap()->get(Attribute::MOVEMENT_SPEED);
            $attr->setValue($attr->getValue() / 1.3, false, true);
            $this->plugin->playermanager->setCrit($player, -30);
        }
        parent::onReset($player);
    }
}