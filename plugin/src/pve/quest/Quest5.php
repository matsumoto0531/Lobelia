<?php
namespace pve\quest;

use pve\mobs\Mobs;

class Quest5 extends Quest {
    const ID = 5;
    const NAME = '§bAkanthos§l討伐';
    const DES = 'Akanthosを1体倒す';

    const COUNT = 1;
    const TARGET = 'Akanthos';

    public function onTouch($player, $nn){
        $name = $player->getName();
        if(isset($this->data[$name])){
            if($this->data[$name] >= self::COUNT){
                $this->clear($player, $nn);
                unset($this->data[$name]);
            }else{
                $player->sendMessage('§a'.$nn.'§f>>Akanthosを頼む。');
            }
        }else{
            if(in_array(self::ID, $this->plugin->playerData[$name]['quest'])){
                $this->cleard($player, $nn);
            }else{
                if(in_array(1, $this->plugin->playerData[$name]['quest'])){
                    $this->start($player);
                    $player->sendMessage('§a'.$nn.'§f>>Akanthosは危険だ。討伐して欲しい。');
                    $this->data[$name] = 0;
                }else{
                    $player->sendMessage('§a'.$nn.'§f>>旅人かい？元気だね。');
                }
            }
        }
    }

    public function clear($player, $nn){
        parent::clear($player, $nn);
        $name = $player->getName();
        $player->sendMessage('§a'.$nn.'§f>>ありがとう！');
        $this->plugin->playerData[$name]['quest'][] = self::ID;
        $this->plugin->playermanager->addMoney($player, 1500);
        $this->plugin->playermanager->addExp($player, 450);
    }

    public function cleard($player, $nn){
        $player->sendMessage('§a'.$nn.'§f>>ありがとな！');
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