<?php
namespace pve\skill;

use pve\PlayerManager as PM;

use pocketmine\block\Block;
use pocketmine\math\Vector3;

use pocketmine\entity\Entity;

use pve\weapon\Weapon;

use pve\Type;

class Kouki extends Skill{
	
	const NAME = '§e§l煌輝';
	const ID = 21;
    const ITEM_ID = 265;
    
    const PER = 10;
	const DESCRIPTION = '光属性で攻撃するとき、、火力が上がる';
	
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
        if(!isset($this->data[$name])) return false;
        $item = $player->getInventory()->getItemInHand();
        $tag = $item->getNamedTagEntry(Weapon::TAG_WEAPON);
        $type = Type::NONE;
        if(isset($tag))
          $type = $tag->getTag(Weapon::TAG_PROPERTY)->getValue();
        if($type == Type::LIGHT){
          $atk = $this->plugin->mob->checkAtk($player, $eid);
          $this->plugin->playermanager->addAtk($player, $atk*($this->data[$name]['per']/100), 1);
        }
    }
}