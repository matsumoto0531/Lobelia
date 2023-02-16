<?php
namespace pve\skill;

use pve\PlayerManager as PM;

use pocketmine\block\Block;
use pocketmine\math\Vector3;

class Ikou extends Skill{
	
	const NAME = '§e威光';
	const ID = 15;
	const ITEM_ID = 265;
    
    const STAT = 50;
	const DESCRIPTION = '体力が多いとステータスが上がる';
	
	public function onSet($player, $lv){
        $name = $player->getName();
        if(!isset($this->data[$name])){
            $this->data[$name] = ['stat' => self::STAT * $lv, 'now' => 0];
        }else{
            $this->data[$name]['stat'] += self::STAT * $lv;
        }
	}

	public function onReset($player, $lv){
        $name = $player->getName();
        if(!isset($this->data[$name])) return false;
        $this->plugin->playermanager->setAtk($player, -1 * $this->data[$name]['now']);
        $this->plugin->playermanager->setDef($player, -1 * $this->data[$name]['now']);
        $this->data[$name]['stat'] -= self::STAT * $lv;
        $this->data[$name]['now'] = 0;
    }
    
    public function onHp($player){
        $name = $player->getName();
        if(!isset($this->data[$name])) return true;
        $per = $this->plugin->playermanager->getHealthPer($player) * 100;
        if($per >= 90){
          if($this->data[$name]['now'] === 0){
            $this->plugin->playermanager->setAtk($player, $this->data[$name]['stat']);
            $this->plugin->playermanager->setDef($player, $this->data[$name]['stat']);
            $this->data[$name]['now'] += $this->data[$name]['stat'];
          }
        }else{
          if($this->data[$name]['now'] !== 0){
            $this->plugin->playermanager->setAtk($player, -1 * $this->data[$name]['stat']);
            $this->plugin->playermanager->setDef($player, -1 * $this->data[$name]['stat']);
            $this->data[$name]['now'] -= $this->data[$name]['stat'];
          }
        }
    }
}