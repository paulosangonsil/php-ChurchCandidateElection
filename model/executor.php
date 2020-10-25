<?php
require_once 'comum.php';

/**
 * @author Administrator
 *
 */
class Executor {
    private $_qtdeIntegrantesDisp,
            $_modoContingencia      = FALSE;

    const   CONTIN_1    = 1,    // Contingencia sem repeticao de nome
            CONTIN_2    = 2,    // Contingencia com repeticao de nome
            CONTIN_ULT  = Executor::CONTIN_2,

            ORD_EXEC    = 0,
            ORD_DATA    = 1;

    /*void*/ function configContingencia(/*int*/ $nivelContingencia) {
        $this->_modoContingencia = $nivelContingencia;
    }

    /*boolean*/ function contingenciaHabilitada() {
        return $this->_modoContingencia;
    }

    /*int[]*/ function filtraListaCandidatoHabilitado(/*ArrayList<CandidatoDaTarefa>*/ $listaCandidatos,
            /*ArrayList<RegistroDeExecucao>*/ $listaRegistros, /*int*/ $tarefa, /*boolean*/ $ignorarRestricoes = FALSE) {
        global  $_listaDeTarefas,
                $_logger;

        $retMtd             = null;

        $listaHabilitados   = null;

        $nomeCandHTML       = null;

        if ($this->get_qtdeIntegrantesDisp() == 0) {
            // Contar qtos integrantes estao presentes
            foreach ($listaCandidatos as $candLista) {
                // Ele deve estar disponivel (presente na celula)
                if ( $candLista->is_disponibilidade() ) {
                    $this->_incrementarQtdeIntegrantesDisp();
                }
            }
        }

        foreach ($listaCandidatos as $candLista) {
            $nomeCandHTML = $candLista->get_nome();

            $nivelContingencia = $this->contingenciaHabilitada();

            if ( ( $candLista->is_saiucelula() ) ) {
                //$_logger->imprimirNovaLinha("> " . $nomeCandHTML .
                //        " n&atilde;o faz mais parte desta c&eacute;lula");

                continue;
            }

            // Para geracao do relatorio
            if (! $ignorarRestricoes) {
                // Ele deve estar disponivel (presente na celula)
                if ( ! $candLista->is_disponibilidade() ) {
                    $_logger->imprimirNovaLinha("> " . $nomeCandHTML .
                            " n&atilde;o est&aacute; presente na c&eacute;lula");

                    continue;
                }

                // Para cada candidato, verificar a lista de restricao
                if ( $this->_tarefaRestritaParaOCandidato($candLista, $tarefa) ) {
                    $_logger->imprimirNovaLinha( "> " . $nomeCandHTML .
                            " n&atilde;o est&aacute; apto para executar a tarefa " .
                            Tarefas::getInstance()->getNome($tarefa) );

                    continue;
                }

                if ( $this->get_qtdeIntegrantesDisp() > count($_listaDeTarefas) ) {
                    // Nao pode ter executado (qquer ou a mesma) tarefa na semana anterior
                    if ($nivelContingencia != Executor::CONTIN_1) {
                        if ( ! $this->_candidatoOciosoSemanaAnterior($candLista, $listaRegistros,
                                ($nivelContingencia == Executor::CONTIN_2) ? $tarefa : -1) ) {
                            $_logger->imprimirNovaLinha("> " . $nomeCandHTML .
                                    " executou tarefa na semana anterior");

                            continue;
                        }
                    }
                }
                /* Se tivermos restricao na quantidade de integrantes, alguns terao que
                    executar mais de uma tarefa e talvez ate tarefas que nao estao aptos */
                else if ($nivelContingencia) {
                    $_logger->imprimirNovaLinha($nomeCandHTML .
                            ": algumas regras n&atilde;o poder&atilde;o ser consideradas pois o " .
                            "n&uacute;mero de participantes da c&eacute;lula est&aacute; restrito");
                }
            }

            // Atribui a tarefa
            $listaHabilitados[] = $candLista->get_id();
        }

        if (count($listaHabilitados) > 0) {
            $retMtd = array();

            for ($elem = 0; $elem < count($listaHabilitados); $elem++) {
                $retMtd[$elem] = $listaHabilitados[$elem];
            }
        }

        return $retMtd;
    }

