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

class Salvia extends Mobs {

  const NAME = 'Salvia';
  const HP = 200000;
  const ATK = 400;
  const DEF = 53;
  const EXP = 250000;
  const MONEY = 50000;
  const DROPS = [12 => 2764, 4 => 364];

  const TYPE = TYPE::LIGHT;
  const ATTACK = "animation.attackingadle";

  public function move1($field, $eid){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $pos = $this->plugin->mob->getPos($field, $eid);
    $this->addCustomParticle(TYPE::PARTICLE[self::TYPE], $pos, $players);
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
    $skillname = '§e神光';
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
      $this->setColors($p, $this->plugin->fieldmanager->level, 4, 40);
    $this->addAnimate($players, $eid, "animation.attack");
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove1'], [$field, $eid, $pos]), 22);
  }

  public function attackmove1($field, $eid, $pos){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addDestroyParticle($field, $pos, Block::get(89), 7);
    $this->addSound($field, $pos, LevelSoundEventPacket::SOUND_EXPLODE); 
    foreach($players as $player){
      if($this->isHit($player))
        $this->plugin->mob->mobAttack($eid, $field, $player, 4);
    }
    $this->addCustomParticle(TYPE::PARTICLE[self::TYPE].'ATTACK', $pos, $players);
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this->plugin->mob, 'Move'], [$field, $eid, 0]), 30);
  }

  public function moved2($field, $eid){
    $skillname = '§e天光';
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $player) $this->send($player, $skillname);
    $pos = $this->plugin->mob->getPos($field, $eid);
    $poss = $this->circle($pos, 5, 5);
    $posd = array_chunk($poss, count($poss)/12);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 1, 80);
    $this->addAnimate($players, $eid, "animation.healing");
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove2'], [$field, $eid, $poss, $pos]), 59);
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
    $this->addCustomParticle(TYPE::PARTICLE[self::TYPE].'ATTACK', $pos, $players);
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this->plugin->mob, 'Move'], [$field, $eid, 0]), 30);
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
    $posd = array_chunk($poss, count($poss)/12);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 5, 40);
    $this->addAnimate($players, $eid, "animation.attack");
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove3'], [$field, $eid, $poss, $pos]), 22);
  }

  public function attackmove3($field, $eid, $poss, $pos){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addDestroyParticle($field, $pos, Block::get(89), 7);
    $this->addSound($field, $pos, LevelSoundEventPacket::SOUND_EXPLODE); 
    foreach($players as $player){
      if($this->isHit($player))
        $this->plugin->mob->mobAttack($eid, $field, $player, 6);
    }
    $this->addCustomParticle(TYPE::PARTICLE[self::TYPE].'ATTACK', $pos, $players);
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this->plugin->mob, 'Move'], [$field, $eid, 0]), 30);
  }

  public function kill($player){
    $this->plugin->playermanager->addTitle($player, 6);
  }

  public function onMove($field, $eid){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addAnimate($players, $eid, "animation.walkingidle", 0);
  }


}