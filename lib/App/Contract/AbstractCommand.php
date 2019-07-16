<?php

namespace App\Contract;

use Strukt\Core\Registry;

abstract class AbstractCommand extends \Strukt\Console\Command{

	public function core(){

		return Registry::getInstance();
	}
}