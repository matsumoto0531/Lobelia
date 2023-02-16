<?php
namespace pve;

use pve\mobs as mobs;

class MobManager {
	
    public static $class = [];

    public static function init($plugin){
    self::register(new mobs\Shadow($plugin));
    
    self::register(new mobs\Camellia($plugin));
    self::register(new mobs\Tuberose($plugin));
    self::register(new mobs\Pelargonium($plugin));
    self::register(new mobs\Majalis($plugin));
    self::register(new mobs\Viola($plugin));
    self::register(new mobs\Kerria($plugin));
    self::register(new mobs\Bellis($plugin));
    self::register(new mobs\Lactiflora($plugin));
    self::register(new mobs\Gerbera($plugin));
    self::register(new mobs\Platy($plugin));
    self::register(new mobs\Anemone($plugin));
    self::register(new mobs\Iris($plugin));

    self::register(new mobs\Kissos($plugin));
    self::register(new mobs\Akanthos($plugin));

    self::register(new mobs\Lilac($plugin));
    self::register(new mobs\LilacA($plugin));
    self::register(new mobs\Amaryllis($plugin));
    self::register(new mobs\AmaryllisA($plugin));
    self::register(new mobs\Clematis($plugin));
    self::register(new mobs\Dahlia($plugin));
    self::register(new mobs\Salvia($plugin));
    self::register(new mobs\SalviaA($plugin));
    self::register(new mobs\Cattleya($plugin));
    self::register(new mobs\CattleyaA($plugin));
    self::register(new mobs\Golem($plugin));

    self::register(new mobs\Exacum($plugin));
    }
   
    public static function register($mob){
	   self::$class[$mob->getName()] = $mob;
    }
	
    public static function getMob($name){
		$result = null;
		if(isset(self::$class[$name])) $result = self::$class[$name];
		return $result;
    }
    
    public static function getAll(){
		return self::$class;
    }

}
