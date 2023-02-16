<?php
namespace pve;

use pve\specialskill as skill;

class SpecialSkillManager {
	
    public static $class = [];

    public static function init($plugin){
	  self::register(new skill\Kagerou($plugin));
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

    public static function onAttack(){
      $c = [    ];
      return $c;
    }

    public static function onDamage(){
      $c = [self::$class[1]];
      return $c;
    }

    public static function onHp(){
      $c = [];
      return $c;
    }

    public static function onSkill(){
      $c = [];
      return $c;
    }

}
