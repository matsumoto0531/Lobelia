<?php
namespace pve\skill;

use pve\PlayerManager as PM;

use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\ParticleIds;

class SecondWind extends Skill{
	
	const NAME = '§aセカンドウィンド';
	const ID = 34;
	const ITEM_ID = 265;
    
	const DESCRIPTION = '体力が一定以下になると回復する(CT: 60秒)';
	
	public function onSet($player, $lv){
        $name = $player->getName();
        if(!isset($this->data[$name])){
            $this->data[$name] = ['cooltime' => 0, 'point' => 0.1 * $lv];
        }else{
            $this->data[$name]['point'] += 0.1 * $lv;
        }
	}

	public function onReset($player, $lv){
        $name = $player->getName();
        if(!isset($this->data[$name])) return false;
        $this->data[$name]['point'] -= 0.1 * $lv;
    }
    
    public function onDamage($player, $field, $eid){
        $name = $player->getName();
        if(!isset($this->data[$name])) return true;
        if($this->data[$name]['point'] == 0) return true;
        $hp = $this->plugin->playermanager->getHealthPer($player);
        if($hp <= 0.4){
            $time = microtime(true);
            $num = $time - $this->data[$name]['cooltime'];
            if($num < 60) return false;
            $this->data[$name]['cooltime'] = $time;
            $this->addSound($player, 'PVE:GRASS');
            $this->addParticle($field, $player->getPosition(), ParticleIds::HEART, 2, 20);
            $heal = $this->plugin->playermanager->data[$name]['mhp'] * $this->data[$name]['point']; 
            $this->plugin->playermanager->addHp($player, $heal);
        }
    }
}