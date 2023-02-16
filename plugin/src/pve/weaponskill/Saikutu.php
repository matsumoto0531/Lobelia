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


class Saikutu extends WeaponSkill {
	
  const NAME = "§c超採掘";
  const ID = 12;
  const ITEM_ID = 264;
  const CT = 30;
	
  const DESCRIPTION = '採掘力が上昇する';

  public function Imposition($player){
    $field = $this->plugin->fieldmanager->getField($player);
    $pos = $player->getPosition();
    $poss = $this->circle($pos, 5, 5);
    $player->addEffect(new EffectInstance(Effect::getEffect(Effect::HASTE), self::CT * 20, 2));
    $players = $this->plugin->fieldmanager->getPlayers($field);
    $this->plugin->playermanager->setImmobile($player, 2);
    $this->addAnimate($players, $player->getId(), "animation.humanoid.saikutu");
    $this->plugin->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'ActionTask'], [$field, $poss, $pos, $player, $players]), 22);
  }

  public function ActionTask($field, $poss, $pos, $player, $players){
    $this->addCustomParticle('PVE:STATUSUP', $pos, $players);
    $this->plugin->playermanager->addMine($player, 60, self::CT);
  }
}