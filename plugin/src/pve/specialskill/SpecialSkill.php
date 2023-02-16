<?php
namespace pve\specialskill;

use pocketmine\item\Item;
use pocketmine\utils\UUID;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\entity\Entity;

use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\math\Vector3;

use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;

use pve\Callback;
use pve\MobManager;


class SpecialSkill {
	
	const NAME = "";
	const ID = "";
	const ITEM_ID = 0;
	
	const TAG_SKILL = 'skill';
	const TAG_SKILL_ID = 'skillid';
	const TAG_SKILL_LV = 'skilllv';
	const DESCRIPTION = '';
	
	public function __construct($plugin){
		$this->plugin = $plugin;
	}
	
	public function onSet($player, $lv){
	}
	
	public function onReset($player, $lv){
	}
	
	public function onAttack($player, $field, $eid){
	}

	public function onDamage($player, $atk){
	}

	public function onHp($player){
	}

	public function onSkill($player, $skill){
	}
    
    public function getName(){
		return static::NAME;
	}
	
	public function getId(){
		return static::ID;
	}

	public function getDes(){
		return static::DESCRIPTION;
	}
	
	public function getOrb($lv){
		
		$item = Item::get(static::ITEM_ID, 0, 1);
		$item->setLore([static::DESCRIPTION]);
		$item->setCustomName(static::NAME.'§f[§dLV: §f'.$lv.']');
		
		$nbt = new CompoundTag(self::TAG_SKILL);
		$nbt->setString(self::TAG_SKILL_ID, static::ID);
		$nbt->setString(self::TAG_SKILL_LV, $lv);
		$item->setNamedTagEntry($nbt);
		$item->setNamedTagEntry(new ByteTag("Unbreakable", 1));
		return $item;
	}
	
	public function Work($player, $message, $lv){
		$player->sendMessage('§aSKILL>>§fスキル発動！ ['.static::NAME.'§f][lv: '.$lv.']: '.$message);
	}

	public function addParticle($field, $pos, $id, $data = 0, $count = 1){
		$pk = [];
		for($i = 0; $i < $count; $i++){
		  $pk[$i] = new LevelEventPacket;
		  $pk[$i]->evid = LevelEventPacket::EVENT_ADD_PARTICLE_MASK | $id & 0xFFF;
		  $pk[$i]->data = $data;
		  $pk[$i]->position = new Vector3
		  ($pos->getX()+mt_rand(-1,1), $pos->getY()+mt_rand(0.6,2.6), $pos->getZ()+mt_rand(-1,1));
		}
		$players = $this->plugin->fieldmanager->getPlayers($field);
		foreach($players as $player){
		  foreach($pk as $p)
			$player->dataPacket($p);
		}
	}

	public function addDestroyParticle($field, $pos, $block, $count = 1){
		$pk = [];
		for($i = 0; $i < $count; $i++){
		  $pk[$i] = new LevelEventPacket;
		  $pk[$i]->evid = LevelEventPacket::EVENT_PARTICLE_DESTROY;
		  $pk[$i]->data = $block->getRuntimeId();
		  $pk[$i]->position = new Vector3
		  ($pos->getX()+mt_rand(-1,1), $pos->getY()+mt_rand(0.6,2.6), $pos->getZ()+mt_rand(-1,1));
		}
		$players = $this->plugin->fieldmanager->getPlayers($field);
		foreach($players as $player){
		  foreach($pk as $p)
			$player->dataPacket($p);
		}
	}

	public function addActor($field, $pos, $id, $time = 1){
		$pk = new AddActorPacket();
		$uuid = mt_rand();//UUID::fromString(md5(uniqid(mt_rand(), true)));
		$pk->entityUniqueId = $uuid;
		$pk->entityRuntimeId = Entity::$entityCount++;
		$pk->type = $id;
		$pk->position = $pos;
		$players = $this->plugin->fieldmanager->getPlayers($field);
		foreach($players as $player){
		  $player->dataPacket($pk);
		}
		$this->plugin->getScheduler()->scheduleDelayedTask(
		  new Callback([$this, 'removeActor'], [$players, $uuid]), 20 * $time);
	}

	public function addCustomParticle($name, $pos, $players){
		MobManager::getMob('shadow')->addCustomParticle($name, $pos, $players);
	}

	public function removeActor($players, $uuid){
		$pk = new RemoveActorPacket();
		$pk->entityUniqueId = $uuid;
		foreach($players as $player){
		  $player->dataPacket($pk);
		}
	}
	

	public function distance($pos1, $pos2){
		$ans = (($pos1->x - $pos2->x)**2 + ($pos1->z - $pos2->z)**2);
		return sqrt($ans);
	  }


}