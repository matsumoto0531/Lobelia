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


class Wind extends WeaponSkill {
	
  const NAME = "§a旋風§f";
  const ID = 5;
  const ITEM_ID = 264;
  const CT = 5;
  const TP = 10;
	
  const DESCRIPTION = "鋭い風を巻き起こして周囲を攻撃する。\n移動速度も上昇する。";

  public function Imposition($player){
    $field = $this->plugin->fieldmanager->getField($player);
    $pos = $player->getPosition();
    $poss = $this->circle($pos, 5, 5);
    $player->addEffect(new EffectInstance(Effect::getEffect(Effect::SPEED), 10 * 20, 2));
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->plugin->playermanager->setImmobile($player, 2);
    $this->plugin->animation->addAnimation($player, "animation.pve.playerattack", 40);
    $atk = $this->plugin->mob->checkAtk($player);
    $type = $this->plugin->mob->getType($player->getInventory()->getItemInHand());
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'ActionTask'], [$field, $poss, $pos, $player, $players, $atk, $type]), 18);
  }

  public function ActionTask($field, $poss, $pos, $player, $players, $atk, $type){
    $posd = array_chunk($poss, count($poss)/4);
    $this->addParticles($field, $posd[0], Particle::TYPE_DRAGON_DESTROY_BLOCK, 2);
    $this->addCustomParticle('PVE:WINDATTACK', $pos, $players);
    $this->plugin->playermanager->addAgi($player, 10, 5);
    if($field == 'spawn')
      return false;
    if(!isset($this->plugin->mob->mobs[$field])) return false;
    $mobs = $this->plugin->mob->mobs[$field];
    foreach($mobs as $eid => $data){
      $mpos = new Vector3($data['x'], $data['y'], $data['z']);
      if($this->distance($pos, $mpos) < 10){
        $this->plugin->mob->CustomAttack($atk * 1.2, $player, $field, $eid, $type);
      }
    }
  }
}