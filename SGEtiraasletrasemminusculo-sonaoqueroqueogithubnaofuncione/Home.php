<?php
error_reporting(E_ALL ^ E_DEPRECATED); // Criei uma página separada para o logout porque ele meio que pode ser feito de várias páginas, eu acho. Daí é melhor chamar a função por fora do que definir ela sempre.
include('Config.php');
include('Logout_Function.php');

function Alterar($Email, $Senha, $SenhaConfirmada, $SenhaOriginal, $Nome, $conexao)
{
    if ($Email == NULL && $Senha == NULL) {
        echo "<div class='erro'>Insira as informações que deseja alterar.</div>";
        return "não funcionou.";
    } elseif ($SenhaOriginal == NULL) {
        echo "<div class='erro'>Insira sua senha original para autorizar a alteração.</div>";
        return "não funcionou.";
    } elseif (password_verify($SenhaOriginal, $_SESSION["Senha"]) == FALSE) { // Caso perca o banco de dados, mude isso para TRUE na hora de alterar a senha do ADM. Seguindo esse passo e o de login, um novo usuário ADM primordial terá sido criado.
        echo "<div class='erro'>Sua senha original não está correta.</div>";
        return "não funcionou.";
    } else {
        if ($Email != NULL) {
            $Email = filter_var($Email, FILTER_SANITIZE_EMAIL); //Remove caracteres indesejados do email
            if (filter_var($Email, FILTER_VALIDATE_EMAIL) != TRUE) { //Verifica se o email tá na estrutura certa
                echo "<div class='erro'>O Email inserido não é válido.</div>";
                return "não funcionou.";
            }

            if (strlen((string)$Email) > 255) {
                echo "<div class='erro'>O email não pode ter mais de 255 caracteres.</div>";
                return "não funcionou.";
            }

            $instrucao = $conexao->prepare("UPDATE USUARIO SET EMAIL = ? WHERE NOME = ?"); //Esse ponto de pergunta é só pra evitar colocar o dado direto (placeholder)
            $instrucao->bind_param("ss", $Email, $Nome); //s=string, i=int, d=double
            $instrucao->execute();
            if ($conexao->affected_rows != 1) {
                echo "<div class='erro'>Um erro desconhecido ocorreu. Tente novamente mais tarde.</div>";
                return "não funcionou.";
            } else {
                $_SESSION["Email"] = $Email;
            }
        }

        if ($Senha != NULL) {
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

            $Senha_Encriptada = password_hash($Senha, PASSWORD_DEFAULT);

            $instrucao = $conexao->prepare("UPDATE USUARIO SET SENHA = ? WHERE NOME = ?"); //Esse ponto de pergunta é só pra evitar colocar o dado direto (placeholder)
            $instrucao->bind_param("ss", $Senha_Encriptada, $Nome); //s=string, i=int, d=double
            $instrucao->execute();
            if ($conexao->affected_rows != 1) {
                echo "<div class='erro'>Um erro desconhecido ocorreu. Tente novamente mais tarde.</div>";
                return "não funcionou.";
            } else {
                $instrucao = $conexao->prepare("SELECT SENHA FROM USUARIO WHERE NOME"); //Esse ponto de pergunta é só pra evitar colocar o dado direto (placeholder)
                $instrucao->bind_param("s", $Nome); //s=string, i=int, d=double
                $instrucao->execute();
                $resultado = $instrucao->get_result();
                $resultadoverificacao = $resultado->fetch_assoc();

                $_SESSION["Senha"] = $resultadoverificacao["SENHA"];
            }
        }

        header('Location: Home.php');
        return "funcionou";
    }
}

 function printar($Email, $Senha, $SenhaConfirmada, $SenhaOriginal) { //Essa função somente testa se os valores estão sendo enviados.
        $elementos = func_get_args(); 
        if ($Senha != NULL) { //Isso foi feito pra verificar se quando você deixa um campo vazio ele é enviado como NULL (sim)
            print_r($elementos);
        }
    } 

 /*
 if (isset($_SESSION['Email']) == FALSE) { // Caso nenhuma sessão tenha iniciado (ou seja, caso o usuário tente entrar sem fazer login)
    header("location: Login.php");
    exit();
} */


if (isset($_GET['Logout'])) {
    logout();
}

if (isset($_POST['AlteracaoSubmit'])) {
    echo "<script type='text/javascript'>
            ',
            'Hide('
            FormularioDeAlteracao ');',
                '
        </script>";

    $resultado_final_decisivo_mega_importante = Alterar($_POST['EmailAlteracao'], $_POST['SenhaAlteracao'], $_POST['SenhaNovaConfirmar'], $_POST['AlterarConfirmarSenha'], $_SESSION["Nome"], $conn);
}

