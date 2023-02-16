<?php
namespace pve\skill;

use pve\PlayerManager as PM;

use pocketmine\block\Block;
use pocketmine\math\Vector3;

class Dark extends Skill{
	
	const NAME = '§b深淵';
	const ID = 5;
	const ITEM_ID = 265;
    
    const STAT = 10;
    const PER = 5;
	const DESCRIPTION = '攻撃を受けたとき、一定確率で相手の攻撃力を下げる';
	
	public function onSet($player, $lv){
        $name = $player->getName();
        if(!isset($this->data[$name])){
            $this->data[$name] = ['per' => self::PER * $lv, 'stat' => self::STAT * $lv];
        }else{
            $this->data[$name]['per'] += self::PER * $lv;
            $this->data[$name]['stat'] += self::STAT * $lv;
        }
	}

	public function onReset($player, $lv){
        $name = $player->getName();
        if(isset($this->data[$name])){
          $this->data[$name]['per'] -= self::PER * $lv;
          $this->data[$name]['stat'] -= self::STAT * $lv;
        }
    }
    
    public function onDamage($player, $field, $eid){
        $name = $player->getName();
        if(!isset($this->data[$name])) return true;
        if($this->data[$name]['per'] > mt_rand(0, 100)){
            $this->plugin->mob->addAtk($field, $eid, -3 * $this->data[$name]['stat'], 20);
            $pos = $this->plugin->mob->getPos($field, $eid);
            $this->addDestroyParticle($field, $pos, Block::get(112), 7); 
            if($this->plugin->mob->log[$player->getName()])
              $player->sendMessage('§l§dL-BAS§f>>深淵が発動したです！');
        }
        
    }
}