<?php

require_once 'integrante.php';

/**
 * @author Administrator
 *
 */
class CandidatoDaTarefa extends IntegranteCelula {
    private $_disponibilidade,
            $_ignorarRegraConjuge;

    /**
     * @return the $_disponibilidade
     */
    function is_disponibilidade() {
        return $this->_disponibilidade;
    }

    /**
     * @param $_disponibilidade the $_disponibilidade to set
     */
    function set_disponibilidade($disponibilidade) {
        $this->_disponibilidade = $disponibilidade;
    }

    /**
     * @return the $_ignorarRegraConjuge
     */
    function is_ignorarRegraConjuge() {
        return $this->_ignorarRegraConjuge;
    }

    /**
     * @param $_ignorarRegraConjuge the $_ignorarRegraConjuge to set
     */
    function set_ignorarRegraConjuge($ignorarRegraConjuge) {
        $this->_ignorarRegraConjuge = $ignorarRegraConjuge;
    }
}
