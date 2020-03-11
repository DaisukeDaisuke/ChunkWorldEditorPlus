<?php
namespace ChunkWorldEditorPlus;

use pocketmine\tile\Tile;
use pocketmine\level\Level;
use pocketmine\math\AxisAlignedBB;

class ChunkWorldEditorAPI{
	public static $tap = [];

	public static function getChunks(Level $level,int $sx,int $sy,int $sz,int $ex,int $ey,int $ez): array{
		$chunks = [];
		for($x = $sx; $x - 16 <= $ex; $x += 16){
			for($z = $sz; $z - 16 <= $ez; $z += 16){
				$chunk = $level->getChunk($x >> 4, $z >> 4, true);
				$chunks[Level::chunkHash($x >> 4, $z >> 4)] = $chunk->fastSerialize();
			}
		}
		return $chunks;
	}

	//=> BaseCommand.php
	public static function DecodeChunks(array $chunks): array{
		$returnchunks = [];
		foreach($chunks as $hash => $binary){
			$returnchunks[$hash] = \pocketmine\level\format\Chunk::fastDeserialize($binary);
		}
		return $returnchunks;
	}

	public static function setChunks(Level $level,array $chunks,array $changedchunk){
		foreach($chunks as $hash => $chunk){
			if(isset($changedchunk[$hash])){
				Level::getXZ($hash, $x, $z);
				$level->setChunk($x, $z, $chunk, false);
			}
		}
	}

	public static function isExecuteundo(array &$args): bool{
	 	if(isset($args[0])&&$args[0] === "-a"){
			unset($args[0]);
			$args = array_values($args);
			return true;
		}
		return false;
	}

	public static function TileBackup(Level $level,array $RangePos): array{
		list($sx,$sy,$sz,$ex,$ey,$ez) = $RangePos;
		$chunkTiles = [];
		for($x = $sx; $x - 16 <= $ex; $x += 16){
			for($z = $sz; $z - 16 <= $ez; $z += 16){
				$chunk = $level->getChunk($x >> 4, $z >> 4, true);
				if (($tiles = self::TileEncode($chunk->getTiles())) !== null) $chunkTiles[] = $tiles;
			}
		}
		var_dump($chunkTiles);
		return $chunkTiles;
	}

	public static function Tileundo(Level $level,array $RangePos,array $tiles,Callable $fun){
		$AxisAlignedBB = new AxisAlignedBB(...$RangePos);//
		foreach(self::TileDecode($level,$RangePos,$tiles) as $tile){
			if($AxisAlignedBB->isVectorInside($tile->asVector3())) continue;
			if($fun($level,$RangePos,$tiles)){
				$level->addTile($tile);
				$tile->spawnToAll();
			}
		}
	}

	public static function onDivide(array $RangePos,int $thread = 4): ?\Generator{
		list($sx,$sy,$sz,$ex,$ey,$ez) = $RangePos;
		if(abs($ex-$sx) >= $thread){
			if($ex-$sx > 0){
				$array = self::divide($sx,$ex,$thread);
			}else{
				$array = self::divide($ex,$sx,$thread);
			}
			$current = $array[0];
			$count1 = count($array)-1;
			for($i = 1; $i <= $count1; $i++){
				if($i === 1){
					yield [$current,$sy,$sz,$array[$i],$ey,$ez];
				}else{
					yield [$current + 1,$sy,$sz,$array[$i],$ey,$ez];
				}
				$current = $array[$i];
			}
		}else if(abs($ez-$sz) >= $thread){
			if($ez-$sz > 0){
				$array = self::divide($sz,$ez,$thread);
			}else{
				$array = self::divide($ez,$sz,$thread);
			}
			$current = $array[0];//
			$count1 = count($array)-1;
			for($i = 1; $i <= $count1; $i++){
				if($i === 1){
					yield [$sx,$sy,$current,$ex,$ey,$array[$i]];//
				}else{
					yield [$sx,$sy,$current + 1,$ex,$ey,$array[$i]];//
				}
				$current = $array[$i];
			}
		}else{
			/*
			$player->sendMessage("x軸の合計及び、y軸の合計は、スレッド数(現在: ".$thread."スレッドです...)よりも一致または、多くする必要があります。");
			$player->sendMessage("代わりと致しましては、「/////set」 又は「/////setpp」を利用して頂きたいです...");
			*/
			return null;
		}
	}

	public static function divide(int $sz,int $ez,int $thread,int $c1 = 1): array{
		$array = [];
		$count = (int) (($ez - $sz) / $thread);
		$remainder = ($ez - $sz) % $thread;
		for($z = $sz;$z + $remainder <= $ez; $z = $z + $count){
			if($z !== $sz&&$remainder !== 0){
				$z++;
				$remainder--;
			}
			$array[] = $z;
		}
		$count2 = count($array)-1;
		$count3 = count($array)-2;
		$max = $array[$count2];
		for($i = 1; $i <= $count3; $i++){
			$return = self::roundUpToAny($array[$i],16);
			if($return < $max){//
				$array[$i] = (int) $return - $c1;
			}else{
				$array[$i] = (int) $max;
			}
		}
		$oldarray = $array;
		for($i = 1; $i <= $count2; $i++){
			if($oldarray[$i-1] == $array[$i]){
				unset($array[$i]);
			}
		}
		return array_values($array);
	}

	public static function TileEncode(array $tiles): ?array{
		$return = [];
		foreach($tiles as $object){
			$return[] = [$object->saveNBT(),get_class($object)];
		}
		return count($return) !== 0 ? $return : null;
	}

	public static function TileDecode(Level $level,array $RangePos,array $ChunkTiles): \Generator{
		//var_dump($ChunkTiles);
		foreach($ChunkTiles as $tiles){
			foreach($tiles as $tile){
				//$className = $tile[1];
				//var_dump($tile);
				yield (new $tile[1]($level,$tile[0]));
			}
		}
	}

	public static function roundUpToAny(int $n,int $x = 5): int{
		return (ceil($n)%$x === 0) ? ceil($n) : round(($n+$x/2)/$x)*$x;
	}

	public static function isTapRestricted(string $name,int $limit = 1): bool{//...?
		$now = time();
		if(isset(self::$tap[$name]) and $now - self::$tap[$name] < $limit){
			//クリック無効期間...
			//unset(self::$tap[$name]);
			return false;
		}else{
			self::$tap[$name] = $now;
			return true;
		}
	}

	/*
		thanks
		https://qiita.com/kojionilk/items/62290d01d3783cc0e526
	*/
	public static function is_natural($val): bool{
		return (bool) preg_match('/\A[1-9][0-9]*\z/', $val);
	}
}