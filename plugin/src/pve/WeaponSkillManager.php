<?php
namespace pve;

use pve\weaponskill as ws;

class WeaponSkillManager {
	
    public static $class = [];

    public static function init($plugin){
    new ws\WeaponSkillListener($plugin);
    self::register(new ws\Thunder($plugin));
    self::register(new ws\Curse($plugin));
    self::register(new ws\Light($plugin));
    self::register(new ws\Fire($plugin));
    self::register(new ws\Wind($plugin));
    self::register(new ws\Ice($plugin));
    self::register(new ws\ThunderA($plugin));
    self::register(new ws\LightA($plugin));
    self::register(new ws\CurseA($plugin));
    self::register(new ws\IceA($plugin));
    self::register(new ws\Saikutu($plugin));
    self::register(new ws\Warp($plugin));
    self::register(new ws\Zero($plugin));
    self::register(new ws\Kusin($plugin));
    self::register(new ws\Ezerro($plugin));
    self::register(new ws\Prominensu($plugin));
    self::register(new ws\Kamae($plugin));
    self::register(new ws\Iai($plugin));
    self::register(new ws\Iai2($plugin));
    }
   
    public static function register($skill){
	   self::$class[$skill->getId()] = $skill;
    }
	
    public static function getSkill($id){
		$result = null;
		if(isset(self::$class[$id])) $result = self::$class[$id];
		return $result;
    }
    
    public static function getAll(){
		return self::$class;
    }
}
