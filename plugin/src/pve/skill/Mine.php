<?php
namespace pve\skill;

use pve\PlayerManager as PM;

class Mine extends Skill{
	
	const NAME = '§c採掘力UP';
	const ID = 19;
	const ITEM_ID = 265;
	
	const STAT = 10;
	const DESCRIPTION = '採掘力が上昇する。';
	
	public function onSet($player, $lv){
		$this->plugin->playermanager->setMine($player, self::STAT * $lv);
		$message = '採掘力が'.self::STAT * $lv.'上昇!';
		$this->Work($player, $message, $lv);
	}

	public function onReset($player, $lv){
		$this->plugin->playermanager->setMine($player, self::STAT * $lv * -1);
	}
}
