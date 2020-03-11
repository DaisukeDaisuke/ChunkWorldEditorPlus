<?php

namespace ChunkWorldEditorPlus\type;

use pocketmine\Server;

use pocketmine\level\Level;
use pocketmine\math\AxisAlignedBB;
use ChunkWorldEditorPlus\command\BaseCommand;

class undo{
	public static $undo = [];

	public $name;
	public $className = null;
	public $LevelName = null;
	public $ChunkTile = null;
	public $RangePos = null;
	public $args = null;
	public $data = null;

	public static function get(String $name): self{
		if(!isset(self::$undo[$name])){
			self::$undo[$name] = new self($name);
		}
		return self::$undo[$name];
	}

	/*public static function set(self $undo){
		self::$undo[$undo->getName()] = $undo;
	}*/

	public function __construct(String $name){
		$this->name = $name;
	}

	public function reset(): self{
		$this->setTarget(null);
		$this->setLevelName(null);
		$this->setChunkTile(null);
		$this->setData(null);
		$this->setArgs(null);
		$this->setRangePos(null);
		//$this->setThreadCount(null);
		return $this;
	}

	/*public function update(){
		 self::set($this);
	}*/

	public function isCanUndo(): bool{
		if(!$this->getTarget() === null){
			return false;
		}
		if(!$this->getLevel() === null){
			return false;
		}
		if(!$this->getChunkTile() === null){
			return false;
		}
		if(!$this->getData() === null){
			return false;
		}
		if(!$this->getArgs() === null){
			return false;
		}
		if(!$this->getRangePos() === null){
			return false;
		}
		/*if(!$this->isComplete()){
			return false;
		}*/
		return true;
	}

	public function getName(): String{
		return $this->name;
	}

	public function getChunkTile(): ?array{
		return $this->ChunkTile;
	}

	public function setChunkTile(?array $ChunkTile){
		$this->ChunkTile = $ChunkTile;
	}

	public function setLevelName(?String $LevelName){
		$this->LevelName = $LevelName;
	}

	public function getLevelName(): ?String{
		return $this->LevelName;
	}


	/*
		メインスレッド専用関数にてございます...
		もし、この関数をメインスレッド外よりコール致しますと、「segmentation fault」は発生致します可能性は高いと、私は思います。
	*/
	public function getLevel(): ?Level{
		if(($levelName = $this->getLevelName()) === null){
			return null;
		}
		return Server::getInstance()->getLevelByName($levelName);
	}

	public function setTarget(?BaseCommand $target){
		if($target === null){
			$this->className = null;
			return;
		}
		$this->className = get_class($target);
	}

	public function getTargetName(): ?String{
		return $this->className;
	}

	public function getTarget(): ?BaseCommand{
		if(($ClassName = $this->getTargetName()) === null){
			return null;
		}
		return new $ClassName();
	}

	/*public function setThreadCount(?int $ThreadCount){
		$this->ThreadCount = $ThreadCount;
	}*/

	//public function getThreadCount(): ?int{
		/*if($this->data === null){
			return null;
		}*/
		//return $this->ThreadCount;
	//}

	/*public function isComplete(): bool{
		if($this->getData() === -1){
			return true;
		}
		return count($this->getData() ?? [])=== $this->getThreadCount();
	}*/

	public function setRangePos(?array $array = []){
		if($array === null){
			$this->RangePos = null;
			return;
		}
		$this->RangePos = json_encode($array);
	}

	/*public function addRangePos(int $ThreadId,array $chunk){
		if($this->RangePos === null){
			$this->RangePos = [];
		}
		$this->RangePos[$ThreadId] = $chunk;
	}*/

	public function getRangePos(): ?array{
		if($this->RangePos === null){
			return null;
		}
		return json_decode($this->RangePos,true);
	}

	public function getAxisAlignedBB(): ?AxisAlignedBB{
		$RangePos = $this->getRangePos();
		if($RangePos === null){
			return null;
		}
		return (new AxisAlignedBB(...$RangePos));
	}

	public function setArgs(?array $array = []){
		if($array === null){
			$this->args = null;
			return;
		}
		$this->args = json_encode($array);
	}

	public function getArgs(): ?array{
		if($this->args === null){
			return null;
		}
		return json_decode($this->args,true);
	}


	public function setData(?array $data){
		if ($data === null) {
			$this->data = null;
			return;
		}
		$this->data = serialize($data);
	}

	public function getData(): ?array{
		if($this->data === null){
			return null;
		}
		return unserialize($this->data);
	}

	/*public function addData(array $chunk){
		if($this->data === null){
			$this->data = [];
		}
		$this->data[$ThreadId] = $chunk;
	}*/

	/*public function getAll(): ?array{
		return $this->data;
	}*/

}