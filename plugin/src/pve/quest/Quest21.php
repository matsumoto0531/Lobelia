<?php
namespace pve\quest;

use pve\mobs\Mobs;

use pve\WeaponManager;

class Quest21 extends Quest {
    const ID = 21;

    public function onTouch($player, $nn){
      $this->plugin->styleform->sendStatusForm($player);
    }

}