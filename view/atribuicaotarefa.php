<?php
    require_once 'comum.php';
    require_once '../model/executor.php';

    const       CMP_CANDPRESENTE    = "_boolIntegr%02dPresent",
                CMP_REGRCONJUGE     = "_boolIntegr%02dRegrConjug";

    $_listaDeTarefasAtribuidas = array_fill(0, count($_listaDeTarefas), -1);

    $_atribuicaoExecutada = FALSE;

    /*void*/ function _complementaListaCandidatos() {
        global $_listaDeCandidatos;

        foreach ($_listaDeCandidatos as $candidatoDaVez) {
            $id                 = $candidatoDaVez->get_id();
            $disponivel         = FALSE;
            $ignorarRegraConj   = FALSE;

            $fromReq = pegaVariavelHTTP( sprintf(CMP_CANDPRESENTE, $id) );

            if ( ! is_null($fromReq) ) {
                $disponivel = TRUE;
            }

            $fromReq = pegaVariavelHTTP( sprintf(CMP_REGRCONJUGE, $id) );

            if ( ! is_null($fromReq) ) {
                $ignorarRegraConj = TRUE;
            }

            $candidatoDaVez->set_disponibilidade($disponivel);
            $candidatoDaVez->set_ignorarRegraConjuge($ignorarRegraConj);
        }
    }

    /*CandidatoDaTarefa*/ function _procurarIntegranteEmLista(/*int*/ $idIntegrante,
            /*ArrayList<CandidatoDaTarefa>*/ $listagem) {
        $mtdRet = null;

        foreach ($listagem as $integranteAtual) {
            if ($integranteAtual->get_id() == $idIntegrante) {
                $mtdRet = $integranteAtual;

                break;
            }
        }

        return $mtdRet;
    }

    /*int[]*/ function _montarListaExecucaoDefinidaPeloUsr() {
        global  $_listaDeTarefas,
                $_listaDeTarefasAtribuidas;

        $retMtd = $_listaDeTarefasAtribuidas;
//die(print_r($_POST));
        for ($tarefaAtual = 0;
                $tarefaAtual < count($_listaDeTarefas); $tarefaAtual++) {
            $fromReq = pegaVariavelHTTP(CMP_TAREFASCANDIDATO . $tarefaAtual);

            if ( ( ! is_null($fromReq) ) &&
                    ($fromReq != -1) ) {
                $retMtd[$tarefaAtual] = intVal($fromReq);
            }
        }

        return $retMtd;
    }

    /*int*/ function _tarefaMenosExecDoCandidato(/*int*/ $candidato,
            /*int*/ $regra = Executor::ORD_EXEC) {
        global  $_listaDeExecucao;

        /*int []*/ $execTarefas = array_fill(Tarefas::DIRECAO, Tarefas::OFERTA, 0);

        // Varrer a lista de execucoes
        for ($diaAtual = 0; $diaAtual < count($_listaDeExecucao); $diaAtual++) {
            $listaTarefasDoDia = $_listaDeExecucao[$diaAtual]->get_tarefas();

            for ($tarefaAtual = Tarefas::DIRECAO;
                $tarefaAtual < Tarefas::QTDE_TAREFA; $tarefaAtual++) {
                if ($listaTarefasDoDia[$tarefaAtual] == $candidato) {
                    if ($regra == Executor::ORD_EXEC) {
                        $execTarefas[$tarefaAtual]++;
                    }
                    else {
                        $execTarefas[$tarefaAtual] = $_listaDeExecucao[$diaAtual]->get_data();
                    }
                }
            }
        }

        // Iniciar o retorno
        /*int*/ $mtdRet     = Tarefas::PALAVRA;
        /*int*/ $qtdeExecs  = $execTarefas[Tarefas::PALAVRA];

        for ($tarefaAtual = Tarefas::DIRECAO;
            $tarefaAtual < Tarefas::QTDE_TAREFA; $tarefaAtual++) {
            if ($qtdeExecs > $execTarefas[$tarefaAtual]) {
                $mtdRet = $tarefaAtual;

                $qtdeExecs = $execTarefas[$tarefaAtual];
            }
        }

        return $mtdRet;
    }

    /*boolean*/ function _haIntegranteComTarefasMultiplas($listaDefUsr, &$listaDeTarefas) {
        $retMtd         = FALSE;

        for ($tarefaAtual = 0;
                $tarefaAtual < count($listaDeTarefas); $tarefaAtual++) {
            for ($tarefaProx = 0;
                    $tarefaProx < count($listaDeTarefas); $tarefaProx++) {
                if ($tarefaProx == $tarefaAtual) {
                    continue;
                }

                if ($listaDeTarefas[$tarefaProx] == $listaDeTarefas[$tarefaAtual]) {
                    // Preservar a pre-selecao feita pelo usr
                    if ( ($listaDefUsr[$tarefaAtual] == -1) ||
                        ( ($listaDeTarefas[$tarefaAtual] != $listaDefUsr[$tarefaAtual]) &&
                            ( ! in_array($listaDeTarefas[$tarefaAtual], $listaDefUsr) ) ) ) {
                        if ( (_tarefaMenosExecDoCandidato($listaDeTarefas[$tarefaAtual]) != $tarefaAtual) ||
                                ( in_array($listaDeTarefas[$tarefaAtual], $listaDeTarefas) ) ) {
                            $listaDeTarefas[$tarefaAtual] = -1;

                            $retMtd = TRUE;

                            //break;
                        }
                    }
                }
            }
        }

        return $retMtd;
    }

    /*void*/ function _fazerAtribuicoes() {
        $objExecutor        = new Executor();

        global  $_listaDeTarefas,
                $_listaDeCandidatos,
                $_listaDeExecucao,
                $_logger,
                $_atribuicaoExecutada;

        $listaDeCandidatos  = array();

        $candidato          = null;

        $qtdeReatribuicao   = 0;

        _complementaListaCandidatos();

        $listaDefUsr = $listaDeTarefas = _montarListaExecucaoDefinidaPeloUsr();

        //$_logger->imprimir("<!--");

        do {
            // Inicializar a lista de tarefas
            for ($tarefaAtual = 0; $tarefaAtual < count($_listaDeTarefas); $tarefaAtual++) {
                $_logger->imprimirNovaLinha("Para a tarefa " .
                    Tarefas::getInstance()->getNome($tarefaAtual) . " temos:");

                // A pre-definicao do usr deve ser mantida
                if ( ($listaDefUsr[$tarefaAtual] != -1) &&
                       ($listaDefUsr[$tarefaAtual] != $listaDeTarefas[$tarefaAtual]) ) {
                    $listaDeTarefas[$tarefaAtual] = $listaDefUsr[$tarefaAtual];
                }

                if ($listaDeTarefas[$tarefaAtual] != -1) {
                    $_logger->imprimirNovaLinha("> " . ListaDeCandidatos::getInstance($_idCelula)->
                                    nomeCandidato( $listaDeTarefas[$tarefaAtual] ) .
                                " j&aacute; foi pre-selecionado(a) para a tarefa&#10;");

                    continue;
                }

//                $objExecutor->configContingencia(0);

                do {
                    $listaDeCandidatos = $objExecutor->
                            filtraListaCandidatoHabilitado($_listaDeCandidatos,
                                    $_listaDeExecucao, $tarefaAtual);

                    for ($candidatoAtual = 0; $candidatoAtual < count($listaDeCandidatos);
                            $candidatoAtual++) {
                        $candidato = _procurarIntegranteEmLista($listaDeCandidatos[$candidatoAtual],
                                        $_listaDeCandidatos);

                        // O candidato nao pode ter tarefa atribuida
                        if ( ( ! $objExecutor->candidatoSemAtribuicao($candidato,
                                    $listaDeCandidatos, $listaDeTarefas) ) ||
                            // O conjuge do candidato nao pode ter atribuicao de tarefas,
                            // a nao ser que a regra deva ser ignorada
                                ( ( ! $candidato->is_ignorarRegraConjuge() ) &&
                                ($objExecutor->conjugeDoCandidatoComAtribuicao($candidato,
                                        $listaDeTarefas) ) ) ) {
                            $listaDeCandidatos[$candidatoAtual] = -1;

                            continue;
                        }
                    }

                    // O candidato nao pode executar a msm tarefa ate que todos
                    // da lista atual tenham participado

                    // Escolher o candidato com menor incidencia para a dada tarefa
                    $listaDeTarefas[$tarefaAtual] = $objExecutor->
                            ordenaCandidatoPorRegraSelecionada/*ordenaCandidatoPorQuantidadeExecucao*/($tarefaAtual,
                                    $listaDeCandidatos, $_listaDeExecucao, Executor::ORD_DATA);

                    if ($listaDeTarefas[$tarefaAtual] == -1) {
                        $objExecutor->configContingencia($objExecutor->contingenciaHabilitada() + 1);

                        $_logger->imprimirNovaLinha("&#10;> N&atilde;o foi encontrado algu&eacute;m para a execu&ccedil;&atilde;o desta tarefa - ser&aacute; aplicada nova regra...&#10;");
                    }
                } while ($listaDeTarefas[$tarefaAtual] == -1);

                $objExecutor->configContingencia($qtdeReatribuicao);

                $_logger->imprimirNovaLinha("&#10;> " . ListaDeCandidatos::getInstance($_idCelula)->nomeCandidato( $listaDeTarefas[$tarefaAtual] ) .
                            " foi selecionado(a)&#10;" /*pois foi quem menos executou a tarefa&#10;*/);
            }

            // Exibir na tela
            $_logger->imprimirNovaLinha("&#10;As atribui&ccedil;&otilde;es foram:");

            for ($tarefaAtual = 0;
                    $tarefaAtual < count($listaDeTarefas); $tarefaAtual++) {
                $_logger->imprimirNovaLinha("> " . Tarefas::getInstance()->getNome($tarefaAtual) . ": " .
                                    ListaDeCandidatos::getInstance($_idCelula)->nomeCandidato( $listaDeTarefas[$tarefaAtual] ) );
            }

            if ( ( $objExecutor->get_qtdeIntegrantesDisp() > count($listaDeTarefas) ) &&
                    ( _haIntegranteComTarefasMultiplas($listaDefUsr, $listaDeTarefas) ) ) {
                if (++$qtdeReatribuicao < Executor::CONTIN_ULT + 1) {
                    $objExecutor->configContingencia($qtdeReatribuicao);

                    $_logger->imprimirNovaLinha("&#10;!!!Houve atribui&ccedil;&atilde;o m&uacute;ltipla, tentemos uma nova distribui&ccedil;&atilde;o&#10;");
                }
            }
            else {
                $qtdeReatribuicao = Executor::CONTIN_ULT + 1;
            }
        } while ($qtdeReatribuicao < Executor::CONTIN_ULT + 1);

        //$_logger->imprimir("--//>");

        $_atribuicaoExecutada = TRUE;

        return $listaDeTarefas;
    }

    if ( ( ! is_null($_dataExec) ) && ( is_numeric($_dataExec) ) ) {
        $_listaDeTarefasAtribuidas = _fazerAtribuicoes();
        //die;
    }
    else {
        $_dataExec = date("Ymd");
    }
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- TemplateBeginEditable name="doctitle" -->
<title>Atribui&ccedil;&atilde;o de Tarefas</title>
<!-- TemplateEndEditable -->
<!-- TemplateBeginEditable name="head" -->
<!-- TemplateEndEditable -->
<style type="text/css">
<!--
body {
	font: 100% Verdana, Arial, Helvetica, sans-serif;
	background: #666666;
	margin: 0; /* it's good practice to zero the margin and padding of the body element to account for differing browser defaults */
	padding: 0;
	text-align: center; /* this centers the container in IE 5* browsers. The text is then set to the left aligned default in the #container selector */
	color: #000000;
}
.oneColElsCtr #container {
	width: 46em;
	background: #FFFFFF;
	margin: 0 auto; /* the auto margins (in conjunction with a width) center the page */
	border: 1px solid #000000;
	text-align: left; /* this overrides the text-align: center on the body element. */
}
.oneColElsCtr #mainContent {
	padding: 0 20px; /* remember that padding is the space inside the div box and margin is the space outside the div box */
}
-->
</style></head>

