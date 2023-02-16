<?php
namespace pve\skill;

use pve\PlayerManager as PM;

class SharpCrit extends Skill{
	
	const NAME = '§b鈍裂';
	const ID = 33;
	const ITEM_ID = 265;
	
    const S_STAT = 10;
    const C_STAT = 5;
	const DESCRIPTION = '切れ味が上がるが、会心率が下がる。';
	
	public function onSet($player, $lv){
        $this->plugin->playermanager->setSharp($player, self::S_STAT * $lv);
        $this->plugin->playermanager->setCrit($player, self::C_STAT * $lv * -1);
        $message = "\n切れ味が".self::S_STAT * $lv."上昇!";
        $message .= "\n会心率が".self::C_STAT * $lv."％低下!";
		$this->Work($player, $message, $lv);
	}

	public function onReset($player, $lv){
        $this->plugin->playermanager->setSharp($player, self::S_STAT * $lv * -1);
        $this->plugin->playermanager->setCrit($player, self::C_STAT * $lv);
	}
}