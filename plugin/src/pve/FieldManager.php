<?php
namespace pve;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\math\Vector3;

use pve\dungeon\DungeonManager;

class FieldManager implements Listener {
  const FIELD_FORM_ID = 0;
  const SPAWN = 'Lobelia';

  public function __construct($plugin){
    $this->plugin = $plugin;
    $this->cooltime = [];
    $this->data = [];
    $this->level = $plugin->getServer()->getWorldManager()->getDefaultWorld();
  }
  
  public function onJoin(PlayerJoinEvent $event){
    $player = $event->getPlayer();
    $this->data[$player->getName()] = 'untiburi';
    if(!isset($this->plugin->lastData[$player->getName()])){
      $this->plugin->lastData[$player->getName()] = self::SPAWN;
      $this->changeField($player, $this->data[$player->getName()], self::SPAWN);
    }else{
      $this->toLastField($player);
    }
    $this->now[$player->getName()] = false;
  }
  
  public function onQuit($event){
    $player = $event->getPlayer();
    $this->QuitField($player);
  }

  
  public function onInteract(PlayerInteractEvent $event){
    $player = $event->getPlayer();
    $name = $player->getName();
    if(!array_key_exists($name, $this->cooltime))
      $this->cooltime[$name] = 0;
		$time = microtime(true);
    $num = $time - $this->cooltime[$name];
    if($num < 0.25){
			return false;
    }
    $this->cooltime[$name] = $time;
    if($event->getBlock()->getId() === 133){
      if($this->now[$player->getName()]) return false;
      $this->plugin->form->sendFieldForm($player);
      $this->now[$player->getName()] = true;
    }
    $pos = $event->getBlock()->getPosition();
    $data = (int)$pos->y * (10**8) + (int)$pos->x * (10**4) + (int) $pos->z;
    if(isset($this->plugin->tpData[$data])){
      $this->changeField($player, $this->getField($player), $this->plugin->tpData[$data]);
    }
    if(isset($this->plugin->dungeonData[$data])){
      $this->plugin->form->sendDungeonForm($player, $this->plugin->dungeonData[$data]);
    }
  }
  
  public function onDeath(PlayerDeathEvent $event){
    $player = $event->getPlayer();
    $event->setKeepInventory(true);
    $event->setDeathMessage('');
    $this->changeField($player, $this->data[$player->getName()], self::SPAWN);
  }
  
  public function changeField($player, $old, $new, $title = true, $dungeon = false){
    $pos = $this->plugin->fieldData[$new];
    if(DungeonManager::isDungeon($player)){
      DungeonManager::getDungeonByPlayer($player)->Death($player);
    }
    $this->plugin->animation->CancelAnimation($player);
    if(isset($this->plugin->playerData[$player->getName()])){
      $quest = $this->plugin->playerData[$player->getName()]['quest'];
      if(isset($pos['req'])){
        if(!(in_array($pos['req'], $quest))){
          $this->now[$player->getName()] = false;
          $this->plugin->lbas->sendMessages($player, ['どうやらまだ移動できないようですね...']);
          return false;
        }
      }
    }
    if($old !== 'untiburi'){
      $this->plugin->mob->FieldKill($player, $old);
      unset($this->data[$player->getName()]);
    }
    $forge = $this->plugin->forgeData;
    if(isset($forge[$old])) $this->plugin->forge->remove($player, $old);
    if(isset($forge[$new])) $this->plugin->forge->sendPacket($player, $new);
    $shop = $this->plugin->shopperData;
    if(isset($shop[$old])) $this->plugin->shop->remove($player, $old);
    if(isset($shop[$new])) $this->plugin->shop->sendPacket($player, $new);
    $this->plugin->npc->remove($player, $old);
    $this->plugin->npc->sendPacket($player, $new);
    $this->data[$player->getName()] = $new;
    $player->teleport(new Vector3($pos['x'], $pos['y'], $pos['z']));
    $this->plugin->mob->FieldSpawn($player, $new);
    if($title)
      $player->sendTitle($new);
    $this->now[$player->getName()] = false;
    if(!$this->isBattleField($new))
      $this->plugin->lastData[$player->getName()] = $new;
    if(!$dungeon)
      $this->plugin->lastBattleData[$player->getName()] = $new;
  }
  
  public function QuitField($player){
    unset($this->data[$player->getName()]);
  }
  
  public function getPlayers($field){
    $players = [];
    foreach($this->data as $name => $pfield){
      if($field === $pfield){
        $players[] = $this->plugin->getServer()->getPlayerExact($name);
      }
    }
    return $players;
  }
  
  public function getField($player){
    $result = null;
    $name = $player->getName();
    if(isset($this->data[$name])) $result = $this->data[$name];
    return $result;
  }
  
  public function toSpawn($player){
    $this->changeField($player, $this->data[$player->getName()], self::SPAWN);
  }

  public function toLastField($player, $title = true){
    $this->changeField($player, $this->data[$player->getName()], $this->plugin->lastData[$player->getName()], $title);
  }

  public function toLastBattleField($player, $title = true){
    $this->changeField($player, $this->data[$player->getName()], $this->plugin->lastBattleData[$player->getName()], $title);
  }

  public function toHomeField($player, $home){
    $this->changeField($player, $this->data[$player->getName()], $home);
  }
  
  public function setNow($player){
    $this->now[$player->getName()] = false;
  }

  public function getLevel(){
    return $this->level;
  }

  public function isBattleField($field){
    if($this->plugin->fieldData[$field]['isbattle'])
      return true;
    return false;
  }

}
?>
  
