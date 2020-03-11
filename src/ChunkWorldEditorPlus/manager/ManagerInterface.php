<?php
namespace ChunkWorldEditorPlus\manager;

use pocketmine\Player;
use pocketmine\level\Level;

use ChunkWorldEditorPlus\type\range;

interface ManagerInterface{
	public static function init();
	public function onRun(String $command,Player $player,range $Range,array $args): bool;//mainより
}
