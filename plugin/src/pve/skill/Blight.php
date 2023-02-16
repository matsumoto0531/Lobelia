<?php
namespace pve\skill;

use pve\PlayerManager as PM;

use pocketmine\block\Block;
use pocketmine\math\Vector3;

class Blight extends Skill{
	
	const NAME = '§e陽光';
	const ID = 9;
	const ITEM_ID = 265;
    
    const STAT = 10;
    const TIME = 10;
    const PER = 1;
	const DESCRIPTION = '攻撃を受けたとき、一定確率で体力を回復し、一定時間防御力をあげる';
	
	public function onSet($player, $lv){
        $name = $player->getName();
        if(!isset($this->data[$name])){
            $this->data[$name] = ['per' => self::PER * $lv, 'stat' => self::STAT * $lv, 'time' => self::TIME * $lv, 'now' => 0];
        }else{
            $this->data[$name]['per'] += self::PER * $lv;
            $this->data[$name]['stat'] += self::STAT * $lv;
            $this->data[$name]['time'] += self::TIME * $lv;
        }
	}

	public function onReset($player, $lv){
        $name = $player->getName();
		if(isset($this->data[$name])){
            $this->data[$name]['per'] -= self::PER * $lv;
            $this->data[$name]['stat'] -= self::STAT * $lv;
            $this->data[$name]['time'] -= self::TIME * $lv;
        }
    }
    
    public function onDamage($player, $field, $eid){
        $name = $player->getName();
        if(!isset($this->data[$name])) return true;
        if($this->data[$name]['per'] > mt_rand(0, 100)){
            $this->plugin->playermanager->addHp($player, $this->data[$name]['stat']);
            $this->plugin->playermanager->addDef($player, $this->data[$name]['stat'], 1);
            $this->data[$name]['now'] = $this->data[$name]['stat'];
            $pos = $player->getPosition();
            $this->addDestroyParticle($field, $pos, Block::get(89), 7);
            if($this->plugin->mob->log[$player->getName()])   
              $player->sendMessage('§l§dL-BAS§f>>陽光が発動したです！');
        }
    }

}