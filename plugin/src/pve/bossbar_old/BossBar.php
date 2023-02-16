<?php

namespace pve\bossbar;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\math\Vector3;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityIds;
use pocketmine\entity\Attribute;
use pocketmine\entity\AttributeMap;

use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\MoveActorAbsolutePacket;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\network\mcpe\protocol\UpdateAttributesPacket;

class BossBar{

	/*用いるEid*/
	private $eid;
	/*タイトル*/
	private $title = "";
	/*ゲージの割合*/
	private $percentage = 1;
	/*ボスバーが表示状態かどうか*/
	private $visible = true;
	/*このボスバーを表示するプレイヤーの配列*/
	private $players = [];
	
	private $attributeMap;

	public function __construct()
	{
		$this->eid = Entity::nextRuntimeId();
		BossBarManager::register($this);
		/*$this->attributeMap = new AttributeMap();
		$this->getAttributeMap()->addAttribute(Attribute::getAttribute(Attribute::HEALTH)->setMaxValue(100.0)->setMinValue(0.0)->setDefaultValue(100.0));*/
	}
	
	public function getAttributeMap()
	{
		return $this->attributeMap;
	}

	public function getEid()
	{
		return $this->eid;
	}

	public function show()
	{
		$this->visible = true;
		foreach ($this->players as $player) {
			$this->showBossBar($player);
		}
	}

	public function hide()
	{
		$this->visible = false;
		foreach ($this->players as $player) {
			$this->hideBossBar($player);
		}
	}

	public function isVisivle()
	{
		return $this->visible;
	}

	public function move()
	{
		foreach ($this->players as $player) {
			$this->moveToPlayer($player);
		}
	}

	public function setTitle($title)
	{
		$this->title = $title;
		foreach ($this->players as $player) {
			$this->updateTitle($player);
		}
	}

	public function setPercentage($percentage)
	{
		$this->percentage = $percentage;
		if($percentage === 0)
		{
			$this->show();
		}
		else{
			foreach ($this->players as $player) {
				$this->updatePercentage($player);
			}
		}
	}

	public function register(Player $player)
	{
		$this->players[$player->getName()] = $player;
		if($this->visible) $this->showBossBar($player);
	}

	public function unregister(Player $player)
	{
		unset($this->players[$player->getName()]);
		if($this->visible) $this->hideBossBar($player);
	}

	public function isRegistered(Player $player)
	{
		return isset($this->players[$player->getName()]);
	}

	public function showBossBar($player)
	{
		/*$apk = new AddActorPacket();
		$apk->actorRuntimeId = $this->eid;
		$apk->type = AddActorPacket::LEGACY_ID_MAP_BC[EntityIds::ENDER_DRAGON];
		$pos = $player->getPosition();
		$apk->position = new Vector3($pos->x, $pos->y+50, $pos->z);
		$apk->metadata = [
			Entity::DATA_LEAD_HOLDER_EID => [
								Entity::DATA_TYPE_LONG, -1
								],
			Entity::DATA_FLAGS => [
								Entity::DATA_TYPE_LONG, 0 ^ 1 << Entity::DATA_FLAG_SILENT ^ 1 << Entity::DATA_FLAG_INVISIBLE ^ 1 << Entity::DATA_FLAG_NO_AI
								],
			Entity::DATA_SCALE => [
								Entity::DATA_TYPE_FLOAT, 0
								],
			Entity::DATA_NAMETAG => [
								Entity::DATA_TYPE_STRING, $this->title
								],
			Entity::DATA_BOUNDING_BOX_WIDTH => [
								Entity::DATA_TYPE_FLOAT, 0
								],
			Entity::DATA_BOUNDING_BOX_HEIGHT => [
								Entity::DATA_TYPE_FLOAT, 0
								]
							];
		//$player->dataPacket($apk);

		$bpk = new BossEventPacket();
		$bpk->bossEid = $this->eid;
		$bpk->eventType = BossEventPacket::TYPE_SHOW;
		$bpk->title = $this->title;
		$bpk->healthPercent = $this->percentage;
		$bpk->unknownShort = 0;
		$bpk->color = 0;
		$bpk->overlay = 0;
		$bpk->playerEid = 0;
		//$player->dataPacket($bpk);*/
	}

	public function hideBossBar($player)
	{
		//$rpk = new RemoveActorPacket();
		//$rpk->entityUniqueId = $this->eid;
		//$player->dataPacket($rpk);
	}

	public function updatePercentage($player)
	{
		/*$upk = new UpdateAttributesPacket();
		$attribute = Attribute::getAttribute(Attribute::HEALTH);
		$attribute->setMaxValue(1000);
		if($this->percentage < 0){
			$this->percentage = 0;
		}
		if($this->percentage > 1) $this->percentage = 1;
		$attribute->setValue(1000 * $this->percentage);
		$upk->entries = [$attribute];
		$upk->entityRuntimeId = $this->eid;
		//$player->dataPacket($upk);

		$bpk = new BossEventPacket();
		$bpk->bossEid = $this->eid;
		$bpk->eventType = BossEventPacket::TYPE_HEALTH_PERCENT;
		$bpk->title = $this->title;
		$bpk->healthPercent = $this->percentage;
		$bpk->unknownShort = 0;
		$bpk->color = 0;
		$bpk->overlay = 0;
		$bpk->playerEid = 0;
		//$player->dataPacket($bpk);*/
	}

	public function updateTitle($player)
	{
		$spk = new SetActorDataPacket();
		$spk->metadata = [
						Entity::DATA_NAMETAG => [
							Entity::DATA_TYPE_STRING, $this->title
						]
					];
		$spk->entityRuntimeId = $this->eid;
		//$player->dataPacket($spk);

		$bpk = new BossEventPacket();
		$bpk->bossEid = $this->eid;
		$bpk->eventType = BossEventPacket::TYPE_SHOW;
		$bpk->title = $this->title;
		$bpk->healthPercent = $this->percentage;
		$bpk->unknownShort = 0;
		$bpk->color = 0;
		$bpk->overlay = 0;
		$bpk->playerEid = 0;
		//$player->dataPacket($bpk);
	}

	public function moveToPlayer($player)
	{
		$mpk = new MoveActorAbsolutePacket();
		$mpk->entityRuntimeId = $this->eid;
		$mpk->flags |= MoveActorAbsolutePacket::FLAG_TELEPORT;
		$pos = $player->getPosition();
		$mpk->position = new Vector3($pos->x, $pos->y+50, $pos->z);
		$mpk->xRot = 0;
		$mpk->yRot = 0;
		$mpk->zRot = 0;
		//$player->dataPacket($mpk);
	}

}
