<?php

namespace App\Contract;

use Doctrine\DBAL\Migrations\AbstractMigration as DoctrineAbstractMigration;

abstract class AbstractMigration extends DoctrineAbstractMigration{

	use CoreTraits;
}