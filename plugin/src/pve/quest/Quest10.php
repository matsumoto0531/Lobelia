<?php
namespace pve\quest;

use pve\mobs\Mobs;

class Quest10 extends Quest {
    const ID = 10;
    const NAME = '§aBellis§l討伐';
    const DES = 'Bellisを300体倒す';

    const COUNT = 300;
    const TARGET = 'Bellis';

    public function onTouch($player, $nn){
        $name = $player->getName();
        if(isset($this->data[$name])){
            if($this->data[$name] >= self::COUNT){
                $this->clear($player, $nn);
                unset($this->data[$name]);
            }else{
                $player->sendMessage('§a'.$nn.'§f>>Bellis300体、お願いします。');
            }
        }else{
            if(in_array(self::ID, $this->plugin->playerData[$name]['quest'])){
                $this->cleard($player, $nn);
            }else{
                if(in_array(9, $this->plugin->playerData[$name]['quest'])){
                    $this->start($player);
                    $player->sendMessage('§a'.$nn.'§f>>暇だなぁ');
                    $this->data[$name] = 0;
                }else{
                    $player->sendMessage('§a'.$nn.'§f>>元気が一番！');
                }
            }
        }
    }

    public function clear($player, $nn){
        parent::clear($player, $nn);
        $name = $player->getName();
        $player->sendMessage('§a'.$nn.'§f>>ありがとう！');
        $this->plugin->playerData[$name]['quest'][] = self::ID;
        $this->plugin->playermanager->addMoney($player, 30000);
        $this->plugin->playermanager->addExp($player, 30000);
    }

    public function cleard($player, $nn){
        $player->sendMessage('§a'.$nn.'§f>>ありがとうございました。');
    }

    public function onKill($player, $n){
        $name = $player->getName();
        if(!isset($this->data[$name])) return false;
        if($n === self::TARGET){
          if($this->data[$name] >= self::COUNT){
              $player->sendMessage('§aQuest§f>>条件達成!依頼者に報告しよう!');
              return true;
          }
          $this->data[$name]++;
          $player->sendMessage('§aQuest§f>>'.$this->data[$name].'体目!');
        }
    }
}