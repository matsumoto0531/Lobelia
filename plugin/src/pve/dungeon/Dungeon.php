<?php
namespace pve\dungeon;

use pocketmine\math\Vector3;

use pve\item\ItemManager;
use pve\WeaponManager;
use pve\ArmorManager;
use pve\Callback;
use pve\inventory\GiftWindow;

class Dungeon {

    const FIELD_NAMES = ['§a§l剛風§fの間', '§c§l豪炎§fの間', '§e§l光明§fの間', '§b§l凍氷§fの間', 'uncho', 'unp'];
    const SWORD_RECIPES = [];
    const ARMOR_RECIPES = [];
    const ORB_RECIPES = [];

    public function __construct($plugin){
        $this->plugin = $plugin;
        $this->money = 10;
        foreach(self::FIELD_NAMES as $name){
            $this->plugin->dddata[$name] = null;
            $this->data[$name] = null;
        }
        $this->id = 0;
        $this->mobs = [
            ['shadow' => 1, 'Camellia' => 2],
        ];
        $this->bossfloor = [];
        $this->maxfloor = 0;
        $this->danger = 1;
        $this->lv = 1;
        $this->name = '';
    }

    public function onStart($players, $guild = false){
        $flag = 0;
        foreach($this->data as $name => $data){
            if(!is_null($this->plugin->dddata[$name])) continue;
            $flag = 1;
            $this->plugin->dddata[$name] = true;
            $this->data[$name]['players'] = $players;
            $this->data[$name]['danger'] = $this->danger;
            $this->data[$name]['floor'] = 0;
            $this->data[$name]['eid'] = [];
            $this->data[$name]['guild'] = $guild;
            $this->data[$name]['time'] = microtime(true);
            foreach($this->data[$name]['players'] as $player){
                if($this->isDungeon($player)){
                    $this->data[$name]['players'] = array_diff($this->data[$name]['players'], [$player]);
                    $this->data[$name]['players'] = array_values($this->data[$name]['players']);
                    continue;
                }
                DungeonManager::addDungeon($player, $this->id);
                $field = $this->plugin->fieldmanager->getField($player);
                $this->plugin->fieldmanager->changeField($player, $field, $name, false, true);
                $player->sendTitle($this->name);
                $this->data[$name]['gift'][$player->getName()] = [];
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

    public function addGift($name, $player, $items){
        $pname = $player->getName();
        $this->data[$name]['gift'][$pname] = array_merge($this->data[$name]['gift'][$pname], $items);
    }

    public function giveGift($name, $player, $items){
        $inv = new GiftWindow();
        if(!isset($items)) return false;
        $inv->setContents($items);
        $player->setCurrentWindow($inv);
    }

    public function nextFloor($name){
        if(!isset($this->data[$name])) return false;
        if($this->data[$name]['floor'] === $this->maxfloor){
            $this->clear($name);
            return false;
        }
        foreach($this->data[$name]['players'] as $player){
            $player->sendTitle(($this->data[$name]['floor']+1).'層');
        }
        $this->data[$name]['floor']++;
        $this->summonMobs($name);
    }

    public function addMobs($name, $mobname, $count, $lv = 1, $isboss = 1, $hp = 1){
        for($i = 0; $i < $count; $i++){
            if(!isset($this->data[$name])) return false;
            $pos = $this->plugin->fieldData[$name];
            $poss = ['x' => $pos['x'] + mt_rand(-5, 5), 'y' => $pos['y'], 'z' => $pos['z'] + mt_rand(-5, 5)];
            $eid = $this->plugin->mob->CustomSpawn($name, $mobname, $lv, $isboss, $poss, $hp);
            $this->data[$name]['eid'][] = $eid;
            $player = $this->data[$name]['players'][array_rand($this->data[$name]['players'], 1)];
            $this->plugin->mob->setTarget($name, $eid, $player);
        }
    }

    public function summonMobs($name){
        if(in_array($this->data[$name]['floor'], $this->bossfloor)){
            foreach($this->mobs[$this->data[$name]['floor']-1] as $boss => $count){
              for($i = 0; $i < $count; $i++){
                if(!isset($this->data[$name])) return false;
                $lv = $this->lv;
                $pos = $this->plugin->fieldData[$name];
                $poss = ['x' => $pos['x'] + mt_rand(-5, 5), 'y' => $pos['y'], 'z' => $pos['z'] + mt_rand(-5, 5)];
                $eid = $this->plugin->mob->CustomSpawn($name, $boss, $lv, 1, $poss);
                $this->data[$name]['eid'][] = $eid;
                $player = $this->data[$name]['players'][array_rand($this->data[$name]['players'], 1)];
                $this->plugin->mob->setTarget($name, $eid, $player);
              }
            }
        }else{
            foreach($this->mobs[$this->data[$name]['floor']-1] as $mob => $count){
              for($i = 0; $i < $count; $i++){
                if(!isset($this->data[$name])) return false;
                $lv = $this->lv;
                $pos = $this->plugin->fieldData[$name];
                $poss = ['x' => $pos['x'] + mt_rand(-3, 3), 'y' => $pos['y'], 'z' => $pos['z'] + mt_rand(-3, 3)];
                $eid = $this->plugin->mob->CustomSpawn($name, $mob, $lv, 0, $poss);
                $this->data[$name]['eid'][] = $eid;
                $player = $this->data[$name]['players'][array_rand($this->data[$name]['players'], 1)];
                $this->plugin->mob->setTarget($name, $eid, $player);
              }
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
                    $player->sendTitle('§a§l突破!!');
                }
                $this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'nextFloor'], [$name]), 5 * 20);
            } 
        }
        return true;
    }

    public function clear($name){
        $players = $this->data[$name]['players'];
        $danger = $this->data[$name]['danger'];
        $guild = $this->data[$name]['guild'];
        $gifts = $this->data[$name]['gift'];
        $time = microtime(true) - $this->data[$name]['time'];
        $time = round($time, 1);
        $this->data[$name] = null;
        $this->plugin->dddata[$name] = null;
        foreach($players as $player){
            $player->sendTitle('§e§lCLEAR!!');
            $message = "§a§lDUNGEON§f§r>>{$this->name}クリア！！\n§7クリアタイム§f: {$time}秒";
            $player->sendMessage($message);
            $this->plugin->playermanager->addMoney($player, $this->money);
            $this->plugin->fieldmanager->toLastBattleField($player, false);
            DungeonManager::removeDungeon($player);
            $player->setImmobile(true);
            $this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'giveGift'], [$name, $player, $gifts[$player->getName()]]), 5);
        }
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
        DungeonManager::removeDungeon($player);
        $player->setImmobile(true);
        $this->plugin->getScheduler()->scheduleDelayedTask(new Callback([$this, 'giveGift'], [$name, $player, $this->data[$name]['gift'][$player->getName()]]), 5);
        if(!count($this->data[$name]['players'])){
            $this->data[$name] = null;
            $this->plugin->dddata[$name] = null;
            $this->plugin->mob->FieldKill($player, $name);
            unset($this->plugin->mob->mobs[$name]);
        }
    }

    public function addRecipes($name){
        $players = $this->data[$name]['players'];
        foreach($players as $player){
          $items = [];
          foreach(static::SWORD_RECIPES as $id => $per){
              if(mt_rand(0, 10000) <= $per){
                  $items[] = $this->plugin->recipe->getSwordRecipe($id, mt_rand(1, 4));
              }
          }
          for($i = 0; $i < 4; $i++){
            foreach(static::ARMOR_RECIPES as $id => $per){
                if(mt_rand(0, 10000) <= $per){
                    $items[] = $this->plugin->recipe->getArmorRecipe($id, mt_rand(1, 4));
                }
            }
          }
          foreach(static::ORB_RECIPES as $id => $per){
              if(mt_rand(0, 10000) <= $per){
                  $items[] = $this->plugin->recipe->getOrbRecipe($id, mt_rand(1, 4));
              }
          }
          $message = "";
          foreach($items as $item){
              $message .= $item->getName()."、";
              $player->getInventory()->addItem($item);
          }
          if($message !== "")
          $message .= "§7を手に入れた";
          $player->sendMessage($message);
          $this->plugin->playermanager->addHp($player, 10000);
        }
    }

    public function isDungeon($player){
        $name = $this->plugin->fieldmanager->getField($player);
        if(!isset($this->data[$name])) return false;
        foreach($this->data[$name]['players'] as $p){
            if($p == $player) return true;
        }
        return false;
    }



}