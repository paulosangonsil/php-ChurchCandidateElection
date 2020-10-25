<?php
require_once 'comum.php';

/**
 * @author Administrator
 *
 */
class ListaDeCandidatos {
    /** @var Singleton */
    private static $_instancia  = NULL;

    private /*ArrayList<CandidatoDaTarefa>*/ $_listaDeCandidatos    = array();

    public static function getInstance($idCelula) {
        if ( is_null( self::$_instancia ) ) {
            self::$_instancia = new static($idCelula);
        }

        return self::$_instancia;
    }

    function    __construct ($idCelula) {
        global $_nomeTbl_Integrantes;

        $contagem = 0;

        $cmdConsulta    = "SELECT * FROM $_nomeTbl_Integrantes WHERE " .
                            IntegranteCelula::COLUNA_TBL_STR_CELULA ." = $idCelula";

        _iniciaConexaoBD();

        $_resConsulta = _getDBSrvHandle()->query($cmdConsulta);

        if ($_resConsulta === FALSE) {
            return;
        }

        // while ( $_linhaRes = mysqli_fetch_array($_manipSrv, $_resConsulta, MYSQL_NUM) ) {
        while ( $_linhaRes = $_resConsulta->fetch() ) {
            $candidatoDaVez = new CandidatoDaTarefa();

            $candidatoDaVez->set_id($_linhaRes[IntegranteCelula::COLUNA_TBL_ID]);
            $candidatoDaVez->set_nome($_linhaRes[IntegranteCelula::COLUNA_TBL_NOME]);
            $candidatoDaVez->set_idConjuge($_linhaRes[IntegranteCelula::COLUNA_TBL_CONJUGE]);

            if ( ! is_null($_linhaRes[IntegranteCelula::COLUNA_TBL_TARRESTR] ) ) {
                $candidatoDaVez->set_tarefasRestritas( unpack("C*",
                        $_linhaRes[IntegranteCelula::COLUNA_TBL_TARRESTR]) );
            }

            $candidatoDaVez->set_estado($_linhaRes[IntegranteCelula::COLUNA_TBL_ESTADO]);

            //$candidatoDaVez->set_ignorarRegraConjuge($ignorarRegraConjuge);
            if ( ! $candidatoDaVez->is_visitante() ) {
                $candidatoDaVez->set_disponibilidade(TRUE);
            }

            $candidatoDaVez->set_celula($idCelula);

            $this->_listaDeCandidatos[$contagem++] = $candidatoDaVez;
        }

        // mysqli_free_result($_resConsulta);

        //shuffle($this->_listaDeCandidatos);
    }

    /*ArrayList<CandidatoDaTarefa>*/ function getListagem() {
        return $this->_listaDeCandidatos;
    }

    /*String*/ function nomeCandidato(/*int*/ $id) {
        $retMtd = NULL;

        foreach ($this->getListagem() as $candidatoDaVez) {
            if ($candidatoDaVez->get_id() == $id) {
                $retMtd = /*htmlentities (*/
                        $candidatoDaVez->get_nome()/*, ENT_COMPAT | ENT_HTML401, 'ISO-8859-1' )*/;

                break;
            }
        }

        return $retMtd;
    }

    function gravar(/*IntegranteCelula*/ $item) {
        global $_nomeTbl_Integrantes;

        $modoEdicao = FALSE;

        $cmdSQL = NULL;

        foreach ($this->_listaDeCandidatos as $candAtual) {
            if ( $item->get_id() == $candAtual->get_id() ) {
                $modoEdicao = TRUE;

                break;
            }
        }

        $restricaoBuffer = NULL;

        $listaRestricao = $item->get_tarefasRestritas();

        if ( ! is_null($listaRestricao) ) {
            for ($contagem = 0; $contagem <
                    count($listaRestricao); $contagem++) {
                $restricaoBuffer = geraListaTarefasSQL($listaRestricao[$contagem], $restricaoBuffer);
            }
        }
        else {
            $restricaoBuffer = 'NULL';
        }

        $idConjuge = $item->get_idConjuge();

        if ($idConjuge == NULL) {
            $idConjuge = 'NULL';
        }

        $estadoIntegr = $item->get_estado();

        if ($estadoIntegr == NULL) {
            $estadoIntegr = 0;
        }

        if (! $modoEdicao) {
            $cmdSQL = "INSERT INTO $_nomeTbl_Integrantes VALUES(NULL, " .
                    $idConjuge . ", '" . $item->get_nome() .
                    "', " . $restricaoBuffer . ", " . $estadoIntegr . ", " . $item->get_celula() . ")";
        }
        else {
            $cmdSQL = "UPDATE $_nomeTbl_Integrantes SET " . IntegranteCelula::COLUNA_TBL_STR_CONJUGE . " = " . $idConjuge .
                    ", " . IntegranteCelula::COLUNA_TBL_STR_NOME . " = '" . $item->get_nome() . "', " .
                    IntegranteCelula::COLUNA_TBL_STR_TARRESTR . " = " . $restricaoBuffer . ", " .
                    IntegranteCelula::COLUNA_TBL_STR_ESTADO . " = " . $estadoIntegr .
                    " WHERE " . IntegranteCelula::COLUNA_TBL_STR_ID . " = " . $item->get_id() .
                    " AND " . IntegranteCelula::COLUNA_TBL_STR_CELULA . " = " . $item->get_celula();
        }

        //die($cmdSQL);
        mysql_query($cmdSQL);
    }
}

//$_nObj = new ListaDeCandidatos();
//print_r($_nObj->getListagem());
