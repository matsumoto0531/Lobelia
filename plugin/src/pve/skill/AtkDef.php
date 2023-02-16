<?php
namespace pve\skill;

use pve\PlayerManager as PM;

class AtkDef extends Skill{
	
	const NAME = '§c捨て身';
	const ID = 26;
	const ITEM_ID = 265;
	
	const STAT = 30;
	const DESCRIPTION = '攻撃力が上昇するが、防御力が下がる。';
	
	public function onSet($player, $lv){
        $this->plugin->playermanager->setAtk($player, self::STAT * $lv);
        $this->plugin->playermanager->setDef($player, self::STAT * $lv * -1);
        $message = "\n攻撃力が".self::STAT * $lv."上昇!";
        $message = "\n防御力が".self::STAT * $lv."低下!";
		$this->Work($player, $message, $lv);
	}

	public function onReset($player, $lv){
        $this->plugin->playermanager->setAtk($player, self::STAT * $lv * -1);
        $this->plugin->playermanager->setDef($player, self::STAT * $lv);
	}
}