<?php
namespace pve\skill;

use pve\PlayerManager as PM;

use pocketmine\block\Block;
use pocketmine\math\Vector3;

class Flame extends Skill{
	
	const NAME = '§c情熱';
	const ID = 6;
	const ITEM_ID = 265;
    
    const STAT = 1;
	const DESCRIPTION = '体力が減れば減るほど火力が上がる';
	
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
		    $this->data[$name]['stat'] -= self::STAT * $lv;
            $this->data[$name]['now'] = 0;
    }
    
    public function onHp($player){
        $name = $player->getName();
        if(!isset($this->data[$name])) return true;
        if(!$this->data[$name]['stat']) return false;
        $per = $this->plugin->playermanager->getHealthPer($player);
        $atk = $this->plugin->playermanager->getAtkData($player);
        $atk -= $this->data[$name]['now'];
        $this->plugin->playermanager->setAtk($player, -1 * $this->data[$name]['now']);
        if($atk == 0) $atk = 1;
        $atk += (($atk * ($this->data[$name]['stat'] / 10)) * (1 - $per) * 5);
        $this->data[$name]['now'] = $atk;
        $this->plugin->playermanager->setAtk($player, $atk);
        if($per*100 < 20){
          $field = $this->plugin->fieldmanager->getField($player);
          $pos = $player->getPosition();
          $this->addDestroyParticle($field, $pos, Block::get(213), 7); 
        }
        if($per*100 < 1){
          if($this->plugin->mob->log[$player->getName()]){
            $player->sendMessage('§l§dL-BAS§f>>最後のフルパワーです！');
            $player->sendMessage('§l§dL-BAS§f>>ケツに力いれやがれですよ！');
          }
        }
    }
}