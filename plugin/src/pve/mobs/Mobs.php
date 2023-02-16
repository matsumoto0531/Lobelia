<?php
namespace pve\mobs;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\enchantment\EnchantmentInstance as EI;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\block\BlockFactory;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityIds;
use pocketmine\math\Vector3;
use Ramsey\Uuid\Uuid;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\NBT;
use pocketmine\world\particle\Particle;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pve\packet\SpawnParticleEffectPacket;
use pocketmine\network\mcpe\protocol\types\LevelEvent;

use pve\packet\AnimateEntityPacket;

use pve\item\ItemManager;
use pve\WeaponManager;
use pve\ArmorManager;
use pve\SkillManager;
use pve\Callback;
use pve\Type;

class Mobs {

  const NAME = '';
  const HP = 0;
  const ATK = 0;
  const DEF = 0;
  const EXP = 0;
  const MONEY = 10;
  const TYPE = Type::NONE;
  const DROPS = [1 => [0 => 200], 0 => [0 => 100]];
  const SKILLS = [];
  const SWORD_RECIPE = [0 => 1];
  const ARMOR_RECIPE = [0 => 1];
  const ORB_RECIPE = [0 => 1];
  const SWORD = [];
  const ARMOR = [];
  const ORB = [];
  const ACC = [];
  const TLERANT = [Type::NONE => 1];
  const POW = 1;
  const AGI = 1;
  const HAN = 1;
  const BODY = 1;
  const SYU = 1;
  const MAGIC = 1;
  
  const TAG_ITEM = 'items';
  const TAG_ID = 'ids';
  const TAG_UNIQUE_ID = 'unique';

  const STATE = [
    [10, 10, 20, 40, 20],
    [5, 10, 65, 20, 0],
    [1, 29, 60, 10, 0],
    [5, 15, 70, 9, 1],
    [0, 20, 70, 10, 0]
  ];

  const KAKURITU = [
    [0, 100],
    [0, 100],
    [0, 100],
    [0, 70],
    [0, 5]
  ];

  const BAIRITU = [
    [1, 1],
    [1, 1],
    [1, 1],
    [1, 1],
    [1, 1]
  ];
  
  public function __construct($plugin){
	  $this->plugin = $plugin;
  }
  
  public function getHp(){
    return static::HP;
  }
  
  public function getAtk(){
    return static::ATK;
  }
  
  public function getDef(){
    return static::DEF;
  }
  
  public function getName(){
    return static::NAME;
  }

  public function getPow(){
    return static::POW;
  }

  public function getAgi(){
    return static::AGI;
  }

  public function getHan(){
    return static::HAN;
  }

  public function getBody(){
    return static::BODY;
  }

  public function getSyu(){
    return static::SYU;
  }

  public function getMagic(){
    return static::MAGIC;
  }
  
  public function getExp(){
    return static::EXP;
  }
  
  public function getDrop($lv){
    $items = [];
    foreach(static::DROPS as $id => $percent){
      $item = $this->getItem($id);
      $count = 0;
      $num = floor($percent / 100);
      $num += 1;
      $per = ($percent % 100);
      for($i = 0; $i < $num; $i++){
        if(mt_rand(0, 100) <= $per)
          $count++;
        }
      $item = $item->setCount($count);
      $items[] = $item;
    }
    foreach(static::SKILLS as $id => $percent){
      $item = ItemManager::getItem($id)->getItem();
      if(mt_rand(0, 10000) <= $percent){
        $items[] = $item;
      }
    }
    return $items;
  }

  public function getRecipe($type){
    $items = [];
    switch($type){
      case 'sword':
        $recipe = static::SWORD_RECIPE;
        break;
      case 'armor':
        $recipe = static::ARMOR_RECIPE;
        break;
      case 'orb':
        $recipe = static::ORB_RECIPE;
        break;
    }
    foreach($recipe as $id => $percent){
      switch($type){
        case 'sword':
          $item = $this->plugin->recipe->getSwordRecipe($id, mt_rand(1, 4));
          break;
        case 'armor':
          $item = $this->plugin->recipe->getArmorRecipe($id, mt_rand(1, 4));
          break;
        case 'orb':
          $item = $this->plugin->recipe->getOrbRecipe($id, mt_rand(1, 4));
          break;
      }  
      if(mt_rand(0, 1000) <= $percent){
        $items[] = $item;
      }
    }
    return $items;
  }

