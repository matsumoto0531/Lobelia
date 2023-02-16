<?php
namespace pve\dungeon;

class Dungeon6 extends Dungeon {

    const SWORD_RECIPES = [5 => 1500];
    const ARMOR_RECIPES = [5 => 2500];
    const ORB_RECIPES = [5 => 500];

    public function __construct($plugin){
        parent::__construct($plugin);
        $this->mobs = [
            ['Camellia' => 12],
            ['Camellia' => 15],
            ['Akanthos' => 1, 'Kissos' => 1],
            ['Dahlia' => 3]
        ];
        $this->id = 6;
        $this->bossfloor = [3, 4];
        $this->maxfloor = 4;
        $this->danger = 3;
        $this->lv = 1;
        $this->name = '§a§l豪風の試練';
        $this->money = 2000;
    }

    public function clear($name){
        //$this->addRecipes($name);
        //$this->addRecipes($name);
        parent::clear($name);
    }
}