<body class="oneColElsCtr">
<div id="container">
  <div id="mainContent">
    <h1> Atribui&ccedil;&atilde;o de Tarefas</h1>
    <p><?php include (PAG_NAVMENU); ?></p>
    <form id="form1" name="form1" method="post" action="">
        <table width="613" border="1">
            <tr>
                <td width="153"><p>Data:</p></td>
                <td width="444"><input name="<?php echo CMP_DATA ?>" type="text" id="<?php echo CMP_DATA ?>" value="<?php echo $_dataExec ?>" /></td>
            </tr>

            <tr>
                <td>Integrante:</td>
                <td><table width="100%" border="1" id="_tblIntegrantes">
                    <tr>
                        <td width="18%"><div align="center"><strong>Presente</strong></div></td>
                        <td width="31%"><div align="center"><strong>Ignorar regra C&ocirc;njuge</strong></div></td>
                        <td width="51%"><div align="center"><strong>Nome</strong></div></td>
                    </tr>
<?php
    foreach ($_listaDeCandidatos as $candidatoDaVez) {
        $id = $candidatoDaVez->get_id();
        $nome = $candidatoDaVez->get_nome();

        if ( $candidatoDaVez->is_saiucelula() ) {
            continue;
        }
?>
                    <tr>
                        <td><div align="center">
                            <input name="<?php echo sprintf(CMP_CANDPRESENTE, $id); ?>" type="checkbox"
                                   id="<?php echo sprintf(CMP_CANDPRESENTE, $id); ?>"<?php if ( $candidatoDaVez->is_disponibilidade() ) { echo " checked"; } ?> />
                        </div></td>
                        <td><div align="center">
                            <input type="checkbox" name="<?php echo sprintf(CMP_REGRCONJUGE, $id); ?>"
                                id="<?php echo sprintf(CMP_REGRCONJUGE, $id); ?>"<?php if ( $candidatoDaVez->is_ignorarRegraConjuge() ) { echo " checked"; } ?> />
                        </div></td>
                        <td><div align="center"><a href="<?php echo PAG_GERINTEGR . "?" .
                                CMD_TIPO_OPERACAO . "=" . VALOR_OPERACAO_EDITAR . "&" .
                                CMD_INTEGR_ID . "=" . $id; ?>"><?php echo $nome; ?></a></div></td>
                    </tr>
<?php
    }
