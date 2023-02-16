<?php
namespace pve\skill;

use pve\PlayerManager as PM;

class SaikutuAtk extends Skill{
	
	const NAME = '§c採掘テク';
	const ID = 29;
	const ITEM_ID = 265;
	
	const S_STAT = 20;
	const A_STAT = 10;
	const DESCRIPTION = '採掘力が上昇するが、攻撃力が低下する。';
	
	public function onSet($player, $lv){
        $this->plugin->playermanager->setMine($player, self::S_STAT * $lv);
        $this->plugin->playermanager->setAtk($player, self::A_STAT * $lv * -1);
        $message = "\n採掘力が".self::S_STAT * $lv."上昇!";
        $message .= "\n攻撃力が".self::A_STAT * $lv."低下!";
		$this->Work($player, $message, $lv);
	}

	public function onReset($player, $lv){
        $this->plugin->playermanager->setMine($player, self::S_STAT * $lv * -1);
        $this->plugin->playermanager->setAtk($player, self::A_STAT * $lv);
	}
}