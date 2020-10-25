<?php

require_once 'comum.php';

/**
 * @author Administrator
 *
 */
class Tarefas {
    /** @var Singleton */
    private static $_instancia;

    private /*ArrayList<String>*/ $_listaDasTarefasConhecidas = array();

    const   DIRECAO     = 0,
            QUEBRA      = 1,
            LOUVOR      = 2,
            PALAVRA     = 3,
            OFERTA      = 4,
            QTDE_TAREFA = 5;

    public static function getInstance() {
        if ( is_null( self::$_instancia ) ) {
            self::$_instancia = new self();
        }

        return self::$_instancia;
    }

    function    __construct () {
        global $_nomeTbl_Tarefas;

        _iniciaConexaoBD();

        $cmdConsulta    = "SELECT * FROM " . $_nomeTbl_Tarefas;

        $_resConsulta = _getDBSrvHandle()->query($cmdConsulta);

        if ($_resConsulta === FALSE) {
            return;
        }

        while ( $_linhaRes = $_resConsulta->fetch() ) {
            //printf("ID: %s  Name: %s\n", $_linhaRes[0], $_linhaRes[1]);  

            $this->_listaDasTarefasConhecidas[$_linhaRes[0]] = $_linhaRes[1];
        }

        // mysqli_free_result($_resConsulta);
    }

    /*String*/ function getNome($indice) {
        $retMtd = null;

        if ( ! is_null($this->_listaDasTarefasConhecidas) ) {
            $retMtd = /*htmlentities ( */$this->_listaDasTarefasConhecidas[ intval($indice) ]/*,
                    ENT_COMPAT | ENT_HTML401, 'ISO-8859-1')*/;
        }

        return $retMtd;
    }

    function qtdeTarefas() {
        return ( count($this->_listaDasTarefasConhecidas) );
    }

    /*ArrayList<String>*/ function getListaTarefas() {
        return $this->_listaDasTarefasConhecidas;
    }
}

//$_nObj = new Tarefas();
//PRINT $_nObj->getNome(0);
