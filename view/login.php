<?php
require_once 'comum.php';

const   CMP_SENHA_CELULA    = 'asdf';

//$_operId = pegaVariavelHTTP(CMD_TIPO_OPERACAO);

$idCelula = pegaVariavelHTTP(CMD_CELULA);

//if ($_operId == VALOR_OPERACAO_EDITAR) {
if ( ! is_null($idCelula) ) {
    $senha = pegaVariavelHTTP(CMP_SENHA_CELULA);

    if ( ( /*( ! is_null($idCelula) ) && */( ! is_null($senha) ) ) &&
        ( is_numeric($idCelula) && ($idCelula > 0) ) ) {
        $regCelula = new Celula($idCelula);

        if (strcmp( $senha, $regCelula->get_senha() ) == 0) {
            session_start();

            $_SESSION[CMD_CELULA] = $regCelula->get_id();

            header("Status: 301 Moved Permanently");
            header("Location: " . PAG_ATRIBTAREFAS);
        }
    }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
        <!-- TemplateBeginEditable name="doctitle" -->
        <title>Atribui&ccedil;&atilde;o de Tarefas</title>
    </head>
    <body>
        <p>Selecione a c&eacute;lula e digite a senha:
            <form action="<?php echo PAG_LOGIN; ?>" method="post" name="mainFrm" id="mainFrm">
                <input name="<?php echo CMD_TIPO_OPERACAO ?>" type="hidden" id="<?php echo CMD_TIPO_OPERACAO ?>" value="<?php echo VALOR_OPERACAO_EDITAR ?>" />
                <select name="<?php echo CMD_CELULA ?>" type="text" id="<?php echo CMD_CELULA ?>" >
                    <option value="-1">Selecione a c&eacute;lula</option>
<?php
    $listaCelulas  = Celula::listarNomes();

    while ( $nomeCelula = current($listaCelulas) ) { ?>
                    <option value="<?php echo key($listaCelulas) ?>"><?php echo $nomeCelula ?></option>
<?php
        next($listaCelulas);
    } ?>
               </select>
                senha: <input type="password" name="<?php echo CMP_SENHA_CELULA ?>" id="<?php echo CMP_SENHA_CELULA ?>" />
                <p><input type="submit" name="_btConfirmar" id="_btConfirmar" value="Confirmar" /></p>
            </form>
        </p>
    </body>
</html>
