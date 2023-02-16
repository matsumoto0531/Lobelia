<?php
namespace pve\dungeon;

class Dungeon8 extends Dungeon {

    const SWORD_RECIPES = [5 => 1500];
    const ARMOR_RECIPES = [5 => 2500];
    const ORB_RECIPES = [5 => 500];

    public function __construct($plugin){
        parent::__construct($plugin);
        $this->mobs = [
            ['Exacum' => 1]
        ];
        $this->id = 8;
        $this->bossfloor = [1];
        $this->maxfloor = 1;
        $this->danger = 4;
        $this->lv = 1;
        $this->name = '§a§l風の§9龍';
        $this->money = 2000;
    }

    public function clear($name){
        //$this->addRecipes($name);
        //$this->addRecipes($name);
        parent::clear($name);
    }
}