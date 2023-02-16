<?php
namespace pve\skill;

use pve\PlayerManager as PM;

use pocketmine\block\Block;
use pocketmine\math\Vector3;

class Kiai extends Skill{
	
	const NAME = '§e気合';
	const ID = 24;
	const ITEM_ID = 265;
    
    const PER = 1;
	const DESCRIPTION = '死にそうになった時に耐えることがある。';
	
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
    
    public function onHp($player){
        $name = $player->getName();
        if(!isset($this->data[$name])) return true;
        if($this->plugin->playermanager->getHp($player) <= 0){
            if(mt_rand(0, 100) < $this->data[$name]['per']){
                $this->plugin->playermanager->addHp($player, 1);
            }
        }
        
    }
}