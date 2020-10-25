<?php

require_once 'comum.php';

class Celula {
    const   COLUNA_TBL_ID           = 0,
            COLUNA_TBL_NOME         = 1,
            COLUNA_TBL_SENHA        = 2,
 
            COLUNA_TBL_STR_ID       = 'id',
            COLUNA_TBL_STR_NOME     = 'conjuge',
            COLUNA_TBL_STR_SENHA    = 'senha';

    /** @var Singleton */
    private static $_instancia;


    /**
     * 
     */
    private /*int*/ $_id                  = NULL;
    private /*String*/ $_nome             = NULL;
    private /*String*/ $_senha            = NULL;

    private /*Hashset<int, String>*/ $_listaDasCelulas  = array();

    public static function getInstance() {
        if ( is_null( self::$_instancia ) ) {
            self::$_instancia = new self();
        }

        return self::$_instancia;
    }

    public function __construct($id = 0) {
        if ($id != 0) {
            global $_nomeTbl_Celula;

            _iniciaConexaoBD();

            $cmdConsulta = "SELECT * FROM " . $_nomeTbl_Celula .
                            " WHERE " . Celula::COLUNA_TBL_STR_ID . "=" . $id;

            $_resConsulta = _getDBSrvHandle()->query($cmdConsulta);

            if ($_resConsulta === FALSE) {
                return;
            }

            while ( $_linhaRes = $_resConsulta->fetch() ) {
                $this->set_id($_linhaRes[Celula::COLUNA_TBL_ID]);
                $this->set_nome($_linhaRes[Celula::COLUNA_TBL_NOME]);
                $this->set_senha($_linhaRes[Celula::COLUNA_TBL_SENHA]);
            }

            // mysqli_free_result($_resConsulta);
        }
        else {
            $this->_populaLista();
        }
    }

    private function _populaLista() {
        global $_nomeTbl_Celula;

        _iniciaConexaoBD();

        $cmdConsulta    = "SELECT * FROM " . $_nomeTbl_Celula;

        $_resConsulta = _getDBSrvHandle()->query($cmdConsulta);

        if ($_resConsulta === FALSE) {
            return;
        }

        while ( $_linhaRes = $_resConsulta->fetch() ) {
            $this->_listaDasCelulas[$_linhaRes[0]] = $_linhaRes[1];
        }

        // mysqli_free_result($_resConsulta);
    }

    /**
     * @return the _id
     */
    /*int*/ public function get_id() {
        return $this->_id;
    }

    /**
     * @param _id the _id to set
     */
    /*void*/ public function set_id($id) {
        $this->_id = $id;
    }

    /**
     * @return the _nome
     */
    /*String*/ public function get_nome() {
        return $this->_nome;
    }

    /**
     * @param _nome the _nome to set
     */
    /*void*/ public function set_nome($nome) {
        $this->_nome = /*htmlentities (*/$nome/*, ENT_COMPAT | ENT_HTML401, 'ISO-8859-1')*/;
    }

    /**
     * @return the senha
     */
    /*String*/ public function get_senha() {
        return $this->_senha;
    }

    /**
     * @param _nome the _nome to set
     */
    /*void*/ public function set_senha($senha) {
        $this->_senha = /*htmlentities (*/$senha/*, ENT_COMPAT | ENT_HTML401, 'ISO-8859-1')*/;
    }

    /*Hashset<int, String>*/ public static function listarNomes() {
        return Celula::getInstance()->_listaDasCelulas;
    }
}
