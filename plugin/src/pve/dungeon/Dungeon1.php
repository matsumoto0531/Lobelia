<?php
namespace pve\dungeon;

class Dungeon1 extends Dungeon {

    public function __construct($plugin){
        parent::__construct($plugin);
        $this->mobs = [
            ['Kerria' => 6],
            ['Kerria' => 9],
            ['Kissos' => 1], 
        ];
        $this->id = 1;
        $this->bossfloor = [3];
        $this->maxfloor = 3;
        $this->danger = 1;
        $this->lv = 1;
        $this->name = '§c火炎§fの洞窟';
        $this->money = 500;
    }
}