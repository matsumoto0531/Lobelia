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

class Cattleya extends Mobs {

  const NAME = 'Cattleya';
  const HP = 360000;
  const ATK = 222;
  const DEF = 53;
  const EXP = 3000;
  const DROPS = [8 => 2764, 2 => 364];

  const TYPE = TYPE::DARK;

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
    $skillname = '§e呪闇';
    $poss = [];
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $pos = $this->plugin->mob->getPos($field, $eid);
    foreach($players as $player){
      $this->send($player, $skillname);
      $poss = array_merge($poss, $this->circle(new Vector3($player->x, 3, $player->z), 4, 4));
    }
    $posd = array_chunk($poss, count($poss)/12);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 7, 40);
    $this->addAnimate($players, $eid, "animation.test.attack1");
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove1'], [$field, $eid, $pos]), 25);
  }

  public function attackmove1($field, $eid, $pos){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addParticle($field, $pos, Particle::TYPE_SMOKE, 2, 7);
    $this->addDestroyParticle($field, $pos, Block::get(112), 7);
    $this->addSound($field, $pos, LevelSoundEventPacket::SOUND_BREATHE); 
    $this->addCustomParticle(TYPE::PARTICLE[self::TYPE].'ATTACK', $pos, $players);
    foreach($players as $player){
        if($this->isHit($player)){
          $player->addEffect(new EffectInstance(Effect::getEffect(Effect::SLOWNESS), 10 * 20, 2));
          $this->plugin->playermanager->addAtk($player, -100, 10);
          $player->sendMessage('§l§dL-BAS§f>>呪いを受けちまってるです！');
          $player->sendMessage('§l§dL-BAS§f>>火力も下がるですからさっさとけりをつけやがれです！');
        }
    }
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this->plugin->mob, 'Move'], [$field, $eid, 0]), 30);
  }

  public function moved2($field, $eid){
    $skillname = '§e常闇';
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $player) $this->send($player, $skillname);
    $pos = $this->plugin->mob->getPos($field, $eid);
    $poss = $this->circle($pos, 10, 10);
    $posd = array_chunk($poss, count($poss)/12);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 6, 40);
    $this->addAnimate($players, $eid, "animation.test.attack2");
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove2'], [$field, $eid, $pos]), 40);
  }

  public function attackmove2($field, $eid, $pos){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addParticle($field, $pos, Particle::TYPE_SMOKE, 2, 7);
    $this->addDestroyParticle($field, $pos, Block::get(112), 7);
    $this->addSound($field, $pos, LevelSoundEventPacket::SOUND_BREATHE); 
    $this->addCustomParticle(TYPE::PARTICLE[self::TYPE].'ATTACK', $pos, $players);
    foreach($players as $player){
      if($this->isHit($player)){
        $this->plugin->mob->mobAttack($eid, $field, $player, 3);
      }
    }
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this->plugin->mob, 'Move'], [$field, $eid, 0]), 30);
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
      $this->setColors($p, $this->plugin->fieldmanager->level, 5, 40);
    $this->addAnimate($players, $eid, "animation.test.attack1");
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove3'], [$field, $eid, $pos, 5]), 20);
  }

  public function attackmove3($field, $eid, $pos){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addParticle($field, $pos, Particle::TYPE_SMOKE, 2, 7);
    $this->addDestroyParticle($field, $pos, Block::get(112), 7);
    $this->addSound($field, $pos, LevelSoundEventPacket::SOUND_BREATHE);  
    $this->addCustomParticle(TYPE::PARTICLE[self::TYPE].'ATTACK', $pos, $players);
    foreach($players as $player){
      if($this->isHit($player))
        $this->plugin->mob->mobAttack($eid, $field, $player, 5);
    }
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this->plugin->mob, 'Move'], [$field, $eid, 0]), 30);
  }

  public function kill($player){
    $this->plugin->playermanager->addTitle($player, 2);
  }

  public function onMove($field, $eid){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addAnimate($players, $eid, "animation.test.walk");
  }


}