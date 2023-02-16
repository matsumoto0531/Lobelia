<?php
namespace pve\item;

class ItemManager {
	
    public static $class = [];

    public static function init($plugin){
      new ItemListener($plugin);
      self::register(new Heal($plugin));
      self::register(new Heall($plugin));
      self::register(new Healll($plugin));
      self::register(new Hunjin($plugin));
      self::register(new Melanight($plugin));
      self::register(new Emelight($plugin));
      self::register(new Lasobaight($plugin));
      self::register(new Anaiasu($plugin)); 
      self::register(new Turuhashi($plugin));
      self::register(new Kijin($plugin));
      self::register(new Kouka($plugin));   
      self::register(new Kijinkona($plugin));
      self::register(new Koukakona($plugin));
      self::register(new Niku($plugin));
      self::register(new Ticket1($plugin));
      self::register(new Ticket2($plugin));
      self::register(new Ticket3($plugin));
      self::register(new Ticket4($plugin));
      self::register(new Ticket5($plugin));
      self::register(new Senpuu($plugin));
      self::register(new Kamae($plugin));
      self::register(new Iai($plugin));
      self::register(new Iai2($plugin));
    }
   
    public static function register($skill){
     self::$class[$skill->getId()] = $skill;
    }
	
    public static function getItem($id){
		$result = null;
    if(isset(self::$class[$id])) $result = self::$class[$id];
		return $result;
    }
    
    public static function getAll(){
		return self::$class;
    }
}