<?php

namespace ChunkWorldEditorPlus\type;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\item\Item;
use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\event\Listener;

use pocketmine\level\Position;
use pocketmine\math\AxisAlignedBB;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerInteractEvent;

class range{
	public static $Range = [];

	public $pos = [];

	public function __construct(?Vector3 $vector3_1 = null,?Vector3 $vector3_2 = null){
		$this->setPos(0,$vector3_1);
		$this->setPos(1,$vector3_2);
	}

	public static function get(String $name): range{
		if(!isset(self::$Range[$name])){
			self::$Range[$name] = new self();
		}
		return self::$Range[$name];
	}

	public function hasPos(int $pointer): bool{
		return isset($this->pos[$pointer]);
	}

	public function setPos(int $pointer,?Vector3 $pos){
		$this->pos[$pointer] = $pos;
	}

	public function getPos(int $pointer): ?Vector3{
		return $this->pos[$pointer] ?? null;
	}

	public function unsetPos(){
		$this->setPos(0,null);
		$this->setPos(1,null);
	}

	public function isCompleted(): bool{
		return $this->hasPos(0)&&$this->hasPos(1);
	}

	public function getRangePos(): ?array{//
		if($this->isCompleted()){
			$pos1 = $this->getPos(0);
			$pos2 = $this->getPos(1);
			$sx = min($pos1->x, $pos2->x);
			$sy = min($pos1->y, $pos2->y);
			$sz = min($pos1->z, $pos2->z);
			$ex = max($pos1->x, $pos2->x);
			$ey = max($pos1->y, $pos2->y);
			$ez = max($pos1->z, $pos2->z);
			return [$sx,$sy,$sz,$ex,$ey,$ez];
		}
		return null;
	}

	public function CountBlocks(): ?int{
		if($this->isCompleted()){
			return self::CountBlocksByRangePos(...$this->getRangePos());
		}
		return null;
	}

	public static function CountBlocksByRangePos($sx,$sy,$sz,$ex,$ey,$ez){
		return ($ex - $sx + 1) * ($ey - $sy +1) * ($ez - $sz + 1);
	}

	public function spread(int $spread = 1){
		
	}

	public static function RangeByPlayer(Player $player,int $spread = 1): ?array{
		//$Range = new self($player->floor(),$player->floor())->;
		//return [(int) ($player->x-$spread),(int) ($player->y-$spread),(int) ($player->z-$spread),(int) ($player->x+$spread),(int) ($player->y+$spread),(int) ($player->z+$spread)];
	}

	/*public function CountBlocksByRangePos(): ?int{
		if($this->isCompleted()){
			list($sx,$sy,$sz,$ex,$ey,$ez) = $this->getRangePos();
			return ($ex - $sx + 1) * ($ey - $sy +1) * ($ez - $sz + 1);
		}
		return null;
	}*/

	/*public function toAxisAlignedBB(): AxisAlignedBB{
		return 
	}*/
}	