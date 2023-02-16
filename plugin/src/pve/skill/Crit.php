<?php
namespace pve\skill;

use pve\PlayerManager as PM;

class Crit extends Skill{
	
	const NAME = '§b会心UP';
	const ID = 18;
	const ITEM_ID = 265;
	
	const STAT = 5;
	const DESCRIPTION = '会心率が上昇する。';
	
	public function onSet($player, $lv){
		$this->plugin->playermanager->setCrit($player, self::STAT * $lv);
		$message = '会心率が'.self::STAT * $lv.'上昇!';
		$this->Work($player, $message, $lv);
	}

	public function onReset($player, $lv){
		$this->plugin->playermanager->setCrit($player, self::STAT * $lv * -1);
	}
}
