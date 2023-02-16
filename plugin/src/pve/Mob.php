<?php
namespace pve;

use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\math\Vector3;
use pocketmine\item\Item;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\entity\Entity;
use pocketmine\event\Listener;
use Ramsey\Uuid\Uuid;
use pocketmine\world\particle\Particle;
use pocketmine\network\mcpe\protocol\types\ParticleIds;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\network\mcpe\protocol\types\ActorEvent;
use pocketmine\network\mcpe\protocol\AnimateEntityPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\network\mcpe\protocol\types\LevelEvent;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\PlayerSkinPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pve\packet\SpawnParticleEffectPacket;
use pocketmine\network\mcpe\convert\SkinAdapterSingleton;
use pocketmine\network\mcpe\protocol\PlaySoundPacket; 
use pocketmine\entity\Skin;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties as EM;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\network\mcpe\protocol\AdventureSettingsPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\network\mcpe\protocol\UpdateAbilitiesPacket;


use pve\Callback;
use pve\MobManager;
use pve\weapon\Weapon;
use pve\PlayerManager;
use pve\bossbar\BossBar;
use pve\Type;
use pve\item\ItemManager;
use pve\SetBonusManager as SBM;
use pve\dungeon\DungeonManager;

class Mob implements Listener{

  public function __construct($plugin){
    $this->plugin = $plugin;
    //$this->plugin->spawnData = $plugin->spawnData;
    //$this->plugin->fieldData = $plugin->fieldData;
    $this->mobs = [];
    $this->spawnAll();
    $this->cooltime = [];
    $this->log = [];
    $skindata = unserialize(file_get_contents($this->plugin->getDataFolder() . "npcs.dat"));
    $this->Skin = [];
    foreach($skindata as $name => $data){
      $this->Skin[$name] = new Skin($data['id'], $data['data'], '', $data['gname'], $data['gdata']);
    }
  }
  
  
  public function spawnAll(){
    foreach($this->plugin->spawnData as $field => $data){
      foreach($data as $name => $data){
        for($i = 0; $i < $data['amount']; $i++){
          $this->spawn($field, $name, $data['level'], $data['boss']);
        }
      }
    }
  }
  
  public function spawn($field, $name, $lv, $boss){
    $eid = Entity::nextRuntimeId();
    $pos = $this->plugin->spawnData[$field][$name]['pos'];
    $this->uuid[$eid] = UUID::fromString(md5(uniqid(mt_rand(), true)));
    if($boss){
      $x = $pos["x"];
      $z = $pos["z"];
      $boss = true;
      $scale = 2;
    }else{
      $x = $pos["x"]+mt_rand(0,2*3)-3;
      $z = $pos["z"]+mt_rand(0,2*3)-3;
      $boss = false;
      $scale = 1;
    }
    foreach($this->plugin->fieldmanager->getPlayers($field) as $player){
      $this->summon($x, $pos["y"], $z, $name, $lv, $eid, $player, $scale);
    }
    $mob = MobManager::getMob($name);
    $this->mobs[$field][$eid] = ['name' => $name, 'lv' => $lv, 'hp' => $mob->getHp() * $lv,'maxhp' => $mob->getHp() * $lv, 'atk' => $mob->getAtk() * $lv, 'def' => $mob->getDef() * $lv, 'x' => $x, 'y' => $pos['y'], 'z' => $z, 'target' => null, 'boss' => $boss, 'bossbar' => null, '+atk' => 0, '+def' => 0, 'ice' => 0, 'ct' => 0, 'attacker' => []];
    if($boss){
      $this->mobs[$field][$eid]['bossbar'] = new BossBar($eid);
      $this->mobs[$field][$eid]['bossbar']->setTitle($name);
      $players = $this->plugin->fieldmanager->getPlayers($field);
      foreach($players as $player){
        $this->mobs[$field][$eid]['bossbar']->register($player);
      }
      $hh = 1; //+ (0.2 * (count($players)-1));
      if($hh < 1) $hh = 1;
      $this->mobs[$field][$eid]['hp'] *= $hh;
      $this->mobs[$field][$eid]['maxhp'] *= $hh;
    }
  }
  
