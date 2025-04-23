// filmes-adicionar.js v1.0.1 - LOGS DE DEPURAÇÃO
// Integração TMDb, chips múltiplos, busca ID/título, spinner, validação de imagens, trailer principal

const TMDB_API_KEY = '9d8ec8b10e9b4acd85853c44b29bd83a';
const TMDB_BASE = 'https://api.themoviedb.org/3/movie/';
const TMDB_SEARCH = 'https://api.themoviedb.org/3/search/movie';
const TMDB_IMG = 'https://image.tmdb.org/t/p/w500';
const TMDB_BACKDROP = 'https://image.tmdb.org/t/p/w780';
const IMG_PLACEHOLDER = '/Player/admin/assets/img/placeholder.png'; // Ajuste o caminho conforme necessário

console.log('[filmes-adicionar.js] Script carregado');

// Utilidades
function slugify(text) {
  return text.toString().toLowerCase()
    .replace(/\s+/g, '-')
    .replace(/[^\w\-]+/g, '')
    .replace(/\-\-+/g, '-')
    .replace(/^-+/, '')
    .replace(/-+$/, '');
}

// Spinner
function showSpinner(el) {
  el.innerHTML = '<span class="spinner-border text-danger spinner-border-sm me-2" role="status"></span>Buscando...';
}
function hideSpinner(el, msg, success = true) {
  el.innerHTML = `<span class="text-${success ? 'success' : 'danger'}">${msg}</span>`;
}

// Chips para múltiplos nomes
function setupChips(inputId, containerId) {
  const input = document.getElementById(inputId);
  const container = document.getElementById(containerId);
  function renderChips() {
    container.innerHTML = '';
    const values = input.value.split(',').map(v => v.trim()).filter(Boolean);
    values.forEach(val => {
      const chip = document.createElement('span');
      chip.className = 'chip';
      chip.textContent = val;
      const close = document.createElement('span');
      close.className = 'chip-close';
      close.innerHTML = '&times;';
      close.onclick = () => {
        const arr = input.value.split(',').map(v => v.trim()).filter(Boolean);
        input.value = arr.filter(a => a !== val).join(', ');
        renderChips();
      };
      chip.appendChild(close);
      container.appendChild(chip);
    });
  }
  input.addEventListener('blur', renderChips);
  input.addEventListener('input', renderChips);
  renderChips();
}

// Validação de imagem
function validarImagem(url, previewId) {
  const img = document.getElementById(previewId);
  if (!url) {
    img.src = IMG_PLACEHOLDER;
    img.classList.remove('d-none');
    return;
  }
  const testImg = new window.Image();
  testImg.onload = () => {
    img.src = url;
    img.classList.remove('d-none');
  };
  testImg.onerror = () => {
    img.src = IMG_PLACEHOLDER;
    img.classList.remove('d-none');
  };
  testImg.src = url;
}

// Busca TMDb por ID ou título
async function buscarTMDb(tipo, valor, statusEl, callback) {
  showSpinner(statusEl);
  let url = '';
  if (tipo === 'id') {
    url = `${TMDB_BASE}${valor}?api_key=${TMDB_API_KEY}&language=pt-BR&append_to_response=credits,videos`;
  } else {
    url = `${TMDB_SEARCH}?api_key=${TMDB_API_KEY}&language=pt-BR&query=${encodeURIComponent(valor)}`;
  }
  console.log('[filmes-adicionar.js] Fetch URL:', url);
  try {
    const res = await fetch(url);
    console.log('[filmes-adicionar.js] Fetch status:', res.status);
    if (!res.ok) throw new Error('Filme não encontrado');
    const data = await res.json();
    console.log('[filmes-adicionar.js] Dados recebidos:', data);
    if (tipo === 'titulo') {
      if (!data.results || !data.results.length) throw new Error('Nenhum filme encontrado');
      callback(data.results);
      hideSpinner(statusEl, 'Selecione o filme na lista.', true);
    } else {
      callback(data);
      hideSpinner(statusEl, 'Filme importado com sucesso!', true);
    }
  } catch (e) {
    console.error('[filmes-adicionar.js] Erro no fetch:', e);
    hideSpinner(statusEl, 'Erro: ' + e.message, false);
  }
}

