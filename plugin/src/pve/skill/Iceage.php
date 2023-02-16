<?php
namespace pve\skill;

use pve\PlayerManager as PM;

use pocketmine\block\Block;
use pocketmine\math\Vector3;

class Iceage extends Skill{
	
	const NAME = '§b氷気';
	const ID = 4;
	const ITEM_ID = 265;
	
    const PER = 5;
    const TIME = 1;
	const DESCRIPTION = '攻撃時一定確率で、相手を凍らせる';
	
	public function onSet($player, $lv){
        $name = $player->getName();
        if(!isset($this->data[$name])){
            $this->data[$name] = ['per' => self::PER * $lv, 'time' => self::TIME * $lv];
        }else{
            $this->data[$name]['per'] += self::PER * $lv;
            $this->data[$name]['time'] += self::TIME * $lv;
        }
	}

	public function onReset($player, $lv){
        $name = $player->getName();
        if(!isset($this->data[$name])) return false;
		$this->data[$name]['per'] -= self::PER * $lv;
        $this->data[$name]['time'] -= self::TIME * $lv;
    }
    
    public function onAttack($player, $field, $eid){
        $name = $player->getName();
        if(!isset($this->data[$name])) return true;
        if($this->data[$name]['per'] > mt_rand(0, 100)){
            $this->plugin->mob->Freeze($field, $eid, 3 * $this->data[$name]['time'], 1);
            $pos = $this->plugin->mob->getPos($field, $eid);
            $this->addDestroyParticle($field, $pos, Block::get(71), 7);
            if($this->plugin->mob->log[$player->getName()]) 
              $player->sendMessage('§l§dL-BAS§f>>氷気が発動したです！');
        }
        
    }
}