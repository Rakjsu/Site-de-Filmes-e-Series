<?php
// diretores.php v1.1.0 - Página de gerenciamento de diretores
$pageTitle = 'Diretores';
$breadcrumbs = ['Diretores' => null];
$pageCss = [];
$pageScripts = [];
include_once 'templates/header.php';
// Dados mockados de diretores
$diretores = [
  [
    'id' => 1,
    'nome' => 'Christopher Nolan',
    'foto' => 'https://image.tmdb.org/t/p/w185/cLH6e6z9U1urY7r1jYz1z5b2F1F.jpg',
    'principais' => 'A Origem, Interestelar, Dunkirk'
  ],
  [
    'id' => 2,
    'nome' => 'Steven Spielberg',
    'foto' => 'https://image.tmdb.org/t/p/w185/poec6RqOKY9iSiIUmfyfPfiLtvB.jpg',
    'principais' => 'Jurassic Park, E.T., Tubarão'
  ]
];
?>
<div class="admin-wrapper">
  <main class="admin-main d-flex bg-light" style="min-height:100vh;">
    <?php include 'templates/sidebar.php'; ?>
    <div class="main-content flex-grow-1 p-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Diretores</h2>
        <a href="#" class="btn btn-primary btn-lg" id="btn-add-diretor" style="min-width:200px;">
          <i class="fas fa-plus me-2"></i> <span class="fw-bold">Adicionar Diretor</span>
        </a>
      </div>
      <div class="table-responsive">
        <table class="table align-middle table-striped table-bordered">
          <thead>
            <tr>
              <th>Foto</th>
              <th>Nome</th>
              <th>Principais Filmes</th>
              <th class="text-center">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($diretores)): ?>
              <tr>
                <td colspan="4" class="text-center text-muted">
                  <i class="fas fa-info-circle me-2"></i> Nenhum diretor cadastrado.
                </td>
              </tr>
            <?php else: foreach ($diretores as $diretor): ?>
              <tr>
                <td>
                  <?php if (!empty($diretor['foto'])): ?>
                    <img src="<?php echo htmlspecialchars($diretor['foto']); ?>" alt="Foto" class="rounded" style="width:48px;height:72px;object-fit:cover;">
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($diretor['nome']); ?></td>
                <td><?php echo htmlspecialchars($diretor['principais']); ?></td>
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