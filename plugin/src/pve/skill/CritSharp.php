<?php
namespace pve\skill;

use pve\PlayerManager as PM;

class CritSharp extends Skill{
	
	const NAME = '§e鋭打';
	const ID = 28;
	const ITEM_ID = 265;
	
    const S_STAT = 5;
    const C_STAT = 10;
	const DESCRIPTION = '会心率が上がるが、切れ味が下がる。';
	
	public function onSet($player, $lv){
        $this->plugin->playermanager->setSharp($player, self::S_STAT * $lv * -1);
        $this->plugin->playermanager->setCrit($player, self::C_STAT * $lv);
        $message = "\n会心率が".self::C_STAT * $lv."上昇!";
        $message .= "\n切れ味が".self::S_STAT * $lv."低下!";
		$this->Work($player, $message, $lv);
	}

	public function onReset($player, $lv){
        $this->plugin->playermanager->setSharp($player, self::S_STAT * $lv);
        $this->plugin->playermanager->setCrit($player, self::C_STAT * $lv * -1);
	}
}