<?php
namespace pve\dungeon;

class Dungeon3 extends Dungeon {

    public function __construct($plugin){
        parent::__construct($plugin);
        $this->mobs = [
            ['Camellia' => 12],
            ['Camellia' => 15],
            ['Dahlia' => 1],
        ];
        $this->id = 3;
        $this->bossfloor = [3];
        $this->maxfloor = 3;
        $this->danger = 2;
        $this->lv = 1;
        $this->name = '§a豪風§fの間';
        $this->money = 1000;
    }
}