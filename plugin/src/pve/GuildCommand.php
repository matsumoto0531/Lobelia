<?php
namespace pve;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;

use pve\WeaponManager;
use pve\ArmorManager;

class GuildCommand extends Command{

	const NAME = "guild";
	const DESCRIPTION = "ギルドコマンドです";
	const USAGE = "";
	
	const PERMISSION = true;
	
	protected $plugin;

    public function __construct(Main $plugin)
    {
		parent::__construct(static::NAME, static::DESCRIPTION, static::USAGE);
		//parent::__construct("guild", static::DESCRIPTION, static::USAGE);

        $this->plugin = $plugin;
        $this->need = [1000, 3000, 10000, 15000];
    }
    
	public function execute(CommandSender $sender, string $label, array $args) : bool {
		if(!$this->plugin->isEnabled())
        {
            return false;
        }
        if(!$this->testPermission($sender))
        {
            return false;
        }
		
		if(!$sender instanceof Player){
			$sender->sendMessage('コンソールからは実行できません');
			return true;
        }
        
		if(!isset($args[0])) return false;
		switch($args[0]){
			case 'make':
                if(!isset($args[1])) return false;
                if(!$this->plugin->playermanager->hasMoney($sender, 30000000)){
                    $sender->sendMessage('§aGUILD§f>>お金が足りません！ギルドの作成には3000万円が必要です！');
                    return false;
                }
                $this->plugin->playermanager->takeMoney($sender, 30000000);
                $this->plugin->guild->makeGuild($args[1], $sender);
			break;
			case 'join':
                if(!isset($args[1])) return false;
                if(!$this->plugin->guild->isExist($args[1])){
                $sender->sendMessage('そのギルドは存在しません。');
                return false;
                }
                if($this->plugin->guild->isLeader($sender)){
                    $sender->sendMessage('リーダーはほかのギルドに行けません。');
                    return false;
                }
                $this->plugin->guild->addReq($args[1], $sender);
                $sender->sendMessage('申請しました！');
			break;
			case 'accept':
				if(!isset($args[1])) return false;
				$guild = $this->plugin->playerData[$sender->getName()]['guild'];
				$player = $this->plugin->getServer()->getPlayer($args[1]);
				if(!isset($player)){
					$sender->sendMessage('そのプレイヤーは存在しません。');
					return false;
                }
                if(!$this->plugin->guild->isLeader($sender)){
                  $sender->sendMessage('あなたはリーダーではありません。');
                  return false;
                }
				if($this->plugin->guild->isReq($guild, $player))
                  $this->plugin->guild->addGuild($guild, $player);
                $sender->sendMessage($player->getName().'参加処理が完了しました！');
            break;
            case 'leave':
                if($this->plugin->guild->isLeader($sender)){
                    $sender->sendMessage('リーダーはギルドを抜けられません。');
                    return false;
                }
                $this->plugin->guild->unsetGuild($sender);
                $sender->sendMessage('ギルドを脱退しました。');
            break;
            case 'kick':
                if(!isset($args[1])) return false;
                if(!$this->plugin->guild->isLeader($sender)){
                    $sender->sendMessage('あなたはリーダーではありません。');
                    return false;
                }
                $player = $this->plugin->getServer()->getPlayer($args[1]);
				if(!isset($player)){
					$sender->sendMessage('そのプレイヤーは存在しません。');
					return false;
                }
                $this->plugin->guild->unsetGuild($player);
                $player->sendMessage('ギルドからkickされました。');
                $sender->sendMessage('ギルドを脱退させました。');
            break;
            case 'delete':
                if(!$this->plugin->guild->isLeader($sender)){
                    $sender->sendMessage('あなたはリーダーではありません。');
                    return false;
                }
                $this->plugin->guild->deleteGuild($sender);
                $sender->sendMessage('ギルドを削除しました。');
            break;
            case 'point':
                $guild = $this->plugin->playerData[$sender->getName()]['guild'];
                if(!$this->plugin->guild->isLeader($sender)){
                    $sender->sendMessage('あなたはリーダーではありません。');
                    return false;
                }
                $field = $this->plugin->fieldmanager->getField($sender); 
                if($this->plugin->fieldmanager->isBattleField($field)){
                    $sender->sendMessage('MOBのいるフィールドには設定できません。');
                    return false;
                }
                $sender->sendMessage('設定しました！');
                $this->plugin->guild->setGuildPoint($guild, $sender->getPosition(), $field);
            break;
            case 'chest':
                $guild = $this->plugin->playerData[$sender->getName()]['guild'];
                if($guild === '旅人'){
                    $sender->sendMessage('初期ギルドではチェストを使用できません。');
                    return false;
                }
                $this->plugin->chest->setQ($sender);
                $sender->sendMessage('ロックしたいチェストをタッチしてください。');
            break;
            case 'tp':
                $this->plugin->guild->TP($sender);
            break;
            case 'check':
                if(!$this->plugin->guild->isJoin($sender)){
                  $sender->sendMessage('§eINFO§f>>ギルドに参加していません！');
                  return false;
                }
                $guild = $this->plugin->guild->getGuild($sender);
                $exp = $this->plugin->guild->getExp($guild);
                $sender->sendMessage('§eINFO§f>>'.$guild.'§f§rの経験値は、'.$exp.'pointです！。');
                break;
            case 'dungeon':
                if(!$this->plugin->guild->isLeader($sender)){
                    $sender->sendMessage('あなたはリーダーではありません。');
                    return false;
                }
                if(!isset($args[1])){
                    $sender->sendMessage('§eINFO§f>>使い方が間違っています。');
                    $sender->sendMessage('§eINFO§f>>/guild dungeon [難易度]');
                    return false;
                }
                if(1 > $args[1] or 4 < $args[1]){
                    $sender->sendMessage('§eINFO§f>>難易度は1-4のみとなっています。');
                    return false;
                }
                $need = $this->need[$args[1] - 1];
                $guild = $this->plugin->guild->getGuild($sender);
                $exp = $this->plugin->guild->getExp($guild);
                if($exp < $need){
                    $sender->sendMessage('§eINFO§f>>経験値が足りません。');
                    return false;
                }
                $this->plugin->guild->addExp($guild, $need * -1);
                $players = $this->plugin->guild->getOnlinePlayers($guild);
                $this->plugin->dungeon->onStart($players, $args[1], true);
            break;
		}
		return true;
    }
}