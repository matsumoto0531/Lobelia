<?php
namespace pve;

use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\Plugin;
use pocketmine\event\Listener;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener{

  public function onEnable() : void{
    $this->server = $this->getServer();
    if (!file_exists($this->getDataFolder())) @mkdir($this->getDataFolder(), 0744, true);
    
    $this->playerConfig = new Config($this->getDataFolder() . "player17.yml",    Config::YAML, ["data" => ["player" => ["lv" => 1, "exp" => 0, "money" => 1, "guild" => "旅人", 'title'=> [], 'quest' => [], 'job' => [0, 0, 0]]]]);
    $this->playerData = $this->playerConfig->get('data');
    
    $this->spawnConfig = new Config($this->getDataFolder() . "spawn13.yml",    Config::YAML, ["data" => ["spawn" => ["shadow" => ['level' => 1, 'amount' => 0, 'boss' => false, 'pos' => ['x' => 0, 'y' => 0, 'z' => 0]]]]]);
    $this->spawnData = $this->spawnConfig->get('data');
    
    $this->fieldConfig = new Config($this->getDataFolder() . "field1.yml",    Config::YAML, ["data" => ["spawn" => ["x" => 55, "y" => 4, "z" => 55, "isbattle" => 0]]]);
    $this->fieldData = $this->fieldConfig->get('data');
    
    $this->itemConfig = new Config($this->getDataFolder() . "Item.yml",    Config::YAML, ["data" => [0 => ["itemid" => 0, "name" => 'error', "weapon" => 0, "armor" => 0, "skillid" => 0, "skilllv" => 0]]]);
    $this->itemData = $this->itemConfig->get('data');
    
    $this->forgeConfig = new Config($this->getDataFolder() . "forge.yml",    Config::YAML, ["data" => ['spawn' => ['x' => 50, 'y' => 4, 'z' => 50, 'yaw' => 90]]]);
    $this->forgeData = $this->forgeConfig->get('data');

    $this->shopperConfig = new Config($this->getDataFolder() . "shopper.yml",    Config::YAML, ["data" => ['spawn' => ['x' => 50, 'y' => 4, 'z' => 50, 'yaw' => 90]]]);
    $this->shopperData = $this->shopperConfig->get('data');

    $this->shopConfig = new Config($this->getDataFolder() . "shop.yml",    Config::YAML, ["data" => [0 => [0, 100]]]);
    $this->shopData = $this->shopConfig->get('data');

    $this->useitemConfig = new Config($this->getDataFolder() . "useitem.yml",    Config::YAML, ["data" => [0 => ['itemid' => 280, 'name' => 'unchi']]]);
    $this->useitemData = $this->useitemConfig->get('data');

    $this->tpConfig = new Config($this->getDataFolder() . "tp.yml",    Config::YAML, ["data" => [1 => 'test']]);
    $this->tpData = $this->tpConfig->get('data');

    $this->npcConfig = new Config($this->getDataFolder() . "npc.yml",    Config::YAML, ["data" => ['spawn' => [0 => ['x' => 100, 'y' => 4, 'z' => 100, 'yaw' => 90, 'name' => '', 'message' => '']]]]);
    $this->npcData = $this->npcConfig->get('data');

    $this->guildConfig = new Config($this->getDataFolder() . "guild.yml",    Config::YAML, ["data" => ['旅人' => ['leader' => 'muku787', 'point' => ['x' => 0, 'y' => 0, 'z' => 0], 'exp' => 0]]]);
    $this->guildData = $this->guildConfig->get('data');

    $this->chestConfig = new Config($this->getDataFolder() . "chest.yml",    Config::YAML, ["data" => [1 => '旅人']]);
    $this->chestData = $this->chestConfig->get('data');

    $this->chestConfig = new Config($this->getDataFolder() . "chest.yml",    Config::YAML, ["data" => [1 => '旅人']]);
    $this->chestData = $this->chestConfig->get('data');

    $this->titleConfig = new Config($this->getDataFolder() . "title.yml",    Config::YAML, ["data" => [0 => '旅の始まり']]);
    $this->titleData = $this->titleConfig->get('data');

    $this->rankConfig = new Config($this->getDataFolder() . "rank.yml",    Config::YAML, ["data" => [1 => 'F']]);
    $this->rankData = $this->rankConfig->get('data');

    $this->jsonConfig = new Config($this->getDataFolder() . "sss.json",    Config::JSON, ["data" => []]);
    $this->jsonData = $this->jsonConfig->get('data');

    $this->lastConfig = new Config($this->getDataFolder() . "last.json",    Config::JSON, ["data" => ["p" => ["x" => 0, "y" => 4, "z" => 0, 'field' => 'Lobelia']]]);
    $this->lastData = $this->lastConfig->get('data');

    $this->homeConfig = new Config($this->getDataFolder() . "home.json",    Config::JSON, ["data" => ["p" => [["x" => 0, "y" => 4, "z" => 0, 'field' => 'Lobelia'], ["x" => 0, "y" => 4, "z" => 5, 'field' => 'Lobelia']]]]);
    $this->homeData = $this->homeConfig->get('data');

    $this->eventConfig = new Config($this->getDataFolder() . "event.json",    Config::JSON, ["data" => ["p" => 'true']]);
    $this->eventData = $this->eventConfig->get('data');

    $this->swordConfig = new Config($this->getDataFolder() . "LobeliaSword.yml",    Config::YAML, ["data" => [0 => '旅の始まり']]);
    $this->swordData = $this->swordConfig->get('data');

    $this->accessoryConfig = new Config($this->getDataFolder() . "accessory.yml",    Config::YAML, ["data" => [0 => '旅の始まり']]);
    $this->accessoryData = $this->accessoryConfig->get('data');

    $this->recipeSwordConfig = new Config($this->getDataFolder() . "LobeliaSwordRecipe.yml",    Config::YAML, ["data" => [0 => '旅の始まり']]);
    $recipeData = $this->recipeSwordConfig->get('data');

    foreach($recipeData as $recipe){
      $type = $this->swordData[$recipe['make']]['type'];
      $this->recipeSwordData[] = ['rare' => $recipe['rare'], 'make' => $recipe['make'], 'items' => $recipe['items'], 'swords' => $recipe['swords'], 'ores' => $recipe['ores'], 'id' => $recipe['id']];
    }

    $this->armorConfig = new Config($this->getDataFolder() . "LobeliaArmor.yml",    Config::YAML, ["data" => [0 => '旅の始まり']]);
    $this->armorData = $this->armorConfig->get('data');
    
    $this->recipeArmorConfig = new Config($this->getDataFolder() . "LobeliaArmorRecipe.yml",    Config::YAML, ["data" => [0 => '旅の始まり']]);
    $recipeData = $this->recipeArmorConfig->get('data');

    foreach($recipeData as $recipe){
      $type = $this->armorData[$recipe['make']]['type'];
      $this->recipeArmorData[] = ['rare' => $recipe['rare'], 'make' => $recipe['make'], 'items' => $recipe['items'], 'armors' => $recipe['armors'], 'ores' => $recipe['ores'], 'id' => $recipe['id']];
    }

    $this->recipeOrbConfig = new Config($this->getDataFolder() . "LobeliaOrbRecipe.yml",    Config::YAML, ["data" => [0 => '旅の始まり']]);
    $recipeData = $this->recipeOrbConfig->get('data');

    foreach($recipeData as $recipe){
      $this->recipeOrbData[] = ['rare' => $recipe['rare'], 'make' => $recipe['make'], 'lv' => $recipe['lv'], 'items' => $recipe['items'], 'ores' => $recipe['ores'], 'id' => $recipe['id']];
    }

    $this->dungeonConfig = new Config($this->getDataFolder() . "dungeon.yml",    Config::YAML, ["data" => [1 => 'test']]);
    $this->dungeonData = $this->dungeonConfig->get('data');
    
    if(!is_file($this->getDataFolder() . "npcs.dat")){
			file_put_contents($this->getDataFolder() . "npcs.dat", serialize([]));
		}

    if(!is_file($this->getDataFolder() . "accessory.dat")){
			file_put_contents($this->getDataFolder() . "accessory.dat", serialize([]));
		}

    if(!is_file($this->getDataFolder() . "souko.dat")){
			file_put_contents($this->getDataFolder() . "souko.dat", serialize([]));
		}
    
    scoreboard\ScoreboardManager::init($this);
    //bossbar\BossBarManager::init($this);
    item\ItemManager::init($this);
    dungeon\DungeonManager::init($this);
    MobManager::init($this);
    WeaponManager::init($this);
    ArmorManager::init($this);
    SkillManager::init($this);
    WeaponSkillManager::init($this);
    QuestManager::init($this);
    SetBonusManager::init($this);
    SpecialSkillManager::init($this);
    inventory\inventoryui\InventoryUI::setup($this);
    $this->playermanager = new PlayerManager($this);
    $this->form = new FormManager($this);
    $this->addItem = new addItem\addItem($this);
    $this->styleform = new StyleForm($this);
    $this->fieldmanager = new FieldManager($this);
    $this->mob = new Mob($this);
    $this->animation = new animation\AddAnimation($this);
    $this->setting = new Setting($this);
    $this->forge = new Forge($this);
    $this->npc = new npc\Npc($this);
    $this->shop = new Shopper($this);
    $this->fieldtp = new FieldTP($this);
    $this->dungeontp = new DungeonTP($this);
    $this->guild = new GuildManager($this);
    $this->chest = new ChestLock($this);
    $this->lbas = new Lbas($this);
    $this->mine = new Mine($this);
    $this->party = new Party($this);
    //$this->dungeon = new dungeon\Dungeon($this);
    $this->entity = new entity\test\Entitys($this);
    $this->recipe = new Recipe($this);
    $this->acc = new accessory\Accessory($this);
    $this->accessory = new inventory\AccessoryInventory($this);
    $this->sellwindow = new inventory\SellWindow($this);
    $this->job = new job\Job($this);
    $this->server->getPluginManager()->registerEvents($this->mob, $this);
    $this->server->getPluginManager()->registerEvents($this->fieldmanager, $this);
    $this->server->getPluginManager()->registerEvents($this->setting, $this);
    $this->server->getPluginManager()->registerEvents($this->form, $this);
    $this->server->getPluginManager()->registerEvents($this->forge, $this);
    $this->server->getPluginManager()->registerEvents($this->npc, $this);
    $this->server->getPluginManager()->registerEvents($this->shop, $this);
    $this->server->getPluginManager()->registerEvents($this->playermanager, $this);
    $this->server->getPluginManager()->registerEvents($this->guild, $this);
    $this->server->getPluginManager()->registerEvents($this->chest, $this);
    $this->server->getPluginManager()->registerEvents($this->mine, $this);
    $this->server->getPluginManager()->registerEvents($this->party, $this);
    $this->server->getPluginManager()->registerEvents($this->styleform, $this);
    $this->server->getPluginManager()->registerEvents($this->accessory, $this);
    $this->server->getPluginManager()->registerEvents($this->sellwindow, $this);
    $map = $this->getServer()->getCommandMap();
    $map->register("pve", new PVECommand($this));
    $map->register("guild", new GuildCommand($this));
    $map->register("log", new LogCommand($this));
    $map->register("mpay", new PayCommand($this));
    $map->register("party", new PartyCommand($this));
    $map->register("home", new HomeCommand($this));
    if($this->server->hasWhitelist())
		{
			$this->server->getNetwork()->setName("§l§eNow Loading...");
		}
	else
		{
			$this->server->getNetwork()->setName("§l§aLobelia §9❀");
		}
    $this->saveLevel();
  }
  
  public function onDisable() : void{
      $this->playerConfig->set('data', $this->playerData);
      $this->playerConfig->save();
      
      $this->spawnConfig->set('data', $this->spawnData);
      $this->spawnConfig->save();
      
      $this->fieldConfig->set('data', $this->fieldData);
      $this->fieldConfig->save();
      
      $this->itemConfig->set('data', $this->itemData);
      $this->itemConfig->save();
      
      $this->forgeConfig->set('data', $this->forgeData);
      $this->forgeConfig->save();

      $this->shopperConfig->set('data', $this->shopperData);
      $this->shopperConfig->save();

      $this->useitemConfig->set('data', $this->useitemData);
      $this->useitemConfig->save();

      $this->tpConfig->set('data', $this->tpData);
      $this->tpConfig->save();
      
      $this->npcConfig->set('data', $this->npcData);
      $this->npcConfig->save();

      $this->guildConfig->set('data', $this->guildData);
      $this->guildConfig->save();

      $this->chestConfig->set('data', $this->chestData);
      $this->chestConfig->save();

      $this->jsonConfig->set('data', $this->jsonData);
      $this->jsonConfig->save();

      $this->lastConfig->set('data', $this->lastData);
      $this->lastConfig->save();

      $this->homeConfig->set('data', $this->homeData);
      $this->homeConfig->save();

      $this->eventConfig->set('data', $this->eventData);
      $this->eventConfig->save();

      $this->dungeonConfig->set('data', $this->dungeonData);
      $this->dungeonConfig->save();
      
      $d = $this->mob->getSkins();
      foreach($d as $name => $skin){
        $data[$name] = ['id' => $skin->getSkinId(), 'data' => $skin->getSkinData(),
                        'gname' => $skin->getGeometryName(), 'gdata' => $skin->getGeometryData()
                       ];
      }
      file_put_contents($this->getDataFolder() . "npcs.dat", serialize($data));
      file_put_contents($this->getDataFolder() . "accessory.dat", serialize($this->accessory->contents));
      file_put_contents($this->getDataFolder() . "souko.dat", serialize($this->accessory->souko));
  }

  public function saveLevel(){
    $this->getServer()->broadCastMessage('§eINFO>>ワールドを保存します。サーバーが重くなる可能性があります。');
    $this->getServer()->getWorldManager()->getDefaultWorld()->save(true);
    $this->getScheduler()->scheduleDelayedTask(
      new Callback([$this, 'saveLevel'], []), 20 * 60 * 30);
  }


}
?>
