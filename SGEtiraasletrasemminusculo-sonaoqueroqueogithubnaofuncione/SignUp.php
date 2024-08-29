<?php
error_reporting(E_ALL ^ E_DEPRECATED); //Erros depreciados significam que há uma maneira mais aceita de fazer, mas não são necessariamente errados. Para mostrar esses erros, só apaga essa linha.

function cadastro($Nome, $Email, $Senha, $SenhaConfirmada, $SIAPE, $Atividades, $Tipo)
{
    /* -----------------Verificação----------------------- */
    include('Config.php');

    $elementos = func_get_args(); // Basicamente, bota todos os argumentos da função numa lista.
    // print_r($elementos); // Printa os argumentos para verificar se está funcionando.

    foreach ($elementos as $valor) {
        if (preg_match("/([<|>])/", $valor) == TRUE) { //preg_match verifica se há a presença do padrão especificado no valor (não sei a sintaxe direito)
            echo "Para evitar problemas de segurança, os caracteres '<' e '>' não são permitidos.";
            return "não funcionou.";
        } //Pode mudar o return para "echo" para testar mais visivelmente.
    };


    $Email = filter_var($Email, FILTER_SANITIZE_EMAIL); //Remove caracteres indesejados do email
    if (filter_var($Email, FILTER_VALIDATE_EMAIL) != TRUE) { //Verifica se o email tá na estrutura certa
        echo "<div class='erro'>O Email inserido não é válido.</div>";
        return "não funcionou.";
    }

    if (strlen((string)$Nome) > 100) {
        echo "<div class='erro'>O nome não pode ter mais de 100 caracteres.</div>";
        return "não funcionou.";
    }

    if (strlen((string)$Email) > 255) {
        echo "<div class='erro'>O email não pode ter mais de 255 caracteres.</div>";
        return "não funcionou.";
    }

    if (($SIAPE != NULL) && (strlen((string)$SIAPE) != 7)) {
        echo "<div class='erro'>O SIAPE deve ter 7 dígitos.</div>";
        return "não funcionou.";
    }

    if (strlen((string)$Senha) > 30) {
        echo "<div class='erro'>A senha não pode ter mais de 30 caracteres.</div>";
        return "não funcionou.";
    } else if (strlen((string)$Senha) < 8) {
        echo "<div class='erro'>Crie uma senha de, no mínimo, 8 caracteres.</div>";
        return "não funcionou.";
    }

    if ($SenhaConfirmada != $Senha) {
        echo "<div class='erro'>O campo de confirmar senha não bate com a senha original.</div>";
        return "não funcionou.";
    }

    if ($SIAPE != NULL && $Atividades != NULL) {
        echo "<div class='erro'>Um erro desconhecido ocorreu. Tente novamente mais tarde.</div>";
        return "não funcionou.";
    }

    // Quando trabalhando com banco de dados, parece que é bom usar instruções preparadas (para evitar problemas de segurança)
    $instrucao = $conn->prepare("SELECT EMAIL FROM USUARIO WHERE EMAIL = ?"); //Esse ponto de pergunta é só pra evitar colocar o dado direto (placeholder)
    $instrucao->bind_param("s", $Email); //s=string, i=int, d=double
    $instrucao->execute();
    $resultado = $instrucao->get_result();
    $resultado = $resultado->fetch_assoc(); //Agrupa o resultado obtido na forma de uma lista.
    if ($resultado != NULL) {
        echo "<div class='erro'>Este email já existe no sistema.</div>";
        return "não funcionou.";
    }

    $instrucao = $conn->prepare("SELECT NOME FROM USUARIO WHERE NOME = ?"); //Esse ponto de pergunta é só pra evitar colocar o dado direto (placeholder)
    $instrucao->bind_param("s", $Nome); //s=string, i=int, d=double
    $instrucao->execute();
    $resultado = $instrucao->get_result();
    $resultado = $resultado->fetch_assoc(); //Agrupa o resultado obtido na forma de uma lista.
    if ($resultado != NULL) {
        echo "<div class='erro'>Este nome já existe no sistema.</div>";
        return "não funcionou.";
    }

    $instrucao = $conn->prepare("SELECT SIAPE FROM PROFESSOR WHERE SIAPE = ?"); //Esse ponto de pergunta é só pra evitar colocar o dado direto (placeholder)
    $instrucao->bind_param("i", $SIAPE); //s=string, i=int, d=double
    $instrucao->execute();
    $resultado = $instrucao->get_result();
    $resultado = $resultado->fetch_assoc(); //Agrupa o resultado obtido na forma de uma lista.
    if ($resultado != NULL) {
        echo "<div class='erro'>Este SIAPE já existe no sistema.</div>";
        return "não funcionou.";
    }

    $Senha_Encriptada = password_hash($Senha, PASSWORD_DEFAULT);

    /* -----------------Cadastro--------------------------- */

    $instrucao = $conn->prepare("INSERT INTO USUARIO(NOME, SENHA, EMAIL, TIPO) VALUES(?,?,?,?)"); //Mesma coisa de antes, mas agora inserindo um dado ao invés de selecionando
    $instrucao->bind_param("ssss", $Nome, $Senha_Encriptada, $Email, $Tipo); //s=string, i=int, d=double
    $instrucao->execute();
    if ($conn->affected_rows != 1) {
        echo "<div class='erro'>Um erro desconhecido ocorreu. Tente novamente mais tarde.</div>";
        return "não funcionou.";
    } else {
        if ($Tipo == "Professor") {
            $instrucao = $conn->prepare("INSERT INTO PROFESSOR(IDPROF, SIAPE) VALUES(LAST_INSERT_ID(), ?)"); //Mesma coisa de antes, mas agora inserindo um dado ao invés de selecionando
            $instrucao->bind_param("i", $SIAPE); //s=string, i=int, d=double
            $instrucao->execute();
            if ($conn->affected_rows != 1) {
                echo "<div class='erro'>Um erro desconhecido ocorreu. Tente novamente mais tarde.</div>";
                return "não funcionou.";
            } else {
                return "funcionou.";
            }
        } else if ($Tipo == "Setor") {
            $instrucao = $conn->prepare("INSERT INTO SETORUSUARIO(IDSETORUSUARIO, ATIVIDADES) VALUES(LAST_INSERT_ID(), ?)"); //Mesma coisa de antes, mas agora inserindo um dado ao invés de selecionando
            $instrucao->bind_param("s", $Atividades); //s=string, i=int, d=double
            $instrucao->execute();
            if ($conn->affected_rows != 1) {
                echo "<div class='erro'>Um erro desconhecido ocorreu. Tente novamente mais tarde.</div>";
                return "não funcionou.";
            } else {
                return "funcionou.";
            }
        } else {
            echo "<div class='erro'>Um erro desconhecido ocorreu. Tente novamente mais tarde.</div>";
            return "não funcionou.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link rel="stylesheet" href="stylessignup.css">
    <title>Cadastro / SGE</title>

    <script>
        function FormHide(tipo) {
            const botaoum = document.querySelector('.botaoum');
            const botaodois = document.querySelector('.botaodois');

            botaoum.classList.remove('clicar', 'subir', 'descer');
            botaodois.classList.remove('clicar', 'subir', 'descer');

            if (tipo == "professor") {
                document.getElementById("SignUpFormSetor").style.display = "none";
                document.getElementById("SignUpFormProfessor").style.display = "block";
                botaoum.classList.add('clicar');
                botaodois.classList.add('descer');
            } else if (tipo == "setor") {
                document.getElementById("SignUpFormProfessor").style.display = "none";
                document.getElementById("SignUpFormSetor").style.display = "block";
                botaodois.classList.add('clicar');
                botaoum.classList.add('descer');
            }
        }
    </script>

</head>

<body>
    <?php if (@$resultado_final_decisivo_mega_importante == "funcionou."): ?> <!-- Fecha o PHP, pra escrever em HTML -->
        <div class="mensagem">
            <p> Parábens! Seu cadastro foi concluído. </p>
        </div>
    <?php endif; ?>
    <span>

        <?php

        if (isset($_POST['ProfessorSubmit'])) { // Caso o formulário de professor seja enviado.
            $resultado_final_decisivo_mega_importante = cadastro($_POST['NomeProfessor'], $_POST['EmailProfessor'], $_POST['SenhaProfessor'], $_POST['senhaProfessorConfirmar'], $_POST['SIAPEProfessor'], NULL, "Professor");
            // function cadastro($Nome, $Email, $Senha, $SenhaConfirmada, $SIAPE, $Atividades, $Tipo) {
        }

        if (isset($_POST['SetorUserSubmit'])) { // Caso o formulário de professor seja enviado.
            $resultado_final_decisivo_mega_importante = cadastro($_POST['nomeSetor'], $_POST['EmailSetor'], $_POST['senhaSetor'], $_POST['senhaSetorConfirmar'], NULL, $_POST['atividadeSetor'], "Setor");
        }

        ?>
    </span>


    <div>
        <div>
            <button class="botaoum clicar" type="button" onclick="FormHide('professor')">Professor</button>
            <button class="botaodois" type="button" onclick="FormHide('setor')">Setor</button>
            <div>

                <div class="container-fluid">
                    <div class="row quadrado align-items-center">

                        <div>
                            <p><b>Cadastre-se!</b></p>
                            <form id="SignUpFormProfessor" action="SignUp.php" method="post" autocomplete="off">
                                <label for="NomeProfessor">Nome Completo:</label><br>
                                <input class="campo" type="text" id="NomeProfessor" name="NomeProfessor" value="<?php echo @$_POST['NomeProfessor']; ?>" required><br> <!-- O @ só impede erros irrelevantes de aparecerem. -->

                                <label for="EmailProfessor">Email</label><br>
                                <input class="campo" type="email" id="EmailProfessor" name="EmailProfessor" value="<?php echo @$_POST['EmailProfessor']; ?>" required><br>

                                <label for="SenhaProfessor">Senha:</label><br>
                                <input class="campo" type="password" id="SenhaProfessor" name="SenhaProfessor" value="<?php echo @$_POST['SenhaProfessor']; ?>" required><br>

                                <label for="senhaProfessorConfirmar">Confirmar Senha</label><br>
                                <input class="campo" type="password" id="senhaProfessorConfirmar" name="senhaProfessorConfirmar" value="<?php echo @$_POST['senhaProfessorConfirmar']; ?>" required><br>

                                <label for="SIAPEProfessor">Matrícula SIAPE:</label><br>
                                <input class="campo" type="number" id="SIAPEProfessor" name="SIAPEProfessor" value="<?php echo @$_POST['SIAPEProfessor']; ?>" required><br>

                                <input class="botao" type="submit" name="ProfessorSubmit" value="Cadastrar"><br>
                                <div class="lc">
                                    Já possui uma conta?
                                    <a href="/SGE/Login.php">Fazer Login</a><br>
                                </div>
                            </form>
                        </div>



                        <div>
                            <form id="SignUpFormSetor" action="SignUp.php" method="post" style="text-align: left; display: none;" autocomplete="off">
                                <label for="nomeSetor">Nome Completo:</label><br>
                                <input class="campo" type="text" id="nomeSetor" name="nomeSetor" value="<?php echo @$_POST['nomeSetor']; ?>" required><br>

                                <label for="EmailSetor">Email</label><br>
                                <input class="campo" type="email" id="EmailSetor" name="EmailSetor" value="<?php echo @$_POST['EmailSetor']; ?>" required><br>

                                <label for="senhaSetor">Senha:</label><br>
                                <input class="campo" type="password" id="senhaSetor" name="senhaSetor" value="<?php echo @$_POST['senhaSetor']; ?>" required><br>

                                <label for="senhaSetorConfirmar">Confirmar Senha</label><br>
                                <input class="campo" type="password" id="senhaSetorConfirmar" name="senhaSetorConfirmar" value="<?php echo @$_POST['senhaSetorConfirmar']; ?>" required><br>

                                <label for="atividadeSetor">Atividade de Acompanhamento</label><br>
                                <input class="campo" type="text" id="atividadeSetor" name="atividadeSetor" value="<?php echo @$_POST['atividadeSetor']; ?>" required><br>

                                <input class="botao" type="submit" name="SetorUserSubmit" value="Cadastrar"><br>

                                <div class="lc">
                                    Já possui uma conta?
                                    <a href="/SGE/Login.php">Fazer Login</a><br>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>

</body>

</html>