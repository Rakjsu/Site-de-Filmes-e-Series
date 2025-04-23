<?php
// filmes-adicionar.php v1.3.0 - Refatorado: JS externo, chips, busca ID/título, spinner, validação de imagens, trailer principal
$pageTitle = 'Adicionar Filme';
$breadcrumbs = ['Filmes' => 'filmes.php', 'Adicionar Filme' => null];
$pageCss = [];
$pageScripts = [];
include_once 'templates/header.php';
?>
<div class="admin-wrapper">
  <main class="admin-main d-flex bg-light" style="min-height:100vh;">
    <?php include 'templates/sidebar.php'; ?>
    <div class="main-content flex-grow-1 p-4">
      <h2 class="fw-bold mb-4">Adicionar Filme</h2>
      <!-- Box de importação TMDb -->
      <div class="card p-4 mb-4 shadow-sm">
        <form class="row g-3 align-items-center" id="import-tmdb-form" autocomplete="off" onsubmit="return false;">
          <div class="col-auto">
            <label for="busca-tipo" class="col-form-label fw-bold">Buscar por:</label>
          </div>
          <div class="col-auto">
            <select id="busca-tipo" class="form-select">
              <option value="id">ID TMDb</option>
              <option value="titulo">Título</option>
            </select>
          </div>
          <div class="col-auto" id="busca-id-box">
            <input type="number" min="1" class="form-control" id="tmdb_id" name="tmdb_id" placeholder="Ex: 603" style="width:140px;">
          </div>
          <div class="col-auto d-none" id="busca-titulo-box">
            <input type="text" class="form-control" id="tmdb_titulo" name="tmdb_titulo" placeholder="Digite o título..." style="width:220px;">
            <div id="autocomplete-list" class="autocomplete-list"></div>
          </div>
          <div class="col-auto">
            <button type="button" class="btn btn-primary" id="btn-buscar-tmdb">
              <i class="fas fa-search me-2"></i>Buscar
            </button>
          </div>
          <div class="col-auto" id="import-status"></div>
        </form>
      </div>
      <!-- Formulário de cadastro/edição -->
      <form id="form-filme" class="row g-4">
        <div class="col-12 col-lg-8">
          <div class="card p-4 mb-4">
            <div class="row g-3">
              <div class="col-12 col-md-8">
                <label class="form-label fw-bold">Título</label>
                <input type="text" class="form-control" id="titulo" name="titulo" required>
              </div>
              <div class="col-12 col-md-4">
                <label class="form-label fw-bold">Slug (URL)</label>
                <input type="text" class="form-control" id="slug" name="slug" placeholder="exemplo-filme">
              </div>
              <div class="col-12">
                <label class="form-label fw-bold">Sinopse</label>
                <textarea class="form-control" id="sinopse" name="sinopse" rows="3"></textarea>
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label fw-bold">Atores</label>
                <input type="text" class="form-control" id="atores" name="atores" placeholder="Separe por vírgula">
                <div id="atores-chips" class="chips-container mt-1"></div>
              </div>
              <div class="col-12 col-md-3">
                <label class="form-label fw-bold">Diretor(es)</label>
                <input type="text" class="form-control" id="diretor" name="diretor" placeholder="Separe por vírgula">
                <div id="diretor-chips" class="chips-container mt-1"></div>
              </div>
              <div class="col-12 col-md-3">
                <label class="form-label fw-bold">Escritor(es)</label>
                <input type="text" class="form-control" id="escritor" name="escritor" placeholder="Separe por vírgula">
                <div id="escritor-chips" class="chips-container mt-1"></div>
              </div>
              <div class="col-12 col-md-4">
                <label class="form-label fw-bold">Nota</label>
                <input type="number" step="0.1" min="0" max="10" class="form-control" id="nota" name="nota">
              </div>
              <div class="col-12 col-md-4">
                <label class="form-label fw-bold">Lançamento</label>
                <input type="date" class="form-control" id="lancamento" name="lancamento">
              </div>
              <div class="col-12 col-md-4">
                <label class="form-label fw-bold">Gênero</label>
                <input type="text" class="form-control" id="genero" name="genero" placeholder="Separe por vírgula">
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label fw-bold">Tempo de Execução (min)</label>
                <input type="number" min="1" class="form-control" id="duracao" name="duracao">
              </div>
              <div class="col-12 col-md-6 d-flex align-items-center justify-content-end">
                <label class="form-label fw-bold mb-0 me-3">Status:</label>
                <div class="form-switch-red">
                  <input class="form-check-input" type="checkbox" id="publicado" name="publicado" checked>
                  <label class="form-check-label ms-2" for="publicado" id="label-publicado">Publicado</label>
                </div>
              </div>
              <div class="col-12">
                <label class="form-label fw-bold">Trailer (YouTube)</label>
                <input type="url" class="form-control" id="trailer" name="trailer" placeholder="https://youtube.com/watch?v=...">
              </div>
            </div>
          </div>
        </div>
        <div class="col-12 col-lg-4">
          <div class="card p-4 mb-4">
            <div class="mb-3">
              <label class="form-label fw-bold">Poster</label>
              <input type="url" class="form-control mb-2" id="poster" name="poster" placeholder="URL do poster">
              <img id="poster-preview" src="/Player/admin/assets/img/placeholder.png" alt="Poster" class="img-fluid rounded shadow-sm" style="max-height:220px;">
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Thumb</label>
              <input type="url" class="form-control mb-2" id="thumb" name="thumb" placeholder="URL da thumb">
              <img id="thumb-preview" src="/Player/admin/assets/img/placeholder.png" alt="Thumb" class="img-fluid rounded shadow-sm" style="max-height:120px;">
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Tags</label>
              <input type="text" class="form-control" id="tags" name="tags" placeholder="Separe por vírgula">
            </div>
          </div>
        </div>
        <div class="col-12 text-end">
          <button type="submit" class="btn btn-success btn-lg px-5">
            <i class="fas fa-save me-2"></i>Salvar Filme
          </button>
        </div>
      </form>
      <style>
      .form-switch-red .form-check-input {
        width: 3em;
        height: 1.5em;
        background-color: #f8d7da;
        border: 2px solid #ef4444;
        transition: background 0.3s, border 0.3s;
        box-shadow: none;
      }
      .form-switch-red .form-check-input:checked {
        background-color: #ef4444;
        border-color: #ef4444;
      }
      .form-switch-red .form-check-input:focus {
        box-shadow: 0 0 0 0.2rem rgba(239,68,68,.25);
      }
      .form-switch-red .form-check-input:checked + .form-check-label {
        color: #ef4444;
        font-weight: bold;
      }
      .chips-container { min-height: 32px; }
      .chip {
        display: inline-block;
        background: #ef4444;
        color: #fff;
        border-radius: 16px;
        padding: 0 10px;
        margin: 2px 4px 2px 0;
        font-size: 0.95em;
        line-height: 28px;
        position: relative;
      }
      .chip-close {
        cursor: pointer;
        margin-left: 8px;
        font-weight: bold;
      }
      .autocomplete-list {
        position: absolute;
        z-index: 10;
        background: #fff;
        border: 1px solid #ddd;
        width: 220px;
        max-height: 200px;
        overflow-y: auto;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      }
      .autocomplete-item {
        padding: 6px 12px;
        cursor: pointer;
      }
      .autocomplete-item:hover {
        background: #f8d7da;
      }
      </style>
      <script src="/Player/admin/js/filmes-adicionar.js?v=1.0.1"></script>
      <script>
      // Alternar busca por ID/título
      document.addEventListener('DOMContentLoaded', function() {
        const tipo = document.getElementById('busca-tipo');
        const boxId = document.getElementById('busca-id-box');
        const boxTitulo = document.getElementById('busca-titulo-box');
        tipo.addEventListener('change', function() {
          if (this.value === 'id') {
            boxId.classList.remove('d-none');
            boxTitulo.classList.add('d-none');
          } else {
            boxId.classList.add('d-none');
            boxTitulo.classList.remove('d-none');
          }
        });
      });
      </script>
    </div>
  </main>
</div>
<?php include_once 'templates/footer.php'; ?> 