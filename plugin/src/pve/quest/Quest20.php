<?php
namespace pve\quest;

use pve\mobs\Mobs;

use pve\WeaponManager;

class Quest20 extends Quest {
    const ID = 20;

    public function onTouch($player, $nn){
      $this->plugin->styleform->sendMenuForm($player);
    }

}