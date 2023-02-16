<?php
namespace pve\dungeon;

class Dungeon7 extends Dungeon {

    const SWORD_RECIPES = [0 => 100, 1 => 100, 2 => 100];
    const ARMOR_RECIPES = [0 => 100, 1 => 100, 2 => 100];
    const ORB_RECIPES = [0 => 100, 1 => 100, 2 => 100];

    public function __construct($plugin){
        parent::__construct($plugin);
        $this->mobs = [
            ['Camellia' => 4, 'Kerria' => 4, 'Majalis' => 4],
            ['Camellia' => 4, 'Kerria' => 4, 'Majalis' => 4],
            ['Camellia' => 4, 'Kerria' => 4, 'Majalis' => 4],
            ['Camellia' => 5, 'Kerria' => 5, 'Majalis' => 5],
            ['Camellia' => 5, 'Kerria' => 5, 'Majalis' => 5],
        ];
        $this->id = 7;
        $this->bossfloor = [];
        $this->maxfloor = 5;
        $this->danger = 1;
        $this->lv = 1;
        $this->name = '§l森の花畑';
        $this->money = 1000;
    }

    public function clear($name){
        $this->addRecipes($name);
        $this->addRecipes($name);
        parent::clear($name);
    }
}