<?php
namespace pve\bossbar;

class BossBar {

    public function __construct($channel){
        $this->players = [];
        $this->title = "";
        $this->percent = 100;
        $this->channel = $channel;
    }

    public function setTitle($name){
        $this->title = $name;
        foreach($this->players as $player)
          BossBarAPI::getInstance()->setTitle($player, $name, $this->channel);
    }

    public function register($player){
        $this->players[] = $player;
        BossBarAPI::getInstance()->sendBossBar($player, $this->title, $this->channel, $this->percent, BossBarAPI::COLOR_RED);
    }

    public function unregister($player){
        $this->players = array_diff($this->players);
        $this->players = array_values($this->players);
        BossBarAPI::getInstance()->hideBossBar($player, $this->channel);
    }

    public function hide(){
        foreach($this->players as $player){
            BossBarAPI::getInstance()->hideBossBar($player, $this->channel);
        }
    }

    public function setPercentage($per){
        $this->percent = $per;
        foreach($this->players as $player){
            BossBarAPI::getInstance()->setPercent($player, $per, $this->channel);
        }
    }
}