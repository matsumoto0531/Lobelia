<?php
namespace pve\quest;

use pve\mobs\Mobs;

class Quest1 extends Quest {
    const ID = 1;
    const NAME = '§cKissos§l討伐';
    const DES = 'Kissosを1体倒す';

    const COUNT = 1;
    const TARGET = 'Kissos';

    public function onTouch($player, $nn){
        $name = $player->getName();
        if(isset($this->data[$name])){
            if($this->data[$name] >= self::COUNT){
                $this->clear($player, $nn);
                unset($this->data[$name]);
            }else{
                $player->sendMessage('§a'.$nn.'§f>>Kissos一体、よろしく頼むよ。');
            }
        }else{
            if(in_array(self::ID, $this->plugin->playerData[$name]['quest'])){
                $this->cleard($player, $nn);
            }else{
                if(in_array(4, $this->plugin->playerData[$name]['quest'])){
                    $this->start($player);
                    $player->sendMessage('§a'.$nn.'§f>>最近この町の近辺のKissosが増えてきている。');
                    $player->sendMessage('§a'.$nn.'§f>>餌不足で町に来られても困る。倒してくれると助かるよ。');
                    $this->data[$name] = 0;
                }else{
                    $player->sendMessage('§a'.$nn.'§f>>ここに来るのはまだ早いんじゃないか？');
                }
            }
        }
    }

    public function clear($player, $nn){
        parent::clear($player, $nn);
        $name = $player->getName();
        $player->sendMessage('§a'.$nn.'§f>>よくやった。');
        $this->plugin->playerData[$name]['quest'][] = self::ID;
        $this->plugin->playermanager->addMoney($player, 1000);
        $this->plugin->playermanager->addExp($player, 200);
    }

    public function cleard($player, $nn){
        $player->sendMessage('§a'.$nn.'§f>>長生きのコツは、油断しないことだ。');
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