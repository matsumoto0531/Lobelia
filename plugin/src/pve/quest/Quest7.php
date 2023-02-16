<?php
namespace pve\quest;

use pve\mobs\Mobs;

class Quest7 extends Quest {
    const ID = 7;
    const NAME = '§9龍§f退治';
    const DES = 'Exacumを討伐する。';

    const COUNT = 2;
    const TARGET = 'Exacum';

    public function onTouch($player, $nn){
        $name = $player->getName();
        if(isset($this->data[$name])){
            if($this->data[$name] >= self::COUNT){
                $this->clear($player, $nn);
                unset($this->data[$name]);
            }else{
                $player->sendMessage('§a'.$nn.'§f>>しっかり、準備を整えてから挑むのだぞ。');
                $player->sendMessage('§a'.$nn.'§f>>奴の分身にはくれぐれも気を付けるように');
            }
        }else{
            if(in_array(self::ID, $this->plugin->playerData[$name]['quest'])){
                $this->cleard($player, $nn);
            }else{
                if(in_array(6, $this->plugin->playerData[$name]['quest'])){
                    $this->start($player);
                    $player->sendMessage('§a'.$nn.'§f>>守護者を倒したか。');
                    $player->sendMessage('§a'.$nn.'§f>>ならば、もはや残すは龍のみだな。');
                    $player->sendMessage('§a'.$nn.'§f>>このクエストが終われば、次の町へ行くことを認めよう。');
                    $this->data[$name] = 0;
                }else{
                    $player->sendMessage('§a'.$nn.'§f>>健闘を祈る。');
                }
            }
        }
    }

    public function clear($player, $nn){
        parent::clear($player, $nn);
        $name = $player->getName();
        $player->sendMessage('§a'.$nn.'§f>>見事だ！');
        $this->plugin->playerData[$name]['quest'][] = self::ID;
        $this->plugin->playermanager->addMoney($player, 5000);
        $this->plugin->playermanager->addExp($player, 1000);
    }

    public function cleard($player, $nn){
        $player->sendMessage('§a'.$nn.'§f>>達者でな。');
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