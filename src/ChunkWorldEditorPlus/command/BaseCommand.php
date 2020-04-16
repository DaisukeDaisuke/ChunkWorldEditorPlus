<?php

namespace ChunkWorldEditorPlus\command;

use pocketmine\Player;
use pocketmine\tile\Tile;

use pocketmine\level\Level;
use ChunkWorldEditorPlus\type\undo;
use ChunkWorldEditorPlus\type\range;
use ChunkWorldEditorPlus\ChunkWorldEditorAPI;

abstract class BaseCommand{
	const TYPE_SYNC = 0;
	const TYPE_MULTI = 1;

	//abstract public function onRun(Player $player,Level $level,array $RangePos,array $args);
	//abstract public function Preprocessing(Player $player,Level $level,array $RangePos,array $RealRangePos,array $args,?int $thread = null): array;
	abstract public function execute(array $RangePos,array $RealRangePos,array $argument): array;
	//abstract public function Success($level,$argument);
	//abstract public static function getCommand(): String;
	//abstract public function getType(): int;
	//public $chunks;
	public $name;
	public $ThreadId = null;
	public $Executeundo = null;

	public function Preprocessing(Player $player,Level $level,array $RangePos,array $RealRangePos,array $args): array{
		$this->setName($player->getName());//??
		//$this->setExecuteundo($Executeundo);
		/*if($this->isExecuteundo()){
			undo::get($player->getNane())->setRangePos($this->getThreadId(),$RealRangePos);
		}*/
		return [];
	}

	public function Success(Level $level,array $argument){
		/*if(!$this->isExecuteundo()){
			return;
		}
		undo::get($this->getNane())->addData($this->getThreadId(),$argument[2]);*/
	}

	/*public function onUndoPreprocessing(): array{
		return [];
	}*/

	public function onUndoBackup(undo $undo,Player $player,Level $level,array $RangePos,array $args){
		$undo->setData($this->getUndoChunks($player->getLevel(),$RangePos,$args));
		$undo->setRangePos($RangePos);
		$undo->setChunkTile(ChunkWorldEditorAPI::TileBackup($player->getLevel(),$RangePos));
		$undo->setLevelName($player->getLevel()->getName());
		$undo->setTarget($this);
		$undo->setArgs($args);
	}

	public function getUndoChunks(Level $level,array $RangePos,array $args){
		return ChunkWorldEditorAPI::getChunks($level,...$RangePos);
	}

	public function onundo(array $RangePos,array $RealRangePos,array $chunks,array $backupchunks,array $args): array{
		return [];
	}

	public function onUndoSuccess(Level $level,array $argument){
		
	}

	public function onTileUndo(Level $level,array $RangePos,array $data,array $args): bool{
		return true;
	}

	private function setName(String $name){
		$this->name = $name;
	}

	public function getName(): String{
		return $this->name;
	}

	/*private function setExecuteundo(bool $Executeundo){
		$this->Executeundo = $Executeundo;
	}

	public function isExecuteundo(): bool{
		return $this->Executeundo;
	}*/

	public function RequestRangePos(): bool{
		return true;
	}
	
	public function getRangePos(Player $player,array $args): ?array{
		return null;
	}

	public function setThreadId(?int $ThreadId){
		$this->ThreadId = $ThreadId;
	}

	public function getThreadId(): ?int{
		return $this->ThreadId;
	}

	public function request(): int{
		return 0;
	}

	public function check(array $args): bool{
		return true;
	}

	public function getLabel(Player $player,array $args): String{
		return "";
	}

	public function getLabelOption(Player $player,array $args): String{
		return "";
	}

	public function getTopLabel(): String{
		return "";
	}

	public function getStartMessage(Player $player,range $Range,array $args,String $label = ""): String{
		return "[WorldEditor_Plus] ".$player->getName()."が変更を開始します…(".$this->getLabel($player,$args).$label." ".$this->getLabelOption($player,$args).") : ".$Range->CountBlocks()."ブロック)";
	}

	public function getEndMessage(): String{
		return "[WorldEditor_Plus] 変更が終了しました。";
	}

	/*public function DecodeChunks(array $chunks){
		$returnchunks = [];
		foreach($chunks as $hash => $binary){
			$returnchunks[$hash] = \pocketmine\level\format\Chunk::fastDeserialize($binary);
		}
		return $returnchunks;
	}*/
}
