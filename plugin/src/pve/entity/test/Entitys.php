<?php
namespace pve\entity\test;

use pocketmine\level\Level;
use pocketmine\entity\Human;
use pocketmine\entity\Entity;
use pocketmine\Player;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\block\Block;
use pocketmine\entity\Skin;

use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\level\sound\AnvilFallSound;

class Entitys {

    private static $geometryCache = null;
	private static $skinCache = null;

    public function __construct($plugin){
        $this->plugin = $plugin;
    }

    public function getSkin($json, $png, $name, $geo){
        if(is_null(self::$geometryCache)) self::$geometryCache = file_get_contents(__DIR__ . $json);

		if(is_null(self::$skinCache))
		{
			$path = __DIR__ . $png;
			$img = @imagecreatefrompng($path);
			self::$skinCache = '';
			$l = (int) @getimagesize($path)[1];
			for ($y = 0; $y < $l; $y++) {
			    for ($x = 0; $x < $l; $x++) {
			        $rgba = @imagecolorat($img, $x, $y);
			        $a = ((~((int)($rgba >> 24))) << 1) & 0xff;
			        $r = ($rgba >> 16) & 0xff;
			        $g = ($rgba >> 8) & 0xff;
			        $b = $rgba & 0xff;
			        self::$skinCache .= chr($r) . chr($g) . chr($b) . chr($a);
			    }
			}
			@imagedestroy($img);
		}

		/*$skinTag = new CompoundTag("Skin", [
			new StringTag("Name", $name),
			new ByteArrayTag("Data", self::$skinCache),
			new ByteArrayTag("CapeData", ""),
			new StringTag("GeometryName", $geo),
			new ByteArrayTag("GeometryData", self::$geometryCache)
        ]);
        $skin = new Skin(
			$skinTag->getString("Name"),
			$skinTag->hasTag("Data", StringTag::class) ? $skinTag->getString("Data") : $skinTag->getByteArray("Data"), //old data (this used to be saved as a StringTag in older versions of PM)
			$skinTag->getByteArray("CapeData", ""),
			$skinTag->getString("GeometryName", ""),
			$skinTag->getByteArray("GeometryData", "")
		);*/
		$skin = new Skin(
			$name,
			self::$skinCache,
			"",
			$geo,
			self::$geometryCache
		);
		//$skin->validate();
		return $skin;
		
    }

}