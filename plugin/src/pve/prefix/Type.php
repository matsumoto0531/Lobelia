<?php
namespace pve;

class Type {
    const ICE = 0;
    const FIRE = 1;
    const WIND = 2;

    const THUNDER = 3;
    const LIGHT = 4;
    const DARK = 5;

    const NONE = 6;

    const WEAKNESS = [
        self::ICE => self::FIRE,
        self::FIRE => self::WIND,
        self::WIND => self::ICE,
        self::THUNDER => self::DARK,
        self::LIGHT => self::THUNDER,
        self::DARK => self::LIGHT,
        self::NONE => -1
    ];

    const NAME = [
        self::ICE => '§b氷',
        self::FIRE => '§4炎',
        self::WIND => '§a風',
        self::THUNDER => '§6雷',
        self::LIGHT => '§e光',
        self::DARK => '§0闇',
        self::NONE => '無し'
    ];

    const COLOR = [
        self::ICE => '§b',
        self::FIRE => '§4',
        self::WIND => '§a',
        self::THUNDER => '§6',
        self::LIGHT => '§e',
        self::DARK => '§0',
        self::NONE => ''
    ];

    const PARTICLE = [
        self::ICE => 'PVE:ICE',
        self::FIRE => 'PVE:FIRE',
        self::WIND => 'PVE:WIND',
        self::THUNDER => 'PVE:THUNDER',
        self::LIGHT => 'PVE:LIGHT',
        self::DARK => 'PVE:DARK',
        self::NONE => ''
    ];


    public static function isWeakness($type1, $type2){
        $result = false;
        if(self::WEAKNESS[$type1] == $type2) $result = true;
        return $result;
    }

    public static function getColor($type){
        return self::COLOR[$type];
    }

    public static function getName($id){
        return self::NAME[$id];
    }
}