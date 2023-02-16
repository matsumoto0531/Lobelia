<?php
namespace pve\quest;

use pve\mobs\Mobs;

class Quest14 extends Quest {
    const ID = 14;
    const NAME = '希望の§b氷の星§l';
    const DES = '氷の星15個を届けよう！';

    public function onTouch($player, $nn){
        $name = $player->getName();
        if(isset($this->data[$name])){
            $item = $this->getItem(36);
            $item->setCount(15);
            if($player->getInventory()->contains($item)){
                $player->getInventory()->removeItem($item);
                $this->clear($player, $nn);
                unset($this->data[$name]);
            }else{
                $player->sendMessage('§a'.$nn.'§f>>氷の星が15個あれば、きっと成功する！');
            }
        }else{
            if(in_array(self::ID, $this->plugin->playerData[$name]['quest'])){
                $this->cleard($player, $nn);
            }else{
                if(in_array(12, $this->plugin->playerData[$name]['quest'])){
                  $this->start($player);
                  $this->data[$name] = 0;
                }else{
                  $player->sendMessage('§a'.$nn.'§f>>ウーン、まだ何か足りない。。');
                }
            }
        }
    }

    public function clear($player, $nn){
        parent::clear($player, $nn);
        $name = $player->getName();
        $player->sendMessage('§a'.$nn.'§f>>おお、ありがとう！');
        $this->plugin->playerData[$name]['quest'][] = self::ID;
        $this->plugin->playermanager->addMoney($player, 30000);
        $this->plugin->playermanager->addExp($player, 30000);
    }

    public function cleard($player, $nn){
        $player->sendMessage('§a'.$nn.'§f>>ウーン...');
    }
}