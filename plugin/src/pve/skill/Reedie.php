<?php
namespace pve\skill;

use pve\PlayerManager as PM;

use pocketmine\block\Block;
use pocketmine\math\Vector3;

use pocketmine\entity\Entity;

class Reedie extends Skill{
	
	const NAME = '§6雷帝';
	const ID = 14;
    const ITEM_ID = 265;
    
    const PER = 5;
    const DAMAGE = 500;
	const DESCRIPTION = '一定確率であたりに雷を落とす';
	
	public function onSet($player, $lv){
        $name = $player->getName();
        if(!isset($this->data[$name])){
            $this->data[$name] = ['per' => self::PER * $lv, 'damage' => self::DAMAGE * $lv];
        }else{ 
            $this->data[$name]['per'] += self::PER * $lv;
            $this->data[$name]['damage'] += self::DAMAGE * $lv;
        }
	}

	public function onReset($player, $lv){
        $name = $player->getName();
        if(!isset($this->data[$name])) return false;
		$this->data[$name]['per'] -= self::PER * $lv;
        $this->data[$name]['damage'] -= self::DAMAGE * $lv;
    }
    
    public function onAttack($player, $field, $eid){
        $name = $player->getName();
        if(!isset($this->data[$name])) return true;
        if($this->data[$name]['per'] >= mt_rand(0, 100)){
            $pos = $player->getPosition();
            $mobs = $this->plugin->mob->mobs[$field];
            foreach($mobs as $eid => $data){
              $mpos = new Vector3($data['x'], $data['y'], $data['z']);
              if($this->distance($pos, $mpos) < 5){
                $this->addActor($field, $mpos, "minecraft:lightning_bolt");
                $this->plugin->mob->CustomAttack($this->data[$name]['damage'], $player, $field, $eid);
              }
            }
            if($this->plugin->mob->log[$player->getName()])
              $player->sendMessage('§l§dL-BAS§f>>ドーンです！');
        }
        
    }
}