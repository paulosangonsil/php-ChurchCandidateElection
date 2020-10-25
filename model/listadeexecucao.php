<?php
require_once 'comum.php';


/**
 * @author Administrator
 *
 */
class ListaDeExecucao {
    private static $_instancia  = NULL;

    private /*ArrayList<RegistroDeExecucao>*/ $_listaDeExecucao = array();

    public static function getInstance($idCelula) {
        if ( is_null( self::$_instancia ) ) {
            self::$_instancia = new static($idCelula);
        }

        return self::$_instancia;
    }

    function    __construct ($idCelula) {
        global $_nomeTbl_RegExec;

        $contagem = 0;

        $cmdConsulta    = "SELECT * FROM $_nomeTbl_RegExec WHERE celula = $idCelula ORDER BY data";

        _iniciaConexaoBD();

        $_resConsulta = _getDBSrvHandle()->query($cmdConsulta);

        if ($_resConsulta === FALSE) {
            return;
        }

        // while ( $_linhaRes = mysqli_fetch_array($_manipSrv, $_resConsulta, MYSQL_NUM) ) {
        while ( $_linhaRes = $_resConsulta->fetch() ) {
            $execucaoDaVez = new RegistroDeExecucao();

            $execucaoDaVez->set_id($_linhaRes[RegistroDeExecucao::COLUNA_TBL_ID]);
            $execucaoDaVez->set_data($_linhaRes[RegistroDeExecucao::COLUNA_TBL_DATA]);
            $execucaoDaVez->set_tarefas( unpack("C*",
                    $_linhaRes[RegistroDeExecucao::COLUNA_TBL_TAREFAS]) );
            $execucaoDaVez->set_celula($idCelula);

            $this->_listaDeExecucao[$contagem++] = $execucaoDaVez;
        }

        // mysqli_free_result($_resConsulta);
    }

    /*ArrayList<RegistroDeExecucao>*/ function getListagem() {
        return $this->_listaDeExecucao;
    }

    function gravar(/*RegistroDeExecucao*/ $item) {
        global $_nomeTbl_RegExec;

        $modoEdicao = FALSE;

        $cmdSQL = NULL;

        foreach ($this->_listaDeExecucao as $execAtual) {
            if ( $item->get_id() == $execAtual->get_id() ) {
                $modoEdicao = TRUE;

                break;
            }
        }

        $tarefasBuffer = NULL;

        $tarefasRegistro = $item->get_tarefas();

        if ( ! is_null($tarefasRegistro) ) {
            for ($contagem = 0; $contagem <
                    count($tarefasRegistro); $contagem++) {
                $tarefasBuffer = geraListaTarefasSQL($tarefasRegistro[$contagem], $tarefasBuffer);
            }
        }
        else {
            $tarefasBuffer = 'NULL';
        }

        if (! $modoEdicao) {
            $cmdSQL = "INSERT INTO $_nomeTbl_RegExec VALUES(NULL, " .
                    $item->get_data() . ", " . $tarefasBuffer . ", " . $item->get_celula() . ")";
        }
        else {
            $cmdSQL = "UPDATE $_nomeTbl_RegExec SET " .
                    RegistroDeExecucao::COLUNA_TBL_STR_DATA . " = " . $item->get_data() .
                    ", " . RegistroDeExecucao::COLUNA_TBL_STR_TAREFAS . " = " . $tarefasBuffer .
                    " WHERE " . RegistroDeExecucao::COLUNA_TBL_STR_ID . " = " . $item->get_id() . " AND " .
                    RegistroDeExecucao::COLUNA_TBL_STR_CELULA . " = " . $item->get_celula();
        }

        //die($cmdSQL);
        mysql_query($cmdSQL);
    }
}

//$_nObj = new ListaDeExecucao();
//print_r($_nObj->getListagem());