    /*boolean*/ function candidatoSemAtribuicao(/*CandidatoDaTarefa*/ $candidato,
            /*int[]*/ $listaCandAptos, /*int[]*/ $atribuicoesAtuais) {
        global  $_logger;

        $retMtd    = TRUE;

        /* Se existir menos integrantes do que a quantidade de tarefas,
           um ou outro integrante tera que executar mais de uma tarefa */
        if ( ( count($listaCandAptos) <=
                $this->_get_qtdeTarefasSemAtribuicao($atribuicoesAtuais) ) ||
                ($this->contingenciaHabilitada() == Executor::CONTIN_2) ) {
            return $retMtd;
        }

        for ($tarefaAtual = Tarefas::DIRECAO;
                ( $tarefaAtual < count($atribuicoesAtuais) ) && $retMtd; $tarefaAtual++) {
            if ( $atribuicoesAtuais[$tarefaAtual] == $candidato->get_id() ) {
                $retMtd = FALSE;

                $_logger->imprimirNovaLinha( "> " . $candidato->get_nome() .
                        " j&aacute; est&aacute; atribu&iacute;do para a tarefa " .
                            Tarefas::getInstance()->getNome($tarefaAtual) );
            }
        }

        return $retMtd;
    }

    /*boolean*/ function _candidatoOciosoSemanaAnterior(/*CandidatoDaTarefa*/ $candidato,
            /*ArrayList<RegistroDeExecucao>*/ $listaRegistros, /*int*/ $tarefaVerificar) {
        $retMtd             = TRUE;

        if ($listaRegistros == NULL) {
            return TRUE;
        }

        $atribuicoesNaData  = $listaRegistros[count($listaRegistros) - 1]->get_tarefas();

        if ($tarefaVerificar != -1) {
            if ( $atribuicoesNaData[$tarefaVerificar] == $candidato->get_id() ) {
                $retMtd = FALSE;
            }
        }
        else {
            foreach ($atribuicoesNaData as $tarefaAtual) {
                if ( $tarefaAtual == $candidato->get_id() ) {
                    $retMtd = FALSE;

                    break;
                }
            }
        }

        return $retMtd;
    }

    /*boolean*/ function _tarefaRestritaParaOCandidato(/*CandidatoDaTarefa*/ $candidato,
            /*int*/ $tarefa) {
        $retMtd             = FALSE;

        $listaDeRestricao   = $candidato->get_tarefasRestritas();

        if ( ($listaDeRestricao != null) && (count($listaDeRestricao) > 0) ) {
            foreach ($listaDeRestricao as $restricaoAtual) {
                if ($restricaoAtual == $tarefa) {
                    $retMtd = TRUE;

                    break;
                }
            }
        }

        return $retMtd;
    }

    /*boolean*/ function conjugeDoCandidatoComAtribuicao(/*CandidatoDaTarefa*/ $candidato,
                    /*int[]*/ $atribuicoesAtuais) {
        global  $_listaDeTarefas,
                $_logger;

        $retMtd     = FALSE;

        $idConjuge  = $candidato->get_idConjuge();

        if ($idConjuge == NULL) {
            return $retMtd;
        }

        // Se existir menos integrantes do que a quantidade de tarefas,
        // um ou outro integrante tera que executar mais de uma tarefa
        if ( ( $this->get_qtdeIntegrantesDisp() <= count($_listaDeTarefas) ) ||
            $this->contingenciaHabilitada() ) {
            return $retMtd;
        }

        if ($idConjuge != -1) {
            for ($tarefaAtual = 0;
                    ( $tarefaAtual < count($atribuicoesAtuais) ) && (! $retMtd); $tarefaAtual++) {
                if ($idConjuge == $atribuicoesAtuais[$tarefaAtual]) {
                    $retMtd = TRUE;

                    $_logger->imprimirNovaLinha("> C&ocirc;njuge de " . $candidato->get_nome() .
                            " j&aacute; est&aacute; trabalhando na c&eacute;lula");
                }
            }
        }

        return $retMtd;
    }

