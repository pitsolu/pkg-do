<?php

namespace App\Provider;

use Strukt\Contract\AbstractProvider;
use Strukt\Contract\AbstractService;
use Strukt\Contract\ProviderInterface;
use Doctrine\ORM\QueryBuilder;

/**
* SchemaMeta
*/
class SchemaMeta extends AbstractProvider implements ProviderInterface{

	public function __construct(){

		//
	}

	public function register(){

		/**
		* Schema Meta
		*/
		$this->core()->set("app.sm", new class extends AbstractService{

			private $sm;
			private $conn;

			public function __construct(){

				$this->conn = $this->em()->getConnection();

				$this->sm = $this->conn->getSchemaManager();
			}

			public function database(){

				return $this->conn->getDatabase();
			}

			public function databases(){

				return $this->sm->listDatabases();
			}

			public function tables($db = null){

				$tbls = $this->sm->listTables($db);

				$tbls_all = [];				
				foreach($tbls as $tbl)
					$tbls_all[] = $tbl->getName();

				return $tbls_all;
			}

			public function columns($table){

				$cols = $this->sm->listTableColumns($table);

				$cols_all = [];
				foreach($cols as $name => $col){

					$raw_col = $col->toArray();

					$raw_col["type"] = $col->getType()->getName();

					$cols_all[$name] = $raw_col;
				}

				return $cols_all;
			}

			public function fks($table){

				$fkeys = $this->sm->listTableForeignKeys($table);

				$fkeys_all = [];
				foreach($fkeys as $fk){

					$fkeys_all[] = array(

						"name"=>$fk->getName(),
						"columns"=>$fk->getColumns(),
						"foreign_table"=>$fk->getForeignTableName(),
						"foreign_columns"=>$fk->getForeignColumns(),
					);
				}

				return $fkeys_all;
			}

			public function indexes($table){

				$indexes = $this->sm->listTableIndexes($table);

				$indexes_all = [];
				foreach($indexes as $index){

					$indexes_all[] = array(

						"name"=>$index->getName(),
						"is_unique"=>$index->isUnique(),
						"is_primary"=>$index->isPrimary(),
						"columns"=>$index->getColumns()
					);
				}

				return $indexes_all;
			}
		});
	}
}