<?php
namespace pve\skill;

use pve\PlayerManager as PM;

class Sengeki extends Skill{
	
	const NAME = '§e閃撃';
	const ID = 25;
	const ITEM_ID = 265;
	
    const STAT_PER = 0.03;
	const DESCRIPTION = '会心倍率が上昇する。';
	
	public function onSet($player, $lv){
        $this->plugin->playermanager->setCritPer($player, self::STAT_PER * $lv);
        $message = '会心倍率が'.self::STAT_PER * $lv.'上昇!';
		$this->Work($player, $message, $lv);
	}

	public function onReset($player, $lv){
        $this->plugin->playermanager->setCritPer($player, self::STAT_PER * $lv * -1);
	}
}
