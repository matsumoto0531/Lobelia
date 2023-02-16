<?php
namespace pve\mobs;

use pocketmine\level\particle\Particle;
use pocketmine\block\Block;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\entity\EntityIds;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\level\Position;
use pocketmine\math\Vector3;


use pve\Callback;
use pve\Type;

class CattleyaA extends Mobs {

  const NAME = '§0§lCattleya';
  const HP = 3000000;
  const ATK = 1000;
  const DEF = 160;
  const EXP = 1000;
  const DROPS = [25 => 2764, 26 => 364];

  const TYPE = TYPE::DARK;

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
    switch(mt_rand(0,3)){
      case 0:
        $this->moved1($field, $eid);
        break;
      case 1:
        $this->moved2($field, $eid);
        break;
      case 2:
        $this->moved4($field, $eid);
        break;
      case 3:
        $this->moved5($field, $eid);
        break;
    }
  }

  public function moved1($field, $eid){
    $skillname = '§e呪闇';
    $poss = [];
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $pos = $this->plugin->mob->getPos($field, $eid);
    foreach($players as $player){
      $this->send($player, $skillname);
      $poss = array_merge($poss, $this->circle(new Vector3($player->x, 3, $player->z), 4, 4));
    }
    $posd = array_chunk($poss, count($poss)/40);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 7, 40);
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove1'], [$field, $eid, $pos]), 10);
  }

  public function attackmove1($field, $eid, $pos){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addParticle($field, $pos, Particle::TYPE_SMOKE, 2, 7);
    $this->addDestroyParticle($field, $pos, Block::get(112), 7);
    $this->addSound($field, $pos, LevelSoundEventPacket::SOUND_BREATHE); 
    foreach($players as $player){
        if($this->isHit($player)){
          $player->addEffect(new EffectInstance(Effect::getEffect(Effect::SLOWNESS), 10 * 20, 2));
          $this->plugin->playermanager->addAtk($player, -100, 10);
          $player->sendMessage('§l§dL-BAS§f>>呪いを受けちまってるです！');
          $player->sendMessage('§l§dL-BAS§f>>火力も下がるですからさっさとけりをつけやがれです！');
        }
    }
    $this->plugin->mob->Move($field, $eid);
  }

  public function moved2($field, $eid){
    $skillname = '§e常闇';
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $player) $this->send($player, $skillname);
    $pos = $this->plugin->mob->getPos($field, $eid);
    $poss = $this->circle($pos, 10, 10);
    $posd = array_chunk($poss, count($poss)/40);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 5, 40);
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove2'], [$field, $eid, $pos]), 10);
  }

  public function attackmove2($field, $eid, $pos){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addParticle($field, $pos, Particle::TYPE_SMOKE, 2, 7);
    $this->addDestroyParticle($field, $pos, Block::get(112), 7);
    $this->addSound($field, $pos, LevelSoundEventPacket::SOUND_BREATHE); 
    foreach($players as $player){
      if($pos->distance($player->getPosition()) <= 10){
        $this->plugin->mob->mobAttack($eid, $field, $player, 3);
      }
    }
    $this->plugin->mob->Move($field, $eid);
  }

  public function moved3($field, $eid){
    $skillname = '§e闇槍';
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $player) $this->send($player, $skillname);
    $pos = $this->plugin->mob->getPos($field, $eid);
    $target = $this->plugin->mob->getTarget($field, $eid);
    $x = $target->x - $pos->x;
    $yaw = atan(($target->z - $pos->z)/$x);
    if($x < 0) $yaw += M_PI;
    $poss = $this->line($pos, $yaw, 20);
    $posd = array_chunk($poss, count($poss)/12);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 5, 30);
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove3'], [$field, $eid, $pos, 5]), 10);
  }

  public function attackmove3($field, $eid, $pos){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addParticle($field, $pos, Particle::TYPE_SMOKE, 2, 7);
    $this->addDestroyParticle($field, $pos, Block::get(112), 7);
    $this->addSound($field, $pos, LevelSoundEventPacket::SOUND_BREATHE);  
    foreach($players as $player){
      if($this->isHit($player))
        $this->plugin->mob->mobAttack($eid, $field, $player, 5);
    }
    $this->plugin->mob->Move($field, $eid);
  }

  public function moved4($field, $eid){
    $skillname = '§c§l漆黒';
    $poss = [];
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $pos = $this->plugin->mob->getPos($field, $eid);
    foreach($players as $player){
      $this->send($player, $skillname);
      $poss = array_merge($poss, $this->circle(new Vector3($player->x, 3, $player->z), 4, 4));
    }
    $posd = array_chunk($poss, count($poss)/50);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 7, 20);
    $poss = [];
    for($i = 0; $i < 10; $i++){
      $posd = new Vector3($pos->x + mt_rand(-20, 20), $pos->y, $pos->z + mt_rand(-20, 20));
      $poss =  array_merge($poss, $this->circle($posd, 4, 4));
    }
    $posd = array_chunk($poss, count($poss)/50);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 7, 20);
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove4'], [$field, $eid, $pos]), 15);
  }

  public function attackmove4($field, $eid, $pos){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addParticle($field, $pos, Particle::TYPE_SMOKE, 2, 7);
    $this->addDestroyParticle($field, $pos, Block::get(112), 7);
    $this->addSound($field, $pos, LevelSoundEventPacket::SOUND_BREATHE); 
    foreach($players as $player){
        if($this->isHit($player)){
          $player->addEffect(new EffectInstance(Effect::getEffect(Effect::SLOWNESS), 10 * 20, 10));
          $player->addEffect(new EffectInstance(Effect::getEffect(Effect::BLINDNESS), 10 * 20, 10));
          $this->plugin->playermanager->addAtk($player, -1000, 10);
          $player->sendMessage('§l§dL-BAS§f>>!?');
          $player->sendMessage('§l§dL-BAS§f>>かなり火力を下げられたです！');
          $this->plugin->mob->mobAttack($eid, $field, $player, 5);
        }
    }
    $this->plugin->mob->Move($field, $eid);
  }

  public function moved5($field, $eid){
    $skillname = '§c§l闇砲';
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $player) $this->send($player, $skillname);
    $pos = $this->plugin->mob->getPos($field, $eid);
    $target = $this->plugin->mob->getTarget($field, $eid);
    $x = $target->x - $pos->x;
    $yaw = atan(($target->z - $pos->z)/$x);
    if($x < 0) $yaw += M_PI;
    $poss = $this->line($pos, $yaw, 20);
    $poss = array_merge($poss, $this->line($pos, $yaw+(M_PI/6), 20));
    $poss = array_merge($poss, $this->line($pos, $yaw-(M_PI/6), 20));
    $posd = array_chunk($poss, count($poss)/40);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 4, 30);
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove5'], [$field, $eid, $pos, 5]), 20);
  }

  public function attackmove5($field, $eid, $pos){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addParticle($field, $pos, Particle::TYPE_SMOKE, 2, 7);
    $this->addDestroyParticle($field, $pos, Block::get(112), 7);
    $this->addSound($field, $pos, LevelSoundEventPacket::SOUND_BREATHE);  
    foreach($players as $player){
      if($this->isHit($player))
        $this->plugin->mob->mobAttack($eid, $field, $player, 20);
    }
    $this->plugin->mob->Move($field, $eid);
  }

  public function kill($player){
    $this->plugin->playermanager->addTitle($player, 8);
  }


}