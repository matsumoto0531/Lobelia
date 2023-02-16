<?php
namespace pve\mobs;

use pocketmine\network\mcpe\protocol\types\ParticleIds as Particle;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\entity\EntityIds;
use pocketmine\world\Position;
use pocketmine\math\Vector3;

use pve\Callback;
use pve\Type;

class Clematis extends Mobs {

  const NAME = 'Clematis';
  const HP = 20000;
  const ATK = 60;
  const DEF = 53;
  const EXP = 300;
  const MONEY = 100;
  const DROPS = [9 => 2764, 5 => 364];

  const TYPE = TYPE::FIRE;

  const moves = [
    [90, 6, 0, 4],
    [20, 10, 60, 10],
    [70, 0, 20, 10],
    [20, 60, 10, 10]
  ];

  public function move1($field, $eid){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $pos = $this->plugin->mob->getPos($field, $eid);
    if(!isset($this->final[$eid])) $this->final[$eid] = 0;
    $kakuritu = self::moves[$this->final[$eid]];
    $rand = mt_rand(0, 100);
    for($i = 0; $i < 4; $i++){
      if($rand <= $kakuritu[$i]) break;
      $rand -= $kakuritu[$i];
    }
    switch($i){
      case 0:
        $this->plugin->getScheduler()->scheduleDelayedTask(
          new Callback([$this->plugin->mob, 'Move'], [$field, $eid]), 6);
        $this->final[$eid] = 0;
        return false;
        break;
      case 1:
        $this->moved1($field, $eid);
        $this->addCustomParticle(TYPE::PARTICLE[self::TYPE], $pos, $players);
        $this->final[$eid] = 1;
        break;
      case 2:
        $this->moved2($field, $eid);
        $this->addCustomParticle(TYPE::PARTICLE[self::TYPE], $pos, $players);
        $this->final[$eid] = 2;
        break;
      case 3:
        $this->moved3($field, $eid);
        $this->addCustomParticle(TYPE::PARTICLE[self::TYPE], $pos, $players);
        $this->final[$eid] = 3;
        break;
    }
  }

  public function moved1($field, $eid){
    $skillname = '§e爆炎';
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $pos = $this->plugin->mob->getPos($field, $eid);
    foreach($players as $player){
      $this->send($player, $skillname);
    }
    $target = $this->plugin->mob->getTarget($field, $eid);
    $position = $target->getPosition();
    $poss = $this->circle(new Vector3($position->x, 3, $position->z), 5, 5);
    $posd = array_chunk($poss, count($poss)/12);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 5, 40);
    $this->addAnimate($players, $eid, "animation.clematis_attack");
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove1'], [$field, $eid, $target->getPosition(), $poss]), 34);
  }

  public function attackmove1($field, $eid, $pos, $poss){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addDestroyParticle($field, $pos, BlockFactory::getInstance()->get(213, 0), 7);
    $this->addParticle($field, $pos, Particle::MOB_FLAME, 2, 7);
    $this->addSound($field, $pos, LevelSoundEvent::FIRE); 
    $this->addCustomParticle(TYPE::PARTICLE[self::TYPE].'ATTACK', $pos, $players);
    foreach($players as $player){
        if($this->isHit($player, $poss))
          $this->plugin->mob->mobAttack($eid, $field, $player, 2);
    }
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this->plugin->mob, 'Move'], [$field, $eid]), 30);
  }

  public function moved2($field, $eid){
    $skillname = '§e煉獄';
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $player) $this->send($player, $skillname);
    $pos = $this->plugin->mob->getPos($field, $eid);
    $poss = $this->circle($pos, 10, 10);
    $posd = array_chunk($poss, count($poss)/12);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 6, 40);
    $this->addAnimate($players, $eid, "animation.clematis_attack");
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove2'], [$field, $eid, $poss, $pos]), 34);
  }

  public function attackmove2($field, $eid, $poss, $pos){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addDestroyParticle($field, $pos, BlockFactory::getInstance()->get(213, 0), 7);
    $this->addParticle($field, $pos, Particle::MOB_FLAME, 2, 7);
    $this->addSound($field, $pos, LevelSoundEvent::FIRE); 
    $this->addCustomParticle(TYPE::PARTICLE[self::TYPE].'ATTACK', $pos, $players);
    foreach($players as $player){
      if($pos->distance($player->getPosition()) <= 10){
        if($player->isSprinting()) continue;
        $this->plugin->mob->mobAttack($eid, $field, $player, 3);
      }
    }
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this->plugin->mob, 'Move'], [$field, $eid]), 30);
  }

  public function moved3($field, $eid){
    $skillname = '§e炎撃';
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $player) $this->send($player, $skillname);
    $pos = $this->plugin->mob->getPos($field, $eid);
    $poss = [];
    for($i = 0; $i < 5; $i++){
      $posd = new Vector3($pos->x + mt_rand(-7, 7), $pos->y, $pos->z + mt_rand(-7, 7));
      $poss =  array_merge($poss, $this->circle($posd, 2, 2));
    }
    $posd = array_chunk($poss, count($poss)/12);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 5, 45);
    $this->addAnimate($players, $eid, "animation.clematis_attack");
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove3'], [$field, $eid, $poss, $pos]), 34);
  }

  public function attackmove3($field, $eid, $poss, $pos){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addDestroyParticle($field, $pos, BlockFactory::getInstance()->get(213, 0), 7);
    $this->addParticle($field, $pos, Particle::MOB_FLAME, 2, 7);
    $this->addSound($field, $pos, LevelSoundEvent::FIRE); 
    $this->addCustomParticle(TYPE::PARTICLE[self::TYPE].'ATTACK', $pos, $players);
    foreach($players as $player){
      if($this->isHit($player, $poss))
        $this->plugin->mob->mobAttack($eid, $field, $player, 5);
    }
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this->plugin->mob, 'Move'], [$field, $eid]), 30);
  }

  public function kill($player){
    $this->plugin->playermanager->addTitle($player, 3, true);
  }


}