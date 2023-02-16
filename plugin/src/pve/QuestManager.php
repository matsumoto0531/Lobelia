<?php
namespace pve;

use pve\quest;

class QuestManager {
	
    public static $class = [];

    public static function init($plugin){
       self::register(new quest\Quest1($plugin));
       self::register(new quest\Quest2($plugin));
       self::register(new quest\Quest3($plugin));
       self::register(new quest\Quest4($plugin));
       self::register(new quest\Quest5($plugin));
       self::register(new quest\Quest6($plugin));
       self::register(new quest\Quest7($plugin));
       self::register(new quest\Quest8($plugin));
       self::register(new quest\Quest9($plugin));
       self::register(new quest\Quest10($plugin));
       self::register(new quest\Quest11($plugin));
       self::register(new quest\Quest12($plugin));
       self::register(new quest\Quest13($plugin));
       self::register(new quest\Quest14($plugin));
       self::register(new quest\Quest15($plugin));
       self::register(new quest\Quest16($plugin));
       self::register(new quest\Quest17($plugin));
       self::register(new quest\Quest18($plugin));
       self::register(new quest\Quest19($plugin));
       self::register(new quest\Quest20($plugin));
       self::register(new quest\Quest21($plugin));
       self::register(new quest\Quest22($plugin));
    }
   
    public static function register($skill){
	   self::$class[$skill->getId()] = $skill;
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