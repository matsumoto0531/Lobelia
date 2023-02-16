<?php
namespace pve\weaponskill;

use pocketmine\item\Item;
use pocketmine\utils\UUID;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\math\Vector3;
use pocketmine\block\Block;
use pve\Callback;

use pocketmine\level\particle\Particle;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;


class Zero extends WeaponSkill {
	
  const NAME = "§b§l零";
  const ID = 15;
  const ITEM_ID = 264;
  const CT = 20;
	
  const DESCRIPTION = '';

  public function Imposition($player){
    $field = $this->plugin->fieldmanager->getField($player);
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $pos = $player->getPosition();
    $poss = $this->line($pos, deg2rad($player->yaw + 90), 10);
    $atk = $this->plugin->mob->checkAtk($player);
    $type = $this->plugin->mob->getType($player->getInventory()->getItemInHand());
    $atk = $this->plugin->playermanager->getAtk($player);
    $this->plugin->playermanager->addAtk($player, $atk * -1, 10);
    $def = $this->plugin->playermanager->getDef($player);
    $this->plugin->playermanager->addDef($player, $def * -1, 10);

    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'ActionTask'], [$field, $poss, $pos, $player, $players, $atk, $type]), 9);
  }

  public function ActionTask($field, $poss, $pos, $player, $players, $atk, $type){
    $this->addDestroyParticles($field, $poss, Block::get(71));
    $this->addCustomParticle('PVE:ICEATTACK', $pos, $players);
    if($field == 'spawn')
      return false;
      if(!isset($this->plugin->mob->mobs[$field])) return false;
      $mobs = $this->plugin->mob->mobs[$field];
    foreach($mobs as $eid => $data){
      $mpos = new Vector3($data['x'], $data['y'], $data['z']);
      foreach($poss as $posi){
        if($this->distance($posi, $mpos) < 10){
          $this->plugin->mob->addAtk($field, $eid, -10000, 1);
          $this->plugin->mob->addDef($field, $eid, -10000, 1);
          $this->plugin->mob->CustomAttack($atk * 8, $player, $field, $eid, $type);
          $this->plugin->mob->Freeze($field, $eid, 10, 500);
        }
      }
    }
  }
}