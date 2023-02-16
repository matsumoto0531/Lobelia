<?php
namespace pve\skill;

use pve\PlayerManager as PM;

class DefCrit extends Skill{
	
	const NAME = '§e重壁';
	const ID = 27;
	const ITEM_ID = 265;
	
    const STAT = 30;
    const C_STAT = 5;
	const DESCRIPTION = '防御力が上昇するが、会心率が下がる。';
	
	public function onSet($player, $lv){
        $this->plugin->playermanager->setDef($player, self::STAT * $lv);
        $this->plugin->playermanager->setCrit($player, self::C_STAT * $lv * -1);
        $message = "\n防御力が".self::STAT * $lv."上昇!";
        $message .= "\n会心率が".self::C_STAT * $lv."低下!";
		$this->Work($player, $message, $lv);
	}

	public function onReset($player, $lv){
        $this->plugin->playermanager->setDef($player, self::STAT * $lv * -1);
        $this->plugin->playermanager->setCrit($player, self::C_STAT * $lv);
	}
}