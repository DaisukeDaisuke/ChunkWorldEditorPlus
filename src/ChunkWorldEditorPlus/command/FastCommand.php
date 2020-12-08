<?php
namespace ChunkWorldEditorPlus\command;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\level\Level;

use ChunkWorldEditorPlus\ChunkWorldEditorAPI;

use ChunkWorldEditorPlus\command\ManagerInterface;

use ChunkWorldEditorPlus\type\undo;
use ChunkWorldEditorPlus\type\range;

use ChunkWorldEditorPlus\task\AsyncUndoTask;

use pocketmine\tile\Tile;

class FastCommand implements ManagerInterface{
	//use ManagerTrait;

	public static function init(){
		
	}

	public function onRun(String $command,Player $player,range $Range,array $args): bool{//mainより
		if($command === "cfast"){
			if(ChunkWorldEditorAPI::ToggleExecuteundo()){
				$player->sendMessage("undoを無効化致しました!");
			}else{
				$player->sendMessage("undoを有効化致しました!");
			}
			return true;
		}
		return false;
	}
}