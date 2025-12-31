<?php
require_once 'passwordService.php';
require_once 'env.php';

session_start();

/**
 * -----------------------------------------
 * Inicialização de Sessão e Segurança
 * -----------------------------------------
 */

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

$csrf = $_SESSION['csrf'];
$service = new PasswordService();
$passwordCorrect = false;

// Inicializa tentativas e janela de bloqueio
$_SESSION['login_attempts']      = $_SESSION['login_attempts']      ?? 0;
$_SESSION['blocked_until']       = $_SESSION['blocked_until']       ?? 0;


/**
 * -----------------------------------------
 * Verifica bloqueio por excesso de tentativas
 * -----------------------------------------
 */

if (time() < $_SESSION['blocked_until']) {

    $remaining = max(1, $_SESSION['blocked_until'] - time());

    echo "<script>
        alert('Muitas tentativas! Você deve aguardar {$remaining} segundos.');
        </script>";

    // Não processa o POST enquanto estiver bloqueado
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /**
     * -----------------------------------------
     * Validação do CSRF
     * -----------------------------------------
     */
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        echo "<script>alert('CSRF inválido! A página será recarregada.'); window.location.reload();</script>";
        exit;
    }

    $password = trim($_POST['password'] ?? '');

    /**
     * -----------------------------------------
     * Validação da Senha
     * -----------------------------------------
     */
    if ($service->validate($password)) {

        // Senha correta: limpa tentativas e gera novo CSRF
        $_SESSION['login_attempts'] = 0;
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
        $passwordCorrect = true;

    } else {

        // Senha incorreta → incrementa tentativas
        $_SESSION['login_attempts']++;

        if ($_SESSION['login_attempts'] >= 5) {
            $_SESSION['blocked_until'] = time() + 120; // 2 minutos
            echo "<script>alert('Você errou a senha 5 vezes. Acesso bloqueado por 2 minutos.');</script>";
        } else {
            $remaining = 5 - $_SESSION['login_attempts'];
            echo "<script>alert('Senha incorreta! Tentativas restantes: {$remaining}');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <title>Acesso Remoto Seguro</title>

        <style>
            body { font-family: sans-serif; display:flex; justify-content:center; align-items:center; min-height:100vh; background:#f6f7f9; margin:0; }
            .box { background:#fff; padding:28px; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.08); width:340px; text-align:center; }
            img.logo { max-width:300px; margin-bottom:20px; }
            input, button { width:100%; padding:12px; box-sizing: border-box; margin-top:8px; font-size:16px; border-radius:6px; border:1px solid #ccc; }
            button { background:#e32025; color:#fff; border:none; cursor:pointer; }
            button:hover { opacity:.9; }
            #msg { display:none; padding:20px; background:#e7ffe7; border:1px solid #6bc16b; border-radius:10px; max-width:350px; }
        </style>
    </head>
    <body>
        <div class="box">
            <img src="/assets/logo.png" class="logo" alt="Logo">
            <h2>Acesso Remoto</h2>

            <?php if (!$passwordCorrect): ?>

            <form action="" method="POST">
                <input type="password" name="password" placeholder="Digite a senha" required autofocus>
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                <button type="submit">Baixar</button>
            </form>

            <?php else: ?>

            <div id="msg">
                <h2>Download iniciado!</h2>
                <p><strong style="color:#e32025">⚠ Não altere o nome do executável.</strong></p>
                <p>Este arquivo é necessário para o suporte remoto.</p>
            </div>

            <script>
                document.getElementById("msg").style.display = "block";
                window.location.href = "force-download.php";
            </script>

            <?php endif; ?>
        </div>
    </body>
</html>
