<?php
namespace pve\item;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\utils\UUID;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ByteTag;

use pve\MobManager;
use pve\WeaponSkillManager;

class PveItem {

    const NAME = '';
    const ID = 0;
    const ITEM_ID = 0;
    const PURITURE = 0;
    const AMOUNT = 10;
    const DESCRIPTION = '';
    const IS_ORE = false;


    const TAG_ID = 'id';
    const TAG_ITEM = 'item';
    const TAG_ITEM_ID = 'item_id';
    const TAG_TYPE = 'type';
    const TAG_UNIQUE_ID = 'uniqueid';

    public function __construct($plugin){
      $this->plugin = $plugin;
    }

    public function getItem(){
		//$data = $this->plugin->useitemData[$id];
		
		$item = ItemFactory::getInstance()->get(static::ITEM_ID, 0, 1);
		$item->setLore(['§c'.static::DESCRIPTION]);
		$item->setCustomName(static::NAME);
		
		$nbt = new CompoundTag();
		$nbt->setString(self::TAG_ID, static::ID);
		$nbt->setString(self::TAG_TYPE, 'item');
		//$nbt->setString(self::TAG_UNIQUE_ID, UUID::fromRandom()->toString());
		$item->getNamedTag()->setTag(self::TAG_ITEM, $nbt);
		$item->getNamedTag()->setTag("Unbreakable", new ByteTag(1));
		return $item;
  }

  public function Interact($event){

  }

  public function getId(){
    return static::ID;
  }

  public function getName(){
    return static::NAME;
  }

  public function getPurity(){
    return static::PURITURE;
  }

  public function isOre(){
    return static::IS_ORE;
  }

  public function getAmount(){
    return static::AMOUNT;
  }

  public function send($player){
  }
  
  public static function isItem($item){
		$result = false;
		$tag = $item->getNamedTag(self::TAG_ITEM)->getTag(self::TAG_ITEM);
		if(isset($tag)) $result = true;
		return $result;
  }
  
  public static function gettingId($item){
		$result = false;
		$tag = $item->getNamedTag(self::TAG_ITEM)->getTag(self::TAG_ITEM);
    if(!isset($tag)) return $result;
    $result = $tag->getTag(self::TAG_ID)->getValue();
		return $result;
  }

  public function addParticle($field, $eid, $id, $data = 0, $count = 1){
    MobManager::getMob('shadow')->addParticle($field, $eid, $id, $data, $count);
  }

  public function addCustomParticle($name, $pos, $players){
    WeaponSkillManager::getSkill(1)->addCustomParticle($name, $pos, $players);
  }


}