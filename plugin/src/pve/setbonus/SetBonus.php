<?php
namespace pve\setbonus;

use pve\Type;

class SetBonus {

    const TYPE = Type::NONE;
    const DES = [
        Type::NONE => '',
        Type::FIRE => '火属性の攻撃力と全属性の被ダメージが1.5倍！',
        Type::WIND => '移動速度1.3倍！会心率30%UP',
        Type::ICE => '切れ味が100上昇！',
        Type::THUNDER => '会心倍率+1倍！',
        Type::LIGHT => '防御力1.2倍！、回復量上昇！',
        Type::DARK => '闇属性攻撃時、一定確率で体力を吸収する！',
    ];

    public function __construct($plugin){
        $this->plugin = $plugin;
        $this->data = [];
    }

    public function getType(){
        return static::TYPE;
    }

    public function onSet($player){
        $name = $player->getName();
        if(!isset($this->data[$name])){
            $this->data[$name] = 1;
        }else{
            $this->data[$name]++;
        }
        if($this->data[$name] === 2){
          $player->sendMessage('§eINFO§f>>'.Type::NAME[static::TYPE].'属性§f装備ボーナス発動[2箇所]！');
          $player->sendMessage('§eINFO§f>>対応属性の火力が1.2倍！、被ダメージを0.8倍！');
        }elseif($this->data[$name] === 4){
          $player->sendMessage('§eINFO§f>>'.Type::NAME[static::TYPE].'属性§f装備ボーナス発動[4箇所]！');
          $player->sendMessage('§eINFO§f>>'.self::DES[static::TYPE]);
        }
    }

    public function onReset($player){
        $name = $player->getName();
        $this->data[$name]--;
    }

    public function onAttack($player, $atk){
        $name = $player->getName();
        if(!isset($this->data[$name])) return $atk;
        if($this->data[$name] >= 2) return $atk*1.2;
        return $atk;
    }

    public function onDamage($player, $atk){
        $name = $player->getName();
        if(!isset($this->data[$name])) return $atk;
        if($this->data[$name] >= 2) return $atk*0.8;
        return $atk;
    }

    public function onHeal($player, $amount){
        return $amount;
    }
}