<?php
namespace pve;

use pve\armor as Armor;

class ArmorManager {
	
    public static $class;

    public static function init($plugin){
	  /*self::register(new armor\ShadowHelm($plugin));
	  self::register(new armor\Muneate($plugin));
	  self::register(new armor\Kutu($plugin));
	  self::register(new armor\Mutantleg($plugin));*/
	  //self::register(new armor\Armor($plugin));
	  self::$class = new armor\Armor($plugin);
    }
   
    public static function register($armor){
	   //self::$class[$armor->getId()] = $armor;
    }
	
    public static function getArmor(){
		$result = null;
		$result = self::$class;
		return $result;
    }
    
    public static function getAll(){
		return self::$class;
    }

}
