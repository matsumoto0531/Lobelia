<?php
namespace pve\dungeon;

class Dungeon5 extends Dungeon {

    const SWORD_RECIPES = [4 => 1500];
    const ARMOR_RECIPES = [4 => 2500];
    const ORB_RECIPES = [4 => 500];

    public function __construct($plugin){
        parent::__construct($plugin);
        $this->mobs = [
            ['Majalis' => 12],
            ['Majalis' => 15],
            ['Akanthos' => 3],
        ];
        $this->id = 5;
        $this->bossfloor = [3];
        $this->maxfloor = 3;
        $this->danger = 2;
        $this->lv = 1;
        $this->name = '§b§l氷気の森';
        $this->money = 1500;
    }

    public function clear($name){
        //$this->addRecipes($name);
        //$this->addRecipes($name);
        parent::clear($name);
    }
}