<?php
namespace pve\mobs;

use pocketmine\level\particle\Particle;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\entity\EntityIds;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\level\Position;
use pocketmine\math\Vector3;

use pve\Callback;
use pve\Type;

class LilacA extends Mobs {

  const NAME = '§6§lLilac';
  const HP = 360000;
  const ATK = 508;
  const DEF = 160;
  const EXP = 1000;
  const DROPS = [23 => 2764, 24 => 364];

  const TYPE = TYPE::THUNDER;

  public function move1($field, $eid){
    switch(mt_rand(0,2)){
      case 0:
        $this->moved4($field, $eid);
        break;
      case 1:
        $this->moved2($field, $eid);
        break;
      case 2:
        $this->moved3($field, $eid);
        break;
    }
  }

  public function move2($field, $eid){
    switch(mt_rand(0,3)){
      case 0:
        $this->moved1($field, $eid);
        break;
      case 1:
        $this->moved5($field, $eid);
        break;
      case 2:
        $this->moved4($field, $eid);
        break;
      case 3:
        $this->moved6($field, $eid);
    }
  }

  public function moved1($field, $eid){
    $skillname = '§e雷歩';
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $player) $this->send($player, $skillname);
    $pos = $this->plugin->mob->getPos($field, $eid);
    $target = $this->plugin->mob->getTarget($field, $eid);
    //$this->tickmove1($pos, $target->getPosition(), $this->plugin->fieldmanager->level, $field, $eid);
    $poss = $this->tuibi($pos, $target->getPosition());
    $posd = array_chunk($poss, ceil(count($poss)/12));
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 5, 40);
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove1'], [$field, $eid, $poss[count($poss)-1]]), 10);
  }

  /*public function tickmove1($pos1, $pos2, $level, $field, $eid){
    $this->setColor($pos1, $level, 5);
    $yaw = rad2deg(atan2($pos2->getZ() - $pos1->getZ(), $pos2->getX() - $pos1->getX())) - 90;
    if($yaw < 0) $yaw += 360.0;
    $rad = deg2rad($yaw);
    $mx = (1 * sin($rad));
    $mz = (1 * cos($rad));
    $pos = new Vector3($pos1->getX() - $mx, $pos1->getY(), $pos1->getZ() + $mz);
    if($this->distance($pos, $pos2) <= 1){
      $this->plugin->getScheduler()->scheduleDelayedTask(
        new Callback([$this, 'attackmove1'], [$field, $eid, $pos]), 20);
    }else{
      $this->plugin->getScheduler()->scheduleDelayedTask(
        new Callback([$this, 'tickmove1'], [$pos, $pos2, $level, $field, $eid]), 1);
    }
  }*/

  public function attackmove1($field, $eid, $pos){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addParticle($field, $pos, Particle::TYPE_REDSTONE, 2, 7);
    $this->addActor($field, $pos, "minecraft:lightning_bolt");
    $this->addSound($field, $pos, LevelSoundEventPacket::SOUND_EXPLODE); 
    $this->plugin->mob->teleport($field, $eid, $pos);
    foreach($players as $player){
      if($pos->distance($player->getPosition()) <=3)
        $this->plugin->mob->mobAttack($eid, $field, $player, 2);
    }
    $this->plugin->mob->Move($field, $eid);
  }

  public function moved2($field, $eid){
    $skillname = '§e轟雷';
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $player) $this->send($player, $skillname);
    $pos = $this->plugin->mob->getPos($field, $eid);
    $poss = $this->circle($pos, 5, 5);
    $posd = array_chunk($poss, count($poss)/12);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 5);
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove2'], [$field, $eid, $poss, $pos, 2]), 20);
  }

  public function attackmove2($field, $eid, $poss, $pos){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addParticle($field, $pos, Particle::TYPE_EXPLODE, 0, 7);
    $this->addActors($field, $poss, "minecraft:lightning_bolt", 7);
    $this->addSound($field, $pos, LevelSoundEventPacket::SOUND_EXPLODE); 
    foreach($players as $player){
      if($pos->distance($player->getPosition()) <= 5)
        $this->plugin->mob->mobAttack($eid, $field, $player, 2);
    }
    $this->plugin->mob->Move($field, $eid);
  }

  public function moved3($field, $eid){
    $skillname = '§e雷砲';
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $player) $this->send($player, $skillname);
    $pos = $this->plugin->mob->getPos($field, $eid);
    $target = $this->plugin->mob->getTarget($field, $eid);
    $x = $target->x - $pos->x;
    $yaw = atan(($target->z - $pos->z)/$x);
    if($x < 0) $yaw += M_PI;
    $poss = $this->line($pos, $yaw, 10);
    $posd = array_chunk($poss, count($poss)/12);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 5, 40);
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove3'], [$field, $eid, $poss, $pos, 3]), 15);
  }

  public function attackmove3($field, $eid, $poss, $pos){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addParticle($field, $pos, Particle::TYPE_EXPLODE, 0, 7);
    $this->addActors($field, $poss, "minecraft:lightning_bolt", 7);
    $this->addSound($field, $pos, LevelSoundEventPacket::SOUND_EXPLODE); 
    foreach($players as $player){
      $block = $this->plugin->fieldmanager->level->getBlock($player->down());
      if($block->getId() === 0) $block = $this->plugin->fieldmanager->level->getBlock(new Vector3($player->x, $player->y-2, $player->z));
      if($block->getId() === 35 && $block->getDamage() === 5)
        $this->plugin->mob->mobAttack($eid, $field, $player, 2);
    }
    $this->plugin->mob->Move($field, $eid);
  }

  public function moved4($field, $eid){
    $skillname = '§c§l滅雷';
    $poss = [];
    $players = $this->plugin->fieldmanager->getPlayers($field);
    if(!isset($players)) return false;
    $pos = $this->plugin->mob->getPos($field, $eid);
    foreach($players as $player){
      $this->send($player, $skillname);
      $poss = array_merge($poss, $this->circle(new Vector3($player->x, 3, $player->z), 4, 4));
    }
    $posd = array_chunk($poss, count($poss)/12);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 5, 40);
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove4'], [$field, $eid, $poss[count($poss)-1], $poss]), 20);
  }

  public function attackmove4($field, $eid, $pos, $poss){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addParticle($field, $pos, Particle::TYPE_EXPLODE, 0, 7);
    $this->addActors($field, $poss, "minecraft:lightning_bolt", 7);
    $this->addSound($field, $pos, LevelSoundEventPacket::SOUND_EXPLODE); 
    foreach($players as $player){
        if($this->isHit($player))
          $this->plugin->mob->mobAttack($eid, $field, $player, 5);
    }
    $this->plugin->mob->Move($field, $eid);
  }

  public function moved5($field, $eid){
    $skillname = '§c§l壊雷';
    $poss = [];
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $player) $this->send($player, $skillname);
    $pos = $this->plugin->mob->getPos($field, $eid);
    $yaw = mt_rand(0, M_PI*2);
    for($i = 0; $i < 10; $i++){
      $poss = array_merge($poss, $this->line($pos, $yaw, 15));
      $yaw += M_PI/5;
    }
    $posd = array_chunk($poss, count($poss)/40);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, mt_rand(4, 5), 40);
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove5'], [$field, $eid, $pos, $poss]), 20);
  }

  public function attackmove5($field, $eid, $pos, $poss){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addParticle($field, $pos, Particle::TYPE_EXPLODE, 0, 7);
    $this->addActors($field, $poss, "minecraft:lightning_bolt", 7);
    $this->addSound($field, $pos, LevelSoundEventPacket::SOUND_EXPLODE); 
    foreach($players as $player){
        if($this->isHit($player))
          $this->plugin->mob->mobAttack($eid, $field, $player, 5);
    }
    $this->plugin->mob->Move($field, $eid);
  }

  public function moved6($field, $eid){
    $skillname = '§c§l烈痺';
    $poss = [];
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $player) $this->send($player, $skillname);
    $pos = $this->plugin->mob->getPos($field, $eid);
    $poss = $this->circle($pos, 7, 7);
    $posd = array_chunk($poss, count($poss)/20);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 7, 40);
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove6'], [$field, $eid, $pos, $poss]), 20);
  }

  public function attackmove6($field, $eid, $pos, $poss){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addParticle($field, $pos, Particle::TYPE_EXPLODE, 0, 7);
    $this->addActors($field, $poss, "minecraft:lightning_bolt", 7);
    $this->addSound($field, $pos, LevelSoundEventPacket::SOUND_EXPLODE); 
    foreach($players as $player){
        if($this->isHit($player)){
          $player->addEffect(new EffectInstance(Effect::getEffect(Effect::SLOWNESS), 10 * 20, 3));
          $this->plugin->mob->mobAttack($eid, $field, $player, 1);
        }
    }
    $this->plugin->mob->Move($field, $eid);
  }

  public function kill($player){
    $this->plugin->playermanager->addTitle($player, 7);
  }


}