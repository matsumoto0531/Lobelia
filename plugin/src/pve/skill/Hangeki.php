<?php
namespace pve\skill;

use pve\PlayerManager as PM;

use pocketmine\block\Block;
use pocketmine\math\Vector3;

use pocketmine\network\mcpe\protocol\types\ParticleIds as Particle;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;

class Hangeki extends Skill{
	
	const NAME = '§a暴風';
	const ID = 35;
	const ITEM_ID = 265;
    
    const DAMAGE = 10;
	const DESCRIPTION = '被弾時に、周囲に風を起こして反撃をする。CT3秒';
	
	public function onSet($player, $lv){
        $name = $player->getName();
        if(!isset($this->data[$name])){
            $this->data[$name] = ['damage' => self::DAMAGE * $lv, 'cooltime' => 0];
        }else{
            $this->data[$name]['damage'] += self::DAMAGE * $lv;
        }
	}

	public function onReset($player, $lv){
        $name = $player->getName();
        if(!isset($this->data[$name])) return false;
		$this->data[$name]['damage'] -= self::DAMAGE * $lv;
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

    public function onDamage($player, $field, $eid){
        $name = $player->getName();
        if(!isset($this->data[$name])) return true;
        if($this->data[$name]['damage'] == 0) return true;
        $time = microtime(true);
        $num = $time - $this->data[$name]['cooltime'];
        if($num < 5) return false;
        $this->data[$name]['cooltime'] = $time;
        $pos = $player->getPosition();
        $this->addParticle($field, $pos, Particle::DRAGON_DESTROY_BLOCK, 2, 7);
        $players = $this->plugin->fieldmanager->getPlayers($field);
        foreach($players as $player) $this->addSound($player, LevelSoundEvent::EXPLODE); 
        $this->addCustomParticle('PVE:WINDATTACK', $pos, $players);
        $mobs = $this->plugin->mob->mobs[$field];
        foreach($mobs as $eid => $data){
            $mpos = new Vector3($data['x'], $data['y'], $data['z']);
            if($this->distance($pos, $mpos) < 5){
                $this->plugin->mob->CustomAttack($this->data[$name]['damage'], $player, $field, $eid, 2);
            }
        }
    }
        
}