  public function getBossRecipe($type){
    $items = [];
    $data = ['sword', 'armor', 'orb'];
    $recipe = static::SWORD_RECIPE;
    $recipe = array_merge($recipe, static::ARMOR_RECIPE);
    $recipe = array_merge($recipe, static::ORB_RECIPE);
    $p = 0;
    foreach($recipe as $id => $percent){
      $p += $percent;
    }
    $rand = mt_rand(0, $p);
    foreach($data as $n){
      $r = $this->getList($n);
      foreach($r as $id => $percent){
        if($rand < $percent){
          $items[] = $this->makeRecipe($n, $id);
          return $items;
        }else{
          $rand -= $percent;
        }
      }
    }
  }

  public function getBossDrop($p){
    $items = [];
    $state = $this->getState($p);
    $data = ['sword', 'armor', 'orb', 'acc'];
    foreach($data as $n){
      $list = $this->getList($n);
      if($n === 'armor') $k = 1;
      else $k = 1;
      foreach($list as $id => $percent){
        for($i = 0; $i < $k; $i++){
          $rand = $this->getRand($state);
          if($rand < $percent){
            $amount = $this->getAmount($state);
            for($j = 0; $j < $amount; $j++)
              $items[] = $this->makeEquip($n, $id);
          }
        }
      }
    }
    return $items;
  }


  public function getZakoDrop($p){
    $items = [];
    $state = 2;
    $data = ['sword', 'armor', 'orb'];
    foreach($data as $n){
      $list = $this->getList($n);
      if($n === 'armor') $k = 1;
      else $k = 1;
      foreach($list as $id => $percent){
        for($i = 0; $i < $k; $i++){
          $rand = $this->getRand($state);
          if($rand < $percent){
            $amount = $this->getAmount($state);
            for($j = 0; $j < $amount; $j++)
              $items[] = $this->makeEquip($n, $id);
          }
        }
      }
    }
    return $items;
  }

  public function getState($player){
    $name = $player->getName();
    if(!isset($this->last[$name])) $this->last[$name] = 2;
    $kakuritu = self::STATE[$this->last[$name]];
    $rand = mt_rand(0, 100);
    for($i = 0; $i < 5; $i++){
      if($rand < $kakuritu[$i]){
        $this->last[$name] = $i;
        //$player->sendMessage($i.'');
        return $i;
      }
      $rand -= $kakuritu[$i];
    }
    return 3;
  }

  public function getRand($state){
    $data = self::KAKURITU[$state];
    return mt_rand($data[0], $data[1]);
  }

  public function getAmount($state){
    $data = self::BAIRITU[$state];
    return mt_rand($data[0], $data[1]);
  }

  public function makeEquip($n, $id){
    switch($n){
      case 'sword':
        $item = WeaponManager::getWeapon()->getItem($id, [mt_rand(70, 150), mt_rand(70, 150)]);
        break;
      case 'armor':
        $skill = mt_rand(0, 100) < 30 ? 1 : 0;
        $item = ArmorManager::getArmor()->getItem($id, 1, mt_rand(0, 3), mt_rand(1, 3), $skill);
        break;
      case 'orb':
        $item = SkillManager::getSkill($id)->getOrb(1);
        break;
      case 'acc':
        $item = $this->plugin->acc->getItem($id, 1, mt_rand(0, 3), mt_rand(0, 1));
        break;
    }
    return $item;
  }

  public function getList($n){
    switch($n){
      case 'sword':
        $recipe = static::SWORD;
        break;
      case 'armor':
        $recipe = static::ARMOR;
        break;
      case 'orb':
        $recipe = static::ORB;
        break;
      case 'acc':
        $recipe = static::ACC;
        break;
    }
    return $recipe;
  }

  public function makeRecipe($n, $id){
    switch($n){
      case 'sword':
        $item = $this->plugin->recipe->getSwordRecipe($id, mt_rand(1, 4));
        break;
      case 'armor':
        $item = $this->plugin->recipe->getArmorRecipe($id, mt_rand(1, 4));
        break;
      case 'orb':
        $item = $this->plugin->recipe->getOrbRecipe($id, mt_rand(1, 4));
        break;
    }
    return $item;  
  }

  public function getMoney($lv){
    return static::MONEY * $lv;
  }

  public function getType(){
    return static::TYPE;
  }

  public function kill($player){
    
  }
  
