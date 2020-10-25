<?php
    require_once 'comum.php';

    // INSERT INTO escolhecandidatocelula.escolhacandidatocelula_integrantes VALUES(NULL, NULL, 'Edgard', 0x03, 0);
    // update escolhecandidatocelula.escolhacandidatocelula_integrantes set id = 0 where id = 1

    const   CMP_NOME_INTEGR     = '_strNome',
            CMP_CONJUG          = '_listaConjuge',

            REG_POR_LISTAGEM    = 7;

    $_qtdeExecExibir    = 1;

    $idIntegrante       = -1;
    $nomeExibir         = '';
    $textVisit          = '';
    $textSaiuCelula     = '';
    $idConjug           = -1;
    $listaRestricao     = NULL;

    $contagem = 0;

    $_pagOffset = pegaVariavelHTTP(CMD_PAG_OFFSET);

    $_operId = pegaVariavelHTTP(CMD_TIPO_OPERACAO);

    if ( ( ! is_null($_operId) ) && ( is_numeric($_operId) ) ) {
        $idIntegrante = pegaVariavelHTTP(CMD_INTEGR_ID);

        if ($_operId == VALOR_OPERACAO_EDITAR) {
            $_operId = VALOR_OPERACAO_LISTAR;

            if ( ( ! is_null($idIntegrante) ) && ( is_numeric($idIntegrante) ) ) {
                // Editar registro
                for ( ; $contagem < count($_listaDeCandidatos); $contagem++) {
                    if ( $_listaDeCandidatos[$contagem]->get_id() == $idIntegrante ) {
                        $_qtdeExecExibir = $contagem + 1;

                        $_operId = VALOR_OPERACAO_GRAVAR;

                        break;
                    }
                }
            }
        }
        else if ($_operId != VALOR_OPERACAO_NOVO) {
            $_operId = VALOR_OPERACAO_LISTAR;

            $nomeExibir = pegaVariavelHTTP(CMP_NOME_INTEGR);

            if ( ( ! is_null($nomeExibir) ) && ( ! is_numeric($nomeExibir) ) ) {
                $objIntegr = new CandidatoDaTarefa();

                $objIntegr->set_nome( htmlentities($nomeExibir, ENT_COMPAT | ENT_HTML401, 'ISO-8859-1') );

                $idConjug = pegaVariavelHTTP(CMP_CONJUG);

                if ( ( ! is_null($idConjug) ) &&
                        ( is_numeric($idConjug) && $idConjug != -1)  ) {
                    $objIntegr->set_idConjuge($idConjug);
                }

                $tarefasRestr = geraListaTarefasRestritasDaPostagem();

                if ( ! is_null($tarefasRestr) ) {
                    $objIntegr->set_tarefasRestritas($tarefasRestr);
                }

                $estadoAtual = 0;

                if ( ! is_null(pegaVariavelHTTP(CMD_INTEGR_VISIT) ) ) {
                    $estadoAtual = IntegranteCelula::ESTADO_VISITANTE;
                }

                if ( ! is_null( pegaVariavelHTTP(CMD_INTEGR_SAIU) ) ) {
                    $estadoAtual |= IntegranteCelula::ESTADO_SAIU_CELULA;
                }

                $objIntegr->set_estado($estadoAtual);

                if ( ( ! is_null($idIntegrante) ) && ( is_numeric($idIntegrante) ) ) {
                    // Editar usuario
                    $objIntegr->set_id($idIntegrante);
                }

                $objIntegr->set_celula($_idCelula);

                ListaDeCandidatos::getInstance($_idCelula)->gravar($objIntegr);

                header( "Location: " . basename(__FILE__) );
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
        $_qtdeExecExibir = count($_listaDeCandidatos);

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
<title>Cadastro/Edi&ccedil;&atilde;o dos Integrantes da C&eacute;lula</title>
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
    <h1> Integrante da C&eacute;lula</h1>
    <p><?php include (PAG_NAVMENU); ?></p>
    <form id="form1" name="form1" method="post" action="<?php echo basename(__FILE__) ?>">
        <input type="hidden" name="<?php echo CMD_TIPO_OPERACAO; ?>" value="<?php echo $_operId; ?>" />
<?php
    for ( ; $contagem < $_qtdeExecExibir; $contagem++) {
        if ( ($_operId != VALOR_OPERACAO_GRAVAR)  ||
                ( ( ! is_null($idIntegrante) ) && ($idIntegrante != -1) ) ) {
            $idIntegrante = $_listaDeCandidatos[$contagem]->get_id();

            $nomeExibir = $_listaDeCandidatos[$contagem]->get_nome();

            $idConjug = $_listaDeCandidatos[$contagem]->get_idConjuge();

            $listaRestricao = $_listaDeCandidatos[$contagem]->get_tarefasRestritas();

            if ( ($_listaDeCandidatos[$contagem]->get_estado() & IntegranteCelula::ESTADO_VISITANTE) ==
                    IntegranteCelula::ESTADO_VISITANTE ) {
                $textVisit = 'checked ';
            }
            else {
                $textVisit = '';
            }

            if ( $_listaDeCandidatos[$contagem]->is_saiucelula() ) {
                $textSaiuCelula = 'checked ';
            }
            else {
                $textSaiuCelula = '';
            }
        }
?>
        <input type="hidden" name="<?php echo CMD_INTEGR_ID; ?>" value="<?php echo $idIntegrante; ?>" />
        <table width="359" border="1">
            <tr>
                <td width="153"><p><a href="<?php echo basename(__FILE__) . "?" . CMD_TIPO_OPERACAO . "=" . VALOR_OPERACAO_EDITAR . "&" .
                        CMD_INTEGR_ID . "=" . $idIntegrante; ?>">Nome:</a></p>
                    </td>
                <td width="190"><input type="text" name="<?php echo CMP_NOME_INTEGR; ?>" id="<?php echo CMP_NOME_INTEGR; ?>" value="<?php echo $nomeExibir; ?>"/></td>
            </tr>
            <tr>
                <td height="24">C&ocirc;njuge:</td>
                <td><label>
                    <select name="<?php echo CMP_CONJUG; ?>" id="<?php echo CMP_CONJUG; ?>">
<?php echo geraListaCandidatos_SelectBox($idConjug); ?>
                    </select>
                </label></td>
            </tr>
            <tr>
                <td>Tarefas restritas:</td>
                <td><table width="100%" border="1">
<?php
        for ($tarefa = 0; $tarefa < count($_listaDeTarefas); $tarefa++) {
            $textRestrito   = '';

            if ( ! is_null($listaRestricao) ) {
                for ($restricao = 0; $restricao < count($listaRestricao); $restricao++) {
                    if ($listaRestricao[$restricao] == $tarefa) {
                        $textRestrito = ' checked';

                        break;
                    }
                }
            }
?>
                    <tr>
                        <td><label>
                            <input type="checkbox" name="<?php echo CMP_TAREFASCANDIDATO .
                                    $tarefa; ?>" id="<?php echo CMP_TAREFASCANDIDATO . $tarefa; ?>"<?php echo $textRestrito; ?>/>
                        </label></td>
                        <td><?php echo $_listaDeTarefas[$tarefa]; ?></td>
                    </tr>
<?php
        }
?>
                </table>
                    <label></label></td>
            </tr>
            <tr>
                <td>&Eacute; visitante?</td>
                <td><input type="checkbox" name="<?php echo CMD_INTEGR_VISIT; ?>" id="<?php echo CMD_INTEGR_VISIT; ?>" <?php echo $textVisit; ?> /></td>
            </tr>
            <tr>
                <td>Saiu da c&eacute;lula?</td>
                <td><input type="checkbox" name="<?php echo CMD_INTEGR_SAIU; ?>" id="<?php echo CMD_INTEGR_SAIU; ?>" <?php echo $textSaiuCelula; ?> /></td>
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
