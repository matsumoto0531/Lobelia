<?php
namespace pve\skill;

use pve\PlayerManager as PM;

use pocketmine\block\Block;
use pocketmine\math\Vector3;

use pocketmine\network\mcpe\protocol\types\ParticleIds as Particle;

class Gale extends Skill{
	
	const NAME = '§a疾風';
	const ID = 7;
	const ITEM_ID = 265;
    
    const PER = 10;
	const DESCRIPTION = '攻撃した際、まれに風属性で追撃する。';
	
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
    
    public function onAttack($player, $field, $eid){
        $name = $player->getName();
        if(!isset($this->data[$name])) return true;
        if($this->data[$name]['per'] > mt_rand(0,100)){
            $this->plugin->mob->CustomAttack($this->plugin->mob->checkAtk($player, $eid) / 5, $player, $field, $eid, 2);
            $pos = $this->plugin->mob->getPos($field, $eid);
            $this->addParticle($field, $pos, Particle::DRAGON_DESTROY_BLOCK, 2, 7);
            if($this->plugin->mob->log[$player->getName()])
              $player->sendMessage('§l§dL-BAS§f>>追撃っ！です！');
        }
        
    }
}