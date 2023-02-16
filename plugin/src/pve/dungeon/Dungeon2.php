<?php
namespace pve\dungeon;

class Dungeon2 extends Dungeon {

    public function __construct($plugin){
        parent::__construct($plugin);
        $this->mobs = [
            ['Majalis' => 12],
            ['Majalis' => 15],
            ['Akanthos' => 1],
        ];
        $this->id = 2;
        $this->bossfloor = [3];
        $this->maxfloor = 3;
        $this->danger = 1;
        $this->lv = 1;
        $this->name = '§b氷気§fの洞窟';
        $this->money = 700;
    }
}