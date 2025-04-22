    <!-- Rodapé do Painel -->
    <footer class="admin-footer">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; <?php echo date('Y'); ?> Player Admin - Todos os direitos reservados</p>
                </div>
                <div class="col-md-6 text-end">
                    <p>Versão 1.0.0</p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Preloader -->
    <div id="preloader">
        <div class="spinner"></div>
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@glidejs/glide"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>
    
    <!-- Scripts principais do Admin -->
    <script src="js/admin.js?v=<?php echo $version ?? time(); ?>"></script>
    
    <!-- Scripts específicos para páginas individuais -->
    <?php if (isset($pageScripts)): ?>
    <?php foreach ($pageScripts as $script): ?>
    <script src="<?php echo $script; ?>?v=<?php echo $version ?? time(); ?>"></script>
    <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Script inline específico para a página -->
    <?php if (isset($inlineScript)): ?>
    <script>
    <?php echo $inlineScript; ?>
    </script>
    <?php endif; ?>
    
</body>
</html> 