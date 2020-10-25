<?php
    require_once 'comum.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- TemplateBeginEditable name="doctitle" -->
<title>Cadastro/Edi&ccedil;&atilde;o das Tarefas</title>
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
      <p><?php include (PAG_NAVMENU); ?></p>
    <h1> Tarefas </h1>
    <form id="form1" name="form1" method="post" action="">
        <table width="200" border="1">
            <tr>
                <td width="22"><label>
                    <input type="checkbox" name="_alterarOpc01" id="_alterarOpc01" tabindex="1" />
                </label></td>
                <td width="162"><label>
                        <input name="_strOpc01" type="text" id="_strOpc01" value="Dire&ccedil;&atilde;o" />
                </label></td>
            </tr>
            <tr>
                <td height="24"><input type="checkbox" name="_alterarOpc02" id="_alterarOpc02" tabindex="1" /></td>
                <td><p>
                    <input name="_strOpc02" type="text" id="_strOpc02" value="Quebra-Gelo" />
                    </p>                    </td>
            </tr>
            <tr>
                <td><input type="checkbox" name="_alterarOpc03" id="_alterarOpc03" tabindex="1" /></td>
                <td><input name="_strOpc03" type="text" id="_strOpc03" value="Louvor" /></td>
            </tr>
            <tr>
                <td><input type="checkbox" name="_alterarOpc04" id="_alterarOpc04" tabindex="1" /></td>
                <td><input name="_strOpc04" type="text" id="_strOpc04" value="Palavra" /></td>
            </tr>
            <tr>
                <td><input type="checkbox" name="_alterarOpc05" id="_alterarOpc05" tabindex="1" /></td>
                <td><input name="_strOpc05" type="text" id="_strOpc05" value="Oferta" /></td>
            </tr>
            <tr>
                <td><input type="checkbox" name="_alterarOpc06" id="_alterarOpc06" tabindex="1" /></td>
                <td><input name="_strOpc06" type="text" id="_strOpc06" value="Nova Tarefa" /></td>
            </tr>
        </table>
        <p>
            <label>
            <input type="submit" name="_btConfirmar" id="_btConfirmar" value="Confirmar" />
            </label>
        </p>
        <p>
            <!-- end #mainContent -->
            </p>
    </form>
    </div>
<!-- end #container --></div>
</body>
</html>
