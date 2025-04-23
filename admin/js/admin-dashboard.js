// admin-dashboard.js v1.2.0 - Grid customizável com drag & drop, auto-save, restauração, logs de ações e feedback visual

document.addEventListener('DOMContentLoaded', function() {
    // Utilitários
    function getWidgetId(el) {
        return el.getAttribute('data-widget-id');
    }
    function getCurrentLayout() {
        const rows = [];
        document.querySelectorAll('.dashboard-widgets-row').forEach(function(row) {
            const ids = [];
            row.querySelectorAll('.dashboard-widget').forEach(function(widget) {
                if (widget.style.display !== 'none') {
                    ids.push(getWidgetId(widget));
                }
            });
            if (ids.length) rows.push(ids);
        });
        return { rows };
    }
    function showToast(message, type = 'info') {
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.style.position = 'fixed';
            toastContainer.style.bottom = '24px';
            toastContainer.style.right = '24px';
            toastContainer.style.zIndex = '9999';
            toastContainer.style.display = 'flex';
            toastContainer.style.flexDirection = 'column';
            toastContainer.style.gap = '8px';
            document.body.appendChild(toastContainer);
        }
        const toast = document.createElement('div');
        toast.className = `toast-message toast-${type}`;
        toast.style.background = type === 'success' ? '#22c55e' : (type === 'error' ? '#ef4444' : '#2563eb');
        toast.style.color = '#fff';
        toast.style.padding = '12px 20px';
        toast.style.borderRadius = '6px';
        toast.style.boxShadow = '0 2px 8px rgba(0,0,0,0.12)';
        toast.style.fontSize = '1rem';
        toast.style.opacity = '0.97';
        toast.style.transition = 'opacity 0.3s';
        toast.textContent = message;
        toastContainer.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 350);
        }, 2200);
    }
    // Array global para logs de ações de widgets
    window.dashboardWidgetLogs = window.dashboardWidgetLogs || [];
    function addWidgetLog(action, widgetId, layout) {
        window.dashboardWidgetLogs.push({
            timestamp: new Date().toISOString(),
            action,
            widgetId,
            layout: JSON.parse(JSON.stringify(layout))
        });
    }
    function saveLayout(action = 'save', widgetId = null) {
        const layout = getCurrentLayout();
        if (action && widgetId) {
            console.log(`[WIDGET] Ação: ${action} | Widget: ${widgetId} | Novo layout:`, layout);
        } else if (action) {
            console.log(`[WIDGET] Ação: ${action} | Novo layout:`, layout);
        }
        addWidgetLog(action, widgetId, layout);
        fetch('ajax/save_dashboard_layout.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(layout)
        }).then(() => {
            let msg = '';
            if (action === 'remover') msg = 'Widget removido do dashboard.';
            else if (action === 'restaurar') msg = 'Widget restaurado ao dashboard!';
            else if (action === 'mover') msg = 'Widget movido.';
            else msg = 'Layout salvo!';
            showToast(msg, 'success');
        });
    }
    function showAddWidgetsPanel() {
        const list = document.getElementById('widgets-list');
        list.innerHTML = '';
        const allIds = ['novos-usuarios','visualizacoes','mais-vistos','avisos'];
        const current = getCurrentLayout().rows.flat();
        allIds.forEach(function(id) {
            if (!current.includes(id)) {
                const btn = document.createElement('button');
                btn.className = 'btn btn-secondary m-1';
                btn.textContent = id.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase());
                btn.onclick = function() {
                    const widget = document.querySelector('.dashboard-widget[data-widget-id="'+id+'"]');
                    if (widget) {
                        widget.style.display = '';
                        saveLayout('restaurar', id);
                        showAddWidgetsPanel();
                    }
                };
                list.appendChild(btn);
            }
        });
        if (!list.innerHTML) list.innerHTML = '<span class="text-muted">Nenhum widget para adicionar</span>';
    }
    // 1. SortableJS para cada linha de widgets
    const rows = document.querySelectorAll('.dashboard-widgets-row');
    rows.forEach(function(row) {
        Sortable.create(row, {
            animation: 180,
            handle: '.dashboard-widget',
            ghostClass: 'sortable-ghost',
            onEnd: function(evt) {
                const movedWidget = evt.item ? getWidgetId(evt.item) : null;
                saveLayout('mover', movedWidget);
            }
        });
    });
    // 2. Botão de remover widget
    document.querySelectorAll('.dashboard-widget').forEach(function(widget) {
        if (!widget.querySelector('.remove-widget-btn')) {
            const btn = document.createElement('button');
            btn.className = 'remove-widget-btn';
            btn.title = 'Remover widget';
            btn.innerHTML = '<i class="fas fa-times"></i>';
            btn.onclick = function(e) {
                e.stopPropagation();
                widget.style.display = 'none';
                saveLayout('remover', getWidgetId(widget));
                showAddWidgetsPanel();
            };
            widget.appendChild(btn);
        }
    });
    // 3. Painel flutuante para adicionar widgets removidos
    if (!document.getElementById('add-widgets-panel')) {
        const panel = document.createElement('div');
        panel.id = 'add-widgets-panel';
        panel.className = 'add-widgets-panel';
        panel.innerHTML = '<button id="show-add-widgets" class="btn btn-primary"><i class="fas fa-plus"></i> Adicionar Widgets</button>' +
            '<button id="reset-widgets-layout" class="btn btn-danger ms-2"><i class="fas fa-undo"></i> Resetar Layout</button>' +
            '<div id="widgets-list" style="display:none;"></div>';
        document.body.appendChild(panel);
        document.getElementById('show-add-widgets').onclick = function() {
            const list = document.getElementById('widgets-list');
            list.style.display = list.style.display === 'none' ? 'block' : 'none';
            showAddWidgetsPanel();
        };
        document.getElementById('reset-widgets-layout').onclick = function() {
            if (confirm('Deseja realmente resetar o layout do dashboard para o padrão?')) {
                fetch('ajax/save_dashboard_layout.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ rows: [ ['novos-usuarios','visualizacoes'], ['mais-vistos','avisos'] ] })
                }).then(() => {
                    showToast('Layout do dashboard restaurado para o padrão.', 'success');
                    console.log('[WIDGET] Ação: resetar | Layout padrão restaurado');
                    window.location.reload();
                });
            }
        };
    }
    // 4. Carregar layout salvo ao abrir
    fetch('ajax/save_dashboard_layout.php')
        .then(r => r.json())
        .then(function(layout) {
            if (!layout.rows) return;
            // Esconde todos os widgets
            document.querySelectorAll('.dashboard-widget').forEach(function(w){w.style.display='none';});
            // Reposiciona e mostra conforme layout salvo
            layout.rows.forEach(function(rowIds, i) {
                const row = document.querySelectorAll('.dashboard-widgets-row')[i];
                if (!row) return;
                rowIds.forEach(function(id) {
                    const widget = document.querySelector('.dashboard-widget[data-widget-id="'+id+'"]');
                    if (widget) {
                        row.appendChild(widget);
                        widget.style.display = '';
                    }
                });
            });
            showAddWidgetsPanel();
        });
}); 