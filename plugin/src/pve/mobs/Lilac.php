<?php
namespace pve\mobs;

use pocketmine\level\particle\Particle;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\entity\EntityIds;
use pocketmine\level\Position;
use pocketmine\math\Vector3;

use pve\Callback;
use pve\Type;

class Lilac extends Mobs {

  const NAME = 'Lilac';
  const HP = 750000;
  const ATK = 268;
  const DEF = 53;
  const EXP = 3500;
  const DROPS = [7 => 2764, 1 => 364];
  const MONEY = 50000;

  const TYPE = TYPE::THUNDER;

  public function move1($field, $eid){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $pos = $this->plugin->mob->getPos($field, $eid);
    $this->addCustomParticle(TYPE::PARTICLE[self::TYPE], $pos, $players);
    $this->addAnimate($players, $eid);
    switch(mt_rand(0,2)){
      case 0:
        $this->moved1($field, $eid);
        break;
      case 1:
        $this->moved2($field, $eid);
        break;
      case 2:
        $this->moved3($field, $eid);
        break;
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
      new Callback([$this, 'attackmove1'], [$field, $eid, $poss[count($poss)-1]]), 40);
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
        $this->plugin->mob->mobAttack($eid, $field, $player, 7);
    }
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this->plugin->mob, 'Move'], [$field, $eid]), 30);
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
      new Callback([$this, 'attackmove2'], [$field, $eid, $poss, $pos, 2]), 40);
  }

  public function attackmove2($field, $eid, $poss, $pos){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addParticle($field, $pos, Particle::TYPE_EXPLODE, 0, 7);
    $this->addActors($field, $poss, "minecraft:lightning_bolt", 7);
    $this->addSound($field, $pos, LevelSoundEventPacket::SOUND_EXPLODE); 
    foreach($players as $player){
      if($pos->distance($player->getPosition()) <= 5)
        $this->plugin->mob->mobAttack($eid, $field, $player);
    }
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this->plugin->mob, 'Move'], [$field, $eid]), 30);
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
      new Callback([$this, 'attackmove3'], [$field, $eid, $poss, $pos, 3]), 20);
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
        $this->plugin->mob->mobAttack($eid, $field, $player);
    }
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this->plugin->mob, 'Move'], [$field, $eid]), 30);
  }

  public function kill($player){
    $this->plugin->playermanager->addTitle($player, 5);
  }


}