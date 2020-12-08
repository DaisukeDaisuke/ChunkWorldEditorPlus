<?php

namespace ChunkWorldEditorPlus\command;

use ChunkWorldEditorPlus\editor\BaseCommand;

trait ManagerTrait{
	public static $list = [];

	public static function registerCommand(String $command,String $class){
		self::$list[$command] = new $class();
	}

	public static function hasCommand(String $command): bool{//
		return isset(self::$list[$command]);
	}

	public static function getCommand(String $command): BaseCommand{//
		return clone self::$list[$command];
	}
}
