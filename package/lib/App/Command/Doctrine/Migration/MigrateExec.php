<?php

namespace App\Command\Doctrine\Migration;

use Strukt\Console\Input;
use Strukt\Console\Output;
use Strukt\Console\Color;
use Strukt\Env;
use Strukt\Raise;
use Strukt\Core\Registry;

use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Configuration\Migration\ExistingConfiguration;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Metadata\Storage\TableMetadataStorageConfiguration;
use Doctrine\Migrations\MigratorConfiguration;

use Doctrine\Migrations\Exception\NoMigrationsFoundWithCriteria;
use Doctrine\Migrations\Exception\NoMigrationsToExecute;
use Doctrine\Migrations\Exception\UnknownMigrationVersion;
use Symfony\Component\Console\Formatter\OutputFormatter;

/**
* migrate:exec  Doctrine Execute Migration
* 
* Usage:
*	
*      migrate:exec [<version>] 
*
* Arguments:
*
*      version     (Optional) The version number (YYYYMMDDHHMMSS) or alias (first, prev, next, 
*								latest) to migrate to. default:latest
*/
class MigrateExec extends \App\Contract\AbstractCommand{

	public function execute(Input $in, Output $out){

		$verAlias = $in->get("version");

		if(empty($verAlias))
			$verAlias = "latest";

		$message = null;

		try{

			$connection = $this->core()->get("app.em")->getConnection();

			$cfg = new Configuration($connection);
			$cfg->addMigrationsDirectory(Env::get("migration_ns"), Env::get("migration_home"));
			$cfg->setAllOrNothing(true);
			$cfg->setCheckDatabasePlatform(false);

			$storeCfg = new TableMetadataStorageConfiguration();
			$storeCfg->setTableName('doctrine_migration_versions');

			$cfg->setMetadataStorageConfiguration($storeCfg);

			$depFactory = DependencyFactory::fromConnection(
			    new ExistingConfiguration($cfg),
			    new ExistingConnection($connection)
			);

			$depFactory->getMetadataStorage()->ensureInitialized();

	        $migRepo = $depFactory->getMigrationRepository();

	        if (count($migRepo->getMigrations()) === 0){

	            new Raise(sprintf(

	                'The version "%s" couldn\'t be reached, there are no registered migrations.',
	                $verAlias
	            ));
	        }

	        try {

	            $version = $depFactory->getVersionAliasResolver()->resolveVersionAlias($verAlias);
	        } 
	        catch (UnknownMigrationVersion $e) {

	            new Raise(sprintf('Unknown version: %s', OutputFormatter::escape($verAlias)));
	        } 
	        catch (NoMigrationsToExecute|NoMigrationsFoundWithCriteria $e) {

	            return $this->exitForAlias($verAlias, $depFactory, $out);
	        }

	        $planCalc = $depFactory->getMigrationPlanCalculator();
	        $statusCalc = $depFactory->getMigrationStatusCalculator();
	        $execUnavailMigs = $statusCalc->getExecutedUnavailableMigrations();

	        if (count($execUnavailMigs) !== 0) {

	        	$out->add(sprintf(Color::write("red","WARN: You have %s previously executed migrations in the database that are not registered migrations.'"),
		                count($execUnavailMigs)
		        ));

	            foreach ($execUnavailMigs->getItems() as $exeUnavailMig) {

	                $out->add(sprintf(Color::write("yellow","    >> %s (").Color::write("yellow", "%s)"),
	                    $exeUnavailMig->getExecutedAt() !== null
	                        ? $exeUnavailMig->getExecutedAt()->format('Y-m-d H:i:s')
	                        : null,
	                    $exeUnavailMig->getVersion()
	                ));
	            }

		        $ans = $in->getInput('Are you sure you wish to continue? (y/n)');
		        if(!in_array(trim(strtolower($ans)), array("y","yes", "")))
		       		new Raise("Migration cancelled");
	        }

	        $plan = $planCalc->getPlanUntilVersion($version);

	        if (count($plan) === 0) {
	            $this->exitForAlias($verAlias, $depFactory, $out);
	        }

	        $migrator = $depFactory->getMigrator();
	        $sql = $migrator->migrate($plan, (new MigratorConfiguration())
										            ->setDryRun(false)
										            ->setTimeAllQueries(true)
										            ->setAllOrNothing(true));

            $writer = $depFactory->getQueryWriter();
            $writer->write("php://stdout", $plan->getDirection(), $sql);
	    }
		catch(\Exception $e){

			$message = $e->getMessage();
		}

		if(!is_null($message))
			throw new \Exception($message);
	}

	private function exitForAlias(string $verAlias, DependencyFactory $depFactory, Output $out){

        $version = (string) $depFactory->getVersionAliasResolver()->resolveVersionAlias('current');

        // Allow meaningful message when latest version already reached.
        if (in_array($verAlias, ['current', 'latest', 'first'], true)) {

            $out->add(sprintf('Already at the %s version ("%s")', $verAlias, $version));
        } 
        elseif (in_array($verAlias, ['next', 'prev'], true) || strpos($verAlias, 'current') === 0) {

            new Raise(sprintf( 'The version "%s" couldn\'t be reached, you are at version "%s"',

            	$verAlias,
                $version
            ));
        } 
        else {

            $out->add(sprintf('You are already at version "%s"', (string) $version));
        }
    }
}