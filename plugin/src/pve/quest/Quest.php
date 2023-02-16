<?php
namespace pve\quest;

use pve\MobManager;

class Quest{

    const ID = 0;
    const NAME = '';
    const DES = '';

    public function __construct($plugin){
        $this->plugin = $plugin;
        $this->data = [];
    }

    public function getId(){
        return static::ID;
    }

    public function getName(){
        return static::NAME;
    }

    public function getDes(){
        return static::DES;
    }

    public function onTouch($player, $nn){

    }

    public function start($player){
        $player->sendTitle(static::NAME, static::DES);
        $player->sendMessage('§aQUEST§f>>'.static::NAME.'§f§r受注');
    }

    public function clear($player, $nn){
        $player->sendMessage('§aQUEST§f>>'.static::NAME.'達成!');
        $player->sendTitle(static::NAME, '§bCLEAR!!');
    }

    public function cleard($player, $nn){
    }

    public function getItem($id){
      $item = MobManager::getMob('shadow')->getItem($id);
      return $item;
    }

    public function onKill($player, $name){
        
    }
}