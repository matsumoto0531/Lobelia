<?php
namespace pve;

use pve\skill as skill;

class SkillManager {
	
    public static $class = [];

    public static function init($plugin){
	  self::register(new skill\Atk($plugin));
	  self::register(new skill\None($plugin));
    self::register(new skill\AtkUp($plugin));
    self::register(new skill\Iceage($plugin));
    self::register(new skill\Dark($plugin));
    self::register(new skill\Flame($plugin));
    self::register(new skill\Gale($plugin));
    self::register(new skill\Conse($plugin));
    self::register(new skill\Blight($plugin));
    self::register(new skill\Heihua($plugin));
    self::register(new skill\Yoiyami($plugin));
    self::register(new skill\Yakedo($plugin));
    self::register(new skill\tailwind($plugin));
    self::register(new skill\Reedie($plugin));
    self::register(new skill\Ikou($plugin));
    self::register(new skill\Def($plugin));
    self::register(new skill\Sharp($plugin));
    self::register(new skill\Crit($plugin));
    self::register(new skill\Mine($plugin));
    self::register(new skill\Jinrai($plugin));
    self::register(new skill\Kouki($plugin));
    self::register(new skill\Kokugeki($plugin));
    self::register(new skill\Touhyou($plugin));
    self::register(new skill\Kiai($plugin));
    self::register(new skill\Sengeki($plugin));
    self::register(new skill\AtkDef($plugin));
    self::register(new skill\DefCrit($plugin));
    self::register(new skill\CritSharp($plugin));
    self::register(new skill\SaikutuAtk($plugin));
    self::register(new skill\Zennou($plugin));
    self::register(new skill\Kougeki($plugin));
    self::register(new skill\Kougi($plugin));
    self::register(new skill\SharpCrit($plugin));
    self::register(new skill\SecondWind($plugin));
    self::register(new skill\Hangeki($plugin));
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
      $c = [self::$class[4], self::$class[7], self::$class[8], self::$class[10],
            self::$class[11], self::$class[12], self::$class[13], self::$class[14],
            self::$class[20], self::$class[21], self::$class[22], self::$class[23],
            self::$class[31]
           ];
      return $c;
    }

    public static function onDamage(){
      $c = [self::$class[5], self::$class[9], self::$class[34], self::$class[35]];
      return $c;
    }

    public static function onHp(){
      $c = [self::$class[6], self::$class[15], self::$class[24]];
      return $c;
    }

    public static function onSkill(){
      $c = [self::$class[32]];
      return $c;
    }

}
