<?php
namespace pve\quest;

use pve\mobs\Mobs;

use pve\WeaponManager;

class Quest18 extends Quest {
    const ID = 18;
    const NAME = '§0闇の§f星集め！';
    const DES = '闇の星を15個集めよう。';

    public function onTouch($player, $nn){
        $name = $player->getName();
        if(isset($this->data[$name])){
            $item = $this->getItem(40);
            $item->setCount(15);
            if($player->getInventory()->contains($item)){
                $player->getInventory()->removeItem($item);
                $this->clear($player, $nn);
                unset($this->data[$name]);
            }else{
                $player->sendMessage('§a'.$nn.'§f>>闇の星が15個も必要なんだ...');
            }
        }else{
            if(in_array(self::ID, $this->plugin->playerData[$name]['quest'])){
                $this->cleard($player, $nn);
            }else{
                if(in_array(17, $this->plugin->playerData[$name]['quest'])){
                  $this->start($player);
                  $this->data[$name] = 0;
                }else{
                  $player->sendMessage('§a'.$nn.'§f>>はぁ。。。');
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
        $player->sendMessage('§a'.$nn.'§f>>助かったよ...');
    }
}