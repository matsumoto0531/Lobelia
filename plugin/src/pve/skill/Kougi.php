<?php
namespace pve\skill;

use pve\PlayerManager as PM;

use pocketmine\block\Block;
use pocketmine\math\Vector3;

use pve\Type;

class Kougi extends Skill{
	
	const NAME = '§7巧技';
	const ID = 32;
    const ITEM_ID = 265;
    
    const PER = 1;
	const DESCRIPTION = '巧みな技でスキルを連発することがある。';
	
	public function onSet($player, $lv){
        $name = $player->getName();
        if(!isset($this->data[$name])){
            $this->data[$name] = ['per' => self::PER * $lv];
        }else{  
            $this->data[$name]['per'] += self::PER * $lv;
        }
	}

	public function onReset($player, $lv){
        $name = $player->getName();
        if(!isset($this->data[$name])) return false;
        $this->data[$name]['per'] -= self::PER * $lv;
    }
    
    public function onSkill($player, $skill){
        $name = $player->getName();
        if(!isset($this->data[$name])) return false;
        if($this->data[$name]['per'] > mt_rand(0, 100)){
          $skill->resetCoolTime($player);
          $pos = $player->getPosition();
          $field = $this->plugin->fieldmanager->getField($player);
          $players = $this->plugin->fieldmanager->getPlayers($field);
          foreach(Type::PARTICLE as $name){
              $this->addCustomParticle($name, $pos, $players);
          }
          if($this->plugin->mob->log[$player->getName()]) 
            $player->sendMessage('§l§dL-BAS§f>>もう一発ぶちかますですよ！');
        }
    }
}