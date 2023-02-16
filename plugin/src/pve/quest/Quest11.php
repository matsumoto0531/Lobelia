<?php
namespace pve\quest;

use pve\mobs\Mobs;

class Quest11 extends Quest {
    const ID = 11;
    const NAME = '§c火の星§lを求めて！';
    const DES = '火の星を15個集めてきてほしい！';

    public function onTouch($player, $nn){
        $name = $player->getName();
        if(isset($this->data[$name])){
            $item = $this->getItem(34);
            $item->setCount(15);
            if($player->getInventory()->contains($item)){
                $player->getInventory()->removeItem($item);
                $this->clear($player, $nn);
                unset($this->data[$name]);
            }else{
                $player->sendMessage('§a'.$nn.'§f>>火の星15個お願いします。');
            }
        }else{
            if(in_array(self::ID, $this->plugin->playerData[$name]['quest'])){
                $this->cleard($player, $nn);
            }else{
                if(in_array(10, $this->plugin->playerData[$name]['quest'])){
                  $this->start($player);
                  $this->data[$name] = 0;
                }else{
                  $player->sendMessage('§a'.$nn.'§f>>キラキラ大好き！');
                }
            }
        }
    }

    public function clear($player, $nn){
        parent::clear($player, $nn);
        $name = $player->getName();
        $player->sendMessage('§a'.$nn.'§f>>ありがとうございます！');
        $this->plugin->playerData[$name]['quest'][] = self::ID;
        $this->plugin->playermanager->addMoney($player, 30000);
        $this->plugin->playermanager->addExp($player, 30000);
    }

    public function cleard($player, $nn){
        $player->sendMessage('§a'.$nn.'§f>>火の星、大事にするね');
    }
}