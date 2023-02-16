<?php
namespace pve\skill;

use pve\PlayerManager as PM;

use pocketmine\block\Block;
use pocketmine\math\Vector3;

use pve\Type;

class Yakedo extends Skill{
	
	const NAME = '§c火炎';
	const ID = 12;
    const ITEM_ID = 265;
    
    const STAT = 5;
    const DAMAGE = 100;
	const DESCRIPTION = '一定確率で相手をやけどにして追撃を与える';
	
	public function onSet($player, $lv){
        $name = $player->getName();
        if(!isset($this->data[$name])){
            $this->data[$name] = ['stat' => self::STAT * $lv, 'damage' => self::DAMAGE * $lv];
        }else{ 
            $this->data[$name]['stat'] += self::STAT * $lv;
            $this->data[$name]['damage'] += self::DAMAGE * $lv;
        }
	}

	public function onReset($player, $lv){
        $name = $player->getName();
        if(!isset($this->data[$name])) return false;
		$this->data[$name]['stat'] -= self::STAT * $lv;
        $this->data[$name]['damage'] -= self::DAMAGE * $lv;
    }
    
    public function onAttack($player, $field, $eid){
        $name = $player->getName();
        if(!isset($this->data[$name])) return true;
        if($this->data[$name]['stat'] > mt_rand(0,100)){
            $this->plugin->mob->Bleed($player, $field, $eid, 3, Type::FIRE, $this->data[$name]['damage']);
            $pos = $this->plugin->mob->getPos($field, $eid);
            $this->addDestroyParticle($field, $pos, Block::get(213), 7);
            if($this->plugin->mob->log[$player->getName()]) 
              $player->sendMessage('§l§dL-BAS§f>>燃え散らかせですっ！');
        }
      }
}