<?php
namespace pve\prefix;

class Prefix {
    const PREFIX_NAME = ['', '§f粗雑な', '§a普通§fの', '§b優れた§f', '§e§l最上級§fの', '§l§e天§c下§d一§a品§fの', '§l§1唯§2一§3無§4二§fの'];
    const PREFIX_KAKURITU = [
        [0, 50, 30, 20, 0, 0, 0],
        [0, 30, 50, 15, 5, 0, 0],
        [0, 9, 10, 60, 20, 1, 0],
        [0, 1, 29, 20, 40, 10, 0],
        [0, 10, 10, 34, 25, 20, 1],
        [0, 30, 0, 0, 25, 40, 2],
        [0, 0, 0, 0, 0, 5, 95]
    ];
    const MAKE_KAKURITU = [0, 1, 19, 40, 30, 9, 1];
    const PREFIX_BAIRITU = [1, 0.8, 1, 1.1, 1.2, 1.4, 1.6];
    const PREFIX_SUBCOUNT = [0, 0, 0, 1, 2, 4, 6];

    const SUB_PREFIX_NAME = [
        "クリティカル発生率",  //0
        "クリティカルダメージ倍率",//1
        "攻撃力",//2
        "防御力",//3
        "最終ダメージ",//4
        "ダメージ軽減",//5
        "攻撃速度上昇",//6
        "氷属性攻撃",//7
        "火属性攻撃",//8
        "風属性攻撃",//9
        "雷属性攻撃",//10
        "光属性攻撃",//11
        "闇属性攻撃",//12
        "無属性攻撃",//13
        "氷属性耐性",//14
        "火属性耐性",//15
        "風属性耐性",//16
        "雷属性耐性",//17
        "光属性耐性",//18
        "闇属性耐性",//19
        "無属性耐性" //20
    ];

    const PREFIX_MULTIPLE = [
        [
            [1, 5], [1, 5], [1, 5], [1, 5],
            [1, 3], [1, 3], [1, 3], [1, 5],
            [1, 5], [1, 5], [1, 5], [1, 5],
            [1, 5], [1, 5], [1, 5], [1, 5],
            [1, 5], [1, 5], [1, 5], [1, 5],
            [1, 5],
        ],
        [
            [1, 7], [1, 7], [1, 7], [1, 7],
            [1, 5], [1, 5], [1, 5], [1, 7],
            [1, 7], [1, 7], [1, 7], [1, 7],
            [1, 7], [1, 7], [1, 7], [1, 7],
            [1, 7], [1, 7], [1, 7], [1, 7],
            [1, 7],
        ],
        [
            [1, 9], [1, 9], [1, 9], [1, 9],
            [1, 5], [1, 5], [1, 5], [3, 9],
            [3, 9], [3, 9], [3, 9], [3, 9],
            [3, 9], [3, 9], [3, 9], [3, 9],
            [3, 9], [3, 9], [3, 9], [3, 9],
            [3, 9],
        ],
        [
            [1, 14], [1, 14], [1, 14], [1, 14],
            [1, 10], [1, 10], [1, 10], [1, 14],
            [1, 14], [1, 14], [1, 14], [1, 14],
            [1, 14], [1, 14], [1, 14], [1, 14],
            [1, 14], [1, 14], [1, 14], [1, 14],
            [1, 14],
        ]
    ];



    public static function get($now){
        $kakuritu = self::PREFIX_KAKURITU[$now];
        $rand = mt_rand(0, 100);
        for($i = 0; $i < 7; $i++){
          if($rand <= $kakuritu[$i]) return $i;
          $rand -= $kakuritu[$i];
        }
        return 0;
    }

    public static function make(){
        $kakuritu = self::MAKE_KAKURITU;
        $rand = mt_rand(0, 100);
        for($i = 0; $i < 7; $i++){
          if($rand <= $kakuritu[$i]) return $i;
          $rand -= $kakuritu[$i];
        }
        return 0;
    }

    public static function getName($prefix){
        $ans = 1;
        if($prefix >= 95) $ans = 6;
        else if($prefix >= 85) $ans = 5;
        else if($prefix >= 70) $ans = 4;
        else if($prefix >= 50) $ans = 3;
        else if($prefix >= 30) $ans = 2;
        return self::PREFIX_NAME[$ans];
    }

    public static function getSub($prefix){
        $count = self::PREFIX_SUBCOUNT[$prefix];
        $subs = [];
        for($i = 0; $i < $count; $i++){
            $num = mt_rand(0, 20);
            $suuti = self::PREFIX_MULTIPLE[$prefix - 3][$num];
            $per = mt_rand($suuti[0], $suuti[1]);
            $subs[] = [$num, $per];
        }
        return $subs;
    }
}