  public function summon($x, $y, $z, $name, $lv, $eid, $player, $scale = 1){
	$pk = new AddPlayerPacket();
	$pk->uuid = $this->uuid[$eid];
	$pk->username = $name;
	$pk->actorRuntimeId = $eid;
	$pk->position = new Vector3($x, $y, $z);
	$pk->motion = new Vector3(0, 0, 0);
	$pk->yaw = mt_rand(0, 359);
	$pk->pitch = 0;
	//$pk->item = Item::get(0, 0, 0);
  $pk->item = ItemStackWrapper::legacy(ItemStack::null());
  $pk->gameMode = 0;
  $pk2 = UpdateAbilitiesPacket::create(0, 0, $eid, []);
  $pk->abilitiesPacket = $pk2;
  $pk->syncedProperties = new PropertySyncData([1], [1.0]);
  $meta = new EntityMetadataCollection();
  $meta->setByte(EM::ALWAYS_SHOW_NAMETAG, 1);
  $meta->setString(EM::NAMETAG, $name.'§f[§elv.§f'.$lv.']');
  $meta->setLong(EM::LEAD_HOLDER_EID, -1);
  $meta->setFloat(EM::SCALE, 1);
  $meta->setFloat(EM::BOUNDING_BOX_WIDTH, $scale);
  $meta->setFloat(EM::BOUNDING_BOX_HEIGHT, $scale * 2);
	$pk->metadata = $meta->getAll();
	/*[
		Entity::DATA_FLAGS => 
			[
				Entity::DATA_TYPE_LONG, 1 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG ^ 1 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG
			],
		Entity::DATA_NAMETAG => 
			[
				Entity::DATA_TYPE_STRING, $name.'§f[§elv.§f'.$lv.']'
			],
		Entity::DATA_LEAD_HOLDER_EID => 
			[
				Entity::DATA_TYPE_LONG, -1
			],
		Entity::DATA_SCALE => 
			[
				Entity::DATA_TYPE_FLOAT,1
      ],
    Entity::DATA_BOUNDING_BOX_WIDTH => 
			[
				Entity::DATA_TYPE_FLOAT,$scale
      ],
    Entity::DATA_BOUNDING_BOX_HEIGHT => 
			[
				Entity::DATA_TYPE_FLOAT,$scale * 2
			]
	];*/
	$player->getNetworkSession()->sendDataPacket($pk);
  
  if(!isset($this->Skin[$name])) return false;
  $pk2 = new PlayerSkinPacket();
  $pk2->uuid = $this->uuid[$eid];
  $pk2->skin = SkinAdapterSingleton::get()->toSkinData($this->Skin[$name]);
  $player->getNetworkSession()->sendDataPacket($pk2);
  }
  