?>
                </table>
                    <label></label></td>
            </tr>
            <tr>
                <td>Tarefas:</td>
                <td><table width="100%" border="1" id="_tblTarefas" >
<?php
    for ($tarefa = 0; $tarefa < count($_listaDeTarefas); $tarefa++) {
?>
                        <tr>
                            <td width="30%"><?php echo $_listaDeTarefas[$tarefa]; ?>:</td>
                            <td width="70%">
                                <select name="<?php echo CMP_TAREFASCANDIDATO . $tarefa; ?>" id="<?php echo CMP_TAREFASCANDIDATO . $tarefa; ?>" >
<?php
        echo geraListaCandidatos_SelectBox($_listaDeTarefasAtribuidas[$tarefa]);
?>
                                </select>
                            </label></td>
                        </tr>
<?php
    }
?>
                    </table>
                        <label></label></td>
            </tr>
        </table>
        <p>
            <label>
            <input type="submit" name="_btConfirmar" id="_btConfirmar" value="Gerar Atribui&ccedil;&atilde;o" />
            </label>
        </p>
        <p<center>
<?php
    if ($_atribuicaoExecutada) {
?>
            <textarea rows="50" cols="73"><?php echo $_logger->obterMemoriaParaImpressao(); ?></textarea>
<?php
    }
?>
            </center>
            <!-- end #mainContent -->
            </p>
    </form>
    </div>
<!-- end #container --></div>
</body>
</html>
