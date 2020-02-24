<?php

namespace ChunkWorldEditorPlus\type;

class checker{
	public static function getId(String $ids): array{//35:0
		$ids = explode(":", $ids);
		return [($ids[0] ?? 0),($ids[1] ?? 0)];
	}

	public static function checkId(String $ids): bool{//35:0
		$ids = explode(":", $ids);
		if(isset($ids[0])){
			if(!self::checkInt($ids[0])){
				return false;
			}
		}else{
			return false;//
		}

		if(isset($ids[1])){
			if(!self::checkInt($ids[1])){
				return false;
			}
		}
		return true;
	}

	public static function checkInt(String $int): bool{
		return preg_match("/[0-9]/", $int);
	}
}
