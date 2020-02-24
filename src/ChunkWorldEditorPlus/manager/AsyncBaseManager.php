<?php
namespace ChunkWorldEditorPlus\manager;

use pocketmine\Player;
use pocketmine\level\Level;

use ChunkWorldEditorPlus\command\BaseCommand;

use ChunkWorldEditorPlus\type\range;

class AsyncBaseManager extends AsyncTask{
	public static $list = [];

	abstract public static function init();
	abstract public function onRun(String $command,Player $player,range $Range,array $args): bool;//mainより

	public function __construct(){
		//none
	}

	public static function registerCommand(String $class){
		self::$list[$class::getCommand()] = new $class();
	}

	public static function hasCommand(String $command): bool{//
		return isset(self::$list[$command]);
	}

	public static function getCommand(String $command): BaseCommand{//
		return clone self::$list[$command];
	}
}
