<?php
namespace pve;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

use pocketmine\math\Vector3;

class GuildManager implements Listener{
    
    const DEFAULT = '';

    public function __construct($plugin){
        $this->plugin = $plugin;
        $this->req = [];
    }

    public function onJoin(PlayerJoinEvent $event){
        $player = $event->getPlayer();
        $name = $player->getName();
        if(!isset($this->plugin->playerData[$name]['guild'])) $this->plugin->playerData[$name]['guild'] = self::DEFAULT;
        $this->plugin->playermanager->setName($player);
     }

    public function addGuild($guild, $player){
        $name = $player->getName();
        $this->plugin->playerData[$name]['guild'] = $guild;
        $player->sendMessage('§eGuild§f>>'.$guild.'への参加が認められました!');
        $this->plugin->playermanager->setName($player);
    }

    public function unsetGuild($player){
        $name = $player->getName();
        $this->plugin->playerData[$name]['guild'] = self::DEFAULT;
        $this->plugin->playermanager->setName($player);
    }

    public function makeGuild($guild, $player){
        $name = $player->getName();
        if(isset($this->plugin->guildData[$guild])){
          $player->sendMessage('§eGuild§f>>その名前は使用済みです！');
        }
        $this->plugin->guildData[$guild]['leader'] = $name;
        $this->plugin->guildData[$guild]['exp'] = 0;
        $this->plugin->playerData[$name]['guild'] = $guild;
        $player->sendMessage('§eGuild§f>>'.$guild.'を設立しました！');
        $this->plugin->playermanager->setName($player);
    }

    public function setGuildPoint($guild, $pos, $field){
        $this->plugin->guildData[$guild]['point'] = [
            'x' => $pos->x,
            'y' => $pos->y,
            'z' => $pos->z,
            'field' => $field
        ];
    }

    public function deleteGuild($player){
        $name = $player->getName();
        $guild = $this->plugin->playerData[$name]['guild'];
        unset($this->plugin->guildData[$guild]);
        foreach($this->plugin->playerData as $n => $data){
            if($data['guild'] == $guild){
              $this->plugin->playerData[$n]['guild'] = '';
              $p = $this->plugin->getServer()->getPlayer($n);
			  if(isset($p)){
                $p->sendMessage('ギルドが解散されました。');
                $this->plugin->playermanager->setName($p);
              }
            }
        }
    }

    public function addReq($guild, $player){
        $this->req[$guild][] = $player->getName();
    }

    public function isReq($guild, $player){
        if(in_array($player->getName(), $this->req[$guild]))
          return true;
        return false;
    }

    public function isExist($guild){
      if(!isset($this->plugin->guildData[$guild])) return false;
      return true;
    }

    public function isLeader($player){
      $guild = $this->plugin->playerData[$player->getName()]['guild'];
      if(!isset($this->plugin->guildData[$guild])) return false;
      if($this->plugin->guildData[$guild]['leader'] === $player->getName())
        return true;
      return false;
    }

    public function TP($player){
      $name = $player->getName();
      $guild = $this->plugin->playerData[$name]['guild'];
      if(!isset($this->plugin->guildData[$guild])){
        $player->sendMessage('Guildに参加していません。');
        return false;
      }
      if(!isset($this->plugin->guildData[$guild]['point'])){
        $player->sendMessage('GuildPointが設定されていません。');
        return false;
      }
      $now = $this->plugin->fieldmanager->getField($player);
      $this->plugin->fieldmanager->changeField($player, $now, $this->plugin->guildData[$guild]['point']['field']);
      $pos = new Vector3($this->plugin->guildData[$guild]['point']['x'], $this->plugin->guildData[$guild]['point']['y'], $this->plugin->guildData[$guild]['point']['z']);
      $player->teleport($pos);
      $player->sendMessage('TPしました。');
    }

    public function isJoin($player){
      $name = $player->getName();
      if($this->plugin->playerData[$name]['guild'] === '') return false;
      return true;
    }

    public function getGuild($player){
      $name = $player->getName();
      return $this->plugin->playerData[$name]['guild'];
    }

    public function addExp($guild, $amount){
      $this->plugin->guildData[$guild]['exp'] += $amount;
    }

    public function getExp($guild){
      return $this->plugin->guildData[$guild]['exp'];
    }

    public function getOnlinePlayers($guild){
      $p = [];
      $players = $this->plugin->getServer()->getOnlinePlayers();
      foreach($players as $player){
        $g = $this->getGuild($player);
        if($g === $guild){
          $p[] = $player;
        }
      }
      return $p;
    }
    
} 