<?php
// filmes.php v1.0.0 - Listagem e gerenciamento de filmes no painel admin
$pageTitle = 'Filmes';
$breadcrumbs = ['Filmes' => null];
$pageCss = [];
$pageScripts = [];
include_once 'templates/header.php';
require_once '../db_config.php';

// Buscar filmes do banco
$filmes = [];
$erro = '';
try {
    $stmt = getConnection()->query('SELECT * FROM filmes ORDER BY criado_em DESC');
    $filmes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $erro = 'Erro ao buscar filmes: ' . $e->getMessage();
}
?>
<div class="admin-wrapper">
  <main class="admin-main d-flex bg-light" style="min-height:100vh;">
    <?php include 'templates/sidebar.php'; ?>
    <div class="main-content flex-grow-1 p-4">
      <!-- Cards de resumo -->
      <div class="row g-4 mb-4">
        <div class="col-12 col-md-4">
          <div class="card shadow-sm border-0 text-center py-4">
            <div class="fs-2 fw-bold text-primary mb-2">
              <i class="fas fa-film me-2"></i><?php echo count($filmes); ?>
            </div>
            <div class="text-muted">Total de Filmes</div>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <div class="card shadow-sm border-0 text-center py-4">
            <div class="fs-2 fw-bold text-success mb-2">
              <i class="fas fa-calendar-plus me-2"></i>
              <?php echo !empty($filmes) ? date('d/m/Y', strtotime($filmes[0]['criado_em'])) : '--'; ?>
            </div>
            <div class="text-muted">Último Adicionado</div>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <div class="card shadow-sm border-0 text-center py-4">
            <div class="fs-2 fw-bold text-warning mb-2">
              <i class="fas fa-star me-2"></i>
              <?php echo !empty($filmes) ? number_format(max(array_column($filmes, 'nota')), 1) : '--'; ?>
            </div>
            <div class="text-muted">Maior Nota</div>
          </div>
        </div>
      </div>
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Filmes</h2>
        <a href="filmes-adicionar.php" class="btn btn-primary btn-lg" id="btn-add-filme" style="min-width:200px;">
          <i class="fas fa-plus me-2"></i> <span class="fw-bold">Adicionar Filme</span>
        </a>
      </div>
      <?php if (!empty($erro)): ?>
        <div class="alert alert-danger mb-4"><?php echo $erro; ?></div>
      <?php endif; ?>
      <div class="table-responsive">
        <table class="table align-middle table-striped table-bordered">
          <thead>
            <tr>
              <th>Poster</th>
              <th>Título</th>
              <th>Ano</th>
              <th>Duração</th>
              <th>Nota</th>
              <th class="text-center">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($filmes)): ?>
              <tr>
                <td colspan="6" class="text-center text-muted">
                  <i class="fas fa-info-circle me-2"></i> Nenhum filme cadastrado.
                </td>
              </tr>
            <?php else: foreach ($filmes as $filme): ?>
              <tr>
                <td>
                  <?php if (!empty($filme['poster_url'])): ?>
                    <img src="<?php echo htmlspecialchars($filme['poster_url']); ?>" alt="Poster" class="rounded" style="width:48px;height:72px;object-fit:cover;">
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($filme['titulo']); ?></td>
                <td><?php echo $filme['ano']; ?></td>
                <td><?php echo $filme['duracao']; ?> min</td>
                <td><?php echo $filme['nota']; ?> (<?php echo $filme['fonte_nota']; ?>)</td>
                <td class="text-center">
                  <button class="btn btn-outline-warning btn-sm me-2" title="Editar"><i class="fas fa-edit"></i></button>
                  <button class="btn btn-outline-danger btn-sm" title="Excluir"><i class="fas fa-trash"></i></button>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>
<?php include_once 'templates/footer.php'; ?> 