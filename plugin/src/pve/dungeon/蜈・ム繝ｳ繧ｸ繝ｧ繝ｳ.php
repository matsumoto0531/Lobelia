<?php
namespace pve\dungeon;

use pocketmine\math\Vector3;

use pve\item\ItemManager;
use pve\WeaponManager;
use pve\ArmorManager;
use pve\Callback;

class Dungeon {

    const FIELD_NAMES = ['§a§l剛風§fの間', '§c§l豪炎§fの間', '§e§l光明§fの間', '§b§l凍氷§fの間'];

    public function __construct($plugin){
        $this->plugin = $plugin;
        foreach(self::FIELD_NAMES as $name){
            $this->data[$name] = null;
        }
        $this->boss = ['Dahlia', 'Clematis', 'Amaryllis', 'Salvia', 'Golem', 'Cattleya'];
        $this->mobs = ['Bellis', 'Camellia', 'Gerbera', 'Kerria', 'Lactiflora', 'Majalis', 'Pelargonium', 'Platy', 'Tuberose', 'Viola'
                       , 'Anemone', 'Iris'];
        $this->gifts = [8, 7, 13, 10, 14];
        $this->armor = ['古びた防具', '§7摩耗した§f防具', '§l§6伝§a説§fの§d防具'];
        $this->skills = [[5], [5], [7], [3], [4, 5], [7], [8, 7], [9, 10], [10, 7]];
        $this->skilltable = [[0], [0], [0], [1], [1, 0], [1], [1, 0], [1, 0], [1, 1]];
        $this->ids = [1, 26, 29, 9, 18, 28, 16, 27, 6, 7, 10, 4, 15, 24, 19, 17, 13, 12, 11, 33, 5];
        $this->rareid = [30, 31, 32, 25];
        $this->weapon = ['古びた武器', '§7摩耗した§f武器', '§l§6伝§a説§fの§d武器'];
        $this->wids = [257, 268, 272, 276, 283];
        $this->wper = [0, 0, 20, 50, 80, 100, 90, 80, 70];
        $this->wtable = [0, 0, 0, 0, 0, 0, 1, 1, 1];
        $this->rarewids = [14, 15, 16, 17];
        $this->wskills = [3, 4, 5, 6, 2];
        $this->rank = ['凡庸', '§b凄腕', '§c§l歴戦'];
        $this->floor = [20, 40, 70, 90, 100];
        $this->startfloor = [0, 0, 40, 60, 70];
        $this->defs = [70, 100, 150, 250, 350, 450, 600, 650, 700];
        $this->atks = [700, 1250, 1800, 3000, 3500, 4000, 5400, 5900, 6400];
        $this->bosslv = [2, 5, 6, 9, 15, 30, 45, 60, 80, 100];
    }