    /*int*/ function ordenaCandidatoPorRegraSelecionada(/*int*/ $tarefa,
            /*int[]*/ $listaDeCandidatos, /*Array<RegistroDeExecucao>*/ $listaExecucao, /*int*/ $regra = Executor::ORD_EXEC) {
        global  $_logger;

        $retMtd                         = -1;

        if (count($listaDeCandidatos) < 1) {
            return $retMtd;
        }

        /*int[]*/ $listaQtdeExecCandidatos        = array_fill(0, count($listaDeCandidatos), 0);
        /*int[]*/ $listaDataUltimaExecCandidatos  = array_fill(0, count($listaDeCandidatos), 0);
        /*int[]*/ $listaTarefasDoDia              = array();
        $indiceMenorValor               = -1;

        // Varrer a lista de candidatos
        for ($candAtual = 0; $candAtual < count($listaDeCandidatos); $candAtual++) {
            if ($listaDeCandidatos[$candAtual] == -1) {
                continue;
            }

            // Varrer a lista de execucoes
            for ($diaAtual = 0; $diaAtual < count($listaExecucao); $diaAtual++) {
                $listaTarefasDoDia = $listaExecucao[$diaAtual]->get_tarefas();

                if ($listaTarefasDoDia[$tarefa] == $listaDeCandidatos[$candAtual]) {
                    $listaQtdeExecCandidatos[$candAtual]++;

                    $dataDaExec = $listaExecucao[$diaAtual]->get_data();

                    if ( ($listaDataUltimaExecCandidatos[$candAtual] == 0) ||
                            ( $listaDataUltimaExecCandidatos[$candAtual] < $dataDaExec ) ) {
                        $listaDataUltimaExecCandidatos[$candAtual] = $dataDaExec;
                    }
                }
            }
        }

        // Encontrar o primeiro valor valido na lista de quantidade de execucao
        for ($candAtual = 0; $candAtual < count($listaDeCandidatos); $candAtual++) {
            if ($listaDeCandidatos[$candAtual] != -1) {
                $indiceMenorValor = $candAtual;

                break;
            }
        }

        if ($indiceMenorValor != -1) {
            $candComOMesmoNumeroDeExec = 0;

            if ($regra == Executor::ORD_EXEC) {
                // Encontrar quem foi o que menos fez a tarefa
                for ($candAtual = $indiceMenorValor;
                        $candAtual < count($listaQtdeExecCandidatos); $candAtual++) {
                    if ($listaDeCandidatos[$candAtual] == -1) {
                        continue;
                    }

                    if ($candAtual != $indiceMenorValor) {
                        if ($listaQtdeExecCandidatos[$candAtual] <
                                $listaQtdeExecCandidatos[$indiceMenorValor]) {
                            $indiceMenorValor = $candAtual;

                            $candComOMesmoNumeroDeExec = 0;
                        }
                        else if ($listaQtdeExecCandidatos[$candAtual] ==
                                $listaQtdeExecCandidatos[$indiceMenorValor]) {
                            $candComOMesmoNumeroDeExec++;
                        }
                    }
                }
            }

            $retMtd = $listaDeCandidatos[$indiceMenorValor];

            // Existem candidatos com o numero de execucoes "empatadas"
            if ( ($regra == Executor::ORD_DATA) ||
                    ( ($listaQtdeExecCandidatos[$indiceMenorValor] != 0) &&
                    ($candComOMesmoNumeroDeExec != 0) ) ) {
                if ($regra == Executor::ORD_EXEC) {
                    $_logger->imprimirNovaLinha("&#10;>!!Houve empate na quantidade de" .
                            " execu&ccedil;&otilde;es, escolheremos quem fez a tarefa primeiro!!<");
                }

                // O desempate acontecera por quem executou a tarefa primeiro
                for ($candAtual = 0; $candAtual < count($listaDataUltimaExecCandidatos); $candAtual++) {
                    if ( ($regra == Executor::ORD_EXEC) ||
                        ($listaDeCandidatos[$candAtual] == -1) ) {
                        if ( ($listaDataUltimaExecCandidatos[$candAtual] == 0) ||
                                ($listaQtdeExecCandidatos[$candAtual] !=
                                $listaQtdeExecCandidatos[$indiceMenorValor]) ) {
                            continue;
                        }
                    }

                    if ( $listaDataUltimaExecCandidatos[$candAtual] <
                            $listaDataUltimaExecCandidatos[$indiceMenorValor] ) {
                        $indiceMenorValor = $candAtual;
                    }
                }

                $retMtd = $listaDeCandidatos[$indiceMenorValor];
            }

            // Ordenar a lista por numero de execucoes...
            $listaOrdenadaCandidatos = array();

            for ($contador = 0; $contador < count($listaDeCandidatos); $contador++) {
                $listaOrdenadaCandidatos[] = $contador;
            }

            if (count ($listaOrdenadaCandidatos) > 1) {
                $candAtual = 0;

                $valorAnterior = 0;

                if ($regra == Executor::ORD_EXEC) {
                    do {
                        if ( $listaQtdeExecCandidatos[ $listaOrdenadaCandidatos[$candAtual + 1] ] <
                                $listaQtdeExecCandidatos[ $listaOrdenadaCandidatos[$candAtual] ] ) {
                            $valorAnterior = $listaOrdenadaCandidatos[$candAtual];

                            $listaOrdenadaCandidatos[$candAtual] = $listaOrdenadaCandidatos[$candAtual + 1];

                            $listaOrdenadaCandidatos[$candAtual + 1] = $valorAnterior;

                            $candAtual = -1;
                        }
                    } while ( (++$candAtual + 1) < count($listaQtdeExecCandidatos) );
                }

                // Ordenar a lista por data de execucao...
                $candAtual = 0;

                do {
                    if ( ($regra == Executor::ORD_DATA) ||
                        ( $listaQtdeExecCandidatos[ $listaOrdenadaCandidatos[$candAtual + 1] ] ==
                            $listaQtdeExecCandidatos[ $listaOrdenadaCandidatos[$candAtual] ] ) ) {
                        if ( $listaDataUltimaExecCandidatos[ $listaOrdenadaCandidatos[$candAtual + 1] ] <
                                $listaDataUltimaExecCandidatos[ $listaOrdenadaCandidatos[$candAtual] ] ) {
                            $valorAnterior = $listaOrdenadaCandidatos[$candAtual];

                            $listaOrdenadaCandidatos[$candAtual] = $listaOrdenadaCandidatos[$candAtual + 1];

                            $listaOrdenadaCandidatos[$candAtual + 1] = $valorAnterior;

                            $candAtual = -1;
                        }
                    }
                } while ( (++$candAtual + 1) < count($listaQtdeExecCandidatos) );
            }

            // Imprimir a ordenacao
            $_logger->imprimirNovaLinha("&#10;Qtde de execu&ccedil;&atilde;o da tarefa (" .
                    Tarefas::getInstance()->getNome($tarefa) .
                        ") para os integrantes:");

            $candAtual = 0;

            do {
                $nomeCandidato = ListaDeCandidatos::getInstance($_idCelula)->
                        nomeCandidato( $listaDeCandidatos[ $listaOrdenadaCandidatos[$candAtual] ] );

                if ($nomeCandidato == NULL) {
                    continue;
                }

                $_logger->imprimir("> " . $nomeCandidato .
                            ": " . $listaQtdeExecCandidatos[ $listaOrdenadaCandidatos[$candAtual] ]);

                if ($listaDataUltimaExecCandidatos[ $listaOrdenadaCandidatos[$candAtual] ] != 0) {
                    $_logger->imprimir(" (&uacute;ltima execu&ccedil;&atilde;o em " .
                            $listaDataUltimaExecCandidatos[ $listaOrdenadaCandidatos[$candAtual] ] . ")");
                }

                $_logger->imprimirNovaLinha("");
            } while ( ++$candAtual < count($listaQtdeExecCandidatos) );
        }

        return $retMtd;
    }

    /*int*/ private function _get_qtdeTarefasSemAtribuicao(/*array*/ $listaAtribTarefas) {
        // Encontrar as tarefas que precisam de atribuicao
        for ($retFn = 0,
                $contPos = 0; $contPos < count($listaAtribTarefas); $contPos++) {
            if ($listaAtribTarefas[$contPos] == -1) {
                $retFn++;
            }
        }

        return $retFn;
    }

    /**
     * @return the $_qtdeIntegrantesDisp
     */
    /*int*/ function get_qtdeIntegrantesDisp() {
        return $this->_qtdeIntegrantesDisp;
    }

    /**
     * @param $_qtdeIntegrantesDisp the $_qtdeIntegrantesDisp to set
     */
    /*void*/ private function _incrementarQtdeIntegrantesDisp() {
        $this->_qtdeIntegrantesDisp++;
    }
}
