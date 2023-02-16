<?php
namespace pve\specialskill;

use pve\PlayerManager as PM;

class Kagerou extends SpecialSkill{
	
	const NAME = '§c§l陽炎§f';
	const ID = 1;
	const ITEM_ID = 265;
	
	const STAT = 10;
	const DESCRIPTION = '姿を揺らめかせ、攻撃をかわしやすくなる。';
	
	public function onDamage($player, $atk){
      if(mt_rand(0, 9) === 1){
          $atk = 0;
      }
      return $atk;
    }

}