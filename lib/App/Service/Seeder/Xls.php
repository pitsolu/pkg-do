<?php

namespace App\Service\Seeder;

use Strukt\Contract\AbstractCore;
use Port\Spreadsheet\SpreadsheetReader;

class Xls extends AbstractCore{

	public static function load($pathtofile){

		$path = pathinfo($pathtofile);

		$file = new \SplFileObject($pathtofile);
		$reader = new SpreadsheetReader($file, 0);

		$em =  self::core()->get("app.em");

		$conn = $em->getConnection();

		$conn->beginTransaction(); // suspend auto-commit

		try {

			foreach($reader as $row)
				$conn->insert($path['filename'], $row);
		
			$em->flush();
    		$conn->commit();

		} catch (Exception $e) {

    		$conn->rollBack();
    		throw $e;
    	}
	}
}