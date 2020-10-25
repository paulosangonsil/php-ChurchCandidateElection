<?php
    set_include_path (get_include_path() . PATH_SEPARATOR .
                        '../view' . PATH_SEPARATOR .
                        'view' . PATH_SEPARATOR .
                        '../model' . PATH_SEPARATOR .
                        'model');

    require_once 'conexao.php';
    require_once 'mylogger.php';
    require_once 'celula.php';
    require_once 'tarefas.php';
    require_once 'candidatodatarefa.php';
    require_once 'registrodeexecucao.php';
    require_once 'listadecandidatos.php';
    require_once 'listadeexecucao.php';

    const   CMD_TIPO_OPERACAO       = 'editar',
            CMP_TAREFASCANDIDATO    = 'strCandTar',
            CMP_DATA                = 'strData',
            CMD_INTEGR_ID           = 'strIntegrId',
            CMD_INTEGR_VISIT        = 'strIntegrVisit',
            CMD_INTEGR_SAIU         = 'strIntegrSaiu',
            CMD_REGISTRO_ID         = 'strRegId',
            CMD_CELULA              = 'strCelula',
            CMD_PAG_ATUAL           = 'strPagAtual',
            CMD_PAG_OFFSET          = 'pagoffset',

            VALOR_OPERACAO_NOVO     = 0,
            VALOR_OPERACAO_EDITAR   = 1,
            VALOR_OPERACAO_LISTAR   = 2,
            VALOR_OPERACAO_GRAVAR   = 3,

            PAG_INDEX               = 'index.php',
            PAG_LOGIN               = 'login.php',
            PAG_ATRIBTAREFAS        = 'atribuicaotarefa.php',
            PAG_GEREXEC             = 'gerexecucao.php',
            PAG_GERINTEGR           = 'gerintegrantes.php',
            PAG_GERTAREFAS          = 'gertarefas.php',
            PAG_GERRELATORIOS       = 'gerrelatorios.php',
            PAG_NAVMENU             = 'navmenu.php';

    $PARM_NOVOREGISTRO              = "?" . CMD_TIPO_OPERACAO . "=" . VALOR_OPERACAO_NOVO;

    $_dataExec = pegaVariavelHTTP(CMP_DATA);

    /*int[]*/ function geraListaTarefasDaPostagem() {
        global $_listaDeTarefas;

        $retMtd = NULL;

        for ($tarefa = 0; $tarefa < count($_listaDeTarefas); $tarefa++) {
            $fromReq = pegaVariavelHTTP(CMP_TAREFASCANDIDATO . $tarefa);

            if ( ($fromReq == NULL) ||
                    ($fromReq == -1) ) {
                break;
            }

            $retMtd[$tarefa] = $fromReq;
        }

        return $retMtd;
    }

    /*int[]*/ function geraListaTarefasRestritasDaPostagem() {
        global $_listaDeTarefas;

        $retMtd = NULL;

        for ($contador = $tarefa = 0; $tarefa < count($_listaDeTarefas); $tarefa++) {
            $fromReq = pegaVariavelHTTP(CMP_TAREFASCANDIDATO . $tarefa);

            if ( ($fromReq == NULL) ||
                    ($fromReq == -1) ) {
                continue;
            }

            $retMtd[$contador++] = $tarefa;
        }

        return $retMtd;
    }

    /*String*/ function geraListaCandidatos_SelectBox($usrSelecionado) {
        global $_listaDeCandidatos;

        $retMtd = "                                    <option value=\"-1\"";

        if ($usrSelecionado == -1) {
            $retMtd .= " selected";
        }

        $retMtd .= ">N&atilde;o atribu&iacute;do</option>\n";

        foreach ($_listaDeCandidatos as $candidatoDaVez) {
            $id = $candidatoDaVez->get_id();
            $nome = $candidatoDaVez->get_nome();

            $retMtd .= "                            <option value=\"$id\"";

            if ($usrSelecionado == $id) {
                $retMtd .= " selected";
            }

            $retMtd .= ">$nome</option>\n";
        }

        return $retMtd;
    }

    /*String*/ function pegaVariavelHTTP($nomeVar) {
        $retMtd = NULL;

        for ($cont = 0; $cont < 2; $cont++) {
            $retMtd = filter_input( ($cont == 0) ? INPUT_POST : INPUT_GET, $nomeVar );

            if ( ! is_null($retMtd) ) {
                break;
            }
        }

        return $retMtd;
    }

    /*String*/ function geraListaTarefasSQL($tarefaAtual, $listaMontada) {
        $retMtd = $listaMontada;

        if ($retMtd == NULL) {
            $retMtd = '0x';
        }

        $retMtd .= sprintf('%02x', $tarefaAtual);

        return $retMtd;
    }

    /*array()*/ function _processaListagemDeRegistros(/*int*/ &$qtdeTotalRegsExibir,
                    /*int*/ &$pagOffset, /*int*/ $qtdeMaxParaExibicao,
            /*String*/ $nomePagBase) {
        if ( is_null($pagOffset) || ( ! is_numeric($pagOffset) ) ) {
            $pagOffset = 0;
        }

        if ($qtdeTotalRegsExibir > $qtdeMaxParaExibicao) {
            $qtdeTotalPags = ceil($qtdeTotalRegsExibir / $qtdeMaxParaExibicao) - 1;

            if ($pagOffset > $qtdeTotalPags) {
                $pagOffset = 0;
            }

            $contagem = $pagOffset * $qtdeMaxParaExibicao;

            if ($contagem + $qtdeMaxParaExibicao < $qtdeTotalRegsExibir) {
                $qtdeTotalRegsExibir = $contagem + $qtdeMaxParaExibicao;
            }
        }

        $linkBase = $nomePagBase . "?" . CMD_PAG_OFFSET . "=";
        $linkHome = NULL;
        $linkBack = NULL;
        $linkNext = NULL;
        $linkLast = NULL;

        if ($qtdeTotalPags > 0) {
            if ($pagOffset > 0) {
                $linkHome = "<a href=\"$linkBase" . "0\">In&iacute;cio</a>|";

                $linkBack = "<a href=\"$linkBase" . ($pagOffset - 1) . "\">Anterior</a>";
            }

            if ($pagOffset != $qtdeTotalPags) {
                $linkNext = "|<a href=\"$linkBase" . ($pagOffset + 1) . "\">Pr&oacute;ximo</a>";

                $linkLast = "|<a href=\"$linkBase$qtdeTotalPags\">&Uacute;ltimo</a>";
            }
        }

        $mtdRet[] = $contagem;
        $mtdRet[] = $linkHome;
        $mtdRet[] = $linkBack;
        $mtdRet[] = $linkNext;
        $mtdRet[] = $linkLast;

        return $mtdRet;
    }

    session_start();

    $_idCelula = $_SESSION[CMD_CELULA];

    $_pagAtual = basename( filter_input(INPUT_SERVER, "REQUEST_URI") );

    if ($_pagAtual == NULL) {
        $_pagAtual = basename($_SERVER["SCRIPT_FILENAME"]/*, '.php'*/);
    }

    if ( ($_pagAtual == NULL) ||
        ( (strstr($_pagAtual, PAG_LOGIN) == NULL) &&
        (strstr($_pagAtual, PAG_INDEX) == NULL) &&
        (strstr($_pagAtual, "EscolhaDeCandidatos") == NULL ) ) ) {
        if ($_idCelula == NULL) {
            header("Status: 301 Moved Permanently");
            header("Location: " . PAG_LOGIN);
        }
    }

    /*if ($_idCelula == NULL) {
        $_idCelula = pegaVariavelHTTP(CMD_CELULA);
    }*/

    if ( ! is_null($_idCelula) ) {
        /*ArrayList<CandidatoDaTarefa>*/ $_listaDeCandidatos     = ListaDeCandidatos::getInstance($_idCelula)->getListagem();

        /*ArrayList<String>*/ $_listaDeTarefas        = Tarefas::getInstance()->getListaTarefas();

        /*ArrayList<RegistroDeExecucao>*/ $_listaDeExecucao       = ListaDeExecucao::getInstance($_idCelula)->getListagem();

        /*MyLogger*/ $_logger   = MyLogger::getInstance();
    }