  public function Move($field, $eid, $count = 0){
     if(!$this->isAlive($field, $eid)) return false;
     if(!isset($this->mobs[$field][$eid]['target'])) return false;
     if(!$this->mobs[$field][$eid]['target']->isOnline()){
       $this->mobs[$field][$eid]['target'] = null;
       return false;
     }
     if($field !== $this->plugin->fieldmanager->getField($this->mobs[$field][$eid]['target'])){
       $this->mobs[$field][$eid]['target'] = null;
       return false;
     }
     if($this->mobs[$field][$eid]['ice']){
       $this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'Move'], [$field, $eid]), 6);
       return false;
     }
     if(!$count) MobManager::getMob($this->mobs[$field][$eid]['name'])->onMove($field, $eid);
     $length = 4;
     $player = $this->mobs[$field][$eid]['target'];
     $mob = $this->mobs[$field][$eid];
     $ppp = $this->plugin->animation->getPos($player);
     $yaw = rad2deg(atan2($ppp->z - $mob['z'], $ppp->x - $mob['x'])) - 90;
     if($yaw < 0){
       $yaw += 360.0;
     }
     $rad = deg2rad($yaw);
     $pk = new MovePlayerPacket();
     $mx = (1 * sin($rad)); //* ($player->getX() > $mob['x']) ? -1 : 1;
     $mz = (1 * cos($rad)); //* ($player->getZ() > $mob['z']) ? -1 : 1;
     $bpos = new Vector3($mob['x'] - $mx, $mob['y']+0.5, $mob['z'] + $mz);
     $pos = new Vector3($mob['x'] - $mx, $mob['y']+1.6, $mob['z'] + $mz);
     $block = $this->plugin->fieldmanager->getLevel()->getBlock($bpos);
     if(!$this->mobs[$field][$eid]['boss']){
       $length = 4;
       if($block->hasEntityCollision()){
        $pos->y += 1;
       }else{
         $bpos2 = new Vector3($mob['x'] - $mx, $mob['y']-0.5, $mob['z'] + $mz);
         if($this->plugin->fieldmanager->getLevel()->getBlock($bpos2)->getId() === 0)
           $pos->y -= 1;
       }
     }
     $pk->position = $pos;
     $pk->actorRuntimeId = $eid;
     $pk->pitch = 0.0;
     $pk->yaw = $yaw;
     $pk->headYaw = $yaw;
     $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $playerd){
      $playerd->getNetworkSession()->sendDataPacket($pk);
    }
    if($pos->distanceSquared($this->plugin->animation->getPos($player)) <= $length){
       $time = microtime(true);
       $num = $time - $this->mobs[$field][$eid]['ct'];
       if($num > 1){
        $this->mobs[$field][$eid]['ct'] = $time;
        $this->mobAttack($eid, $field, $player);
        if(!$this->isAlive($field, $eid)) return false;
      }
    }
    $this->mobs[$field][$eid]['x'] = $pos->getX();
    $this->mobs[$field][$eid]['y'] = $pos->getY()-1.6;
    $this->mobs[$field][$eid]['z'] = $pos->getZ();

    if($this->mobs[$field][$eid]['boss']){

      if($this->mobs[$field][$eid]['hp']/$this->mobs[$field][$eid]['maxhp'] < 0.5){
        MobManager::getMob($this->mobs[$field][$eid]['name'])->move2($field, $eid);
        return false;
      }else{
        MobManager::getMob($this->mobs[$field][$eid]['name'])->move1($field, $eid);
        return false;
      }
      
    }
    
    $this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'Move'], [$field, $eid, 1]), 6);
  }
  
  public function mobAttack($eid, $field, $player, $mag = 1){
    /*$ev = new EntityDamageEvent($player,  EntityDamageEvent::CAUSE_VOID, 0);
    $player->attack($ev);*/
    if(!$this->isAlive($field, $eid)) return false;
    $mobname = $this->mobs[$field][$eid]['name'];
    $class = MobManager::getMob($mobname);
    /*$agi = $this->plugin->playermanager->getAgi($player) - $class->getAgi();
    if($agi < 0) $agi = 0;
    $kaihi = 5*sqrt($agi);
    $han = $this->plugin->playermanager->getHan($player) - $class->getHan();
    if($han < 0) $han = 0;
    $ref = 2*sqrt($han);
    $pos = $this->getPos($field, $eid);
    $dis = MobManager::getMob('shadow')->distance($pos, $player->getPosition());
    if($dis <= 4 and $ref >= mt_rand(0, 100)){
      $player->sendPopup('§a§l反撃！！');
      $this->addSound($player, 'PVE:HANSYA', 0.03);
      $atk = $this->checkAtk($player);
      $this->CustomAttack($atk * 2, $player, $field, $eid);
      return false;
    }elseif($kaihi >= mt_rand(0, 100)){
      $player->sendPopup('§b§l回避！！');
      $this->addSound($player, 'PVE:KAIHI', 0.03);
      $po = $player->getPosition();
      $po->y += 2;
      $players = $this->plugin->fieldmanager->getPlayers($field);
      MobManager::getMob('shadow')->addCustomParticle('PVE:KAIHI', $po, $players);
      return false;
    }*/
    $pk = new ActorEventPacket();
    $pk->actorRuntimeId = $player->getId();
    $pk->eventId = ActorEvent::HURT_ANIMATION;
    $player->getNetworkSession()->sendDataPacket($pk);
    $skills = SkillManager::onDamage();
    foreach ($skills as $skill){
      $skill->onDamage($player, $field, $eid);
    }
    if(!$this->isAlive($field, $eid)) return true;
    $atk =  $this->mobs[$field][$eid]['atk'] +  $this->mobs[$field][$eid]['+atk'];
    if($atk < 0) $atk = 0;
    $atk *= $mag;
    $type = MobManager::getMob($this->mobs[$field][$eid]['name'])->getType();
    $atk = SBM::get(Type::FIRE)->onD($player, $atk);
    $atk = SBM::get($type)->onDamage($player, $atk);
    $item = $player->getInventory()->getItemInHand();
    $tag = $item->getNamedTag(Weapon::TAG_WEAPON);
    $atk *= (1 - $this->plugin->playermanager->getTypeDamageCutMultiple($player, $type));
    /*$skill = 0;
    if(isset($tag)){
      $tt = $tag->getTag(Weapon::TAG_SKILL);
      if(!isset($tt)){
        $player->sendMessage('その武器は使用できません。');
        $name = $player->getName();
        var_dump($name);
        return false;
      }
      $skill = $tag->getTag(Weapon::TAG_SKILL)->getValue();
    }
    if($skill != 0){
      $atk = SpecialSkillManager::getSkill($skill)->onDamage($player, $atk);
    }*/
    if($atk <= 0) return false;
    $this->plugin->playermanager->Attack($player, $atk);
  }
  
  public function isAlive($field, $eid){
    $result = false;
    if(!isset($this->mobs[$field])){
      return false;
    }
    if(isset($this->mobs[$field][$eid])) $result = true;
    return $result;
  }
  
  public function kill($eid, $field, $player){
    $lv = $this->mobs[$field][$eid]['lv'];
    $name = $this->mobs[$field][$eid]['name'];
    if($this->log[$player->getName()]) $player->sendMessage($this->mobs[$field][$eid]['name'].'§7を倒した');
    $pos = new Vector3($this->mobs[$field][$eid]['x'],  $this->mobs[$field][$eid]['y'],  $this->mobs[$field][$eid]['z']);
    $mob = MobManager::getMob($name);
    $quests = QuestManager::getAll();
    $exp = MobManager::getMob($name)->getExp() * $lv;
    if(DungeonManager::isDungeon($player))
      DungeonManager::getDungeonByPlayer($player)->onKill($field, $eid);
    if($this->mobs[$field][$eid]['boss']){
      $this->mobs[$field][$eid]['bossbar']->hide();
      $players = $this->plugin->fieldmanager->getPlayers($field);
      foreach($players as $p){
          foreach($quests as $quest){
            $quest->onKill($p, $name);
          }
          $this->plugin->playermanager->addExp($p, $exp, $this->log[$player->getName()]);
          $mob->kill($p);
          $items = [];//$mob->getDrop($lv);
          /*$ds = ['sword', 'armor', 'orb'];
          foreach($ds as $g){
            $items = array_merge($items, $mob->getBossRecipe($g));
          }*/
          $items = array_merge($items, $mob->getBossDrop($p));
          if(DungeonManager::isDungeon($p)){
            DungeonManager::getDungeonByPlayer($p)->addGift($field, $p, $items);
          }else{
            foreach($items as $item){
              if($item->getCount() === 0) continue;
              $p->getInventory()->addItem($item);
              $p->sendMessage($item->getName().'§fx'.$item->getCount().'§7を手に入れた');
            }
          }
          if($this->plugin->guild->isJoin($p)){
            $guild = $this->plugin->guild->getGuild($p);
            $this->plugin->guild->addExp($guild, 100);
          }
      }
      if(!DungeonManager::isDungeon($player))
      $this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'spawn'], [$field, $name, $lv, $this->mobs[$field][$eid]['boss']]), 20 * 30);
    }else{
      $items = [];//$mob->getDrop($lv);
      $items = array_merge($items, $mob->getZakoDrop($player));
      $mob->kill($player);
      $message = '';
      if(DungeonManager::isDungeon($player)){
        DungeonManager::getDungeonByPlayer($player)->addGift($field, $player, $items);
      }else{
        foreach($items as $item){
          if($item->getCount() === 0) continue;
          $player->getInventory()->addItem($item);
          if($this->log[$player->getName()]) $player->sendMessage($item->getName().'§fx'.$item->getCount().'§7を手に入れた');
        }
      }
      if(!DungeonManager::isDungeon($player))
      $this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'spawn'], [$field, $name, $lv, $this->mobs[$field][$eid]['boss']]), 20 * 5);
      foreach($quests as $quest){
        $quest->onKill($player, $name);
      }
      if($this->plugin->guild->isJoin($player)){
        $guild = $this->plugin->guild->getGuild($player);
        $this->plugin->guild->addExp($guild, 1);
      }
      $this->plugin->playermanager->addExp($player, $exp, $this->log[$player->getName()]);
    }
    $money = $mob->getMoney($lv);
    $this->plugin->playermanager->addMoney($player, $money, $this->log[$player->getName()]);
    $pk = new LevelSoundEventPacket();
    $pk->sound = LevelSoundEvent::EXPLODE;
    $pk->position = $pos;
    
    $pk2 = new LevelEventPacket;
    $pk2->eventId = LevelEvent::ADD_PARTICLE_MASK | ParticleIds::DRAGON_DESTROY_BLOCK & 0xFFF;
    $pk2->position = $pos;
    $pk2->eventData = 0;

    $pk3 = new SpawnParticleEffectPacket();
    $pk3->position = new Vector3($pos->x, $pos->y+1, $pos->z);
    $pk3->particleName = "PVE:ONKILL";
    $pk3->molangVariablesJson = null;
    unset($this->mobs[$field][$eid]);
    unset($this->uuid[$eid]);
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $player){
      $player->getNetworkSession()->sendDataPacket($pk);
      $player->getNetworkSession()->sendDataPacket($pk2);
      $player->getNetworkSession()->sendDataPacket($pk3);
      $this->remove($eid, $player);
    }
    
  }
  
  public function remove($eid, $player){
    $pk = new RemoveActorPacket();
    $pk->actorUniqueId = $eid;
    if(!$player->isOnline()) return false;
    $player->getNetworkSession()->sendDataPacket($pk);
  }
  
  public function onInteract(PlayerInteractEvent $event){
    $player = $event->getPlayer();
    /*if($event->getBlock()->getId === 130){
      $this->sendFieldForm($player);
    }*/
    //$this->summon($player->getX(), $player->getY(), $player->getZ(), $player);
  }
  
  public function onReceive(DataPacketReceiveEvent $event){
    $pk = $event->getPacket();
    $player = $event->getOrigin()->getPlayer();
    if($pk instanceof InventoryTransactionPacket && $pk->trData instanceof UseItemOnEntityTransactionData){
      $eid = $pk->trData->getActorRuntimeId();
      if(isset($this->mobs[$this->plugin->fieldmanager->getField($player)][$eid])){
	      $this->attack($player, $eid);
      }    
    }
  }
  
  public function attack($player, $eid){
    $name = $player->getName();
    if(!array_key_exists($name, $this->cooltime))
      $this->cooltime[$name] = 0;
    $time = microtime(true);
    $num = $time - $this->cooltime[$name];
    $tansyuku = $this->plugin->playermanager->getAtkSpeed($player);
    if($num < 0.5 / $tansyuku){
      return false;
    }
    $this->cooltime[$name] = $time;
    $field = $this->plugin->fieldmanager->getField($player);
    if(!$this->isAlive($field, $eid)) return false;
    $skills = SkillManager::onAttack();
    foreach ($skills as $skill){
      $skill->onAttack($player, $field, $eid);
    }
    if(!$this->isAlive($field, $eid)) return false;
    $atk = $this->checkAtk($player);
    if(!isset($this->mobs[$field][$eid])) return false;
    $def = $this->mobs[$field][$eid]['def'] + $this->mobs[$field][$eid]['+def'];
    if($def < 0) $def = 0;
    $atk = (int)($atk * (160 / (160 + $def)));
    $item = $player->getInventory()->getItemInHand();
    $tag = $item->getNamedTag(Weapon::TAG_WEAPON)->getTag(Weapon::TAG_WEAPON);
    $type = Type::NONE;
    $cm = '';
    if(isset($tag))
      $type = $tag->getTag(Weapon::TAG_PROPERTY)->getValue();
    $atk = SBM::get($type)->onAttack($player, $atk);
    $crit = $this->plugin->playermanager->getCrit($player);
    $crit += 3*sqrt($this->plugin->playermanager->getSyu($player));
    if($crit >= mt_rand(0,100)){
      $atk *= $this->plugin->playermanager->getCritPer($player);
      if($this->log[$player->getName()]) $player->sendMessage('§l§dL-BAS§f>>会心の一撃です！');
      $cm = '§d†§f';
      $this->addSound($player, 'PVE:CRIT', 0.05);
      $po = $this->getPos($field, $eid);
      $po->y+=2;
      $players = $this->plugin->fieldmanager->getPlayers($field);
      MobManager::getMob('shadow')->addCustomParticle('PVE:CRIT', $po, $players);
    }
    if(!$this->isAlive($field, $eid)) return false;
    if($atk < 0) $atk = 0;
    $atk *= $this->plugin->playermanager->getFinalDamageMultiple($player);
    $atk *= $this->plugin->playermanager->getTypeDamageMultiple($player, $type);
    $mobdata = $this->mobs[$field][$eid];
    $pos = new Vector3($mobdata['x']+mt_rand(0, 2)-1, $mobdata['y']+mt_rand(0, 2)-1, $mobdata['z']+mt_rand(0, 2)-1);
    if(Type::isWeakness(MobManager::getMob($this->mobs[$field][$eid]['name'])->getType(), $type)){
      $atk *= 1.5;
      $this->addFloatingText($player, '§l'.$cm.Type::getColor($type).$atk.$cm, $pos);
      $pk = new LevelSoundEventPacket();
      $pk->sound = LevelSoundEvent::BOW;
      $pk->position = $player->getPosition();
      $player->getNetworkSession()->sendDataPacket($pk);
    }else{
      $this->addFloatingText($player, $cm.Type::getColor($type).$atk.$cm, $pos);
    }
    
    $mobname = $this->mobs[$field][$eid]['name'];
    $class = MobManager::getMob($mobname);
    /*$agi = $class->getAgi() - $this->plugin->playermanager->getAgi($player);
    if($agi < 0) $agi = 0;
    $kaihi = 5*sqrt($agi);
    $han = $class->getHan() - $this->plugin->playermanager->getHan($player);
    if($han < 0) $han = 0;
    $ref = 2*sqrt($han);
    $pos = $this->getPos($field, $eid);
    $dis = $class->distance($pos, $player->getPosition());
    if($dis <= 4 and $ref >= mt_rand(1, 100)){
      $a = $this->mobs[$field][$eid]['atk'];
      $this->mobAttack($eid, $field, $player, $atk / $a);
      $this->addSound($player, 'PVE:HANSYA', 0.03);
      return false;
    }elseif($kaihi >= mt_rand(1, 100)){
      $this->addSound($player, 'PVE:KAIHI', 0.03);
      $pos->y += 2;
      $players = $this->plugin->fieldmanager->getPlayers($field);
      MobManager::getMob('shadow')->addCustomParticle('PVE:KAIHI', $pos, $players);
      return false;
    }*/
    $this->mobs[$field][$eid]['hp'] -= $atk;
    $this->plugin->playermanager->setDamageData($player, $atk);
    if($this->mobs[$field][$eid]['boss']){
       if(!in_array($player->getName(), $this->mobs[$field][$eid]['attacker']))
         $this->mobs[$field][$eid]['attacker'][] = $player->getName();
       $this->mobs[$field][$eid]['bossbar']->setPercentage($this->mobs[$field][$eid]['hp']/$this->mobs[$field][$eid]['maxhp']);
      if(($this->mobs[$field][$eid]['hp']+$atk)/$this->mobs[$field][$eid]['maxhp'] > 0.5 and ($this->mobs[$field][$eid]['hp'])/$this->mobs[$field][$eid]['maxhp'] <= 0.5)
        MobManager::getMob($this->mobs[$field][$eid]['name'])->movehalf($field, $eid);
      if(($this->mobs[$field][$eid]['hp']+$atk)/$this->mobs[$field][$eid]['maxhp'] > 0.25 and ($this->mobs[$field][$eid]['hp'])/$this->mobs[$field][$eid]['maxhp'] <= 0.25)
        MobManager::getMob($this->mobs[$field][$eid]['name'])->movequarter($field, $eid);
    }
    
    $pk = new ActorEventPacket();
    $pk->actorRuntimeId = $eid;
    if($this->mobs[$field][$eid]['hp'] <= 0){
      $pk->eventId = ActorEvent::DEATH_ANIMATION;
      $this->kill($eid, $field, $player);
    }else{
      $pk->eventId = ActorEvent::HURT_ANIMATION;
      if(!isset($this->mobs[$field][$eid]['target'])){
        $this->mobs[$field][$eid]['target'] = $player;
        $this->Move($field, $eid);
      }else{
        if(mt_rand(0,2) === 0){
          $this->mobs[$field][$eid]['target'] = $player;
        }
      }
    }
    $player->getNetworkSession()->sendDataPacket($pk);
    
  }
  
  public function FieldKill($player, $field){
    
    if(!isset($this->mobs[$field])) {
      return true;
    }
    
    foreach($this->mobs[$field] as $eid => $data){
      $this->remove($eid, $player);
      if($this->mobs[$field][$eid]['boss']){
        $this->mobs[$field][$eid]['bossbar']->unregister($player);
      }
    }
  }
  
  public function FieldSpawn($player, $field){
    if(!isset($this->mobs[$field])) {
      return true;
    }
    foreach($this->mobs[$field] as $eid => $data){
      $scale = 1;
      if($this->mobs[$field][$eid]['boss']){
        $this->mobs[$field][$eid]['bossbar']->register($player);
        $scale = 2;
      }
      $this->summon($data["x"], $data["y"], $data["z"], $data['name'], $data['lv'], $eid, $player, $scale);
    }
  }
  
  public function addFloatingText($player, $text, $pos){
    $eid = Entity::nextRuntimeId();
    $pk = new AddPlayerPacket();
    $pk->uuid = UUID::fromString(md5(uniqid(mt_rand(), true)));
    $pk->username = ''.$text;
    $pk->actorRuntimeId = $eid;
    $pk->position = $pos; //TODO: check offset
    $pk->item = ItemStackWrapper::legacy(ItemStack::null());
    $pk->gameMode = 0; 
    $pk2 = UpdateAbilitiesPacket::create(0, 0, $eid, []);
    $pk->abilitiesPacket = $pk2;
    $pk->syncedProperties = new PropertySyncData([1], [1.0]);
    $meta = new EntityMetadataCollection();
    $meta->setByte(EM::ALWAYS_SHOW_NAMETAG, 1);
    $meta->setString(EM::NAMETAG, $text);
    $meta->setLong(EM::LEAD_HOLDER_EID, -1);
    $meta->setFloat(EM::SCALE, 0.01);
    $meta->setGenericFlag(EntityMetadataFlags::INVISIBLE, 0);
    $meta->setGenericFlag(EntityMetadataFlags::ALWAYS_SHOW_NAMETAG, 1);
    $pk->metadata = $meta->getAll();
    $player->getNetworkSession()->sendDataPacket($pk);
    $this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'remove'], [$eid, $player]), 20*2);
  }
  
  public function getSkins(){
    return $this->Skin;
  }

  public function getPos($field, $eid){
    if(!$this->isAlive($field, $eid)) return new Vector3(0,0,0);
    $mob = $this->mobs[$field][$eid];
    return new Vector3($mob['x'], $mob['y'], $mob['z']);
  }
  
  public function teleport($field, $eid, $pos){
    if(!$this->isAlive($field, $eid)) return false;
    $pk = new MovePlayerPacket();
    $pos1 = new Vector3($pos->getX(), $pos->getY()+1.6, $pos->getZ());
    $pk->position = $pos1;
    $pk->actorRuntimeId = $eid;
    $pk->pitch = 0.0;
    $pk->yaw = 0;
    $pk->headYaw = 0;
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $playerd){
     $playerd->getNetworkSession()->sendDataPacket($pk);
    }
    $this->mobs[$field][$eid]['x'] = $pos1->getX();
    $this->mobs[$field][$eid]['y'] = $pos1->getY()-1.6;
    $this->mobs[$field][$eid]['z'] = $pos1->getZ();
  }

  public function getTarget($field, $eid){
    if(!isset($this->mobs[$field][$eid]['target'])) return false;
    return $this->mobs[$field][$eid]['target'];
  }

  public function addAtk($field, $eid, $amount, $time = 10){
    if(!$this->isAlive($field, $eid)) return false;
    $this->mobs[$field][$eid]['+atk'] += $amount;
    $this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'setAtk'], [$field, $eid, -$amount]), 20*$time);
  }

  public function setAtk($field, $eid, $amount){
    if(!$this->isAlive($field, $eid)) return false;
    $this->mobs[$field][$eid]['+atk'] += $amount;
  }

  public function addDef($field, $eid, $amount, $time = 10){
    if(!$this->isAlive($field, $eid)) return false;
    $this->mobs[$field][$eid]['+def'] += $amount;
    $this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'setDef'], [$field, $eid, -$amount]), 20*$time);
  }

  public function setDef($field, $eid, $amount){
    if(!$this->isAlive($field, $eid)) return false;
    $this->mobs[$field][$eid]['+def'] += $amount;
  }

  public function Bleed($player, $field, $eid, $count, $type = null, $atk = null){
    if($count === 0) return false;
    if(!isset($atk)) $atk = $this->checkAtk($player, $eid);
    if(!isset($type)) $type = Type::NONE;
    $this->CustomAttack($atk, $player, $field, $eid, $type);
    $this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'Bleed'], [$player, $field, $eid, --$count, $type, $atk]), 20);
  }

  public function checkAtk($player){
    $field = $this->plugin->fieldmanager->getField($player);
    $item = $player->getInventory()->getItemInHand();
    $style = $this->plugin->playermanager->getStyle($player);
    $atk = $this->plugin->playermanager->getAtk($player);
    if($style === 'sword'){
      /*$tag = $item->getNamedTag(Weapon::TAG_WEAPON)->getTag(Weapon::TAG_WEAPON);
      if(isset($tag)){
        $watk = $tag->getTag(Weapon::TAG_ATK)->getValue();
        $level = $tag->getTag(Weapon::TAG_LEVEL)->getValue();
        $sharp = $tag->getTag(Weapon::TAG_SHARP)->getValue();
        $sharp += $this->plugin->playermanager->getSharp($player);
        $watk /= 3;
        //$watk *= 1 + ($level/10);
        $watk *= $sharp / 300;
        $atk += $watk;
      }*/
      $atk += $atk * $this->plugin->playermanager->getPow($player) / 100;
    }
    return round($atk);
  }

  public function Freeze($field, $eid, $time, $true){
    if(!$this->isAlive($field, $eid)) return false;
    $this->mobs[$field][$eid]['ice'] = $true;
    $lv = $this->mobs[$field][$eid]['lv'];
    if(!$time) return false;
    $this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'Freeze'], [$field, $eid, 0, 0]), 20*$time/$lv);
  }

  public function CustomAttack($atk, $player, $field, $eid, $type = null){
    if($this->plugin->fieldmanager->getField($player) !== $field) return false;
    if(!$this->isAlive($field, $eid)) return false;
    $item = $player->getInventory()->getItemInHand();
    $tag = $item->getNamedTag(Weapon::TAG_WEAPON)->getTag(Weapon::TAG_WEAPON);
    if(!isset($type)){
      $type = Type::NONE;
      if(isset($tag))
        $type = $tag->getTag(Weapon::TAG_PROPERTY)->getValue();
    }
    $atk = SBM::get($type)->onAttack($player, $atk);
    $crit = $this->plugin->playermanager->getCrit($player);
    $crit += 3*sqrt($this->plugin->playermanager->getSyu($player));
    $cm = '';
    if($crit >= mt_rand(0,100)){
      $atk *= $this->plugin->playermanager->getCritPer($player);
      $cm = '§d†§f';
      if($this->log[$player->getName()]) $player->sendMessage('§l§dL-BAS§f>>会心の一撃です！');
      $this->addSound($player, 'PVE:CRIT', 0.05);
      $po = $this->getPos($field, $eid);
      $po->y+=2;
      $players = $this->plugin->fieldmanager->getPlayers($field);
      MobManager::getMob('shadow')->addCustomParticle('PVE:CRIT', $po, $players);
    }
    if(!isset($this->mobs[$field][$eid])) return false;
    $def = $this->mobs[$field][$eid]['def'] + $this->mobs[$field][$eid]['+def'];
    if($def < 0) $def = 0;
    $atk = (int)($atk * (160 / (160 + $def)));
    $atk *= $this->plugin->playermanager->getFinalDamageMultiple($player);
    $atk *= $this->plugin->playermanager->getTypeDamageMultiple($player, $type);
    $mobdata = $this->mobs[$field][$eid];
    $pos = new Vector3($mobdata['x']+mt_rand(0, 2)-1, $mobdata['y']+mt_rand(0, 2)-1, $mobdata['z']+mt_rand(0, 2)-1);
    if(Type::isWeakness(MobManager::getMob($this->mobs[$field][$eid]['name'])->getType(), $type)){
      $atk *= 1.5;
      $this->addFloatingText($player, $cm.'§l'.Type::getColor($type).$atk.$cm, $pos);
      $pk = new LevelSoundEventPacket();
      $pk->sound = LevelSoundEvent::BOW;
      $pk->position = $player->getPosition();
      $player->getNetworkSession()->sendDataPacket($pk);
    }else{
      $this->addFloatingText($player, $cm.Type::getColor($type).$atk.$cm, $pos);
    }
    if($atk < 0) $atk = 0;
    $mobname = $this->mobs[$field][$eid]['name'];
    $class = MobManager::getMob($mobname);
    /*$agi = $class->getAgi() - $this->plugin->playermanager->getAgi($player);
    if($agi < 0) $agi = 0;
    $kaihi = 5*sqrt($agi);
    $han = $class->getHan() - $this->plugin->playermanager->getHan($player);
    if($han < 0) $han = 0;
    $ref = 2*sqrt($han);
    $pos = $this->getPos($field, $eid);
    $dis = $class->distance($pos, $player->getPosition());
    if($dis <= 4 and $ref >= mt_rand(0, 100)){
      $a = $this->mobs[$field][$eid]['atk'];
      $this->mobAttack($eid, $field, $player, $atk / $a);
      $this->addSound($player, 'PVE:HANSYA', 0.03);
      return false;
    }elseif($kaihi >= mt_rand(0, 100)){
      $this->addSound($player, 'PVE:KAIHI', 0.03);
      $pos->y += 2;
      $players = $this->plugin->fieldmanager->getPlayers($field);
      MobManager::getMob('shadow')->addCustomParticle('PVE:KAIHI', $pos, $players);
      return false;
    }*/
    $this->mobs[$field][$eid]['hp'] -= $atk;
    $this->plugin->playermanager->setDamageData($player, $atk);
    if($this->mobs[$field][$eid]['boss']){
      if(!in_array($player->getName(), $this->mobs[$field][$eid]['attacker']))
         $this->mobs[$field][$eid]['attacker'][] = $player->getName();
       $this->mobs[$field][$eid]['bossbar']->setPercentage($this->mobs[$field][$eid]['hp']/$this->mobs[$field][$eid]['maxhp']);
       if(($this->mobs[$field][$eid]['hp']+$atk)/$this->mobs[$field][$eid]['maxhp'] > 0.5 and ($this->mobs[$field][$eid]['hp'])/$this->mobs[$field][$eid]['maxhp'] <= 0.5)
        MobManager::getMob($this->mobs[$field][$eid]['name'])->movehalf($field, $eid);
       if(($this->mobs[$field][$eid]['hp']+$atk)/$this->mobs[$field][$eid]['maxhp'] > 0.25 and ($this->mobs[$field][$eid]['hp'])/$this->mobs[$field][$eid]['maxhp'] <= 0.25)
        MobManager::getMob($this->mobs[$field][$eid]['name'])->movequarter($field, $eid);
    }
    $pk = new ActorEventPacket();
    $pk->actorRuntimeId = $eid;
    if($this->mobs[$field][$eid]['hp'] <= 0){
      $pk->eventId = ActorEvent::DEATH_ANIMATION;
      $this->kill($eid, $field, $player);
    }else{
      $pk->eventId = ActorEvent::HURT_ANIMATION;
      if(!isset($this->mobs[$field][$eid]['target'])){
        $this->mobs[$field][$eid]['target'] = $player;
        $this->Move($field, $eid);
      }else{
        if(mt_rand(0,2) === 0){
          $this->mobs[$field][$eid]['target'] = $player;
        }
      }
    }
    $player->getNetworkSession()->sendDataPacket($pk);
  }

  public function getType($item){
    $tag = $item->getNamedTag(Weapon::TAG_WEAPON)->getTag(Weapon::TAG_WEAPON);
    $type = Type::NONE;
    if(isset($tag))
      $type = $tag->getTag(Weapon::TAG_PROPERTY)->getValue();
    return $type;
  }

  public function setLog($player, $bool){
    $this->log[$player->getName()] = $bool;
  }

  public function onJoin(PlayerJoinEvent $event){
    $player = $event->getPlayer();
    $this->setLog($player, true);
  }

  public function CustomSpawn($field, $name, $lv, $boss, $pos, $hp = 1){
    $eid = Entity::nextRuntimeId();
    $this->uuid[$eid] = UUID::fromString(md5(uniqid(mt_rand(), true)));
    if($boss){
      $x = $pos["x"];
      $z = $pos["z"];
      $boss = true;
      $scale = 2;
    }else{
      $x = $pos["x"]+mt_rand(0,2*3)-3;
      $z = $pos["z"]+mt_rand(0,2*3)-3;
      $boss = false;
      $scale = 1;
    }
    foreach($this->plugin->fieldmanager->getPlayers($field) as $player){
      $this->summon($x, $pos["y"], $z, $name, $lv, $eid, $player, $scale);
    }
    $mob = MobManager::getMob($name);
    $this->mobs[$field][$eid] = ['name' => $name, 'lv' => $lv, 'hp' => $mob->getHp() * (0.9 + $lv/10) * $hp ,'maxhp' => $mob->getHp() * $lv, 'atk' => $mob->getAtk() * $lv, 'def' => $mob->getDef(), 'x' => $x, 'y' => $pos['y'], 'z' => $z, 'target' => null, 'boss' => $boss, 'bossbar' => null, '+atk' => 0, '+def' => 0, 'ice' => 0, 'ct' => 0, 'attacker' => []];
    if($boss){
      $this->mobs[$field][$eid]['bossbar'] = new BossBar($eid);
      $this->mobs[$field][$eid]['bossbar']->setTitle($name);
      $this->mobs[$field][$eid]['bossbar']->setPercentage($this->mobs[$field][$eid]['hp']/$this->mobs[$field][$eid]['maxhp']);
      $players = $this->plugin->fieldmanager->getPlayers($field);
      foreach($players as $player){
        $this->mobs[$field][$eid]['bossbar']->register($player);
      }
      $hh = 1 + (0.4 * (count($players)-1));
      if($hh < 1) $hh = 1;
      $this->mobs[$field][$eid]['hp'] *= $hh;
      $this->mobs[$field][$eid]['maxhp'] *= $hh;
    }
    return $eid;
  }

  public function setTarget($field, $eid, $player){
    if(!$this->isAlive($field, $eid)) return false;
    if(!isset($this->mobs[$field][$eid]['target'])){
      $this->mobs[$field][$eid]['target'] = $player;
      $this->Move($field, $eid);
    }else{
      if(mt_rand(0,2) === 0){
        $this->mobs[$field][$eid]['target'] = $player;
      }
    }
  }
  
  public function addSound($player, $name, $volume = 1){
			$player->getNetworkSession()->sendDataPacket($this->makeSound($player, $name, $volume));
  }

  public function makeSound($player, $name, $volume = 1){
      $pk = new PlaySoundPacket();
			$pk->soundName = $name;
      $pos = $player->getPosition();
			$pk->x = $pos->getX();
			$pk->y = $pos->getY();
			$pk->z = $pos->getZ();
			$pk->volume = $volume;
			$pk->pitch = 1;
			return $pk;
  }
  
}
?>
