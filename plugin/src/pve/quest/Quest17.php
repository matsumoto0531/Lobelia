<?php
namespace pve\quest;

use pve\mobs\Mobs;

class Quest17 extends Quest {
    const ID = 17;
    const NAME = '§e§l光の守護者';
    const DES = 'Salviaを倒す';

    const COUNT = 1;
    const TARGET = 'Salvia';

    public function onTouch($player, $nn){
        $name = $player->getName();
        if(isset($this->data[$name])){
            if($this->data[$name] >= self::COUNT){
                $this->clear($player, $nn);
                unset($this->data[$name]);
            }else{
                $player->sendMessage('§a'.$nn.'§f>>応援してるよ。');
            }
        }else{
            if(in_array(self::ID, $this->plugin->playerData[$name]['quest'])){
                $this->cleard($player, $nn);
            }else{
                if(in_array(15, $this->plugin->playerData[$name]['quest'])){
                    $this->start($player);
                    $player->sendMessage('§a'.$nn.'§f>>守護者への挑戦を認めよう。');
                    $this->data[$name] = 0;
                }else{
                    $player->sendMessage('§a'.$nn.'§f>>依頼をすべてこなしたらまたおいで。');
                }
            }
        }
    }

    public function clear($player, $nn){
        parent::clear($player, $nn);
        $name = $player->getName();
        $player->sendMessage('§a'.$nn.'§f>>おめでとう。');
        $player->sendMessage('§e§lINFO§r§f>>Adonisへ行けるようになりました！');
        $this->plugin->playerData[$name]['quest'][] = self::ID;
        $this->plugin->playermanager->addMoney($player, 10000);
        $this->plugin->playermanager->addExp($player, 10000);
    }

    public function cleard($player, $nn){
        $player->sendMessage('§a'.$nn.'§f>>応援してるよ。');
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