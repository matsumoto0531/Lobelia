<?php
namespace pve\skill;

use pve\PlayerManager as PM;

use pocketmine\block\Block;
use pocketmine\math\Vector3;

use pocketmine\data\bedrock\EffectIds as Effect;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\network\mcpe\protocol\types\ParticleIds as Particle;
use pocketmine\network\mcpe\protocol\types\LevelEvent;

class tailwind extends Skill{
	
	const NAME = '§a追い風';
	const ID = 13;
    const ITEM_ID = 265;
    
    const PER = 5;
    const TIME = 2;
    const LV = 1;
	const DESCRIPTION = '殴ったときに速度が上昇することがある';
	
	public function onSet($player, $lv){
        $name = $player->getName();
        if(!isset($this->data[$name])){
            $this->data[$name] = ['per' => self::PER * $lv, 'time' => self::TIME * $lv, 'lv' => self::LV * $lv];
        }else{ 
            $this->data[$name]['per'] += self::PER * $lv;
            $this->data[$name]['time'] += self::TIME * $lv;
            $this->data[$name]['lv'] += self::LV * $lv; 
        }
	}

	public function onReset($player, $lv){
        $name = $player->getName();
        if(!isset($this->data[$name])) return false;
		$this->data[$name]['per'] -= self::PER * $lv;
        $this->data[$name]['time'] -= self::TIME * $lv;
        $this->data[$name]['lv'] -= self::LV * $lv; 
    }
    
    public function onAttack($player, $field, $eid){
        $name = $player->getName();
        if(!isset($this->data[$name])) return true;
        if($this->data[$name]['per'] >= mt_rand(0, 100)){
            $pos = $player->getPosition();
            $this->addParticle($field, $pos, Particle::DRAGON_DESTROY_BLOCK, 2);
            $effect = VanillaEffects::getAll();
            $player->getEffects()->add(new EffectInstance($effect["SPEED"], 
            $this->data[$name]['time'] * 20, $this->data[$name]['lv']));
            if($this->plugin->mob->log[$player->getName()])
              $player->sendMessage('§l§dL-BAS§f>>追い風です！');
        }
        
    }
}