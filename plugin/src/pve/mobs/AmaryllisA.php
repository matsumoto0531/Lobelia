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

class AmaryllisA extends Mobs {

  const NAME = '§b§lAmaryllis';
  const HP = 50000;
  const ATK = 2000;
  const DEF = 160;
  const EXP = 2000;
  const DROPS = [11 => 964, 3 => 64];

  const TYPE = TYPE::ICE;

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
    $skillname = '§e結露';
    $poss = [];
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $pos = $this->plugin->mob->getPos($field, $eid);
    foreach($players as $player){
      $this->send($player, $skillname);
      $poss = array_merge($poss, $this->circle(new Vector3($player->x, 3, $player->z), 2, 2));
    }
    $posd = array_chunk($poss, count($poss)/30);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 5, 20);
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove1'], [$field, $eid, $poss[count($poss)-1], $poss]), 15);
  }

  public function attackmove1($field, $eid, $pos, $poss){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addDestroyParticle($field, $pos, Block::get(71), 7);
    $this->addActors($field, $poss, "minecraft:ice_bomb", 7);
    $this->addSound($field, $pos, LevelSoundEventPacket::SOUND_BREAK); 
    foreach($players as $player){
        $block = $this->plugin->fieldmanager->level->getBlock($player->down());
        if($block->getId() === 0) $block = $this->plugin->fieldmanager->level->getBlock(new Vector3($player->x, $player->y-2, $player->z));
        if($block->getId() === 35 && $block->getDamage() === 5)
          $this->plugin->mob->mobAttack($eid, $field, $player, 3);
    }
    $this->plugin->mob->Move($field, $eid);
  }

  public function moved2($field, $eid){
    $skillname = '§e吹雪';
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $player) $this->send($player, $skillname);
    $pos = $this->plugin->mob->getPos($field, $eid);
    $poss = $this->circle($pos, 10, 10);
    $posd = array_chunk($poss, count($poss)/30);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 4, 20);
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove2'], [$field, $eid, $poss, $pos]), 15);
  }

  public function attackmove2($field, $eid, $poss, $pos){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addDestroyParticle($field, $pos, Block::get(71), 7);
    $this->addActors($field, $poss, "minecraft:ice_bomb", 7);
    $this->addSound($field, $pos, LevelSoundEventPacket::SOUND_BREAK); 
    foreach($players as $player){
      if($pos->distance($player->getPosition()) <= 10){
        if($player->isSneaking()) continue;
        $this->plugin->mob->mobAttack($eid, $field, $player, 2);
      }
    }
    $this->plugin->mob->Move($field, $eid);
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
    $posd = array_chunk($poss, count($poss)/30);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 5, 15);
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove3'], [$field, $eid, $poss, $pos]), 10);
  }

  public function attackmove3($field, $eid, $poss, $pos){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addDestroyParticle($field, $pos, Block::get(71), 7);
    $this->addActors($field, $poss, "minecraft:ice_bomb", 7);
    $this->addSound($field, $pos, LevelSoundEventPacket::SOUND_BREAK); 
    foreach($players as $player){
      $block = $this->plugin->fieldmanager->level->getBlock($player->down());
      if($block->getId() === 0) $block = $this->plugin->fieldmanager->level->getBlock(new Vector3($player->x, $player->y-2, $player->z));
      if($block->getId() === 35 && $block->getDamage() === 5)
        $this->plugin->mob->mobAttack($eid, $field, $player, 2);
    }
    $this->plugin->mob->Move($field, $eid);
  }

  public function moved4($field, $eid){
    $skillname = '§c§l絶凍';
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $player) $this->send($player, $skillname);
    $poss = [];
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $pos = $this->plugin->mob->getPos($field, $eid);
    foreach($players as $player){
      $this->send($player, $skillname);
      $poss = array_merge($poss, $this->circle(new Vector3($player->x, 3, $player->z), 5, 5));
    }
    $posd = array_chunk($poss, count($poss)/50);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 7, 20);
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove4'], [$field, $eid, $poss, $pos]), 10);
  }

  public function attackmove4($field, $eid, $poss, $pos){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addDestroyParticle($field, $pos, Block::get(71), 7);
    $this->addActors($field, $poss, "minecraft:ice_bomb", 7);
    $this->addSound($field, $pos, LevelSoundEventPacket::SOUND_BREAK); 
    foreach($players as $player){
        if($this->isHit($player)){
          $this->plugin->mob->mobAttack($eid, $field, $player, 5);
          $this->plugin->playermanager->setImmobile($player, 10);
          $this->addDestroyParticle($field, $player->getPosition(), Block::get(71), 7);
          $player->sendMessage('§l§dL-BAS§f>足が凍り付いちまったです！');
          $player->sendMessage('§l§dL-BAS§f>>はやくなんとかしやがれです！');
        }
    }
    $this->plugin->mob->Move($field, $eid);
  }

  public function moved5($field, $eid){
    $skillname = '§c§l凍壊';
    $players = $this->plugin->fieldmanager->getPlayers($field);
    foreach($players as $player) $this->send($player, $skillname);
    $poss = [];
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $pos = $this->plugin->mob->getPos($field, $eid);
    foreach($players as $player){
      $this->send($player, $skillname);
      $poss = array_merge($poss, $this->circle(new Vector3($player->x, 3, $player->z), 5, 5));
    }
    $posd = array_chunk($poss, count($poss)/50);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 7, 20);
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove5'], [$field, $eid, $poss, $pos]), 10);
  }

  public function attackmove5($field, $eid, $poss, $pos){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addDestroyParticle($field, $pos, Block::get(71), 7);
    $this->addActors($field, $poss, "minecraft:ice_bomb", 7);
    $this->addSound($field, $pos, LevelSoundEventPacket::SOUND_BREAK); 
    foreach($players as $player){
        if($this->isHit($player)){
          $this->plugin->mob->mobAttack($eid, $field, $player, 5);
          $this->plugin->playermanager->setImmobile($player, 10);
          $this->addDestroyParticle($field, $player->getPosition(), Block::get(71), 7);
          $player->sendMessage('§l§dL-BAS§f>足が凍り付いちまったです！');
          $player->sendMessage('§l§dL-BAS§f>>はやくなんとかしやがれです！');
        }
    }
    $this->plugin->mob->Move($field, $eid);
  }

  public function moved6($field, $eid){
    $skillname = '§c§l凍壊';
    $poss = [];
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $pos = $this->plugin->mob->getPos($field, $eid);
    foreach($players as $player){
      $this->send($player, $skillname);
      $poss = array_merge($poss, $this->circle(new Vector3($player->x, 3, $player->z), 2, 2));
    }
    $posd = array_chunk($poss, count($poss)/30);
    foreach($posd as $p)
      $this->setColors($p, $this->plugin->fieldmanager->level, 14, 20);
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'attackmove6'], [$field, $eid, $poss, $pos]), 10);
  }

  public function attackmove6($field, $eid, $poss, $pos){
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->addDestroyParticle($field, $pos, Block::get(71), 7);
    $this->addActors($field, $poss, "minecraft:ice_bomb", 7);
    $this->addSound($field, $pos, LevelSoundEventPacket::SOUND_BREAK); 
    foreach($players as $player){
        if($this->isHit($player)){
          $this->plugin->mob->mobAttack($eid, $field, $player, 500);
        }
    }
    $this->plugin->mob->Move($field, $eid);
  }


  public function kill($player){
    $this->plugin->playermanager->addTitle($player, 10);
  }


}