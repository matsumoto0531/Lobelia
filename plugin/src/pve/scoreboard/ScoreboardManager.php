<?php
namespace pve\scoreboard;

use pocketmine\player\Player;
use pocketmine\network\mcpe\protocol\ { SetScorePacket, RemoveObjectivePacket, SetDisplayObjectivePacket };
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;

class ScoreboardManager {

	const OBJECT_NAME = 'pveserver';
	const DISPLAY_NAME = '§l§aLobelia §9❀';

	const LINE_LEVEL = 0;
	const LINE_EXP = 1;
	const LINE_ATK = 2;
	const LINE_DEF = 3;
	const LINE_HP = 4;
	const LINE_MONEY = 5;
	const LINE_NEXT = 6;

	public static function init($plugin)
	{
		$plugin->getServer()->getPluginManager()->registerEvents(new ScoreboardListener($plugin), $plugin);
	}

	public static function prepare(Player $player)
	{
		$pk = new SetDisplayObjectivePacket();
		$pk->displaySlot = 'sidebar';
		$pk->objectiveName = self::OBJECT_NAME;
		$pk->displayName = self::DISPLAY_NAME;
		$pk->criteriaName = "dummy";
		$pk->sortOrder = 0;

		$player->getNetworkSession()->sendDataPacket($pk);
	}

	public static function setLine(Player $player, int $line, string $message)
	{
		$entry = new ScorePacketEntry();
		$entry->objectiveName = self::OBJECT_NAME;
		$entry->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
		$entry->customName = str_pad("・" . $message, ((strlen(self::DISPLAY_NAME) * 2) - strlen($message)));;
		$entry->score = $line;
		$entry->scoreboardId = $line;
		
		$pk = new SetScorePacket();
		$pk->type = SetScorePacket::TYPE_CHANGE;
		$pk->entries[] = $entry;
		$player->getNetworkSession()->sendDataPacket($pk);
	}

	public static function removeLine(Player $player, int $line){
		$entry = new ScorePacketEntry();
		$entry->objectiveName = self::OBJECT_NAME;
		$entry->score = $line;
		$entry->scoreboardId = $line;
		
		$pk = new SetScorePacket();
		$pk->type = SetScorePacket::TYPE_REMOVE;
		$pk->entries[] = $entry;
		
		$player->getNetworkSession()->sendDataPacket($pk);
	}

	public static function updateLine(Player $player, int $line, string $message)
	{
		self::removeLine($player, $line);
		self::setLine($player, $line, $message);
	}

	public static function hide(Player $player)
	{
		$pk = new RemoveObjectPacket();
		$pk->objectiveName = self::OBJECT_NAME;
		
		$player->dataPacket($pk);
	}

}
		
