// UCMWebChatBot.js - Módulo JavaScript para integração do WebChatBot UCM

class UCMWebChatBot {
    constructor(config = {}) {
        // Configurações padrão
        this.config = {
            baseUrl: config.baseUrl || 'http://localhost:8000/api/web-chatbot',
            containerId: config.containerId || 'ucm-chatbot-container',
            theme: config.theme || 'default',
            language: config.language || 'pt',
            autoInit: config.autoInit !== false,
            userIdentifier: config.userIdentifier || 'web_user@ucm.ac.mz',
            ...config
        };
        
        // Estado interno
        this.sessionId = 'ucm_web_session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        this.isLoading = false;
        this.awaitingStudentCode = false;
        this.awaitingType = null;
        this.chatHistory = [];
        this.currentOptions = [];
        
        // Callbacks
        this.onMessageReceived = config.onMessageReceived || null;
        this.onStudentInfoReceived = config.onStudentInfoReceived || null;
        this.onChatEnded = config.onChatEnded || null;
        this.onError = config.onError || null;
        
        if (this.config.autoInit) {
            this.init();
        }
    }
    
    // Inicialização
    async init() {
        try {
            this.createChatInterface();
            await this.initChat();
        } catch (error) {
            console.error('Erro ao inicializar UCM WebChatBot:', error);
            this.handleError('Erro de inicialização', error);
        }
    }
    
    // Criar interface do chat
    createChatInterface() {
        const container = document.getElementById(this.config.containerId);
        if (!container) {
            throw new Error(`Container com ID '${this.config.containerId}' não encontrado`);
        }
        
        container.innerHTML = `
            <div class="ucm-chatbot ${this.config.theme}">
                <div class="ucm-chatbot-header">
                    <div class="ucm-chatbot-title">
                        <h3>🎓 UCM Assistant</h3>
                        <span>Assistente Virtual</span>
                    </div>
                    <div class="ucm-chatbot-actions">
                        <button id="ucm-minimize-btn" title="Minimizar">−</button>
                        <button id="ucm-close-btn" title="Fechar">×</button>
                    </div>
                </div>
                
                <div class="ucm-chatbot-messages" id="ucm-messages-container">
                    <!-- Mensagens serão inseridas aqui -->
                </div>
                
                <div class="ucm-chatbot-input">
                    <div class="ucm-input-group">
                        <input 
                            type="text" 
                            id="ucm-message-input" 
                            placeholder="Digite sua mensagem..."
                            disabled
                        />
                        <button id="ucm-send-btn" disabled>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M2,21L23,12L2,3V10L17,12L2,14V21Z"/>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="ucm-action-buttons">
                        <button id="ucm-back-btn" disabled>⬅️ Voltar</button>
                        <button id="ucm-restart-btn">🔄 Reiniciar</button>
                        <button id="ucm-end-btn">🔚 Encerrar</button>
                    </div>
                </div>
                
                <div class="ucm-chatbot-loading" id="ucm-loading" style="display: none;">
                    <div class="ucm-typing-indicator">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <span>Digitando...</span>
                </div>
            </div>
        `;
        
        this.setupEventListeners();
        this.injectStyles();
    }
    
