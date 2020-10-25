<?php

/**
 * @author Administrator
 *
 */
class RegistroDeExecucao {
    const   COLUNA_TBL_ID           = 0,
            COLUNA_TBL_DATA         = 1,
            COLUNA_TBL_TAREFAS      = 2,
            COLUNA_TBL_CELULA       = 3,

            COLUNA_TBL_STR_ID       = 'id',
            COLUNA_TBL_STR_DATA     = 'data',
            COLUNA_TBL_STR_TAREFAS  = 'tarefas',
            COLUNA_TBL_STR_CELULA   = 'celula';

    private $_id,
            $_data,
            $_tarefas,
            $_celula;

    /**
     * @return the _id
     */
    function get_id() {
        return $this->_id;
    }

    /**
     * @param _id the _id to set
     */
    function set_id($id) {
        $this->_id = $id;
    }

    /**
     * @return the _data
     */
    /*int*/ function get_data() {
        return $this->_data;
    }

    /**
     * @param _data the _data to set
     */
    function set_data(/*int*/ $_data) {
        $this->_data = $_data;
    }

    /**
     * @return the _tarefas
     */
    /*int[]*/ function get_tarefas() {
        return $this->_tarefas;
    }

    /**
     * @param _tarefas the _tarefas to set
     */
    function set_tarefas(/*int[]*/ $tarefas) {
        if ($tarefas == NULL) {
            return;
        }

        $contador = 0;

        foreach ($tarefas as $tarefa) {
            $this->_tarefas[$contador++] = $tarefa;
        }
    }

    /**
     * @return the _celula
     */
    /*int*/ function get_celula() {
        return $this->_celula;
    }

    /**
     * @param _celula the _celula to set
     */
    function set_celula(/*int*/ $_celula) {
        $this->_celula = $_celula;
    }
}
