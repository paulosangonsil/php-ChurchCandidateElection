<?php

class MyLogger {
    /** @var Singleton */
    private static $_instancia;

    private $_memoriaParaImpressao = NULL;

    public static function getInstance() {
        if ( is_null( self::$_instancia ) ) {
            self::$_instancia = new self();
        }

        return self::$_instancia;
    }

    /*void*/ public function imprimir($textoImpressao) {
        if ($this->_memoriaParaImpressao == NULL) {
            $this->_memoriaParaImpressao = $textoImpressao;
        }
        else {
            $this->_memoriaParaImpressao .= $textoImpressao;
        }
    }

    /*void*/ public function imprimirNovaLinha($textoImpressao) {
         $this->imprimir($textoImpressao . '&#10;');
    }

    /*String*/ public function obterMemoriaParaImpressao() {
        return $this->_memoriaParaImpressao;
    }
}
