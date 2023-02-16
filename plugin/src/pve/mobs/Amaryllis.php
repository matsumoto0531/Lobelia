<?php
namespace pve\mobs;

use pocketmine\level\particle\Particle;
use pocketmine\block\Block;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\entity\EntityIds;
use pocketmine\level\Position;
use pocketmine\math\Vector3;

use pve\Callback;
use pve\Type;

class Amaryllis extends Mobs {

  const NAME = 'Amaryllis';
  const HP = 150000;
  const ATK = 132;
  const DEF = 53;
  const EXP = 2000;
  const MONEY = 20000;
  const DROPS = [11 => 2764, 3 => 364];

  const TYPE = TYPE::ICE;

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
    $skillname = '§e結露';
    $poss = [];
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $pos = $this->plugin->mob->getPos($field, $eid);
    foreach($players as $player){
      $this->send($player, $skillname);
      $poss = array_merge($poss, $this->circle(new Vector3($player->x, 3, $player->z), 2, 2));
    }
    $posd = array_chunk($poss, count($poss)/12);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 5, 40);
    $this->addAnimate($players, $eid, "animation.amary_attack2");
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove1'], [$field, $eid, $poss[count($poss)-1], $poss]), 31);
  }

  public function attackmove1($field, $eid, $pos, $poss){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addDestroyParticle($field, $pos, Block::get(71), 7);
    $this->addActors($field, $poss, "minecraft:ice_bomb", 7);
    $this->addSound($field, $pos, LevelSoundEventPacket::SOUND_BREAK); 
    $this->addCustomParticle(TYPE::PARTICLE[self::TYPE].'ATTACK', new Vector3($pos->x, $pos->y+1, $pos->z), $players);
    foreach($players as $player){
        $block = $this->plugin->fieldmanager->level->getBlock($player->down());
        if($block->getId() === 0) $block = $this->plugin->fieldmanager->level->getBlock(new Vector3($player->x, $player->y-2, $player->z));
        if($block->getId() === 35 && $block->getDamage() === 5)
          $this->plugin->mob->mobAttack($eid, $field, $player, 9);
    }
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this->plugin->mob, 'Move'], [$field, $eid, 0]), 30);
  }

  public function moved2($field, $eid){
    $skillname = '§e吹雪';
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $player) $this->send($player, $skillname);
    $pos = $this->plugin->mob->getPos($field, $eid);
    $poss = $this->circle($pos, 10, 10);
    $posd = array_chunk($poss, count($poss)/32);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 4, 40);
    $this->addAnimate($players, $eid, "animation.amary_attack");
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove2'], [$field, $eid, $poss, $pos]), 24);
  }

  public function attackmove2($field, $eid, $poss, $pos){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addDestroyParticle($field, $pos, Block::get(71), 7);
    $this->addActors($field, $poss, "minecraft:ice_bomb", 7);
    $this->addSound($field, $pos, LevelSoundEventPacket::SOUND_BREAK); 
    $this->addCustomParticle(TYPE::PARTICLE[self::TYPE].'ATTACK', $pos, $players);
    foreach($players as $player){
      if($pos->distance($player->getPosition()) <= 10){
        if($player->isSneaking()) continue;
        $this->plugin->mob->mobAttack($eid, $field, $player, 5);
      }
    }
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this->plugin->mob, 'Move'], [$field, $eid, 0]), 30);
  }

  public function moved3($field, $eid){
    $skillname = '§e氷槍';
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
    $this->addAnimate($players, $eid, "animation.amary_attack2");
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove3'], [$field, $eid, $poss, $pos]), 31);
  }

  public function attackmove3($field, $eid, $poss, $pos){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addDestroyParticle($field, $pos, Block::get(71), 7);
    $this->addActors($field, $poss, "minecraft:ice_bomb", 7);
    $this->addSound($field, $pos, LevelSoundEventPacket::SOUND_BREAK); 
    $this->addCustomParticle(TYPE::PARTICLE[self::TYPE].'ATTACK', $pos, $players);
    foreach($players as $player){
      $block = $this->plugin->fieldmanager->level->getBlock($player->down());
      if($block->getId() === 0) $block = $this->plugin->fieldmanager->level->getBlock(new Vector3($player->x, $player->y-2, $player->z));
      if($block->getId() === 35 && $block->getDamage() === 5)
        $this->plugin->mob->mobAttack($eid, $field, $player, 6);
    }
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this->plugin->mob, 'Move'], [$field, $eid, 0]), 30);
  }

  public function kill($player){
    $this->plugin->playermanager->addTitle($player, 1, true);
  }

  public function onMove($field, $eid){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addAnimate($players, $eid, "animation.amary_walk");
  }


}