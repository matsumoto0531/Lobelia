<?php
namespace pve\skill;

use pve\PlayerManager as PM;

class Sharp extends Skill{
	
	const NAME = '§c切れ味UP';
	const ID = 17;
	const ITEM_ID = 265;
	
	const STAT = 5;
	const DESCRIPTION = '切れ味が上昇する。';
	
	public function onSet($player, $lv){
		$this->plugin->playermanager->setSharp($player, self::STAT * $lv);
		$message = '切れ味が'.self::STAT * $lv.'上昇!';
		$this->Work($player, $message, $lv);
	}

	public function onReset($player, $lv){
		$this->plugin->playermanager->setSharp($player, self::STAT * $lv * -1);
	}
}
