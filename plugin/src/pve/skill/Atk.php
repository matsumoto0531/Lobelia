<?php
namespace pve\skill;

use pve\PlayerManager as PM;

class Atk extends Skill{
	
	const NAME = '§c攻撃力UP';
	const ID = 1;
	const ITEM_ID = 265;
	
	const STAT = 3;
	const DESCRIPTION = '攻撃力が上昇する';
	
	public function onSet($player, $lv){
		$this->plugin->playermanager->setAtk($player, self::STAT * $lv);
		$message = '攻撃力が'.self::STAT * $lv.'上昇!';
		$this->Work($player, $message, $lv);
	}

	public function onReset($player, $lv){
		$this->plugin->playermanager->setAtk($player, self::STAT * $lv * -1);
	}
}
