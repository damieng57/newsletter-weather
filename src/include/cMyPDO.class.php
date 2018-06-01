<?php

require_once('cPDO.class.php');
require_once('cMyPDOError.class.php');

class cMyPDO extends PDO
{
    private $iBeginTransaction = 0;

    function __construct( $sServer, $sUser, $sPasswd )
    {
        parent::__construct( $sServer, $sUser, $sPasswd );
        $this->setAttribute( PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true );
    }

    function DumpSql( $sSql, $rParam = array() )
    {
        foreach( $rParam as $sKey => $sValue )
        {
            $sSql = str_replace( ':'.$sKey, '"'.addslashes( $sValue ).'"', $sSql );
        }

        Dump( $sSql );
    }


    function Debug( $cPdoSt )
    {
        if( $this->bDebug )
            echo $cPdoSt->queryString.'<hr/>';
    }

    /**
     * Lecture d'une requete
     * @param $sSql
     * @param $rParam tableau associatif array( 'IdClient' => 12, ... )
     * @return Tableau de 'row' des resultats de la requete
     */
    function Read( $sSql, $rParam = array(), $bFirstRow = false, $sFetchStyle = PDO::FETCH_ASSOC )
    {
        //$this->DumpSql( $sSql, $rParam );
        //cError::Warning( 'Ici' );

        $cPdoSt = $this->prepare( $sSql );

        foreach( $rParam as $sField => $sValue )
            $cPdoSt->bindValue( $sField, $sValue, PDO::PARAM_STR );

        $cPdoSt->execute();

        //$this->Debug( $cPdoSt );

        if( $bFirstRow )
        {
            $rReturn = $cPdoSt->fetch( $sFetchStyle );
            if( $rReturn === false )
                return array();

            return $rReturn;
        }
        else
            return $cPdoSt->fetchall( $sFetchStyle );
    }


    function ReadResource( $sSql, $rParam = array() )
    {
        $cPdoSt = $this->prepare( $sSql );

        foreach( $rParam as $sField => $sValue )
            $cPdoSt->bindValue( $sField, $sValue, PDO::PARAM_STR );

        $cPdoSt->execute();
        return $cPdoSt;
    }

    function FetchResource( $cPdoSt, $sFetchStyle = PDO::FETCH_ASSOC )
    {
        return $cPdoSt->fetch( $sFetchStyle );
    }

    function SetDebug( $bDebug )
    {
        $this->bDebug = $bDebug;
    }

    /**
     * Lire la 1er ligne du resultat de la requete
     * @param $sSql
     * @param $rParam tableau associatif array( 'IdClient' => 12, .... )
     * @return La 1er ligne de resultat
     */
    function ReadFirstRow( $sSql, $rParam = array() )
    {
        return $this->Read( $sSql, $rParam , true );
    }

    /**
     * Lire la 1er valeur de la 1er ligne du resultat de la requete
     * @param $sSql
     * @param $rParam tableau associatif array( 'IdClient' => 12, .... )
     * @return
     */
    function ReadOneValue( $sSql, $rParam = array() )
    {
        $rRow = $this->Read( $sSql, $rParam , true, PDO::FETCH_NUM );

        if( isset($rRow[0]) )
            return $rRow[0];

        return NULL;
    }

    /**
     * Lire la 1er valeur de toute les lignes pour creer un tableau non-associatif
     * @param $sSql
     * @param $rParam tableau non-associatif array( 12, 10, 1, ... )
     * @return
     */
    function ReadArrayOneValue( $sSql, $rParam = array() )
    {
        $arRow = $this->Read( $sSql, $rParam );

        if( $arRow == NULL ) return array();

        $aReturn = array();
        foreach( $arRow as $rRow )
        {
            $aReturn[] = array_pop( $rRow );
        }

        return $aReturn;
    }

    /**
     * Retourne un tableau associatif en fonction de la cl� passer en parametre
     * @param $sSql : requete
     * @param $rParam
     * @param $sAssoc : cl� qui sera utilis�e dans le tableau
     */
    function ReadAssoc( $sSql, $sAssoc, $rParam = array())
    {
        $arRow = $this->Read( $sSql, $rParam );

        if( $arRow == NULL ) return array();

        $aReturn = array();
        foreach( $arRow as $rRow )
        {
            $aReturn[$rRow[$sAssoc]] = $rRow;
        }
        return $aReturn;
    }


    /**
     * Ecriture d'une requete
     * @author Manuel Masiello
     * @param $sSql
     * @param $rParam tableau associatif array( 'IdClient' => 12, ... )
     * @return Tableau de 'row' des resultats de la requete
     */
    function Write( $sSql, $rParam = array() )
    {
        $cPdoSt = $this->prepare( $sSql );

        foreach( $rParam as $sField => $sValue )
            $cPdoSt->bindValue( $sField, $sValue, PDO::PARAM_STR );

        $rReturn = $cPdoSt->execute();

        //$this->Debug( $cPdoSt );

        return $rReturn;
    }

    /**
     * Insertion d'une requete
     * @param $sSql
     * @param $rParam tableau associatif array( 'IdClient' => 12, ... )
     * @return le Last insert Id
     */
    function Insert( $sSql, $rParam = array() )
    {
        $cPdoSt = $this->prepare( $sSql );

        foreach( $rParam as $sField => $sValue )
        {
            $cPdoSt->bindValue( $sField, $sValue, PDO::PARAM_STR );
        }
        $cPdoSt->execute();

        //$this->Debug( $cPdoSt );

        return $this->lastInsertId();
    }

    /**
     * Insertion d'une requete
     * @param $sSql
     * @param $rParam tableau associatif array( 'IdClient' => 12, ... )
     * @return le nombre de ligne affectée
     */
    function InsertAffected( $sSql, $rParam = array() )
    {
        $cPdoSt = $this->prepare( $sSql );

        foreach( $rParam as $sField => $sValue )
        {
            $cPdoSt->bindValue( $sField, $sValue, PDO::PARAM_STR );
        }
        $cPdoSt->execute();

        //$this->Debug( $cPdoSt );

        return $cPdoSt->rowCount();
    }

    function beginTransaction()
    {
        if( $this->iBeginTransaction == 0 )
        {
            //echo "beginTransaction\n";
            parent::beginTransaction();
        }
        $this->iBeginTransaction++;
    }

    function rollBack()
    {
        $this->iBeginTransaction--;
        if( $this->iBeginTransaction == 0 )
        {
            //echo "rollBack\n";
            parent::rollBack();
        }

    }

    function commit()
    {
        $this->iBeginTransaction--;
        if( $this->iBeginTransaction == 0 )
        {
            //echo "commit\n";
            parent::commit();
        }
    }

}