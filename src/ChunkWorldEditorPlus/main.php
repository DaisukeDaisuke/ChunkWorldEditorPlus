<?php
namespace ChunkWorldEditorPlus;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;

use pocketmine\command\Command;
use pocketmine\plugin\PluginBase;

use ChunkWorldEditorPlus\type\range;
use pocketmine\command\CommandSender;
use ChunkWorldEditorPlus\manager\SyncManager;
use ChunkWorldEditorPlus\manager\MultiManager;
use ChunkWorldEditorPlus\manager\undo\UndoManager;

class main extends PluginBase{
	public static $managerlists = [];
	//public static $shortNames = [];

	public function onEnable(){
		$this->saveResource("config.yml");
		$config = new Config($this->getDataFolder()."config.yml",Config::YAML,[
			"ItemId" => "369",
			"ItemDamage" => "0",
			"Item_CustomName" => "§eChunkWorldEditor範囲指定",
		]);

		$this->loadmanager();

		$this->getServer()->getPluginManager()->registerEvents(new EventListener($config), $this);
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		if(!$sender->isOP()) return true;
		$Range = range::get($sender->getName());
		$managerlist = self::getmanagerlist();
		foreach($managerlist as $manager){
			if($manager->onRun($label,$sender,$Range,$args)){
				return true;
			}
		}
		return true;
	}

	public function loadmanager(){
		$array = [
			SyncManager::class,
			MultiManager::class,
			UndoManager::class,
		];
		foreach($array as $className){
			self::registerManager($className);
		}
	}

	public static function registerManager(String $className){
		$class = new \ReflectionClass($className);
		self::$managerlists[$class->getShortName()] = new $className();
		//self::$shortNames[] = $class->getShortName();
		
		$className::init();
	}

	public static function getmanagerlist(): array{
		return self::$managerlists;
	}
}
