<?php

namespace App\Contract;

use PHPUnit\Framework\TestCase as PhpUnitTestCase;

abstract class AbstractTestCase extends PhpUnitTestCase{

	public function core(){

		return Registry::getInstance();
	}
} 