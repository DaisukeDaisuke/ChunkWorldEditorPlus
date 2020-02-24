<?php
namespace ChunkWorldEditorPlus\command\multi;

use ChunkWorldEditorPlus\command\sync\setCommand;

use pocketmine\Player;
use pocketmine\level\Level;

use ChunkWorldEditorPlus\command\BaseCommand;
use ChunkWorldEditorPlus\type\checker;
use ChunkWorldEditorPlus\type\range;

use ChunkWorldEditorPlus\ChunkWorldEditorAPI;

class multisetcommand extends setCommand{
	public static function getCommand(): String{
		return "cmset";
	}

	public function getLabel(Player $player,array $args): String{
		$ids = checker::getId($args[0]);
		return "Async_set_faster_multi ".$ids[0].":".$ids[1];
	}
}
