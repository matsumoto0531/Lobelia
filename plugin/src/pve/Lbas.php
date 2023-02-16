<?php
namespace pve;

class Lbas {

    const LBAS = '§l§dL-BAS§f>>';

    public function __construct($plugin){
        $this->plugin = $plugin;
    }

    public function sendMessages($player, $messages){
        $count = 0;
        foreach($messages as $message){
            $this->plugin->getScheduler()->scheduleDelayedTask(new Callback
            ([$this, 'sendMessage'], [$player, $message]), $count * 100);
            $count++;
        }
    }

    public function sendMessage($player, $message){
        $player->sendMessage(self::LBAS.$message);
    }

}