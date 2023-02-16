<?php
namespace pve\quest;

use pve\mobs\Mobs;

class Quest4 extends Quest {
    const ID = 4;
    const NAME = '§bMajalis§l討伐';
    const DES = 'Majalisを50体倒す';

    const COUNT = 50;
    const TARGET = 'Majalis';

    public function onTouch($player, $nn){
        $name = $player->getName();
        if(isset($this->data[$name])){
            if($this->data[$name] >= self::COUNT){
                $this->clear($player, $nn);
                unset($this->data[$name]);
            }else{
                $player->sendMessage('§a'.$nn.'§f>>Majalis50体ヨロです!');
            }
        }else{
            if(in_array(self::ID, $this->plugin->playerData[$name]['quest'])){
                $this->cleard($player, $nn);
            }else{
                if(in_array(3, $this->plugin->playerData[$name]['quest'])){
                    $this->start($player);
                    $player->sendMessage('§a'.$nn.'§f>>Majalis50体ヨロです!');
                    $this->data[$name] = 0;
                }else{
                    $player->sendMessage('§a'.$nn.'§f>>I am sleepy...');
                }
            }
        }
    }

    public function clear($player, $nn){
        parent::clear($player, $nn);
        $name = $player->getName();
        $player->sendMessage('§a'.$nn.'§f>>BIG TY!');
        $this->plugin->playerData[$name]['quest'][] = self::ID;
        $this->plugin->playermanager->addMoney($player, 500);
        $this->plugin->playermanager->addExp($player, 300);
    }

    public function cleard($player, $nn){
        $player->sendMessage('§a'.$nn.'§f>>BIG TY!');
    }

    public function onKill($player, $n){
        $name = $player->getName();
        if(!isset($this->data[$name])) return false;
        if($n === self::TARGET){
          $this->data[$name]++;
          if($this->data[$name] >= self::COUNT){
              $player->sendMessage('§aQuest§f>>条件達成!依頼者に報告しよう!');
              return true;
          }
          $player->sendMessage('§aQuest§f>>'.$this->data[$name].'体目!');
        }
    }
}