<?php
namespace pve\weaponskill;

use pocketmine\item\Item;
use pocketmine\utils\UUID;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\math\Vector3;
use pocketmine\block\Block;
use pve\Callback;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;

use pocketmine\level\particle\Particle;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;


class Iai extends WeaponSkill {
	
  const NAME = "§b居合§f";
  const ID = 19;
  const ITEM_ID = 264;
  const CT = 5;
  const TP = 10;
	
  const DESCRIPTION = "直進して、立ちふさがるものを両断する。";

  public function Imposition($player){
    $field = $this->plugin->fieldmanager->getField($player);
    $pos = $player->getPosition();
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->plugin->playermanager->setImmobile($player, 2);
    $this->plugin->animation->addAnimation($player, "animation.pve.playerattack", 40);
    $atk = $this->plugin->mob->checkAtk($player);
    $type = $this->plugin->mob->getType($player->getInventory()->getItemInHand());
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'ActionTask'], [$field, $pos, $player, $players, $atk, $type]), 18);
  }

  public function ActionTask($field, $pos, $player, $players, $atk, $type){
    if($field == 'spawn')
      return false;
    if(!isset($this->plugin->mob->mobs[$field])) return false;
    $mobs = $this->plugin->mob->mobs[$field];
    $poss = $this->line($pos, deg2rad($this->plugin->animation->getYaw($player) + 90), 10);
    $count = 0;
    foreach($poss as $p){
      $this->addCustomParticle('PVE:IAI', $p, $players);
      if((!$this->checkBlock($p)) or (!$this->checkBlock(new Vector3($p->x, $p->y+1, $p->z)))){
          if($count !== 0)
            $p = $poss[$count-1];
          else
            $p = $pos;
        break;
      }
      foreach($mobs as $eid => $data){
        $mpos = new Vector3($data['x'], $data['y'], $data['z']);
        if($this->distance($p, $mpos) < 1){
          $this->plugin->mob->CustomAttack($atk * 1.2, $player, $field, $eid, $type);
          $this->plugin->mob->addSound($player, "PVE:SWORD", 0.3);
          $this->addCustomParticle('PVE:SWORD', $p, $players);
        }
      }
      $count++;
    }
    $this->plugin->animation->moveDummy($player, $p);
  }
}