<?php
namespace pve\quest;

use pve\mobs\Mobs;

class Quest2 extends Quest {
    const ID = 2;
    const NAME = '§aCamellia§l討伐';
    const DES = 'Camelliaを50体倒す';

    const COUNT = 50;
    const TARGET = 'Camellia';

    public function onTouch($player, $nn){
        $name = $player->getName();
        if(isset($this->data[$name])){
            if($this->data[$name] >= self::COUNT){
                $this->clear($player, $nn);
                unset($this->data[$name]);
            }else{
                $player->sendMessage('§a'.$nn.'§f>>Camellia50体、お願いできますか?');
            }
        }else{
            if(in_array(self::ID, $this->plugin->playerData[$name]['quest'])){
                $this->cleard($player, $nn);
            }else{
                if(true){//in_array(1, $this->plugin->playerData[$name]['quest'])){
                    $this->start($player);
                    $player->sendMessage('§a'.$nn.'§f>>Camelliaのせいで散歩にいけなくて困ってるんです');
                    $this->data[$name] = 0;
                }else{
                    $player->sendMessage('§a'.$nn.'§f>>今日も散歩びよりだなあ');
                }
            }
        }
    }

    public function clear($player, $nn){
        parent::clear($player, $nn);
        $name = $player->getName();
        $player->sendMessage('§a'.$nn.'§f>>ありがとうございます！');
        $this->plugin->playerData[$name]['quest'][] = self::ID;
        $this->plugin->playermanager->addMoney($player, 500);
        $this->plugin->playermanager->addExp($player, 100);
    }

    public function cleard($player, $nn){
        $player->sendMessage('§a'.$nn.'§f>>おかげさまで散歩に出られるよ！');
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