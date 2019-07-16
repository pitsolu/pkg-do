<?php

namespace App\Contract;

use Doctrine\DBAL\Migrations\AbstractMigration as DoctrineAbstractMigration;
use Strukt\Core\Registry;

abstract class AbstractMigration extends DoctrineAbstractMigration{

	public function core(){

		return Registry::getInstance();
	}
}