if ($_SESSION['Tipo'] == 'ADM') {
    echo '<a href="/SGE/GerenciarUsuários.php" id="CriarUser" style="display: block;"> Gerenciar Usuários</a><br>';
    echo '<a href="/SGE/GerenciarCursos.php" id="CriarUser" style="display: block;"> Gerenciar Cursos</a><br>';
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="styleshome.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <title>Página inicial / SGE</title>

    <script>
        function Hide(ID) {
            if (ID == "DadosDoUsuario") {
                document.getElementById(ID).style.display = 'none';
                document.getElementById("FormularioDeAlteracao").style.display = 'block';
            }

            if (ID == "FormularioDeAlteracao") {
                document.getElementById("DadosDoUsuario").style.display = 'block';
                document.getElementById(ID).style.display = 'none';
            }
        }
    </script>
</head>

<body>
    <div class="container-fluid">
        <div class="slidebar">
            <img class="logoiff" src="images/logoiff.png" alt="Logoiff">
            <hr>
            <div class="conta">
                <a href="youtube.com" class="menu-item" style="display:flex; align-items:center;">
                    <i class="uil uil-user-circle"></i>
                    <span style="margin-left: 8px;">Minha conta</span>
                </a><br>
            </div>
            <div class="sair">
                <a href="youtube.com" class="menu-item" style="display:flex; align-items:center;">
                    <i class="uil uil-clipboard-notes"></i>
                    <span style="margin-left: 8px;">Tabelas</span>
                </a><br>
            </div>
            <div class="sair">
                <a href="youtube.com" class="menu-item" style="display:flex; align-items:center;">
                    <i class="uil uil-folder-open"></i>
                    <span style="margin-left: 8px;">Arquivos</span>
                </a><br>
            </div>
            <div class="baixo">
                <div class="sair">
                    <a href="?Logout" style="display:flex; align-items:center;">
                        <i class="uil uil-search"></i>
                        <span style="margin-left: 8px;">Sair</span>
                    </a><br>
                </div>
                <div class="relatprob">
                    <a href="/SGE/Reclamacao.php" style="display:flex; align-items:center;">
                        <i class="uil uil-exclamation-triangle"></i>
                        <span style="margin-left: 8px;">Relatar problema</span>
                    </a><br>
                </div>
            </div>
            <!-- Barra que segue o mouse -->
            <div class="highlight-bar"></div>
        </div>

        <div class="row quadrado align-items-center">
            <span id="DadosDoUsuario" style="display: block;">
                <p> Seu nome: <?php echo $_SESSION['Nome'] ?></p>
                <p> Seu tipo: <?php echo $_SESSION['Tipo'] ?></p>
                <p> Seu email: <?php echo $_SESSION['Email'] ?></p>

                <button class="botao" type="button" onclick="Hide('DadosDoUsuario')">Atualizar Dados</button><br><br>
            </span>

            <form action="Home.php" id="FormularioDeAlteracao" method="post" style="display: none;" autocomplete="off">
                <p> <?php echo $_SESSION['Nome'] ?></p>
                <p> <?php echo $_SESSION['Tipo'] ?></p>
                <p> <?php echo $_SESSION['Email'] ?></p>

                <label for="EmailAlteracao">Email:</label><br>
                <input class="campo" type="email" id="EmailAlteracao" name="EmailAlteracao" value="<?php echo @$_POST['EmailAlteracao']; ?>"><br>
                <label for="SenhaAlteracao">Senha Nova:</label><br>
                <input class="campo" type="password" id="SenhaAlteracao" name="SenhaAlteracao" value="<?php echo @$_POST['SenhaAlteracao']; ?>"><br>

                <label for="SenhaNovaConfirmar">Confirme nova senha:</label><br>
                <input class="campo" type="password" id="SenhaNovaConfirmar" name="SenhaNovaConfirmar" value="<?php echo @$_POST['SenhaNovaConfirmar']; ?>"><br>

                <label for="AlterarConfirmarSenha">Insira sua senha atual:</label><br>
                <input class="campo" type="password" id="AlterarConfirmarSenha" name="AlterarConfirmarSenha" value="<?php echo @$_POST['AlterarConfirmarSenha']; ?>"><br>

                <input class="botaoum" type="submit" name="AlteracaoSubmit" value="Salvar Alterações"><br>
                <input class="botaodois" type="button" onclick="Hide('FormularioDeAlteracao')" name="AlteracaoCancel" value="Cancelar"><br>
            </form>
        </div>
    </div>

    <!-- Adicione o script JavaScript aqui -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const updateButton = document.querySelector('button[onclick="Hide(\'DadosDoUsuario\')"]');
            const cancelButton = document.querySelector('input[name="AlteracaoCancel"]');
            const saveButton = document.querySelector('input[name="AlteracaoSubmit"]');
            const quadrado = document.querySelector('.quadrado');

            updateButton.addEventListener('click', () => {
                quadrado.classList.add('expanded');
            });

            cancelButton.addEventListener('click', () => {
                quadrado.classList.remove('expanded');
            });

            saveButton.addEventListener('click', () => {
                quadrado.classList.remove('expanded');
            });
        });
    </script>
</body>

</html>