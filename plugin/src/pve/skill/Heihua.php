<?php
namespace pve\skill;

use pve\PlayerManager as PM;

use pocketmine\block\Block;
use pocketmine\math\Vector3;

class Heihua extends Skill{
	
	const NAME = '§b氷華';
	const ID = 10;
    const ITEM_ID = 265;
    const CT = 0.5;
    
    const STAT = 20;
	const DESCRIPTION = '氷をまとい、切れ味を上昇させる。攻撃間隔が短いと効果が下がる';
	
	public function onSet($player, $lv){
        $name = $player->getName();
        if(!isset($this->data[$name])){
            $this->data[$name] = ['stat' => self::STAT * $lv, 'time' => 0, 'now' => 0];
        }else{ 
            $this->data[$name]['stat'] += self::STAT * $lv;
        }
	}

	public function onReset($player, $lv){
        $name = $player->getName();
        if(!isset($this->data[$name])) return false;
        $this->plugin->playermanager->setSharp($player, -1 * $this->data[$name]['now']);
        $this->data[$name]['now'] = 0;
        $this->data[$name]['stat'] -= self::STAT * $lv;
    }
    
    public function onAttack($player, $field, $eid){
        $name = $player->getName();
        if(!isset($this->data[$name])) return true;
        $time = microtime(true);
        $num = $time - $this->data[$name]['time'];
        $this->data[$name]['time'] = $time;
        if($num > self::CT){
          if($this->data[$name]['now'] == 0){
            $this->data[$name]['now'] += $this->data[$name]['stat'];
            $this->plugin->playermanager->setSharp($player, $this->data[$name]['now']);
          }
        }else{
          if($this->data[$name]['now'] != 0){
            $this->plugin->playermanager->setSharp($player, -1 * $this->data[$name]['now']);
            $this->data[$name]['now'] = 0;
          }
        }
      }
}