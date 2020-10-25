<?php
    require_once 'comum.php';

    $_qtdeExecExibir    = 1;
    $_tarefasDoDia      = array_fill(0, count($_listaDeTarefas), -1);

    const   REG_POR_LISTAGEM = 10;

    if ($_dataExec == NULL) {
        $_dataExec = date("Ymd");
    }

    $_idExibir          = -1;
    $regId              = -1;
    $_qtdeTotalPags     = 1;

    $contagem = 0;

    $_operId = pegaVariavelHTTP(CMD_TIPO_OPERACAO);

    $_pagOffset = pegaVariavelHTTP(CMD_PAG_OFFSET);

    $seqTarefas = NULL;

    if ( ( ! is_null($_operId) ) && ( is_numeric($_operId) ) ) {
        if ($_operId == VALOR_OPERACAO_EDITAR) {
            $_operId = VALOR_OPERACAO_LISTAR;

            // Se tiver data
            if ( ( ! is_null($_dataExec) ) && ( is_numeric($_dataExec) ) ) {
                // Listar registro
                for ( ; $contagem < count($_listaDeExecucao); $contagem++) {
                    if ( $_listaDeExecucao[$contagem]->get_data() == $_dataExec ) {
                        $_qtdeExecExibir = $contagem + 1;

                        $regId = $_listaDeExecucao[$contagem]->get_id();

                        $_operId = VALOR_OPERACAO_GRAVAR;

                        break;
                    }
                }

                if ( $contagem > count($_listaDeExecucao) ) {
                    $contagem = 0;
                }
            }
        }
        else if ($_operId != VALOR_OPERACAO_NOVO) {
            $_operId = VALOR_OPERACAO_LISTAR;

            if ( ( ! is_null($_dataExec) ) && ( is_numeric($_dataExec) ) ) {
                $seqTarefas = geraListaTarefasDaPostagem();

                if ( ! is_null($seqTarefas) ) {
                    $execNova = new RegistroDeExecucao();

                    $execNova->set_data($_dataExec);
                    $execNova->set_tarefas($seqTarefas);

                    $regId = pegaVariavelHTTP(CMD_REGISTRO_ID);

                    if ( ( ! is_null($regId) ) || ( is_numeric($regId) ) ) {
                        // Edicao
                        $execNova->set_id($regId);
                    }

                    $execNova->set_celula($_idCelula);

                    ListaDeExecucao::getInstance($_idCelula)->gravar($execNova);

                    header( "Location: " . basename(__FILE__) );
                }
            }
        }
        else {
            $_operId = VALOR_OPERACAO_GRAVAR;
        }
    }
    else {
        $_operId = VALOR_OPERACAO_LISTAR;
    }

    if ($_operId == VALOR_OPERACAO_LISTAR) {
        // Listar todos
        $_qtdeExecExibir = count($_listaDeExecucao);

        $_procList = _processaListagemDeRegistros( $_qtdeExecExibir,
                        $_pagOffset, REG_POR_LISTAGEM, basename(__FILE__) );

        $contagem = $_procList[0];
    }
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- TemplateBeginEditable name="doctitle" -->
<title>Cadastro/Edi&ccedil;&atilde;o dos Registros da C&eacute;lula</title>
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
    <h1> Registro de Execu&ccedil;&atilde;o</h1>
    <p><?php include (PAG_NAVMENU); ?></p>
    <form id="form1" name="form1" method="post" action="<?php echo basename(__FILE__) ?>">
        <input type="hidden" name="<?php echo CMD_TIPO_OPERACAO; ?>" value="<?php echo $_operId; ?>" />
        <input type="hidden" name="<?php echo CMD_REGISTRO_ID; ?>" value="<?php echo $regId; ?>" />
<?php
    for ( ; $contagem < $_qtdeExecExibir; $contagem++) {
        if ( ($_operId != VALOR_OPERACAO_GRAVAR) ||
                ( ( ! is_null($regId) ) && ($regId != -1) ) ) {
            $_dataExec = $_listaDeExecucao[$contagem]->get_data();

            $_tarefasDoDia = $_listaDeExecucao[$contagem]->get_tarefas();
        }
?>
        <table width="613" border="1">
            <tr>
                <td width="153"><p><a href="<?php echo basename(__FILE__) . "?" .
                        CMD_TIPO_OPERACAO . "=" . VALOR_OPERACAO_EDITAR . "&" .
                        CMP_DATA . "=$_dataExec"; ?>">Data:</a></p></td>
                <td width="444"><input name="<?php echo CMP_DATA; ?>"
                                       type="text" id="<?php echo CMP_DATA; ?>" value="<?php echo $_dataExec; ?>" /></td>
            </tr>

            <tr>
                <td>Tarefas:</td>
                <td><table width="100%" border="1" id="_tblTarefas">
                    <tr>
<?php
    for ($tarefa = 0; $tarefa < count($_listaDeTarefas); $tarefa++) {
?>
                        <td width="30%"><?php echo $_listaDeTarefas[$tarefa]; ?>:</td>
                        <td width="70%">
                        <select name="<?php echo CMP_TAREFASCANDIDATO . $tarefa ?>" id="<?php echo CMP_TAREFASCANDIDATO . $tarefa ?>">
<?php echo geraListaCandidatos_SelectBox($_tarefasDoDia[$tarefa]); ?>
                        </select>
                        </td>
                        </tr>
<?php
    }
?>
                    </table>
                </td>
            </tr>
        </table>
        <p>&nbsp;</p>
<?php
    }

    if ($_operId != VALOR_OPERACAO_LISTAR) {
?>
        <p>
            <label>
            <input type="submit" name="_btConfirmar" id="_btConfirmar" value="Confirmar" />
            </label>
        </p>
<?php
    }
?>
        <p>
<?php
    if ($_operId == VALOR_OPERACAO_LISTAR) {
        for ($contagem = 1; $contagem < count($_procList); $contagem++) {
            if ( ! is_null($_procList[$contagem]) ) {
                echo $_procList[$contagem];
            }
        }
    }
?>
            <!-- end #mainContent -->
            </p>
    </form>
    </div>
<!-- end #container --></div>
</body>
</html>
