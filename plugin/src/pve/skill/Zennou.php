<?php
namespace pve\skill;

use pve\PlayerManager as PM;

class Zennou extends Skill{
	
	const NAME = '§l全能§r';
	const ID = 30;
	const ITEM_ID = 264;
	
	const STAT = 50;
	const DESCRIPTION = 'すべてのステータスが上昇';
	
	public function onSet($player, $lv){
        $this->plugin->playermanager->setAtk($player, self::STAT * $lv);
        $this->plugin->playermanager->setDef($player, self::STAT * $lv);
        $this->plugin->playermanager->setSharp($player, self::STAT * $lv / 10);
        $this->plugin->playermanager->setCrit($player, self::STAT * $lv / 10);
        $message = "攻撃力が".self::STAT * $lv.'上昇!';
        $message .= "\n防御力が".self::STAT * $lv.'上昇!';
        $message .= "\n切れ味が".(self::STAT * $lv / 10).'上昇!';
        $message .= "\n会心率が".(self::STAT * $lv / 10).'%上昇!';
		$this->Work($player, $message, $lv);
	}

	public function onReset($player, $lv){
		$this->plugin->playermanager->setAtk($player, self::STAT * $lv * -1);
        $this->plugin->playermanager->setDef($player, self::STAT * $lv * -1);
        $this->plugin->playermanager->setSharp($player, self::STAT * $lv / 10 * -1);
        $this->plugin->playermanager->setCrit($player, self::STAT * $lv / 10 * -1);
	}
}
