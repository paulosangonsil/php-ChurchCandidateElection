<?php
$_nomeSrv   = 'localhost';
$_nomeUsr   = 'psgsilva';
$_senhaUsr  = 'psgsilva';
$_manipSrv  = NULL;

$_nomeBD                = 'escolhecandidatocelula';

$_nomeTbl_Tarefas       = 'escolhacandidatocelula_tarefas';
$_nomeTbl_RegExec       = 'escolhacandidatocelula_registrodeexecucao';
$_nomeTbl_Integrantes   = 'escolhacandidatocelula_integrantes';
$_nomeTbl_Celula        = 'escolhacandidatocelula_celulas';

$_conexIniciada = FALSE;

function _getDBSrvHandle() {
    global  $_manipSrv;

    return $_manipSrv;
}

function    _iniciaConexaoBD() {
    global  $_conexIniciada,
            $_manipSrv,
            $_nomeSrv,
            $_nomeUsr,
            $_senhaUsr,
            $_nomeBD;

    if ($_conexIniciada) {
        return;
    }

    try {
        $dsn = "mysql:host=" . $_nomeSrv . ";dbname=" . $_nomeBD;

        $_manipSrv = new PDO( $dsn, $_nomeUsr, $_senhaUsr );
    } catch (PDOException $e){
        die($e);
    }

    $_conexIniciada = TRUE;
}
