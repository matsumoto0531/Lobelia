<?php
namespace pve\quest;

use pve\mobs\Mobs;

class Quest6 extends Quest {
    const ID = 6;
    const NAME = '§a守護者§fに§c挑戦§f!';
    const DES = 'Dahliaを討伐する。';

    const COUNT = 1;
    const TARGET = 'Dahlia';

    public function onTouch($player, $nn){
        $name = $player->getName();
        if(isset($this->data[$name])){
            if($this->data[$name] >= self::COUNT){
                $this->clear($player, $nn);
                unset($this->data[$name]);
            }else{
                $player->sendMessage('§a'.$nn.'§f>>Dahliaは手ごわいぞ。');
            }
        }else{
            if(in_array(self::ID, $this->plugin->playerData[$name]['quest'])){
                $this->cleard($player, $nn);
            }else{
                if(in_array(1, $this->plugin->playerData[$name]['quest'])){
                    $this->start($player);
                    $player->sendMessage('§a'.$nn.'§f>>キミの活躍は聞いている。');
                    $player->sendMessage('§a'.$nn.'§f>>そろそろ、守護者に挑んでもいい頃合いだろう。');
                    $this->data[$name] = 0;
                }else{
                    $player->sendMessage('§a'.$nn.'§f>>頑張りたまえよ。');
                }
            }
        }
    }

    public function clear($player, $nn){
        parent::clear($player, $nn);
        $name = $player->getName();
        $player->sendMessage('§a'.$nn.'§f>>よくやった！');
        $this->plugin->playerData[$name]['quest'][] = self::ID;
        $this->plugin->playermanager->addMoney($player, 2000);
        $this->plugin->playermanager->addExp($player, 600);
    }

    public function cleard($player, $nn){
        $player->sendMessage('§a'.$nn.'§f>>良い旅を!');
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