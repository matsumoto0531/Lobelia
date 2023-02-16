<?php
namespace pve;

use pve\weapon as weapon;

class WeaponManager {
	
    public static $class;

    public static function init($plugin){
	  /*self::register(new weapon\ShadowSword($plugin));
	  self::register(new weapon\DarkBrade($plugin));
	  self::register(new weapon\Nikunome($plugin));
    self::register(new weapon\Konbou($plugin));*/
    self::$class = new weapon\Weapon($plugin);
    }
   
    public static function register($weapon){
	   self::$class[$weapon->getId()] = $weapon;
    }
	
    /*public static function getWeapon($id){
		$result = null;
		if(isset(self::$class[$id])) $result = self::$class[$id];
		return $result;
    }*/

    public static function getWeapon(){
      $result = null;
      $result = self::$class;
      return $result;
    }
    
    public static function getAll(){
		return self::$class;
    }

}
