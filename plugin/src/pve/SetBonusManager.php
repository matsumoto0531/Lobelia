<?php
namespace pve;
use pve\setbonus as sb;

class SetBonusManager {
	
    public static $class = [];

    public static function init($plugin){
        self::register(new sb\Fire($plugin));
        self::register(new sb\Wind($plugin));
        self::register(new sb\None($plugin));
        self::register(new sb\Ice($plugin));
        self::register(new sb\Thunder($plugin));
        self::register(new sb\Light($plugin));
        self::register(new sb\Dark($plugin));
    }
   
    public static function register($type){
	   self::$class[$type->getType()] = $type;
    }
	
    public static function get($id){
		$result = null;
		if(isset(self::$class[$id])) $result = self::$class[$id];
		return $result;
    }
    
    public static function getAll(){
		return self::$class;
    }
}