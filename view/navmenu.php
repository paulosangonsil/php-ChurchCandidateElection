<?php
    require_once 'comum.php';
?>

<a href="<?php echo PAG_GERINTEGR; ?>">Integrantes</a> (<a href="<?php echo PAG_GERINTEGR .
            $PARM_NOVOREGISTRO; ?>">Novo</a>) | <a href="<?php echo PAG_GEREXEC; ?>">Execu&ccedil;&atilde;o</a>
            (<a href="<?php echo PAG_GEREXEC . $PARM_NOVOREGISTRO; ?>">Novo</a>) | 
        <a href="<?php echo PAG_ATRIBTAREFAS; ?>">Atribui&ccedil;&atilde;o de tarefas</a>
        (<a href="<?php echo PAG_GERTAREFAS . $PARM_NOVOREGISTRO; ?>">Nova</a>) | 
        <a href="<?php echo PAG_GERRELATORIOS; ?>">Relat&oacute;rio de Execu&ccedil;&atilde;o de Tarefas</a>