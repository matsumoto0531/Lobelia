<?php
namespace pve\job;

class Job {

    const LIST = [
        '無し',
        '§cアタッカー§r',
        '§bディフェンダー§r',
        '§aテイマー§r'
    ];

    const EMOJI = [
        '無',
        '⚔️',
        '۞',
        '🐺'
    ];

    public function __construct($plugin){
        $this->plugin = $plugin;
    }

    public function getList(){
        return self::LIST;
    }



}