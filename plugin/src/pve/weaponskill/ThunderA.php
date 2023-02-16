<?php
namespace pve\weaponskill;

use pocketmine\item\Item;
use pocketmine\utils\UUID;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\math\Vector3;
use pve\Callback;

use pocketmine\level\particle\Particle;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;


class ThunderA extends WeaponSkill {
	
	const NAME = "§c§l壊雷";
	const ID = 7;
  const ITEM_ID = 264;
  const CT = 45;
	
  const DESCRIPTION = 'あたりにすさまじい雷が降り注いで敵を攻撃する';


  public function Interact($weapon, $player){
      $name = $player->getName();
      if(!array_key_exists($name, $this->data))
        $this->data[$name] = 0;
      $time = microtime(true);
      $num = $time - $this->data[$name];
      if($num > self::CT){
        $this->data[$name] = $time;
        $this->Imposition($player);
        $this->send($player);
      }
  }

  public function Imposition($player){
    $field = $this->plugin->fieldmanager->getField($player);
    $pos = $player->getPosition();
    $poss = $this->circle($pos, 10, 10);
    //$posd = array_chunk($poss, count($poss)/8);
    //foreach($posd as $p)
      //$this->setColors($p, $this->plugin->fieldmanager->level, 4);
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $p){
      $p->removeEffect(2);
      $amount = 500;
      if($p === $player) $amount *= 2;
      $this->plugin->playermanager->addAtk($p, $amount, 20);
      $this->plugin->playermanager->addDef($p, $amount, 20);
      $this->addCustomParticle("PVE:STATUSUP", $p->getPosition(), $players);
    }
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'ActionTask'], [$field, $poss, $pos, $player, 0]), 1);
  }

  public function ActionTask($field, $poss, $pos, $player, $count = 0){
    if($count > 5) return false;
    $this->addParticle($field, $pos, Particle::TYPE_EXPLODE, 0, 7);
    $this->addActors($field, $poss, "minecraft:lightning_bolt", 7);
    $this->addSound($field, $pos, LevelSoundEventPacket::SOUND_EXPLODE);
    if($field == 'spawn')
      return false;
    if(!isset($this->plugin->mob->mobs[$field])) return false;
    $mobs = $this->plugin->mob->mobs[$field];
    
    foreach($mobs as $eid => $data){
      $mpos = new Vector3($data['x'], $data['y'], $data['z']);
      if($this->distance($pos, $mpos) < 20){
        $atk = $this->plugin->mob->checkAtk($player, $eid);
        $this->plugin->mob->CustomAttack($atk * 5, $player, $field, $eid);
        $player->sendMessage('§e§lHIT!!');
      }
    }
    $count ++;
    $this->plugin->getScheduler()->scheduleDelayedTask(
        new Callback([$this, 'ActionTask'], [$field, $poss, $pos, $player, $count]), 20);
  }
}