// Preencher campos do formulário com dados do TMDb
function preencherCamposTMDb(data) {
  document.getElementById('titulo').value = data.title || '';
  document.getElementById('slug').value = slugify(data.title || '');
  document.getElementById('sinopse').value = data.overview || '';
  document.getElementById('nota').value = data.vote_average || '';
  document.getElementById('lancamento').value = data.release_date || '';
  document.getElementById('genero').value = (data.genres || []).map(g => g.name).join(', ');
  document.getElementById('duracao').value = data.runtime || '';
  document.getElementById('poster').value = data.poster_path ? TMDB_IMG + data.poster_path : '';
  document.getElementById('thumb').value = data.backdrop_path ? TMDB_BACKDROP + data.backdrop_path : '';
  // Trailer principal do YouTube
  let trailer = '';
  if (data.videos && data.videos.results) {
    const yt = data.videos.results.find(v => v.site === 'YouTube' && v.type === 'Trailer');
    if (yt) trailer = 'https://youtube.com/watch?v=' + yt.key;
  }
  document.getElementById('trailer').value = trailer;
  // Elenco, diretor, escritor (chips)
  let atores = [], diretores = [], escritores = [];
  if (data.credits && data.credits.cast) {
    atores = data.credits.cast.slice(0, 5).map(a => a.name);
  }
  if (data.credits && data.credits.crew) {
    diretores = data.credits.crew.filter(p => p.job === 'Director').map(p => p.name);
    escritores = data.credits.crew.filter(p => p.job === 'Writer' || p.job === 'Screenplay').map(p => p.name);
  }
  document.getElementById('atores').value = atores.join(', ');
  document.getElementById('diretor').value = diretores.join(', ');
  document.getElementById('escritor').value = escritores.join(', ');
  // Atualizar chips
  if (window.renderChipsDiretor) window.renderChipsDiretor();
  if (window.renderChipsEscritor) window.renderChipsEscritor();
  if (window.renderChipsAtores) window.renderChipsAtores();
  // Atualizar previews
  validarImagem(document.getElementById('poster').value, 'poster-preview');
  validarImagem(document.getElementById('thumb').value, 'thumb-preview');
}

// Autocomplete de títulos
async function autocompleteTitulos(input, listId) {
  const val = input.value.trim();
  if (val.length < 2) return;
  const url = `${TMDB_SEARCH}?api_key=${TMDB_API_KEY}&language=pt-BR&query=${encodeURIComponent(val)}`;
  const res = await fetch(url);
  const data = await res.json();
  const list = document.getElementById(listId);
  list.innerHTML = '';
  if (data.results && data.results.length) {
    data.results.slice(0, 7).forEach(filme => {
      const item = document.createElement('div');
      item.className = 'autocomplete-item';
      item.textContent = `${filme.title} (${filme.release_date ? filme.release_date.substring(0,4) : ''})`;
      item.onclick = () => {
        document.getElementById('tmdb_titulo').value = filme.title;
        document.getElementById('tmdb_id').value = filme.id;
        document.getElementById('autocomplete-list').innerHTML = '';
      };
      list.appendChild(item);
    });
  }
}

// Inicialização
window.addEventListener('DOMContentLoaded', function() {
  console.log('[filmes-adicionar.js] DOMContentLoaded');
  // Chips
  setupChips('atores', 'atores-chips');
  setupChips('diretor', 'diretor-chips');
  setupChips('escritor', 'escritor-chips');
  window.renderChipsAtores = () => setupChips('atores', 'atores-chips');
  window.renderChipsDiretor = () => setupChips('diretor', 'diretor-chips');
  window.renderChipsEscritor = () => setupChips('escritor', 'escritor-chips');
  // Preview de imagens
  document.getElementById('poster').addEventListener('input', function() { validarImagem(this.value, 'poster-preview'); });
  document.getElementById('thumb').addEventListener('input', function() { validarImagem(this.value, 'thumb-preview'); });
  // Busca TMDb
  document.getElementById('btn-buscar-tmdb').addEventListener('click', function() {
    console.log('[filmes-adicionar.js] Clique no botão buscar');
    const tipo = document.getElementById('busca-tipo').value;
    const valor = tipo === 'id' ? document.getElementById('tmdb_id').value.trim() : document.getElementById('tmdb_titulo').value.trim();
    const status = document.getElementById('import-status');
    console.log('[filmes-adicionar.js] Tipo:', tipo, 'Valor:', valor);
    if (!valor) {
      status.innerHTML = '<span class="text-danger">Informe o valor para busca.</span>';
      return;
    }
    buscarTMDb(tipo, valor, status, function(data) {
      if (tipo === 'titulo') {
        // Se for busca por título, mostrar lista para seleção
        const list = document.getElementById('autocomplete-list');
        list.innerHTML = '';
        data.slice(0, 7).forEach(filme => {
          const item = document.createElement('div');
          item.className = 'autocomplete-item';
          item.textContent = `${filme.title} (${filme.release_date ? filme.release_date.substring(0,4) : ''})`;
          item.onclick = () => {
            document.getElementById('tmdb_titulo').value = filme.title;
            document.getElementById('tmdb_id').value = filme.id;
            list.innerHTML = '';
            buscarTMDb('id', filme.id, status, preencherCamposTMDb);
          };
          list.appendChild(item);
        });
      } else {
        preencherCamposTMDb(data);
      }
    });
  });
  // Autocomplete
  document.getElementById('tmdb_titulo').addEventListener('input', function() {
    console.log('[filmes-adicionar.js] Autocomplete input:', this.value);
    autocompleteTitulos(this, 'autocomplete-list');
  });
}); 