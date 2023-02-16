<?php
namespace pve\quest;

use pve\mobs\Mobs;

class Quest13 extends Quest {
    const ID = 13;
    const NAME = 'モンスター討伐';
    const DES = 'モンスターを倒す';

    const COUNT = 300;

    public function onTouch($player, $nn){
        $name = $player->getName();
        if(isset($this->data[$name])){
            if($this->data[$name] >= self::COUNT){
                $this->clear($player, $nn);
                unset($this->data[$name]);
            }else{
                $player->sendMessage('§a'.$nn.'§f>>討伐、お願いします！');
            }
        }else{
            if(in_array(12, $this->plugin->playerData[$name]['quest'])){
                $this->start($player);
                $player->sendMessage('§a'.$nn.'§f>>間引きも大事な仕事です！');
                $this->data[$name] = 0;
            }else{
                $player->sendMessage('§a'.$nn.'§f>>もう少し経験を積んでからお越しください。');
            }
        }
    }

    public function clear($player, $nn){
        parent::clear($player, $nn);
        $name = $player->getName();
        $player->sendMessage('§a'.$nn.'§f>>ありがとうございます！');
        $this->plugin->playerData[$name]['quest'][] = self::ID;
        $this->plugin->playermanager->addMoney($player, 100000);
    }

    public function onKill($player, $n){
        $name = $player->getName();
        if(!isset($this->data[$name])) return false;
        $this->data[$name]++;
        if($this->data[$name] >= self::COUNT){
            $player->sendMessage('§aQuest§f>>条件達成!依頼者に報告しよう!');
            return true;
        }    
        $player->sendMessage('§aQuest§f>>'.$this->data[$name].'体目!');
    }
}