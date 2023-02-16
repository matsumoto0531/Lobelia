<?php
namespace pve\skill;

use pve\PlayerManager as PM;

class AtkUp extends Skill{
	
	const NAME = '§c攻撃力倍加';
	const ID = 3;
	const ITEM_ID = 265;
	
	const STAT = 1.0;
	const DESCRIPTION = '攻撃力が上昇する';
	
	public function onSet($player, $lv){
		$atk = $this->plugin->playermanager->getAtkData($player);
		$atk *=  self::STAT + ($lv * 0.1);
		$this->plugin->playermanager->setAtk($player, $atk);
		$message = '攻撃力が'.(self::STAT + ($lv * 0.1)).'倍';
		$this->Work($player, $message, $lv);
	}

	public function onReset($player, $lv){
		$atk = $this->plugin->playermanager->getAtkData($player);
		$atk *=  self::STAT + ($lv * 0.1);
		$this->plugin->playermanager->setAtk($player, -$atk);
	}
}
