<?php

class DeedfaxDAOFactory{

	public static function getDAO($type, $configuration = null){
		if(class_exists($type)) {
			return new $type($configuration);
		}
		else {
			throw new Exception("Invalid type given.");
		}

	}
}