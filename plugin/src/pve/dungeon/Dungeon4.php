<?php
namespace pve\dungeon;

class Dungeon4 extends Dungeon {

    const SWORD_RECIPES = [3 => 1500, 6 => 50];
    const ARMOR_RECIPES = [3 => 1500];
    const ORB_RECIPES = [3 => 1500];

    public function __construct($plugin){
        parent::__construct($plugin);
        $this->mobs = [
            ['Kerria' => 12],
            ['Kerria' => 15],
            ['Kissos' => 3],
        ];
        $this->id = 4;
        $this->bossfloor = [3];
        $this->maxfloor = 3;
        $this->danger = 2;
        $this->lv = 1;
        $this->name = '§c§l炎天獄';
        $this->money = 1000;
    }

    public function clear($name){
        //$this->addRecipes($name);
        //$this->addRecipes($name);
        parent::clear($name);
    }
}