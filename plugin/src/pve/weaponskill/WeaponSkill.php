<?php
namespace pve\weaponskill;

use pocketmine\item\Item;
use pocketmine\utils\UUID;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\level\particle\Particle;
use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\block\BlockFactory;

use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket;

use pve\Callback;
use pve\MobManager;
use pve\SkillManager;


class WeaponSkill {
	
	const NAME = "";
	const ID = "";
	const ITEM_ID = 0;
	const CT = 0;
	const RANK = 1;
	const LEVEL = 1;
	const TP = 1;
	
	const TAG_SKILL = 'skill';
	const TAG_SKILL_ID = 'skillid';
	const DESCRIPTION = '';
	
	public function __construct($plugin){
		$this->plugin = $plugin;
		$this->data = ['a' => 0];
	}


	
	public function onSet($data, $player, $lv){
		return $data;
    }

    public function Interact($weapon, $player){
      $name = $player->getName();
      if(!array_key_exists($name, $this->data))
      $this->data[$name] = 0;
      $time = microtime(true);
      $num = $time - $this->data[$name];
      if($num > static::CT){
        $tp = static::TP;
	    $xp  = $player->getXpLevel();
	    if($xp < $tp){
		  return false;
	    }else{
		  if($player->isImmobile()) return false;
		  $this->data[$name] = $time;
		  $nxp = $xp - $tp;
		  $player->setXpLevel($nxp);
		  $player->setXpProgress($nxp/100);
		  $this->Imposition($player);
		  $this->send($player);
		  $skills = SkillManager::onSkill();
            foreach ($skills as $skil){
              $skil->onSkill($player, $this);
            }
	    }
	  }
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

	public function getLevel(){
		return static::LEVEL;
	}

	public function getRank(){
		return static::RANK;
	}

	public function getCt(){
		return static::CT;
	}

	public function getTp(){
		return static::TP;
	}
	
	public function getOrb(){
		
		$item = Item::get(static::ITEM_ID, 0, 1);
		$item->setLore([static::DESCRIPTION]);
		$item->setCustomName(static::NAME.'§f[§dLV: §f1]');
		
		$nbt = new CompoundTag(self::TAG_SKILL);
		$nbt->setString(self::TAG_SKILL_ID, static::ID);
		$item->setNamedTagEntry($nbt);
		$item->setNamedTagEntry(new ByteTag("Unbreakable", 1));
		return $item;
	}
	
	public function Work($player, $message, $lv){
		$player->sendMessage('§aSKILL>>§fスキル発動！ ['.static::NAME.'§f][lv: 1]: '.$message);
	}

	public function send($player){
		$field = $this->plugin->fieldmanager->getField($player);
		$players = $this->plugin->fieldmanager->getPlayers($field);
		foreach($players as $p)
		  $p->addTitle
		    (' ', $player->getName().'>>'.static::NAME, 10, 10, 10);
	}

	public function addParticles($field, $poss, $id, $data = 0){
		foreach($poss as $pos){
			$this->addParticle($field, $pos, $id, $data);
		}
	}

	public function addParticle($field, $pos, $id, $data = 0, $count = 1){
		$pk = [];
		for($i = 0; $i < $count; $i++){
		  $pk[] = new LevelEventPacket;
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
	
	  public function addSound($field, $pos, $id){
		$pk = new LevelSoundEventPacket();
		$pk->sound = $id;
		$pk->position = $pos;
		$players = $this->plugin->fieldmanager->getPlayers($field);
		foreach($players as $player){
		  $player->dataPacket($pk);
		}
	  }
	
	  public function addActors($field, $poss, $id, $count){
		for($i = 0; $i < $count; $i++)
		  $this->addActor($field, $poss[mt_rand(0, count($poss)-1)], $id);
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
	
	  public function removeActor($players, $uuid){
		$pk = new RemoveActorPacket();
		$pk->entityUniqueId = $uuid;
		foreach($players as $player){
		  $player->dataPacket($pk);
		}
	  }

	  public function addDestroyParticles($field, $poss, $block){
        foreach($poss as $pos){
			$this->addDestroyParticle($field, $pos, $block, 1);
		}
	  }

	  public function addDestroyParticle($field, $pos, $block, $count = 1){
		$pk = [];
		for($i = 0; $i < $count; $i++){
		  $pk[$i] = new LevelEventPacket;
		  $pk[$i]->evid = LevelEventPacket::EVENT_PARTICLE_DESTROY;
		  $pk[$i]->data = $block->getRuntimeId();
		  $pk[$i]->position = $pos;
		}
		$players = $this->plugin->fieldmanager->getPlayers($field);
		foreach($players as $player){
		  foreach($pk as $p)
			$player->dataPacket($p);
		}
	  }

	  public function distance($pos1, $pos2){
		$ans = (($pos1->x - $pos2->x)**2 + ($pos1->z - $pos2->z)**2);
		return sqrt($ans);
	  }

	  public function circle($pos, $radiusX, $radiusZ){
		$sets = [
		  [1,1], [1,-1], [-1,1], [-1,-1]
		];
		$poss = [];
		$baseX = 1/$radiusX;
		$baseZ = 1/$radiusZ;
		$nextx = 0;
		$fx = false;
		for($x = 0; $x <= $radiusX and !$fx; $x++){
		  $nx = $nextx;
		  $nextx = ($x+1) * $baseX;
		  $nextZ = 0;
		  for($z = 0; $z <= $radiusZ; $z++){
			$nz = $nextZ;
			$nextZ = ($z+1) * $baseZ;
			if($nx**2 + $nz**2 > 1){
			  if($z == 0){
				$fx = true;
			  break;
			  }
			break;
			}
			for($i = 0; $i < 4; $i++)
			  $poss[] = new Vector3($pos->x + ($x * $sets[$i][0]), $pos->y, $pos->z + ($z * $sets[$i][1]));
		  }
		}
		return $poss;
	  }

	  public function setColors($poss, $level, $damage, $count = 0){
		if(isset($poss[$count])){
		  $this->setColor($poss[$count], $level, $damage);
		  $this->plugin->getScheduler()->scheduleDelayedTask(
			new Callback([$this, 'setColors'], [$poss, $level, $damage, ++$count]), 1);
		}
	  }
	
	  public function setColor($pos, $level, $damage){
		  $sets = [
			[0,0], [1,0], [-1,0], [0,1], [0,-1]
		  ];
		  $block = BlockFactory::get(35, $damage);
		  $this->plugin->getScheduler()->scheduleDelayedTask(
			new Callback([$this, 'resetColor'], [$pos, $level]), 20);
		  for($i=0; $i<5; $i++)
			$level->setblock(new Vector3($pos->getX()+$sets[$i][0], $pos->getY(), $pos->getZ()+$sets[$i][1]), $block);
	  }
	
	  public function resetColor($pos, $level){
		$sets = [
		  [0,0], [1,0], [-1,0], [0,1], [0,-1]
		];
		$block = BlockFactory::get(35, 0);
		for($i=0; $i<5; $i++)
			$level->setblock(new Vector3($pos->getX()+$sets[$i][0], $pos->getY(), $pos->getZ()+$sets[$i][1]), $block, false, false);
	  }

	  public function line($pos, $yaw, $range){
		$count = 0;
		$nowX = $pos->x;
		$nowZ = $pos->z;
		$poss = [];
		for($count = 0; $count < $range; $count++){
		  $nowX += 1 * cos($yaw);
		  $nowZ += 1 * sin($yaw);
		  $poss[] = new Vector3($nowX, $pos->y, $nowZ); 
		}
		return $poss;
	  }

	  public function addCustomParticle($name, $pos, $players){
		$pk = new SpawnParticleEffectPacket();
		$pk->position = $pos;
		$pk->particleName = $name;
		foreach($players as $player)
		$player->dataPacket($pk);
	  }

	  public function addAnimate($players, $eid, $name, $time = 5){
        MobManager::getMob('shadow')->addAnimate($players, $eid, $name);
	  }

	  public function resetCoolTime($player){
		  $this->data[$player->getName()] = 0;
	  }

	  public function checkBlock($pos){
		$level = $this->plugin->fieldmanager->getLevel();
		$block = $level->getBlock($pos);
		if($block->canPassThrough()) return true;
		return false;
	  }
}
