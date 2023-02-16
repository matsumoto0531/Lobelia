<?php
namespace pve\skill;

use pve\PlayerManager as PM;

use pocketmine\block\Block;
use pocketmine\math\Vector3;

class Yoiyami extends Skill{
	
	const NAME = '§0宵闇';
	const ID = 11;
    const ITEM_ID = 265;
    
    const STAT = 10;
	const DESCRIPTION = '攻撃力が高いと、会心率が上昇する。';
	
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
        $this->plugin->playermanager->setCrit($player, -1 * $this->data[$name]['now']);
        $this->data[$name]['stat'] -= self::STAT * $lv;
        $this->data[$name]['now'] = 0;
    }
    
    public function onAttack($player, $field, $eid){
        $name = $player->getName();
        if(!isset($this->data[$name])) return true;
        $atk = $this->plugin->playermanager->getAtk($player, $field, $eid);
        if($atk > 500){
          if($this->data[$name]['now'] === 0){
            $this->data[$name]['now'] += $this->data[$name]['stat'];
            $this->plugin->playermanager->setCrit($player, $this->data[$name]['now']);
          }
        }else{
          $this->plugin->playermanager->setCrit($player, -1 * $this->data[$name]['now']);
          $this->data[$name]['now'] = 0;
        }
      }
}