  public function getItem($id){
    if(!isset($this->plugin->itemData[$id])) return ItemFactory::getInstance()->get(0, 0, 0);
    $data = $this->plugin->itemData[$id];
    $item = ItemFactory::getInstance()->get($data['itemid'], 0, 1);
    $item->setCustomName($data["name"]);
    $nbt = new CompoundTag();
    $nbt->setString(self::TAG_ID, $id);
    //$nbt->setString(self::TAG_UNIQUE_ID, UUID::fromRandom()->toString());
    $item->getNamedTag()->setTag(self::TAG_ITEM, $nbt);
    $ench = new ListTag([], NBT::TAG_Compound);
    $entag = new CompoundTag();
    $entag->setShort("id", -1);
    $entag->setShort("lvl", 0);
    $ench->push($entag);
    $item->getNamedTag()->setTag(Item::TAG_ENCH, $ench);
    //$item->setNamedTagEntry(new ByteTag("ench", 1));
    return $item;
  }
  
  public function move1($field, $eid){
    $this->plugin->mob->Move($field, $eid);
  }
  
  public function move2($field, $eid){
    $this->move1($field, $eid);
  }

  public function movehalf($field, $eid){
  }

  public function movequarter($field, $eid){
  }

  public function setColors($poss, $level, $damage, $tick = 20, $count = 0){
    if(isset($poss[$count])){
      $this->setColor($poss[$count], $level, $damage, $tick);
      $this->plugin->getScheduler()->scheduleDelayedTask(
        new Callback([$this, 'setColors'], [$poss, $level, $damage, $tick, ++$count]), 1);
    }
  }

  public function setColor($pos, $level, $damage, $tick){
      $block = BlockFactory::getInstance()->get(35, $damage);
      $this->plugin->getScheduler()->scheduleDelayedTask(
        new Callback([$this, 'resetColor'], [$pos, $level]), $tick);
        $level->setblock(new Vector3($pos->getX(), $pos->getY(), $pos->getZ()), $block);
  }

  public function resetColor($pos, $level){
    $block = BlockFactory::getInstance()->get(35, 0);
    $level->setblock(new Vector3($pos->getX(), $pos->getY(), $pos->getZ()), $block, false, false);
  }

  public function send($player, $skillname, $message = null){
    if(!isset($message)){
      $this->plugin->playermanager->sendJukePop($player, static::NAME.'>>'.$skillname);
    }else{
      $this->plugin->playermanager->sendJukePop($player, static::NAME.'>>'.$message);
    }
  }

