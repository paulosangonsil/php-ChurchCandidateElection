<?php

class IntegranteCelula {
    const   COLUNA_TBL_ID           = 0,
            COLUNA_TBL_CONJUGE      = 1,
            COLUNA_TBL_NOME         = 2,
            COLUNA_TBL_TARRESTR     = 3,
            COLUNA_TBL_ESTADO       = 4,
            COLUNA_TBL_CELULA       = 5,

            COLUNA_TBL_STR_ID       = 'id',
            COLUNA_TBL_STR_CONJUGE  = 'conjuge',
            COLUNA_TBL_STR_NOME     = 'nome',
            COLUNA_TBL_STR_TARRESTR = 'tarefasrestritas',
            COLUNA_TBL_STR_ESTADO   = 'estado',
            COLUNA_TBL_STR_CELULA   = 'celula',

            ESTADO_VISITANTE        = 1,
            ESTADO_SAIU_CELULA      = 2;

    /**
     * 
     */
    private /*int*/ $_id                    = NULL;
    private /*int*/ $_idConjuge             = NULL;
    private /*String*/ $_nome               = NULL;
    private /*int[]*/ $_tarefasRestritas    = NULL;
    private /*int*/ $_estado         = FALSE;
    private /*int*/ $_celula                = NULL;

    public function __construct() {
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
     * @return the _idConjuge
     */
    /*int*/ public function get_idConjuge() {
        return $this->_idConjuge;
    }

    /**
     * @param _idConjuge the _idConjuge to set
     */
    /*void*/ public function set_idConjuge(/*int*/ $idConjuge) {
        $this->_idConjuge = $idConjuge;
    }

    /**
     * @return the _tarefasRestritas
     */
    /*int[]*/ public function get_tarefasRestritas() {
        return $this->_tarefasRestritas;
    }

    /**
     * @param _tarefasRestritas the _tarefasRestritas to set
     */
    /*void*/ public function set_tarefasRestritas(/*int[]*/ $tarefasRestritas) {
        if ($tarefasRestritas == NULL) {
            return;
        }

        $contador = 0;

        foreach ($tarefasRestritas as $tarefa) {
            $this->_tarefasRestritas[$contador++] = $tarefa;
        }
    }

    /**
     * @return the _estado
     */
    /*boolean*/ public function is_visitante() {
        return ( ($this->get_estado() & IntegranteCelula::ESTADO_VISITANTE) ==
                IntegranteCelula::ESTADO_VISITANTE );
    }

    /**
     * @return the _estado
     */
    /*boolean*/ public function is_saiucelula() {
        return ( ($this->get_estado() & IntegranteCelula::ESTADO_SAIU_CELULA) ==
                IntegranteCelula::ESTADO_SAIU_CELULA );
    }

    /**
     * @return the _estado
     */
    /*boolean*/ public function get_estado() {
        return $this->_estado;
    }

    /**
     * Podem ser usadas as flags IntegranteCelula::ESTADO_VISITANTE e
     * IntegranteCelula::ESTADO_SAIU_CELULA.
     * @param _visitante the _visitante to set
     */
    /*void*/ public function set_estado(/*int*/ $visitante) {
        $this->_estado = $visitante;
    }

    /**
     * @return the _celula
     */
    /*int[]*/ public function get_celula() {
        return $this->_celula;
    }

    /**
     * @param _celula the celula to set
     */
    /*void*/ public function set_celula(/*int[]*/ $celula) {
        if ($celula < 0) {
            return;
        }

        $this->_celula = $celula;
    }
}
