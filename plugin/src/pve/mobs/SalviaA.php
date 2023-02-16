<?php
namespace pve\mobs;

use pocketmine\level\particle\Particle;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\entity\EntityIds;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\block\Block;

use pve\Callback;
use pve\Type;

class SalviaA extends Mobs {

  const NAME = '§e§lSalvia';
  const HP = 120000;
  const ATK = 1500;
  const DEF = 160;
  const EXP = 2500;
  const DROPS = [12 => 964, 4 => 64];

  const TYPE = TYPE::LIGHT;

  public function move1($field, $eid){
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

  public function move2($field, $eid){
    switch(mt_rand(0,2)){
      case 0:
        $this->moved1($field, $eid);
        break;
      case 1:
        $this->moved4($field, $eid);
        break;
      case 2:
        $this->moved5($field, $eid);
        break;
    }
  }

  public function moved1($field, $eid){
    $skillname = '§e神光';
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $player) $this->send($player, $skillname);
    $pos = $this->plugin->mob->getPos($field, $eid);
    $target = $this->plugin->mob->getTarget($field, $eid);
    $x = $target->x - $pos->x;
    $yaw = atan(($target->z - $pos->z)/$x);
    if($x < 0) $yaw += M_PI;
    $poss = $this->line($pos, $yaw, 20);
    $posd = array_chunk($poss, count($poss)/30);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 4, 20);
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove1'], [$field, $eid, $pos]), 15);
  }

  public function attackmove1($field, $eid, $pos){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addDestroyParticle($field, $pos, Block::get(89), 7);
    $this->addSound($field, $pos, LevelSoundEventPacket::SOUND_EXPLODE); 
    $this->plugin->mob->teleport($field, $eid, $pos);
    foreach($players as $player){
      if($this->isHit($player))
        $this->plugin->mob->mobAttack($eid, $field, $player, 4);
    }
    $this->plugin->mob->Move($field, $eid);
  }

  public function moved2($field, $eid){
    $skillname = '§e天光';
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $player) $this->send($player, $skillname);
    $pos = $this->plugin->mob->getPos($field, $eid);
    $poss = $this->circle($pos, 5, 5);
    $posd = array_chunk($poss, count($poss)/30);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 1, 15);
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove2'], [$field, $eid, $poss, $pos]), 10);
  }

  public function attackmove2($field, $eid, $poss, $pos){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addParticle($field, $pos, Particle::TYPE_HEART, 2, 7);
    $this->addSound($field, $pos, LevelSoundEventPacket::SOUND_EXPLODE); 
    if(!isset($this->plugin->mob->mobs[$field][$eid])) return false;
    $this->plugin->mob->mobs[$field][$eid]['+atk'] += 100;
    $count = count($players);
    if($this->plugin->mob->mobs[$field][$eid]['hp']+(3000*$count) < self::HP * $this->plugin->mob->mobs[$field][$eid]['lv'])
    $this->plugin->mob->mobs[$field][$eid]['hp'] += 3000*$count;
    $this->plugin->mob->Move($field, $eid);
  }

  public function moved3($field, $eid){
    $skillname = '§e滅光';
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $player) $this->send($player, $skillname);
    $pos = $this->plugin->mob->getPos($field, $eid);
    $poss = [];
    for($i = 0; $i < 10; $i++){
      $posd = new Vector3($pos->x + mt_rand(-7, 7), $pos->y, $pos->z + mt_rand(-7, 7));
      $poss =  array_merge($poss, $this->circle($posd, 2, 2));
    }
    $posd = array_chunk($poss, count($poss)/30);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 5, 15);
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove3'], [$field, $eid, $poss, $pos]), 10);
  }

  public function attackmove3($field, $eid, $poss, $pos){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addDestroyParticle($field, $pos, Block::get(89), 7);
    $this->addSound($field, $pos, LevelSoundEventPacket::SOUND_EXPLODE); 
    foreach($players as $player){
      if($this->isHit($player))
        $this->plugin->mob->mobAttack($eid, $field, $player, 3);
    }
    $this->plugin->mob->Move($field, $eid);
  }

  public function moved4($field, $eid){
    $skillname = '§c§l煌撃';
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $player) $this->send($player, $skillname);
    $pos = $this->plugin->mob->getPos($field, $eid);
    $poss = [];
    foreach($players as $player){
        $this->send($player, $skillname);
        $poss = array_merge($poss, $this->circle(new Vector3($player->x, 3, $player->z), 6, 6));
    }
    $posd = array_chunk($poss, count($poss)/20);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 6, 15);
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove4'], [$field, $eid, $poss, $pos]), 10);
  }

  public function attackmove4($field, $eid, $poss, $pos){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addDestroyParticle($field, $pos, Block::get(89), 7);
    $this->addSound($field, $pos, LevelSoundEventPacket::SOUND_EXPLODE); 
    foreach($players as $player){
      if($this->isHit($player))
        $this->plugin->mob->mobAttack($eid, $field, $player, 9);
    }
    $this->plugin->mob->Move($field, $eid);
  }

  public function moved5($field, $eid){
    $skillname = '§c§l栄光';
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $player) $this->send($player, $skillname);
    $pos = $this->plugin->mob->getPos($field, $eid);
    $poss = $this->circle($pos, 5, 5);
    $posd = array_chunk($poss, count($poss)/12);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 1);
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove5'], [$field, $eid, $poss, $pos]), 20);
    $target = $this->plugin->mob->getTarget($field, $eid);
    $x = $target->x - $pos->x;
    $yaw = atan(($target->z - $pos->z)/$x);
    if($x < 0) $yaw += M_PI;
    $poss = $this->line($pos, $yaw, 20);
    $poss = array_merge($poss, $this->line($pos, $yaw+(M_PI/6), 20));
    $poss = array_merge($poss, $this->line($pos, $yaw-(M_PI/6), 20));
    $posd = array_chunk($poss, count($poss)/40);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 5, 25);
  }

  public function attackmove5($field, $eid, $poss, $pos){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addParticle($field, $pos, Particle::TYPE_HEART, 2, 14);
    $this->addSound($field, $pos, LevelSoundEventPacket::SOUND_EXPLODE);
    if(!isset($this->plugin->mob->mobs[$field][$eid])) return false;
    $this->plugin->mob->mobs[$field][$eid]['+atk'] += 1000;
    $count = count($players);
    if($this->plugin->mob->mobs[$field][$eid]['hp']+(30000*$count) < self::HP * $this->plugin->mob->mobs[$field][$eid]['lv'])
    //$this->plugin->mob->mobs[$field][$eid]['hp'] += 30000*$count;
    foreach($players as $player){
      if($this->isHit($player))
        $this->plugin->mob->mobAttack($eid, $field, $player, 9);
    }
    $this->plugin->mob->Move($field, $eid);
  }

  public function kill($player){
    $this->plugin->playermanager->addTitle($player, 9);
  }


}