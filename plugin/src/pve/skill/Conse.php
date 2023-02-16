<?php
namespace pve\skill;

use pve\PlayerManager as PM;

use pocketmine\block\Block;
use pocketmine\math\Vector3;

class Conse extends Skill{
	
	const NAME = '§e連撃';
	const ID = 8;
    const ITEM_ID = 265;
    const CT = 10;
    const MAX = 100;
    
    const STAT = 5;
	const DESCRIPTION = '連続で攻撃を行うと火力が上昇する';
	
	public function onSet($player, $lv){
        $name = $player->getName();
        if(!isset($this->data[$name])){
            $this->data[$name] = ['stat' => self::STAT * $lv, 'time' => 0, 'now' => 0, 'max' => self::MAX * $lv];
        }else{ 
            $this->data[$name]['stat'] += self::STAT * $lv;
            $this->data[$name]['max'] += self::MAX * $lv;
        }
	}

	public function onReset($player, $lv){
        $name = $player->getName();
        if(!isset($this->data[$name])) return false;
        $this->plugin->playermanager->setAtk($player, -1 * $this->data[$name]['now']);
        $this->data[$name]['now'] = 0; 
        $this->data[$name]['stat'] -= self::STAT * $lv;
        $this->data[$name]['max'] -= self::MAX * $lv;
    }
    
    public function onAttack($player, $field, $eid){
        $name = $player->getName();
        if(!isset($this->data[$name])) return true;
        $time = microtime(true);
        $num = $time - $this->data[$name]['time'];
        $this->data[$name]['time'] = $time;
        if($num < self::CT){
          if($this->data[$name]['now'] >= $this->data[$name]['max']) return false;
          $this->data[$name]['now'] += $this->data[$name]['stat'];
          $this->plugin->playermanager->setAtk($player, $this->data[$name]['stat']);
        }else{
          $this->plugin->playermanager->setAtk($player, -1 * $this->data[$name]['now']);
          $this->data[$name]['now'] = 0;
        }
      }
}