<?php
/**
 * Template de Cabeçalho para o Painel Administrativo
 * Versão: 1.0.0
 */

// Definir título padrão se não for especificado
$pageTitle = $pageTitle ?? 'Painel Administrativo';

// Verificar ambiente (desenvolvimento ou produção)
$isDev = true; // Altere para false em produção
$version = $isDev ? time() : '1.0.0';
?>
<!DOCTYPE html>
<html lang="pt-br" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $pageTitle; ?> - Player Admin</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="../assets/img/favicon.ico" type="image/x-icon">
    
    <!-- CSS Externos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@glidejs/glide/dist/css/glide.core.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css">
    
    <!-- CSS Principal do Admin -->
    <link rel="stylesheet" href="css/admin.css?v=<?php echo $version; ?>">
    
    <!-- CSS Específico para páginas individuais -->
    <?php if (isset($pageCss)): ?>
    <?php foreach ($pageCss as $css): ?>
    <link rel="stylesheet" href="<?php echo $css; ?>?v=<?php echo $version; ?>">
    <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Scripts de cabeçalho (mínimo) -->
    <script>
        // Verificar o tema salvo
        const savedTheme = localStorage.getItem('admin_theme');
        if (savedTheme) {
            document.documentElement.setAttribute('data-theme', savedTheme);
        } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    </script>
</head>
<!-- Corpo será aberto no arquivo específico da página --> 