  public function addParticle($field, $pos, $id, $data = 0, $count = 1){
    $pk = [];
    for($i = 0; $i < $count; $i++){
      $pk[$i] = new LevelEventPacket;
      $pk[$i]->eventId = LevelEvent::ADD_PARTICLE_MASK | $id & 0xFFF;
      $pk[$i]->eventData = $data;
      $pk[$i]->position = new Vector3
      ($pos->getX()+mt_rand(-1,1), $pos->getY()+mt_rand(0.6,2.6), $pos->getZ()+mt_rand(-1,1));
    }
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $player){
      foreach($pk as $p)
        $player->getNetworkSession()->sendDataPacket($p);
    }
  }

  public function addDestroyParticle($field, $pos, $block, $count = 1){
    $pk = [];
    for($i = 0; $i < $count; $i++){
      $pk[$i] = new LevelEventPacket;
      $pk[$i]->eventId = LevelEvent::PARTICLE_DESTROY;
      $pk[$i]->eventData = $block->getId();
      $pk[$i]->position = new Vector3
      ($pos->getX()+mt_rand(-1,1), $pos->getY()+mt_rand(0.6,2.6), $pos->getZ()+mt_rand(-1,1));
    }
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $player){
      foreach($pk as $p)
        $player->getNetworkSession()->sendDataPacket($p);
    }
  }

  public function addSound($field, $pos, $id){
    $pk = new LevelSoundEventPacket();
    $pk->sound = $id;
    $pk->position = $pos;
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $player){
      $player->getNetworkSession()->sendDataPacket($pk);
    }
  }

  public function addActors($field, $poss, $id, $count){
    for($i = 0; $i < $count; $i++)
      $this->addActor($field, $poss[mt_rand(0, count($poss)-1)], $id);
  }

  public function addActor($field, $pos, $id, $time = 1){
    $pk = new AddActorPacket();
    $uuid = mt_rand();//UUID::fromString(md5(uniqid(mt_rand(), true)));
    $pk->actorUniqueId = $uuid;
    $pk->actorRuntimeId = Entity::nextRuntimeId();
    $pk->type = $id;
    $pk->position = $pos;
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $player){
      $player->getNetworkSession()->sendDataPacket($pk);
    }
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'removeActor'], [$players, $uuid]), 20 * $time);
  }

  public function removeActor($players, $uuid){
    $pk = new RemoveActorPacket();
    $pk->actorUniqueId = $uuid;
    foreach($players as $player){
      $player->getNetworkSession()->sendDataPacket($pk);
    }
  }

  public function distance($pos1, $pos2){
    $ans = (($pos1->x - $pos2->x)**2 + ($pos1->z - $pos2->z)**2);
    return sqrt($ans);
  }

  public function tuibi($pos1, $pos2){
    $sets = [
      [0,0], [1,0], [-1,0], [0,1], [0,-1]
    ];
    while(true){
      $yaw = rad2deg(atan2($pos2->getZ() - $pos1->getZ(), $pos2->getX() - $pos1->getX())) - 90;
      if($yaw < 0) $yaw += 360.0;
      $rad = deg2rad($yaw);
      $mx = (1 * sin($rad));
      $mz = (1 * cos($rad));
      $pos1 = new Vector3($pos1->getX() - $mx, $pos1->getY(), $pos1->getZ() + $mz);
      foreach($sets as $set)
        $poss[] = new Vector3((int)($pos1->x + $set[0]), (int)$pos1->y, (int)($pos1->z + $set[1]));
      if($this->distance($pos1, $pos2) <= 1){
        return $poss;
      }
    }
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
          $poss[] = new Vector3((int)($pos->x + ($x * $sets[$i][0])), (int)$pos->y, (int)($pos->z + ($z * $sets[$i][1])));
      }
    }
    return $poss;
  }

  public function tuibiend($id, ...$data){
  }

  public function line($pos, $yaw, $range){
    $sets = [
      [0,0], [1,0], [-1,0], [0,1], [0,-1]
    ];
    $count = 0;
    $nowX = $pos->x;
    $nowZ = $pos->z;
    $poss = [];
    for($count = 0; $count < $range; $count++){
      $nowX += 1 * cos($yaw);
      $nowZ += 1 * sin($yaw);
      foreach($sets as $set){
        $poss[] = new Vector3((int)($nowX + $set[0]), (int)($pos->y), (int)($nowZ + $set[1])); 
      }
    }
    return $poss;
  }

  public function isHit($player, $poss){
    $result = false;
    $flag = false;
    $pos = $this->plugin->animation->getPos($player);
    $block = $this->plugin->fieldmanager->level->getBlock($pos->down());
    if($block->getId() === 0){
      $block = $this->plugin->fieldmanager->level->getBlock(new Vector3($pos->x, $pos->y-2, $pos->z));
      $flag = true;
    }
    if($block->getId() === 35){
      switch($block->getMeta()){
        case 4:
          if(!$player->isSneaking()) $result = true; 
        break;
        case 5:
          $result = true;
        break;
        case 6:
          if(!$player->isSprinting()) $result = true;
        break;
        case 7:
        case 14:
          $result = true;
        break;
      }
    }
    if($result){
      if($flag){
        $pos = new Vector3((int)$pos->x, (int)$pos->y-2, (int)$pos->z);
      }else{
        $pos = new Vector3((int)$pos->x, (int)$pos->y-1, (int)$pos->z);
      }
      if(!in_array($pos, $poss)) $result = false;
    }
    return $result;
  }

  public function addCustomParticle($name, $pos, $players){
    $pk = new SpawnParticleEffectPacket();
    $pk->position = $pos;
    $pk->particleName = $name;
		foreach($players as $player)
		$player->getNetworkSession()->sendDataPacket($pk);
  }

  public function addAnimate($players, $eid, $name, $time = 5){
    $pk = new AnimateEntityPacket();
    $pk->animation = $name;
		$pk->nextState = "none";
		$pk->stopExpression = "query.is_sneaking";//"query.anim_time > 1";
		$pk->controller = "";
		$pk->blendOutTime = 0;
		$pk->actorRuntimeIds = [$eid];
		foreach($players as $player){
      $player->getNetworkSession()->sendDataPacket($pk);
    }
  }

  public function sendTask($player, $pk){
    $player->getNetworkSession()->sendDataPacket($pk);
  }

  public function onMove($field, $eid){
    
  }

  public function makeSound($field, $pos, $name, $volume = 1){
    $pk = new PlaySoundPacket();
    $pk->soundName = $name;
    $pk->x = $pos->getX();
    $pk->y = $pos->getY();
    $pk->z = $pos->getZ();
    $pk->volume = $volume;
    $pk->pitch = 1;
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $player){
      $player->getNetworkSession()->sendDataPacket($pk);
    }
  }
 
}
?>