    public function onStart($players, $danger, $guild = false){
        $flag = 0;
        foreach($this->data as $name => $data){
            if(!is_null($data)) continue;
            $flag = 1;
            $this->data[$name]['players'] = $players;
            $this->data[$name]['danger'] = $danger;
            $this->data[$name]['floor'] = $this->startfloor[$danger - 1];
            $this->data[$name]['eid'] = [];
            $this->data[$name]['guild'] = $guild;
            foreach($players as $player){
                if($this->isDungeon($player)){
                    $this->data[$name]['players'] = array_diff($this->data[$name]['players'], [$player]);
                }
                $field = $this->plugin->fieldmanager->getField($player);
                $this->plugin->fieldmanager->changeField($player, $field, $name);
            }
            $this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'nextFloor'], [$name]), 7 * 20);
            break;
        }
        if(!$flag){
            foreach($players as $player){
                $player->sendMessage('§eINFO§f>>ダンジョンが満員です。');
            }
        }
    }

    public function nextFloor($name){
        if(!isset($this->data[$name])) return false;
        if($this->data[$name]['floor'] === $this->floor[$this->data[$name]['danger']-1]){
            $this->clear($name);
            return false;
        }
        foreach($this->data[$name]['players'] as $player){
            $player->addTitle(($this->data[$name]['floor']+1).'層');
        }
        $this->data[$name]['floor']++;
        $this->summonMobs($name);
    }

    public function addGift($name){
        $amount = $this->data[$name]['danger'] * $this->data[$name]['floor'] * 500;
        $players = $this->data[$name]['players'];
        foreach($players as $player){
          $this->plugin->playermanager->addMoney($player, $amount);
          $this->plugin->playermanager->addExp($player, $amount);
        }
        if($this->data[$name]['floor'] % 10 === 0){
          $f = $this->data[$name]['floor'] / 10;
          if($f >= 1) $this->addItem($players);
          if($f >= 2) $this->addItem($players);
          if($f >= 2) $this->addArmor($players, $amount = $this->data[$name]['danger'] * $this->data[$name]['floor'], $f);
          if($f >= 2) $this->addWeapon($players, $amount = $this->data[$name]['danger'] * $this->data[$name]['floor'], $f);
          //if($f === 5 || $f === 9) $this->addLevelUpper($players);
        }
    }

    public function addItem($players){
        foreach($players as $player){
            $id = $this->gifts[mt_rand(0, count($this->gifts)-1)];
            $item = ItemManager::getItem($id)->getItem()->setCount(mt_rand(5, 13));
            $player->sendMessage($item->getName().'§fx'.$item->getCount().'§7を手に入れた');
            $player->getInventory()->addItem($item);
        }
    }

    public function addArmor($players, $sisuu, $f){
        if($f >= 2){
            $name = $this->armor[0];
        }
        if($f >= 5){
            $name = $this->armor[1];
        }
        if($f >= 9){
            $name = $this->armor[2];
        }
        foreach($players as $player){
            $defdata = $this->defs[$f-2];
            $def = mt_rand($defdata * 0.9, $defdata * 1.1);
            $itemid = mt_rand(298, 317);
            $type = mt_rand(0, 6);
            $rank = mt_rand(0, 2);
            $hosei = 0.8 + ($rank / 10);
            $data = ['def' => $def, 'itemid' => $itemid, 'type' => $type, 'name' => $name];
            $item = ArmorManager::getArmor()->getCustomItem($data, 999, $hosei);
            $player->sendMessage($item->getName().'§fx'.$item->getCount().'§7を手に入れた');
            $skills = $this->skills[$f-2];
            $count = 0;
            foreach($skills as $skill){
              if($this->skilltable[$f-2][$count] and mt_rand(0, 100) <= 2*$f){
                $id = $this->rareid[mt_rand(0, count($this->rareid)-1)];
              }else{
                $id = $this->ids[mt_rand(0, count($this->ids)-1)];
              }
              $lv = mt_rand($skill-1, $skill+1);
              if(mt_rand(0, 3) >= 1){
                $item = ArmorManager::getArmor()->setSkill($item, $id, $lv, $count);
                if($lv >= 5)
                  ArmorManager::getArmor()->addEnch($item);
              }
              $count++;
            }
            $item->setCustomName($item->getName().' '.$this->rank[$rank]);
            $player->getInventory()->addItem($item);
        }
    }

    public function addWeapon($players, $sisuu, $f){
        if($f >= 2){
            $name = $this->weapon[0];
        }
        if($f >= 5){
            $name = $this->weapon[1];
        }
        if($f >= 9){
            $name = $this->weapon[2];
        }
        foreach($players as $player){
            $atkdata = $this->atks[$f-2];
            $atk = mt_rand($atkdata * 0.9, $atkdata * 1.1);
            $sharp = mt_rand(100, 1200);
            $atk = $atk * (300 / $sharp);
            $itemid = $this->wids[mt_rand(0, count($this->wids)-1)];
            $type = mt_rand(0, 6);
            $rank = mt_rand(0, 2);
            $hosei = 0.8 + ($rank / 10);
            $data = ['atk' => $atk, 'sharp' => $sharp, 'def' => 0, 'itemid' => $itemid, 'type' => $type, 'name' => $name];
            $item = WeaponManager::getWeapon()->getCustomItem($data, 999, $hosei);
            $player->sendMessage($item->getName().'§fx'.$item->getCount().'§7を手に入れた');
            if(mt_rand(0, 100) <= $this->wper[$f-2]){
              $skillid = $this->wskills[mt_rand(0, count($this->wskills)-1)];
              $item = WeaponManager::getWeapon()->setSkill($item, $skillid);
            }elseif($this->wtable[$f-2]){
              $skillid = $this->rarewids[mt_rand(0, count($this->rarewids)-1)];
              $item = WeaponManager::getWeapon()->setSkill($item, $skillid);
            }
            $item->setCustomName($item->getName().' '.$this->rank[$rank]);
            $player->getInventory()->addItem($item);
        }
    }

    public function summonMobs($name){
        if($this->data[$name]['floor'] % 10 === 0){
            $count = floor($this->data[$name]['floor'] / 40)+1;
            $f = $this->data[$name]['floor'] / 10;
            for($i = 0; $i < $count; $i++){
              if(!isset($this->data[$name])) return false;
              $boss = $this->boss[mt_rand(0, count($this->boss) -1)];
              $lv = $this->bosslv[$f-1];
              $pos = $this->plugin->fieldData[$name];
              $poss = ['x' => $pos['x'] + mt_rand(-5, 5), 'y' => $pos['y'], 'z' => $pos['z'] + mt_rand(-5, 5)];
              $eid = $this->plugin->mob->CustomSpawn($name, $boss, $lv, 1, $poss);
              $this->data[$name]['eid'][] = $eid;
              $player = $this->data[$name]['players'][array_rand($this->data[$name]['players'], 1)];
              $this->plugin->mob->setTarget($name, $eid, $player);
            }
        }else{
            $count = floor($this->data[$name]['floor'] / 40)+1;
            for($i = 0; $i < $count * 5; $i++){
              if(!isset($this->data[$name])) return false;
              $mob = $this->mobs[mt_rand(0, count($this->mobs) - 1)];
              $lv = round(($this->data[$name]['danger'] * $this->data[$name]['floor']) / 5) + 1;
              $pos = $this->plugin->fieldData[$name];
              $poss = ['x' => $pos['x'] + mt_rand(-3, 3), 'y' => $pos['y'], 'z' => $pos['z'] + mt_rand(-3, 3)];
              $eid = $this->plugin->mob->CustomSpawn($name, $mob, $lv, 0, $poss);
              $this->data[$name]['eid'][] = $eid;
              $player = $this->data[$name]['players'][array_rand($this->data[$name]['players'], 1)];
              $this->plugin->mob->setTarget($name, $eid, $player);
            }
        }
    }

    public function onKill($name, $eid){
        if(!isset($this->data[$name])) return false;
        if(in_array($eid, $this->data[$name]['eid'])){
            $this->data[$name]['eid'] = array_diff($this->data[$name]['eid'], [$eid]);
            $this->data[$name]['eid'] = array_values($this->data[$name]['eid']);
            if(!count($this->data[$name]['eid'])){
                foreach($this->data[$name]['players'] as $player){
                    $player->addTitle('§a§l突破!!');
                }
                $this->addGift($name);
                $this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'nextFloor'], [$name]), 5 * 20);
            } 
        }
        return true;
    }

    public function clear($name){
        $message = '§a§lCONGRATURATON§f>>';
        $players = $this->data[$name]['players'];
        $danger = $this->data[$name]['danger'];
        $guild = $this->data[$name]['guild'];
        $this->addArmor($players, 1, $this->floor[$this->data[$name]['danger']-1]/10);
        $this->addWeapon($players, 1, $this->floor[$this->data[$name]['danger']-1]/10);
        $this->addArmor($players, 1, $this->floor[$this->data[$name]['danger']-1]/10);
        $this->addWeapon($players, 1, $this->floor[$this->data[$name]['danger']-1]/10);
        $this->data[$name] = null;
        foreach($players as $player){
            $player->sendTitle('§e§lCLEAR!!');
            $message .= $player->getName().'さん、';
            $this->plugin->fieldmanager->toLastField($player);
        }
        if(!$guild){
          $message .= 'らが危険度'.$danger.'のダンジョンを突破しました！';
        }else{
          $guild = $this->plugin->guild->getGuild($players[array_rand($players, 1)]);
          $message = '§a§lCONGRATURATON§f>>'.$guild.'が危険度'.$danger.'のダンジョンを突破しました！';
        }
        $this->plugin->getServer()->broadcastMessage($message);
    }

    public function Death($player){
        if(!$this->isDungeon($player)) return false;
        $name = $this->plugin->fieldmanager->getField($player);
        $this->data[$name]['players'] = array_diff($this->data[$name]['players'], [$player]);
        $this->data[$name]['players'] = array_values($this->data[$name]['players']);
        $player->sendMessage('ダンジョンから離脱してしまいました...');
        foreach($this->data[$name]['players'] as $p){
            $p->sendMessage('§eINFO§f>>'.$player->getName().'さんが離脱しました！');
        }
        if(!count($this->data[$name]['players'])){
            $this->data[$name] = null;
            $this->plugin->mob->FieldKill($player, $name);
            unset($this->plugin->mob->mobs[$name]);
        }
    }

    public function isDungeon($player){
        $name = $this->plugin->fieldmanager->getField($player);
        if(!isset($this->data[$name])) return false;
        foreach($this->data[$name]['players'] as $p){
            if($p === $player) return true;
        }
        return false;
    }



}