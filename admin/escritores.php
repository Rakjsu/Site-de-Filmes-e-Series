<?php
// escritores.php v1.1.0 - Página de gerenciamento de escritores
$pageTitle = 'Escritores';
$breadcrumbs = ['Escritores' => null];
$pageCss = [];
$pageScripts = [];
include_once 'templates/header.php';
// Dados mockados de escritores
$escritores = [
  [
    'id' => 1,
    'nome' => 'J.K. Rowling',
    'foto' => 'https://upload.wikimedia.org/wikipedia/commons/5/5d/J._K._Rowling_2010.jpg',
    'principais' => 'Harry Potter, Morte Súbita'
  ],
  [
    'id' => 2,
    'nome' => 'George R.R. Martin',
    'foto' => 'https://upload.wikimedia.org/wikipedia/commons/1/1f/George_R._R._Martin_by_Gage_Skidmore_2.jpg',
    'principais' => 'Game of Thrones, Wild Cards'
  ]
];
?>
<div class="admin-wrapper">
  <main class="admin-main d-flex bg-light" style="min-height:100vh;">
    <?php include 'templates/sidebar.php'; ?>
    <div class="main-content flex-grow-1 p-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Escritores</h2>
        <a href="#" class="btn btn-primary btn-lg" id="btn-add-escritor" style="min-width:200px;">
          <i class="fas fa-plus me-2"></i> <span class="fw-bold">Adicionar Escritor</span>
        </a>
      </div>
      <div class="table-responsive">
        <table class="table align-middle table-striped table-bordered">
          <thead>
            <tr>
              <th>Foto</th>
              <th>Nome</th>
              <th>Principais Obras</th>
              <th class="text-center">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($escritores)): ?>
              <tr>
                <td colspan="4" class="text-center text-muted">
                  <i class="fas fa-info-circle me-2"></i> Nenhum escritor cadastrado.
                </td>
              </tr>
            <?php else: foreach ($escritores as $escritor): ?>
              <tr>
                <td>
                  <?php if (!empty($escritor['foto'])): ?>
                    <img src="<?php echo htmlspecialchars($escritor['foto']); ?>" alt="Foto" class="rounded" style="width:48px;height:72px;object-fit:cover;">
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($escritor['nome']); ?></td>
                <td><?php echo htmlspecialchars($escritor['principais']); ?></td>
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