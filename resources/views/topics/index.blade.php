@extends('layouts.app')

@section('title', 'Tópicos MQTT')

@section('content')
<div class="admin-dashboard">
    <div class="page-header">
        <h1>📡 Tópicos MQTT</h1>
        <p>Gerencie e monitore todos os tópicos MQTT do sistema</p>
    </div>

    <!-- Estatísticas dos Tópicos -->
    <div class="quick-stats">
        <h2>📊 Estatísticas dos Tópicos</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="stat-item">
                <div class="stat-value">{{ $stats['totalTopics'] ?? 0 }}</div>
                <div class="stat-label">Total de Tópicos</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $stats['activeTopics'] ?? 0 }}</div>
                <div class="stat-label">Tópicos Ativos</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $stats['deviceTopics'] ?? 0 }}</div>
                <div class="stat-label">Tópicos de Dispositivos</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $stats['systemTopics'] ?? 0 }}</div>
                <div class="stat-label">Tópicos do Sistema</div>
            </div>
        </div>
    </div>

    <!-- Lista de Tópicos -->
    <div class="dashboard-card">
        <div class="flex justify-between items-center mb-6">
            <h2>📋 Lista de Tópicos</h2>
            <button onclick="refreshTopics()" class="btn-primary">
                🔄 Atualizar
            </button>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                ✅ {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                ❌ {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(isset($topics) && count($topics) > 0)
            <div class="topics-list">
                @foreach($topics as $topic)
                    <div class="topic-item">
                        <div class="topic-header">
                            <div class="topic-name">
                                <h3>{{ $topic['name'] ?? 'N/A' }}</h3>
                                <span class="topic-type">{{ $topic['type'] ?? 'device' }}</span>
                            </div>
                            <div class="topic-actions">
                                <button onclick="viewTopic('{{ $topic['id'] ?? '' }}')" class="btn-outline-primary">
                                    👁️ Ver
                                </button>
                                <button onclick="editTopic('{{ $topic['id'] ?? '' }}')" class="btn-outline-primary">
                                    ✏️ Editar
                                </button>
                                <button onclick="deleteTopic('{{ $topic['id'] ?? '' }}')" class="btn-outline-primary text-red-600">
                                    🚫 Desativar
                                </button>
                            </div>
                        </div>
                        <div class="topic-details">
                            <p><strong>ID:</strong> {{ $topic['id'] ?? 'N/A' }}</p>
                            <p><strong>Descrição:</strong> {{ $topic['description'] ?? 'Sem descrição' }}</p>
                            <p><strong>Criado em:</strong> {{ $topic['created_at'] ?? 'N/A' }}</p>
                            <p><strong>Status:</strong>
                                <span class="status-badge {{ $topic['status'] ?? 'active' }}">
                                    {{ $topic['status'] ?? 'Ativo' }}
                                </span>
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="no-topics">
                <div class="text-center py-8">
                    <div class="text-6xl mb-4">📡</div>
                    <h3 class="text-xl font-semibold mb-2">Nenhum tópico encontrado</h3>
                    <p class="text-gray-600 mb-4">Não há tópicos MQTT cadastrados no sistema.</p>
                    <button onclick="createTopic()" class="btn-primary">
                        ➕ Criar Primeiro Tópico
                    </button>
                </div>
            </div>
        @endif
    </div>

    <!-- Modal para criar/editar tópico -->
    <div id="topicModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Criar Tópico MQTT</h3>
                <button onclick="closeModal()" class="close-btn">&times;</button>
            </div>
            <div class="modal-body">
                <form id="topicForm">
                    <div class="form-group">
                        <label for="topicName">Nome do Tópico</label>
                        <input type="text" id="topicName" name="name" required class="form-input" placeholder="Ex: device/sensor/temperature">
                    </div>
                    <div class="form-group">
                        <label for="topicDescription">Descrição</label>
                        <textarea id="topicDescription" name="description" class="form-input" rows="3" placeholder="Descreva o propósito deste tópico"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="topicType">Tipo</label>
                        <select id="topicType" name="type" class="form-input">
                            <option value="device">Dispositivo</option>
                            <option value="system">Sistema</option>
                            <option value="sensor">Sensor</option>
                            <option value="actuator">Atuador</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button onclick="closeModal()" class="btn-secondary">Cancelar</button>
                <button onclick="saveTopic()" class="btn-primary">Salvar</button>
            </div>
        </div>
    </div>
</div>

<style>
.topics-list {
    space-y: 1rem;
}

.topic-item {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.topic-item:hover {
    background: #e9ecef;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.topic-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.topic-name h3 {
    margin: 0;
    color: var(--color-primary-dark);
    font-size: 1.2rem;
    font-weight: 600;
}

.topic-type {
    background: var(--color-primary-lightest);
    color: var(--color-primary-dark);
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
    margin-left: 0.5rem;
}

.topic-actions {
    display: flex;
    gap: 0.5rem;
}

.topic-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 0.5rem;
}

.topic-details p {
    margin: 0.25rem 0;
    color: #6c757d;
    font-size: 0.9rem;
}

.status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-badge.active {
    background: #d4edda;
    color: #155724;
}

.status-badge.inactive {
    background: #f8d7da;
    color: #721c24;
}

.no-topics {
    text-align: center;
    padding: 2rem;
}

.text-6xl {
    font-size: 4rem;
    line-height: 1;
}

.text-xl {
    font-size: 1.25rem;
    line-height: 1.75rem;
}

.font-semibold {
    font-weight: 600;
}

.text-gray-600 {
    color: #6b7280;
}

.py-8 {
    padding-top: 2rem;
    padding-bottom: 2rem;
}

.mb-4 {
    margin-bottom: 1rem;
}

.mb-2 {
    margin-bottom: 0.5rem;
}

/* Modal styles */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #e9ecef;
}

.modal-header h3 {
    margin: 0;
    color: var(--color-primary-dark);
    font-size: 1.5rem;
    font-weight: 600;
}

.close-btn {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #6c757d;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.close-btn:hover {
    color: #495057;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    padding: 1.5rem;
    border-top: 1px solid #e9ecef;
}

.flex {
    display: flex;
}

.justify-between {
    justify-content: space-between;
}

.items-center {
    align-items: center;
}

.mb-6 {
    margin-bottom: 1.5rem;
}

.text-center {
    text-align: center;
}

.grid {
    display: grid;
}

.grid-cols-1 {
    grid-template-columns: repeat(1, minmax(0, 1fr));
}

@media (min-width: 768px) {
    .md\:grid-cols-4 {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
}

.gap-4 {
    gap: 1rem;
}

.space-y-1 > * + * {
    margin-top: 0.25rem;
}

/* Alert styles */
.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    font-weight: 500;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert ul {
    margin: 0.5rem 0 0 0;
    padding-left: 1.5rem;
}

.alert li {
    margin: 0.25rem 0;
}

/* Botão de exclusão com cor vermelha */
.text-red-600 {
    color: #dc2626 !important;
}

.btn-outline-primary.text-red-600 {
    color: #dc2626 !important;
    border-color: #dc2626 !important;
}

.btn-outline-primary.text-red-600:hover {
    background-color: #dc2626 !important;
    color: white !important;
}

/* Animações para exclusão */
.topic-item {
    transition: all 0.3s ease;
}

.topic-item.removing {
    opacity: 0.5;
    transform: scale(0.95);
}
</style>

<script>
let currentTopicId = null;

function refreshTopics() {
    location.reload();
}


function createTopic() {
    currentTopicId = null;
    document.getElementById('modalTitle').textContent = 'Criar Tópico MQTT';
    document.getElementById('topicForm').reset();
    document.getElementById('topicModal').style.display = 'flex';
}

function editTopic(topicId) {
    currentTopicId = topicId;
    document.getElementById('modalTitle').textContent = 'Editar Tópico MQTT';
    // Aqui você carregaria os dados do tópico
    document.getElementById('topicModal').style.display = 'flex';
}

function viewTopic(topicId) {
    alert('Visualizar tópico: ' + topicId);
}

function deleteTopic(topicId) {
    if (confirm('Tem certeza que deseja desativar este tópico?')) {
        // Mostrar loading
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = '⏳ Desativando...';
        button.disabled = true;

        // Criar formulário para desativação real
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/topics/${topicId}/deactivate`;

        // Adicionar CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);

        // Adicionar método PATCH (para desativação)
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'PATCH';
        form.appendChild(methodInput);

        // Adicionar ao DOM e submeter
        document.body.appendChild(form);
        form.submit();
    }
}

function showAlert(message, type = 'success') {
    // Remover alertas existentes
    const existingAlerts = document.querySelectorAll('.alert');
    existingAlerts.forEach(alert => alert.remove());

    // Criar novo alerta
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;

    // Inserir no topo da lista de tópicos
    const topicsCard = document.querySelector('.dashboard-card');
    const topicsList = topicsCard.querySelector('.topics-list, .no-topics');
    topicsCard.insertBefore(alert, topicsList);

    // Remover após 5 segundos
    setTimeout(() => {
        alert.remove();
    }, 5000);
}

function updateStats() {
    const topicItems = document.querySelectorAll('.topic-item');
    const totalTopics = topicItems.length;
    const activeTopics = Array.from(topicItems).filter(item =>
        !item.querySelector('.status-badge')?.textContent.includes('inactive')
    ).length;

    // Atualizar estatísticas na página
    const statValues = document.querySelectorAll('.stat-value');
    if (statValues.length >= 2) {
        statValues[0].textContent = totalTopics;
        statValues[1].textContent = activeTopics;
    }
}

function closeModal() {
    document.getElementById('topicModal').style.display = 'none';
}

function saveTopic() {
    const form = document.getElementById('topicForm');
    const formData = new FormData(form);

    // Validar campos obrigatórios
    const name = formData.get('name');
    const description = formData.get('description');
    const type = formData.get('type');

    if (!name || !type) {
        showAlert('Por favor, preencha todos os campos obrigatórios', 'error');
        return;
    }

    if (currentTopicId) {
        // Editar tópico existente
        showAlert('Tópico editado com sucesso! (Modo demonstração)', 'success');
    } else {
        // Criar novo tópico
        const newTopicId = Date.now(); // ID único baseado em timestamp

        // Criar elemento do tópico
        const topicItem = document.createElement('div');
        topicItem.className = 'topic-item';
        topicItem.innerHTML = `
            <div class="topic-header">
                <div class="topic-name">
                    <h3>${name}</h3>
                    <span class="topic-type">${type}</span>
                </div>
                <div class="topic-actions">
                    <button onclick="viewTopic('${newTopicId}')" class="btn-outline-primary">
                        👁️ Ver
                    </button>
                    <button onclick="editTopic('${newTopicId}')" class="btn-outline-primary">
                        ✏️ Editar
                    </button>
                    <button onclick="deleteTopic('${newTopicId}')" class="btn-outline-primary text-red-600">
                        🚫 Desativar
                    </button>
                </div>
            </div>
            <div class="topic-details">
                <p><strong>ID:</strong> ${newTopicId}</p>
                <p><strong>Descrição:</strong> ${description || 'Sem descrição'}</p>
                <p><strong>Criado em:</strong> ${new Date().toLocaleString('pt-BR')}</p>
                <p><strong>Status:</strong>
                    <span class="status-badge active">Ativo</span>
                </p>
            </div>
        `;

        // Adicionar à lista de tópicos
        const topicsList = document.querySelector('.topics-list');
        if (topicsList) {
            topicsList.appendChild(topicItem);

            // Se não havia tópicos, remover mensagem de "nenhum tópico"
            const noTopics = document.querySelector('.no-topics');
            if (noTopics) {
                noTopics.remove();
            }
        }

        showAlert(`Tópico '${name}' criado com sucesso! (Modo demonstração)`, 'success');
        updateStats();
    }

    closeModal();
}

// Fechar modal ao clicar fora
window.onclick = function(event) {
    const modal = document.getElementById('topicModal');
    if (event.target === modal) {
        closeModal();
    }
}
</script>
@endsection
