<?php
namespace pve\dungeon;

class DungeonManager {
	
    public static $class = [];
    public static $players = [];

    public static function init($plugin){
      self::register(new Dungeon1($plugin));
      self::register(new Dungeon2($plugin));
      self::register(new Dungeon3($plugin));
      self::register(new Dungeon4($plugin));
      self::register(new Dungeon5($plugin));
      self::register(new Dungeon6($plugin));
      self::register(new Dungeon7($plugin));
      self::register(new Dungeon8($plugin));
    }
   
    public static function register($skill){
     self::$class[$skill->id] = $skill;
    }
	
    public static function getDungeon($id){
		$result = null;
    if(isset(self::$class[$id])) $result = self::$class[$id];
		return $result;
    }
    
    public static function getAll(){
		return self::$class;
    }

    public static function addDungeon($player, $id){
        self::$players[$player->getName()] = $id;
    }

    public static function removeDungeon($player){
        unset(self::$players[$player->getName()]);
    }

    public static function isDungeon($player){
        $result = false;
        if(isset(self::$players[$player->getName()])) $result = true;
        return $result;
    }

    public static function getDungeonByPlayer($player){
        return self::$class[self::$players[$player->getName()]];
    }
}