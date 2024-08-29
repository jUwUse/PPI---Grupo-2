<?php
error_reporting(E_ALL ^ E_DEPRECATED);

function login($Email, $Senha)
{

    echo "chegou aqui";
    include('Config.php');

    if ($Email == "" || $Senha == "") {
        echo "<div class='erro'>Um erro desconhecido ocorreu. Tente novamente.</div>";
        return "não funcionou.";
    }

    $Email = filter_var($Email, FILTER_SANITIZE_EMAIL);

    $instrucao = $conn->prepare("SELECT EMAIL, SENHA FROM USUARIO WHERE EMAIL = ?"); //Esse ponto de pergunta é só pra evitar colocar o dado direto (placeholder)
    $instrucao->bind_param("s", $Email); //s=string, i=int, d=double
    $instrucao->execute();
    $resultado = $instrucao->get_result();
    $resultadoverificacao = $resultado->fetch_assoc(); //Agrupa o resultado obtido na forma de uma lista.
    if ($resultadoverificacao == NULL) {
        echo "<div class='erro'>Email ou senha incorretos.</div>";
        return "não funcionou.";
    }

    $instrucao = $conn->prepare("SELECT NOME, TIPO FROM USUARIO WHERE EMAIL = ?"); //Esse ponto de pergunta é só pra evitar colocar o dado direto (placeholder)
    $instrucao->bind_param("s", $Email); //s=string, i=int, d=double
    $instrucao->execute();
    $resultado = $instrucao->get_result();
    $InformacoesExtras = $resultado->fetch_assoc();

    if (password_verify($Senha, $resultadoverificacao['SENHA']) == False) { // Isso vai fazer a verificação descriptografando o hash da senha.
        echo "<div class='erro'>Senha incorreta.</div>";
        return "não funcionou.";
    } else { //Caso tenha tudo funcionado.
        $_SESSION["Email"] = $Email; //Armazena o email do usuário
        $_SESSION["Nome"] = $InformacoesExtras["NOME"];
        $_SESSION["Tipo"] = $InformacoesExtras["TIPO"];
        header("location: https://www.youtube.com/watch?v=OYSrbdKlcsE");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="styleslogin.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <title>Entrar / SGE</title>
</head>

<body>
    <div class="container-fluid">
        <div class="row quadrado align-items-center">
            <div class="col-sm-5 text-center">
                <img class="logo" src="images/logo.png" alt="Logo">
            </div>
            <div class="col-sm-1 d-flex justify-content-center">
                <div class="vertical-divider"></div>
            </div>
            <div class="col-sm-6">
                <form action="Home.php" method="post" autocomplete="off">
                    <label for="EmailLogin">Email:</label><br>
                    <i class="uil uil-exclamation-circle"></i>
                    <input class="campo" type="email" id="EmailLogin" name="EmailLogin" value="<?php echo @$_POST['EmailLogin']; ?>" required><br>

                    <label for="UserPasswordLogin">Senha:</label><br>
                    <i class="uil uil-exclamation-circle"></i>
                    <input class="campo" type="password" id="UserPasswordLogin" name="UserPasswordLogin" value="<?php echo @$_POST['UserPasswordLogin']; ?>" required><br>
                    
                    <input class="botao" type="submit" name="LoginSubmit" value="Avançar"><br>

                    Não tem uma conta?
                    <a href="/SGE/SignUp.php">Cadastre-se</a><br>

                    Perdeu sua senha?
                    <a href="/SGE/RecoverPassword.php">Recupere-a</a><br>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>