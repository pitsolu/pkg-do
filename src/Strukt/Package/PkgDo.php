<?php

namespace Strukt\Package;

use Strukt\Contract\Package as Pkg;

class PkgDo implements Pkg{

	private $manifest;

	public function __construct(){

		$this->manifest = array(

			"package"=>"pkg-do",
			"files"=>array(

				"cfg/db.ini",
				"database/schema/Schema/Migration/Version20170630235421.php",
				"database/seeder/Seed/User20180626221519.php",
				"database/seeder/Seed/Role20180626221244.php",
				"lib/App/Provider/EntityManager.php",
				"lib/App/Provider/EntityManagerAdapter.php",
				"lib/App/Provider/SchemaMeta.php",
				"lib/App/Provider/Normalizer.php",
				"lib/App/Service/Normalizer/DateTime.php",
				"lib/App/Service/Seeder/Json.php",
				"lib/App/Service/Seeder/Xls.php",
				"lib/App/Service/Logger/Monologer.php",
				"lib/App/Contract/AbstractMigration.php",
				"lib/App/Contract/AbstractSeeder.php",
				"lib/App/Contract/AbstractTestCase.php",
				"lib/App/Contract/Entity.php",
				"lib/App/Command/Doctrine/SqlExec.php",
				"lib/App/Command/Doctrine/Migration/GenerateMigration.php",
				"lib/App/Command/Doctrine/Migration/MigrateExec.php",
				"lib/App/Command/Doctrine/GenerateModels.php",
				"lib/App/Command/Doctrine/Seeder/SeederXls.php",
				"lib/App/Command/Doctrine/Seeder/GenerateSeeder.php",
				"lib/App/Command/Doctrine/Seeder/SeederJson.php",
				"lib/App/Command/Doctrine/Seeder/SeederExec.php",
				"tpl/sgf/database/schema/Schema/Migration/Version_.sgf",
				"tpl/sgf/database/seeder/Seed/NameVer.sgf"
			)
		);
	}

	public function getName(){

		return $this->manifest["package"];
	}

	public function getFiles(){

		return $this->manifest["files"];
	}

	public function getModules(){

		return null;
	}
}