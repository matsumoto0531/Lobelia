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


class Warp extends WeaponSkill {
	
  const NAME = "§d転移";
  const ID = 13;
  const ITEM_ID = 264;
  const CT = 60;
	
  const DESCRIPTION = '最後に記憶した場所に転移する。';


  public function Interact($weapon, $player){
      $name = $player->getName();
      if($player->isSneaking()){
        if($this->plugin->dungeon->isDungeon($player)){
          $player->sendMessage('§d転移§f>>ここは記憶できません。');
          return false;
        }
          $this->dat[$player->getName()] = [
              'field' => $this->plugin->fieldmanager->getField($player),
              'pos' => $player->getPosition()
          ];
          $player->sendMessage('§d転移§f>>場所を記憶しました。');
          return true;
      }
      if(!array_key_exists($name, $this->data))
      $this->data[$name] = 0;
      $time = microtime(true);
      $num = $time - $this->data[$name];
      if($num > self::CT){
        $this->data[$name] = $time;
        $this->Imposition($player);
        $this->send($player);
      }else{
        $player->sendMessage('§d転移§f>>力を貯めています...');
      }
  }

  public function Imposition($player){
    if(!isset($this->dat[$player->getName()])){
        $player->sendMessage('TP先が設定されていません！');
        return false;
    }
    $field = $this->plugin->fieldmanager->getField($player);
    $this->plugin->fieldmanager->changeField($player, $field, $this->dat[$player->getName()]['field']);
    $player->teleport($this->dat[$player->getName()]['pos']);
    //$this->plugin->getScheduler()->scheduleDelayedTask(
      //new Callback([$this, 'ActionTask'], [$field, $poss, $pos, $player, $players, $atk, $type]), 18);
  }

  public function ActionTask($field, $poss, $pos, $player, $players, $atk, $type){
    $posd = array_chunk($poss, count($poss)/4);
    $this->addParticles($field, $posd[0], Particle::TYPE_DRAGON_DESTROY_BLOCK, 2);
    $this->addCustomParticle('PVE:WINDATTACK', $pos, $players);
    if($field == 'spawn')
      return false;
    if(!isset($this->plugin->mob->mobs[$field])) return false;
    $mobs = $this->plugin->mob->mobs[$field];
    foreach($mobs as $eid => $data){
      $mpos = new Vector3($data['x'], $data['y'], $data['z']);
      if($this->distance($pos, $mpos) < 10){
        $this->plugin->mob->CustomAttack($atk * 3, $player, $field, $eid, $type);
      }
    }
  }
}