<?php
namespace pve\skill;

use pve\PlayerManager as PM;

class Def extends Skill{
	
	const NAME = '§e防御力UP';
	const ID = 16;
	const ITEM_ID = 265;
	
	const STAT = 1;
	const DESCRIPTION = '防御力が上昇する';
	
	public function onSet($player, $lv){
		$this->plugin->playermanager->setDef($player, self::STAT * $lv);
		$message = '防御力が'.self::STAT * $lv.'上昇!';
		$this->Work($player, $message, $lv);
	}

	public function onReset($player, $lv){
		$this->plugin->playermanager->setDef($player, self::STAT * $lv * -1);
	}
}
