<?php

namespace App\Command\Doctrine;

use Strukt\Console\Input;
use Strukt\Console\Output;

/**
* sql:exec  Doctrine Run SQL
* 
* Usage:
*   
*      sql:exec [--nostyle] <sql> 
*
* Arguments:
*
*      sql    SQL statement in quotes
*
* Options:
* 
*      --nostyle -n       Do not apply style
*/
class SqlExec extends \Strukt\Console\Command{

    public function execute(Input $in, Output $out){

        $registry = \Strukt\Core\Registry::getInstance();

        $conn = $registry->get("app.em")->getConnection();

        if (($sql = $in->get('sql')) === null) {
            throw new \RuntimeException("Argument 'SQL' is required in order to execute this command correctly.");
        }

        if (stripos($sql, 'select') === 0)
            $rs = $conn->fetchAll($sql);
        else
            $rs = $conn->executeUpdate($sql);

        // ob_start();
        // \Doctrine\Common\Util\Debug::dump($rs, 8);
        // $message = ob_get_clean();

        if(is_array($rs))
            $rs = json_encode($rs, JSON_PRETTY_PRINT);
        elseif(is_int($rs))
            if($rs == 1)
                $rs = "Query executed successfully.";
            else
                $rs = "Query execution was unsuccessful!";

        $no_style = $in->get("nostyle");

        if($no_style)
            exit($rs);
        
        $out->add($rs);
    }
}