<?php

/*
License

The MIT License (MIT)

Copyright (c) 2017 Falkirks

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

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
