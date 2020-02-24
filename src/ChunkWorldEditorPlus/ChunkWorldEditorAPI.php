<?php
namespace ChunkWorldEditorPlus;

use pocketmine\level\Level;

class ChunkWorldEditorAPI{
	public static function getChunks(Level $level,int $sx,int $sy,int $sz,int $ex,int $ey,int $ez): array{
		$chunks = [];
		for($x = $sx; $x - 16 <= $ex; $x += 16){
			for($z = $sz; $z - 16 <= $ez; $z += 16){
				$chunk = $level->getChunk($x >> 4, $z >> 4, true);
				$chunks[Level::chunkHash($x >> 4, $z >> 4)] = $chunk;
			}
		}
		return $chunks;
	}

	public static function setChunks(Level $level,array $chunks){
		foreach($chunks as $hash => $chunk){
			Level::getXZ($hash, $x, $z);
			$level->setChunk($x, $z, $chunk, false);
		}
	}

	public static function onDivide(array $RangePos,int $thread = 4){
		//yield
		list($sx,$sy,$sz,$ex,$ey,$ez) = $RealRangePos;
		if(abs($ex-$sx) >= $thread){
			if($ex-$sx > 0){
				$array = $this->divide($sx,$ex,$thread);
			}else{
				$array = $this->divide($ex,$sx,$thread);
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
				$array = $this->divide($sz,$ez,$thread);
			}else{
				$array = $this->divide($ez,$sz,$thread);
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

	public static function divide($sz,$ez,$thread,$c1 = 1){
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
		$array = array_values($array);
		return $array;
	}

	public static function roundUpToAny($n,$x=5) {
		return (ceil($n)%$x === 0) ? ceil($n) : round(($n+$x/2)/$x)*$x;
	}
}