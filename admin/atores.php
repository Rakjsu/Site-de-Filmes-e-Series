<?php
// atores.php v1.1.0 - Página de gerenciamento de atores
$pageTitle = 'Atores';
$breadcrumbs = ['Atores' => null];
$pageCss = [];
$pageScripts = [];
include_once 'templates/header.php';
// Dados mockados de atores
$atores = [
  [
    'id' => 1,
    'nome' => 'Robert Downey Jr.',
    'foto' => 'https://image.tmdb.org/t/p/w185/1YjdSym1jTG7xjHSI0yGGWEsw5i.jpg',
    'nascimento' => '1965-04-04',
    'nacionalidade' => 'EUA'
  ],
  [
    'id' => 2,
    'nome' => 'Scarlett Johansson',
    'foto' => 'https://image.tmdb.org/t/p/w185/6NsMbJXRlDZuDzatN2akFdGuTvx.jpg',
    'nascimento' => '1984-11-22',
    'nacionalidade' => 'EUA'
  ]
];
?>
<div class="admin-wrapper">
  <main class="admin-main d-flex bg-light" style="min-height:100vh;">
    <?php include 'templates/sidebar.php'; ?>
    <div class="main-content flex-grow-1 p-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Atores</h2>
        <a href="#" class="btn btn-primary btn-lg" id="btn-add-ator" style="min-width:200px;">
          <i class="fas fa-plus me-2"></i> <span class="fw-bold">Adicionar Ator</span>
        </a>
      </div>
      <div class="table-responsive">
        <table class="table align-middle table-striped table-bordered">
          <thead>
            <tr>
              <th>Foto</th>
              <th>Nome</th>
              <th>Nascimento</th>
              <th>Nacionalidade</th>
              <th class="text-center">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($atores)): ?>
              <tr>
                <td colspan="5" class="text-center text-muted">
                  <i class="fas fa-info-circle me-2"></i> Nenhum ator cadastrado.
                </td>
              </tr>
            <?php else: foreach ($atores as $ator): ?>
              <tr>
                <td>
                  <?php if (!empty($ator['foto'])): ?>
                    <img src="<?php echo htmlspecialchars($ator['foto']); ?>" alt="Foto" class="rounded" style="width:48px;height:72px;object-fit:cover;">
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($ator['nome']); ?></td>
                <td><?php echo date('d/m/Y', strtotime($ator['nascimento'])); ?></td>
                <td><?php echo htmlspecialchars($ator['nacionalidade']); ?></td>
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