    // Configurar event listeners
    setupEventListeners() {
        const messageInput = document.getElementById('ucm-message-input');
        const sendBtn = document.getElementById('ucm-send-btn');
        const backBtn = document.getElementById('ucm-back-btn');
        const restartBtn = document.getElementById('ucm-restart-btn');
        const endBtn = document.getElementById('ucm-end-btn');
        const minimizeBtn = document.getElementById('ucm-minimize-btn');
        const closeBtn = document.getElementById('ucm-close-btn');
        
        messageInput?.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !this.isLoading) {
                this.sendMessage();
            }
        });
        
        sendBtn?.addEventListener('click', () => this.sendMessage());
        backBtn?.addEventListener('click', () => this.goBack());
        restartBtn?.addEventListener('click', () => this.restartChat());
        endBtn?.addEventListener('click', () => this.endChat());
        
        minimizeBtn?.addEventListener('click', () => this.toggleMinimize());
        closeBtn?.addEventListener('click', () => this.closeChat());
    }
    
    // Injetar estilos CSS
    injectStyles() {
        if (document.getElementById('ucm-chatbot-styles')) return;
        
        const styles = document.createElement('style');
        styles.id = 'ucm-chatbot-styles';
        styles.textContent = `
            .ucm-chatbot {
                width: 100%;
                max-width: 400px;
                height: 600px;
                background: white;
                border-radius: 12px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                display: flex;
                flex-direction: column;
                overflow: hidden;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            }
            
            .ucm-chatbot-header {
                background: linear-gradient(135deg, #2c3e50, #3498db);
                color: white;
                padding: 16px;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .ucm-chatbot-title h3 {
                margin: 0;
                font-size: 1.1em;
            }
            
            .ucm-chatbot-title span {
                font-size: 0.85em;
                opacity: 0.9;
            }
            
            .ucm-chatbot-actions button {
                background: none;
                border: none;
                color: white;
                font-size: 1.2em;
                cursor: pointer;
                padding: 4px 8px;
                margin-left: 8px;
                border-radius: 4px;
            }
            
            .ucm-chatbot-actions button:hover {
                background: rgba(255,255,255,0.2);
            }
            
            .ucm-chatbot-messages {
                flex: 1;
                overflow-y: auto;
                padding: 16px;
                background: #f8f9fa;
            }
            
            .ucm-message {
                margin-bottom: 12px;
                animation: ucmFadeIn 0.3s ease;
            }
            
            @keyframes ucmFadeIn {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }
            
            .ucm-message.bot {
                text-align: left;
            }
            
            .ucm-message.user {
                text-align: right;
            }
            
            .ucm-message-bubble {
                display: inline-block;
                max-width: 85%;
                padding: 10px 14px;
                border-radius: 16px;
                word-wrap: break-word;
                line-height: 1.4;
            }
            
            .ucm-message.bot .ucm-message-bubble {
                background: #e3f2fd;
                color: #1976d2;
                border-bottom-left-radius: 4px;
            }
            
            .ucm-message.user .ucm-message-bubble {
                background: #2196f3;
                color: white;
                border-bottom-right-radius: 4px;
            }
            
            .ucm-options-container {
                margin-top: 8px;
                max-width: 85%;
            }
            
            .ucm-option-btn {
                display: block;
                width: 100%;
                margin: 4px 0;
                padding: 8px 12px;
                background: white;
                border: 1px solid #2196f3;
                border-radius: 20px;
                color: #2196f3;
                cursor: pointer;
                transition: all 0.2s ease;
                font-size: 0.9em;
            }
            
            .ucm-option-btn:hover {
                background: #2196f3;
                color: white;
                transform: translateY(-1px);
            }
            
            .ucm-student-info {
                background: #e8f5e8;
                border: 1px solid #4caf50;
                border-radius: 8px;
                padding: 12px;
                margin: 8px 0;
                max-width: 85%;
            }
            
            .ucm-student-info h4 {
                color: #2e7d32;
                margin: 0 0 8px 0;
                font-size: 1em;
            }
            
            .ucm-student-info p {
                margin: 3px 0;
                color: #388e3c;
                font-size: 0.9em;
            }
            
            .ucm-chatbot-input {
                padding: 16px;
                background: white;
                border-top: 1px solid #e0e0e0;
            }
            
            .ucm-input-group {
                display: flex;
                gap: 8px;
                margin-bottom: 8px;
            }
            
            .ucm-input-group input {
                flex: 1;
                padding: 10px 14px;
                border: 1px solid #ddd;
                border-radius: 20px;
                outline: none;
                font-size: 0.9em;
            }
            
            .ucm-input-group input:focus {
                border-color: #2196f3;
            }
            
            .ucm-input-group button {
                padding: 10px 12px;
                background: #2196f3;
                color: white;
                border: none;
                border-radius: 20px;
                cursor: pointer;
                transition: background 0.2s ease;
            }
            
            .ucm-input-group button:hover:not(:disabled) {
                background: #1976d2;
            }
            
            .ucm-input-group button:disabled {
                background: #ccc;
                cursor: not-allowed;
            }
            
            .ucm-action-buttons {
                display: flex;
                gap: 8px;
            }
            
            .ucm-action-buttons button {
                flex: 1;
                padding: 6px 10px;
                background: #f5f5f5;
                border: 1px solid #ddd;
                border-radius: 12px;
                cursor: pointer;
                font-size: 0.75em;
                transition: background 0.2s ease;
            }
            
            .ucm-action-buttons button:hover:not(:disabled) {
                background: #e0e0e0;
            }
            
            .ucm-action-buttons button:disabled {
                opacity: 0.5;
                cursor: not-allowed;
            }
            
            .ucm-chatbot-loading {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                padding: 12px;
                background: rgba(255,255,255,0.9);
                border-top: 1px solid #eee;
                color: #666;
                font-size: 0.9em;
            }
            
            .ucm-typing-indicator {
                display: flex;
                gap: 3px;
            }
            
            .ucm-typing-indicator span {
                width: 6px;
                height: 6px;
                border-radius: 50%;
                background: #999;
                animation: ucmTyping 1.4s infinite;
            }
            
            .ucm-typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
            .ucm-typing-indicator span:nth-child(3) { animation-delay: 0.4s; }
            
            @keyframes ucmTyping {
                0%, 60%, 100% { transform: translateY(0); }
                30% { transform: translateY(-8px); }
            }
            
            .ucm-chatbot.minimized .ucm-chatbot-messages,
            .ucm-chatbot.minimized .ucm-chatbot-input,
            .ucm-chatbot.minimized .ucm-chatbot-loading {
                display: none;
            }
            
            .ucm-chatbot.minimized {
                height: auto;
            }
        `;
        
        document.head.appendChild(styles);
    }
    
    // API calls
    async initChat() {
        try {
            this.setLoading(true);
            
            const response = await this.apiCall('/init', {
                session_id: this.sessionId,
                user_identifier: this.config.userIdentifier
            });
            
            if (response.success) {
                this.handleBotResponse(response.data);
                this.addToChatHistory('system', 'Chat iniciado');
            } else {
                this.handleError('Erro ao inicializar', response.message);
            }
        } catch (error) {
            this.handleError('Erro de conexão', error.message);
        } finally {
            this.setLoading(false);
        }
    }
    
    async sendMessage() {
        const messageInput = document.getElementById('ucm-message-input');
        const messageText = messageInput?.value.trim();
        
        if (!messageText || this.isLoading) return;
        
        // Exibir mensagem do usuário
        this.displayUserMessage(messageText);
        this.addToChatHistory('user', messageText);
        messageInput.value = '';
        
        try {
            this.setLoading(true);
            
            const response = await this.apiCall('/message', {
                session_id: this.sessionId,
                message: messageText
            });
            
            this.handleBotResponse(response.data);
            
        } catch (error) {
            this.handleError('Erro ao enviar mensagem', error.message);
        } finally {
            this.setLoading(false);
        }
    }
    
    async selectOption(optionValue) {
        try {
            this.setLoading(true);
            
            const response = await this.apiCall('/message', {
                session_id: this.sessionId,
                option_value: optionValue
            });
            
            // Encontrar o label da opção selecionada
            const selectedOption = this.currentOptions.find(opt => opt.value === optionValue);
            if (selectedOption) {
                this.displayUserMessage(selectedOption.label);
                this.addToChatHistory('user', selectedOption.label);
            }
            
            this.handleBotResponse(response.data);
            
        } catch (error) {
            this.handleError('Erro ao processar opção', error.message);
        } finally {
            this.setLoading(false);
        }
    }
    
    async goBack() {
        try {
            this.setLoading(true);
            
            const response = await this.apiCall('/message', {
                session_id: this.sessionId,
                action: 'back'
            });
            
            this.handleBotResponse(response.data);
            
        } catch (error) {
            this.handleError('Erro ao voltar', error.message);
        } finally {
            this.setLoading(false);
        }
    }
    
    async restartChat() {
        // Limpar interface
        const messagesContainer = document.getElementById('ucm-messages-container');
        if (messagesContainer) {
            messagesContainer.innerHTML = '';
        }
        
        // Resetar estado
        this.sessionId = 'ucm_web_session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        this.awaitingStudentCode = false;
        this.awaitingType = null;
        this.chatHistory = [];
        this.currentOptions = [];
        
        // Reinicializar
        await this.initChat();
    }
    
    async endChat() {
        try {
            await this.apiCall('/end', {
                session_id: this.sessionId
            });
            
            this.displayBotMessage({
                question: { text: '👋 Obrigado por usar o assistente da UCM! Tenha um ótimo dia!' },
                options: [],
                session_info: { can_go_back: false }
            });
            
            this.disableInput();
            this.addToChatHistory('system', 'Chat encerrado');
            
            if (this.onChatEnded) {
                this.onChatEnded(this.chatHistory);
            }
            
        } catch (error) {
            this.handleError('Erro ao encerrar chat', error.message);
        }
    }
    
    // Utility methods
    async apiCall(endpoint, data) {
        const response = await fetch(`${this.config.baseUrl}${endpoint}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        return await response.json();
    }
    
    handleBotResponse(data) {
        if (!data) {
            this.handleError('Resposta inválida', 'Dados não encontrados na resposta');
            return;
        }
        
        this.addToChatHistory('bot', data);
        
        if (this.onMessageReceived) {
            this.onMessageReceived(data);
        }
        
        switch (data.type) {
            case 'awaiting_student_code':
                this.awaitingStudentCode = true;
                this.awaitingType = data.awaiting_type;
                this.displayBotMessage({
                    question: { text: data.message },
                    options: [],
                    session_info: { can_go_back: true }
                });
                this.enableInput();
                break;
                
            case 'student_info':
                this.awaitingStudentCode = false;
                this.displayStudentInfo(data);
                this.disableInput();
                
                if (this.onStudentInfoReceived) {
                    this.onStudentInfoReceived(data.student);
                }
                break;
                
            case 'conversation_end':
                this.displayBotMessage({
                    question: { text: data.message },
                    options: [],
                    session_info: { can_go_back: false }
                });
                this.disableInput();
                break;
                
            default:
                if (data.question) {
                    this.awaitingStudentCode = false;
                    this.displayBotMessage(data);
                } else {
                    this.handleError('Resposta inesperada', 'Formato de resposta não reconhecido');
                }
        }
    }
    
    displayBotMessage(data) {
        const messagesContainer = document.getElementById('ucm-messages-container');
        if (!messagesContainer) return;
        
        const messageDiv = document.createElement('div');
        messageDiv.className = 'ucm-message bot';
        
        let content = `<div class="ucm-message-bubble">${data.question.text}</div>`;
        
        if (data.options && data.options.length > 0) {
            this.currentOptions = data.options;
            content += '<div class="ucm-options-container">';
            data.options.forEach(option => {
                content += `<button class="ucm-option-btn" onclick="window.ucmChatBot?.selectOption('${option.value}')">${option.label}</button>`;
            });
            content += '</div>';
            this.disableInput();
        } else if (this.awaitingStudentCode) {
            this.enableInput();
        } else {
            this.disableInput();
        }
        
        messageDiv.innerHTML = content;
        messagesContainer.appendChild(messageDiv);
        this.scrollToBottom();
        
        // Atualizar botões
        if (data.session_info) {
            const backBtn = document.getElementById('ucm-back-btn');
            if (backBtn) {
                backBtn.disabled = !data.session_info.can_go_back;
            }
        }
    }
    
    displayUserMessage(text) {
        const messagesContainer = document.getElementById('ucm-messages-container');
        if (!messagesContainer) return;
        
        const messageDiv = document.createElement('div');
        messageDiv.className = 'ucm-message user';
        messageDiv.innerHTML = `<div class="ucm-message-bubble">${text}</div>`;
        messagesContainer.appendChild(messageDiv);
        this.scrollToBottom();
    }
    
    displayStudentInfo(data) {
        const messagesContainer = document.getElementById('ucm-messages-container');
        if (!messagesContainer) return;
        
        const messageDiv = document.createElement('div');
        messageDiv.className = 'ucm-message bot';
        
        const student = data.student;
        let content = `
            <div class="ucm-message-bubble">${data.message}</div>
            <div class="ucm-student-info">
                <h4>📋 Informações do Estudante</h4>
                <p><strong>Nome:</strong> ${student.name}</p>
                <p><strong>Código:</strong> ${student.code}</p>
        `;
        
        if (student.situacao_academica) {
            content += `<p><strong>Situação Acadêmica:</strong> ${student.situacao_academica}</p>`;
        }
        
        if (student.situacao_financeira) {
            content += `<p><strong>Situação Financeira:</strong> ${student.situacao_financeira}</p>`;
        }
        
        content += '</div>';
        
        messageDiv.innerHTML = content;
        messagesContainer.appendChild(messageDiv);
        this.scrollToBottom();
    }
    
    handleError(title, message) {
        console.error(`UCM ChatBot - ${title}:`, message);
        
        const messagesContainer = document.getElementById('ucm-messages-container');
        if (messagesContainer) {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'ucm-message bot';
            messageDiv.innerHTML = `
                <div class="ucm-message-bubble" style="background: #ffebee; color: #c62828; border: 1px solid #f44336;">
                    ❌ ${title}: ${message}
                </div>
            `;
            messagesContainer.appendChild(messageDiv);
            this.scrollToBottom();
        }
        
        if (this.onError) {
            this.onError(title, message);
        }
    }
    
    setLoading(loading) {
        this.isLoading = loading;
        
        const loadingElement = document.getElementById('ucm-loading');
        const sendBtn = document.getElementById('ucm-send-btn');
        const messageInput = document.getElementById('ucm-message-input');
        
        if (loadingElement) {
            loadingElement.style.display = loading ? 'flex' : 'none';
        }
        
        if (loading) {
            if (sendBtn) sendBtn.disabled = true;
            if (messageInput) messageInput.disabled = true;
        } else if (this.awaitingStudentCode) {
            this.enableInput();
        }
    }
    
    enableInput() {
        const messageInput = document.getElementById('ucm-message-input');
        const sendBtn = document.getElementById('ucm-send-btn');
        
        if (messageInput) {
            messageInput.disabled = false;
            messageInput.focus();
        }
        if (sendBtn) sendBtn.disabled = false;
    }
    
    disableInput() {
        const messageInput = document.getElementById('ucm-message-input');
        const sendBtn = document.getElementById('ucm-send-btn');
        
        if (messageInput) messageInput.disabled = true;
        if (sendBtn) sendBtn.disabled = true;
    }
    
    scrollToBottom() {
        const messagesContainer = document.getElementById('ucm-messages-container');
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    }
    
    addToChatHistory(type, content) {
        this.chatHistory.push({
            timestamp: new Date().toISOString(),
            type: type,
            content: content
        });
    }
    
    toggleMinimize() {
        const chatbot = document.querySelector('.ucm-chatbot');
        if (chatbot) {
            chatbot.classList.toggle('minimized');
        }
    }
    
    closeChat() {
        const container = document.getElementById(this.config.containerId);
        if (container) {
            container.style.display = 'none';
        }
    }
    
    // Métodos públicos para controle externo
    show() {
        const container = document.getElementById(this.config.containerId);
        if (container) {
            container.style.display = 'block';
        }
    }
    
    hide() {
        this.closeChat();
    }
    
    getChatHistory() {
        return this.chatHistory;
    }
    
    getSessionId() {
        return this.sessionId;
    }
    
    isAwaitingInput() {
        return this.awaitingStudentCode;
    }
}

// Exportar para uso global
if (typeof window !== 'undefined') {
    window.UCMWebChatBot = UCMWebChatBot;
}

// Exportar para módulos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = UCMWebChatBot;
}

// Exemplo de uso:
/*
// Uso básico
const chatBot = new UCMWebChatBot({
    containerId: 'meu-chatbot-container',
    baseUrl: 'https://api.ucm.ac.mz/api/web-chatbot',
    userIdentifier: 'user@example.com'
});

// Uso avançado com callbacks
const chatBot = new UCMWebChatBot({
    containerId: 'meu-chatbot-container',
    baseUrl: 'https://api.ucm.ac.mz/api/web-chatbot',
    theme: 'custom',
    onMessageReceived: (data) => {
        console.log('Mensagem recebida:', data);
    },
    onStudentInfoReceived: (student) => {
        console.log('Info do estudante:', student);
        // Integrar com sistema externo
    },
    onChatEnded: (history) => {
        console.log('Chat encerrado. Histórico:', history);
        // Salvar histórico ou analytics
    },
    onError: (title, message) => {
        console.error('Erro no chatbot:', title, message);
        // Enviar para sistema de monitoramento
    }
});

// Controle programático
chatBot.show();
chatBot.hide();
const history = chatBot.getChatHistory();
const sessionId = chatBot.getSessionId();
*/