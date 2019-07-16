<?php

namespace App\Contract;

use Strukt\Core\Registry;

abstract class AbstractSeeder{

	public function core(){

		return Registry::getInstance();
	}
}