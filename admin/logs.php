<?php
// logs.php v1.0.0 - Visualização de logs de ações de widgets (sessão atual)
$pageTitle = 'Logs';
$breadcrumbs = ['Logs' => null];
$pageCss = [];
$pageScripts = [];
include_once 'templates/header.php';
?>
<main class="admin-main">
    <div class="main-content">
        <h2>Logs</h2>
        <div id="widget-logs-panel">
            <table id="widget-logs-table" class="table table-bordered table-sm" style="margin-top:16px;">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Ação</th>
                        <th>Widget</th>
                        <th>Layout</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Logs serão inseridos via JS -->
                </tbody>
            </table>
            <div id="no-logs-msg" class="text-muted" style="display:none;">Nenhuma ação registrada nesta sessão.</div>
        </div>
        <script>
        function renderWidgetLogs() {
            const logs = window.dashboardWidgetLogs || [];
            const tbody = document.querySelector('#widget-logs-table tbody');
            const noLogsMsg = document.getElementById('no-logs-msg');
            tbody.innerHTML = '';
            if (!logs.length) {
                noLogsMsg.style.display = 'block';
                return;
            }
            noLogsMsg.style.display = 'none';
            logs.slice().reverse().forEach(log => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${new Date(log.timestamp).toLocaleString()}</td>
                    <td>${log.action}</td>
                    <td>${log.widgetId || '-'}</td>
                    <td><pre style="white-space:pre-wrap;font-size:0.95em;">${JSON.stringify(log.layout.rows)}</pre></td>
                `;
                tbody.appendChild(tr);
            });
        }
        document.addEventListener('DOMContentLoaded', renderWidgetLogs);
        </script>
    </div>
</main>
<?php include_once 'templates/footer.php'; ?> 