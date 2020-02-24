<?php

namespace ChunkWorldEditorPlus\command;

use pocketmine\Player;
use pocketmine\level\Level;

use ChunkWorldEditorPlus\type\range;

abstract class BaseCommand{
	//abstract public function onRun(Player $player,Level $level,array $RangePos,array $args);
	abstract public function Preprocessing(Player $player,Level $level,array $RangePos,array $RealRangePos,array $args,?int $thread = null): array;
	abstract public function execute(array $RangePos,array $RealRangePos,array $argument): array;
	abstract public function Success($level,$argument);
	abstract public static function getCommand(): String;

	public $ThreadId = null;

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

	public function getTopLabel(): String{
		return "";
	}

	public function getStartMessage(Player $player,range $Range,array $args): String{
		return "[WorldEditor_Plus] ".$player->getName()."が変更を開始します…(".$this->getLabel($player,$args).") : ".$Range->CountBlocks()."ブロック)";
	}

	public function getEndMessage(): String{
		return "[WorldEditor_Plus] 変更が終了しました。";
	}
}
