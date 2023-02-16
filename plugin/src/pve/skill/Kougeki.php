<?php
namespace pve\skill;

use pve\PlayerManager as PM;

use pocketmine\block\Block;
use pocketmine\math\Vector3;

use pve\Type;

class Kougeki extends Skill{
	
	const NAME = '§c虹§b撃';
	const ID = 31;
    const ITEM_ID = 265;
    
    const STAT = 5;
    const PER = 10;
	const DESCRIPTION = '６属性の力で追撃を加える。';
	
	public function onSet($player, $lv){
        $name = $player->getName();
        if(!isset($this->data[$name])){
            $this->data[$name] = ['stat' => self::STAT * $lv, 'per' => self::PER * $lv];
        }else{  
            $this->data[$name]['stat'] += self::STAT * $lv;
            $this->data[$name]['per'] += self::PER * $lv;
        }
	}

	public function onReset($player, $lv){
        $name = $player->getName();
        if(!isset($this->data[$name])) return false;
        $this->data[$name]['stat'] -= self::STAT * $lv;
        $this->data[$name]['per'] -= self::PER * $lv;
    }
    
    public function onAttack($player, $field, $eid){
        $name = $player->getName();
        if(!isset($this->data[$name])) return true;
        if($this->data[$name]['stat'] > mt_rand(0,100)){
            $atk = $this->plugin->mob->checkAtk($player) * $this->data[$name]['per'] / 100;
            for($i = 0; $i < 6; $i++)
              $this->plugin->mob->CustomAttack($atk, $player, $field, $eid, $i);
            if($this->plugin->mob->log[$player->getName()]) 
              $player->sendMessage('§l§dL-BAS§f>>追撃6回です！');
        }
      }
}