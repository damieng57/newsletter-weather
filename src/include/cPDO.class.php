<?php

require_once('cMyPDOError.class.php');
require_once('cMyPDO.class.php');

// build globals PDO Singleton Object
class cPDO
{
    private static $oPDO = NULL;

    static function GetPDO( ){
        if( cPDO::$oPDO != NULL ) return cPDO::$oPDO;

        define('MODE_DEVELOPMENT', true);

        try{
            if(defined('MODE_DEVELOPMENT') && MODE_DEVELOPMENT===true):
                $sql_type = "mysql";
                $sql_user = "devel";
                $sql_pass = "xofefi32";
                $sql_host = "192.168.100.42";
                $sql_db   = "weather";
            else:
                $sql_type = "mysql";
                $sql_user = "";
                $sql_pass = "";
                $sql_host = "";
                $sql_db   = "";
            endif;

            cPDO::$oPDO = new cMyPDO( $sql_type.':dbname='.$sql_db.';host='.$sql_host, $sql_user, $sql_pass );
            cPDO::$oPDO->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
            cPDO::$oPDO->exec( "SET NAMES 'UTF8'" );
            cPDO::$oPDO->exec( "SET CHARACTER SET 'UTF8'" );
            return cPDO::$oPDO;
        }
        catch( PDOException $exPDO ) {
            cPDO::$oPDO = new cMyPDOError();
            return cPDO::$oPDO;
            die("Erreur de connexion : " . $exPDO->getMessage() );
        }
    }

    static function SetDebug( $bDebug ){
        cPDO::GetPDO()->SetDebug( $bDebug );
    }
}

function __UnitTest_PDO_Test(){
    $oPdo = cPDO::GetPDO();
    if( $oPdo == NULL ) return false;
    return true;
}