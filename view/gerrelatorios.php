<?php
    require_once 'comum.php';
    require_once '../model/executor.php';

    const   CMP_TIPO_RELATORIO  = "tiporelat",
            TIPO_RELATORIO_01   = 0;

    global  $_listaDeCandidatos,
            $_listaDeExecucao;

    $_objExecutor    = new Executor();

    $_objExecutor->configContingencia(Executor::CONTIN_2);

    for ($tarefaAtual = 0; $tarefaAtual < count($_listaDeTarefas); $tarefaAtual++) {
        $listaDeCandidatos = $_objExecutor->
                filtraListaCandidatoHabilitado($_listaDeCandidatos,
                        $_listaDeExecucao, $_listaDeCandidatos, TRUE);

        $_objExecutor->ordenaCandidatoPorRegraSelecionada/*ordenaCandidatoPorQuantidadeExecucao*/($tarefaAtual,
                        $listaDeCandidatos, $_listaDeExecucao, Executor::ORD_DATA);
    }
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
</style></head>

<body class="oneColElsCtr">
<div id="container" style="text-align:center">
    <p><?php include (PAG_NAVMENU); ?></p>
    <p>
    Hist&oacute;rico de execu&ccedil;&atilde;o das tarefa para todos os usu&aacute;rios:
    <form id="form1" name="form1" method="post" action="">
            <textarea rows="50" cols="73"><?php echo $_logger->obterMemoriaParaImpressao(); ?></textarea>
        </form>
    </p>
</div>
<!-- end #container --></div>
</body>
</html>
