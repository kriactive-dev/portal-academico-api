// UCMChatBotReact.jsx - Componente React para o WebChatBot UCM

import React, { useState, useEffect, useRef, useCallback } from 'react';

const UCMChatBotReact = ({
  baseUrl = 'http://localhost:8000/api/web-chatbot',
  userIdentifier = 'web_user@ucm.ac.mz',
  theme = 'default',
  onMessageReceived = null,
  onStudentInfoReceived = null,
  onChatEnded = null,
  onError = null,
  className = '',
  style = {},
  minimized = false,
  onMinimizeToggle = null
}) => {
  // Estados
  const [sessionId] = useState(() => 
    'ucm_web_session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9)
  );
  const [messages, setMessages] = useState([]);
  const [isLoading, setIsLoading] = useState(false);
  const [awaitingStudentCode, setAwaitingStudentCode] = useState(false);
  const [awaitingType, setAwaitingType] = useState(null);
  const [currentOptions, setCurrentOptions] = useState([]);
  const [canGoBack, setCanGoBack] = useState(false);
  const [messageInput, setMessageInput] = useState('');
  const [chatHistory, setChatHistory] = useState([]);
  const [isMinimized, setIsMinimized] = useState(minimized);
  
  // Refs
  const messagesEndRef = useRef(null);
  const inputRef = useRef(null);
  
  // Scroll para o final das mensagens
  const scrollToBottom = useCallback(() => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, []);
  
  useEffect(() => {
    scrollToBottom();
  }, [messages, scrollToBottom]);
  
  // Adicionar ao histórico
  const addToChatHistory = useCallback((type, content) => {
    setChatHistory(prev => [...prev, {
      timestamp: new Date().toISOString(),
      type,
      content
    }]);
  }, []);
  
  // Chamada para API
  const apiCall = useCallback(async (endpoint, data) => {
    try {
      const response = await fetch(`${baseUrl}${endpoint}`, {
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
    } catch (error) {
      console.error('API Error:', error);
      throw error;
    }
  }, [baseUrl]);
  
  // Manipular resposta do bot
  const handleBotResponse = useCallback((data) => {
    if (!data) {
      handleError('Resposta inválida', 'Dados não encontrados na resposta');
      return;
    }
    
    addToChatHistory('bot', data);
    
    if (onMessageReceived) {
      onMessageReceived(data);
    }
    
    switch (data.type) {
      case 'awaiting_student_code':
        setAwaitingStudentCode(true);
        setAwaitingType(data.awaiting_type);
        addBotMessage(data.message);
        setCanGoBack(true);
        break;
        
      case 'student_info':
        setAwaitingStudentCode(false);
        addStudentInfoMessage(data);
        if (onStudentInfoReceived) {
          onStudentInfoReceived(data.student);
        }
        break;
        
      case 'conversation_end':
        addBotMessage(data.message);
        setCanGoBack(false);
        setCurrentOptions([]);
        break;
        
      default:
        if (data.question) {
          setAwaitingStudentCode(false);
          addBotMessage(data.question.text, data.options || []);
          setCanGoBack(data.session_info?.can_go_back || false);
        } else {
          handleError('Resposta inesperada', 'Formato de resposta não reconhecido');
        }
    }
  }, [addToChatHistory, onMessageReceived, onStudentInfoReceived]);
  
  // Manipular erros
  const handleError = useCallback((title, message) => {
    console.error(`UCM ChatBot - ${title}:`, message);
    
    setMessages(prev => [...prev, {
      id: Date.now(),
      type: 'error',
      content: `${title}: ${message}`,
      timestamp: new Date().toISOString()
    }]);
    
    if (onError) {
      onError(title, message);
    }
  }, [onError]);
  
  // Adicionar mensagem do bot
  const addBotMessage = useCallback((text, options = []) => {
    const message = {
      id: Date.now(),
      type: 'bot',
      content: text,
      options: options,
      timestamp: new Date().toISOString()
    };
    
    setMessages(prev => [...prev, message]);
    setCurrentOptions(options);
  }, []);
  
  // Adicionar mensagem do usuário
  const addUserMessage = useCallback((text) => {
    const message = {
      id: Date.now(),
      type: 'user',
      content: text,
      timestamp: new Date().toISOString()
    };
    
    setMessages(prev => [...prev, message]);
    addToChatHistory('user', text);
  }, [addToChatHistory]);
  
  // Adicionar informações do estudante
  const addStudentInfoMessage = useCallback((data) => {
    const message = {
      id: Date.now(),
      type: 'student_info',
      content: data.message,
      student: data.student,
      timestamp: new Date().toISOString()
    };
    
    setMessages(prev => [...prev, message]);
  }, []);
  
  // Inicializar chat
  const initChat = useCallback(async () => {
    try {
      setIsLoading(true);
      
      const response = await apiCall('/init', {
        session_id: sessionId,
        user_identifier: userIdentifier
      });
      
      if (response.success) {
        handleBotResponse(response.data);
        addToChatHistory('system', 'Chat iniciado');
      } else {
        handleError('Erro ao inicializar', response.message);
      }
    } catch (error) {
      handleError('Erro de conexão', error.message);
    } finally {
      setIsLoading(false);
    }
  }, [sessionId, userIdentifier, apiCall, handleBotResponse, addToChatHistory, handleError]);
  
  // Enviar mensagem
  const sendMessage = useCallback(async () => {
    const text = messageInput.trim();
    if (!text || isLoading) return;
    
    addUserMessage(text);
    setMessageInput('');
    
    try {
      setIsLoading(true);
      
      const response = await apiCall('/message', {
        session_id: sessionId,
        message: text
      });
      
      if (response.success) {
        handleBotResponse(response.data);
      } else {
        handleError('Erro na mensagem', response.message);
      }
    } catch (error) {
      handleError('Erro ao enviar mensagem', error.message);
    } finally {
      setIsLoading(false);
    }
  }, [messageInput, isLoading, addUserMessage, sessionId, apiCall, handleBotResponse, handleError]);
  
  // Selecionar opção
  const selectOption = useCallback(async (optionValue) => {
    try {
      setIsLoading(true);
      
      // Encontrar o label da opção selecionada
      const selectedOption = currentOptions.find(opt => opt.value === optionValue);
      if (selectedOption) {
        addUserMessage(selectedOption.label);
      }
      
      const response = await apiCall('/message', {
        session_id: sessionId,
        option_value: optionValue
      });
      
      if (response.success) {
        handleBotResponse(response.data);
      } else {
        handleError('Erro na opção', response.message);
      }
    } catch (error) {
      handleError('Erro ao processar opção', error.message);
    } finally {
      setIsLoading(false);
    }
  }, [currentOptions, addUserMessage, sessionId, apiCall, handleBotResponse, handleError]);
  
  // Voltar
  const goBack = useCallback(async () => {
    try {
      setIsLoading(true);
      
      const response = await apiCall('/message', {
        session_id: sessionId,
        action: 'back'
      });
      
      if (response.success) {
        handleBotResponse(response.data);
      } else {
        handleError('Erro ao voltar', response.message);
      }
    } catch (error) {
      handleError('Erro ao voltar', error.message);
    } finally {
      setIsLoading(false);
    }
  }, [sessionId, apiCall, handleBotResponse, handleError]);
  
  // Reiniciar chat
  const restartChat = useCallback(async () => {
    setMessages([]);
    setAwaitingStudentCode(false);
    setAwaitingType(null);
    setCurrentOptions([]);
    setCanGoBack(false);
    setMessageInput('');
    setChatHistory([]);
    
    await initChat();
  }, [initChat]);
  
  // Encerrar chat
  const endChat = useCallback(async () => {
    try {
      await apiCall('/end', {
        session_id: sessionId
      });
      
      addBotMessage('👋 Obrigado por usar o assistente da UCM! Tenha um ótimo dia!');
      setCanGoBack(false);
      setCurrentOptions([]);
      addToChatHistory('system', 'Chat encerrado');
      
      if (onChatEnded) {
        onChatEnded(chatHistory);
      }
    } catch (error) {
      handleError('Erro ao encerrar chat', error.message);
    }
  }, [sessionId, apiCall, addBotMessage, addToChatHistory, onChatEnded, chatHistory, handleError]);
  
  // Toggle minimizar
  const toggleMinimize = useCallback(() => {
    const newMinimized = !isMinimized;
    setIsMinimized(newMinimized);
    if (onMinimizeToggle) {
      onMinimizeToggle(newMinimized);
    }
  }, [isMinimized, onMinimizeToggle]);
  
  // Inicializar ao montar
  useEffect(() => {
    initChat();
  }, [initChat]);
  
  // Focar no input quando apropriado
  useEffect(() => {
    if (awaitingStudentCode && inputRef.current) {
      inputRef.current.focus();
    }
  }, [awaitingStudentCode]);
  
  // Componente de mensagem
  const MessageComponent = ({ message }) => {
    switch (message.type) {
      case 'bot':
        return (
          <div className="ucm-message bot">
            <div className="ucm-message-bubble">
              {message.content}
            </div>
            {message.options && message.options.length > 0 && (
              <div className="ucm-options-container">
                {message.options.map((option, index) => (
                  <button
                    key={index}
                    className="ucm-option-btn"
                    onClick={() => selectOption(option.value)}
                    disabled={isLoading}
                  >
                    {option.label}
                  </button>
                ))}
              </div>
            )}
          </div>
        );
        
      case 'user':
        return (
          <div className="ucm-message user">
            <div className="ucm-message-bubble">
              {message.content}
            </div>
          </div>
        );
        
      case 'student_info':
        return (
          <div className="ucm-message bot">
            <div className="ucm-message-bubble">
              {message.content}
            </div>
            <div className="ucm-student-info">
              <h4>📋 Informações do Estudante</h4>
              <p><strong>Nome:</strong> {message.student.name}</p>
              <p><strong>Código:</strong> {message.student.code}</p>
              {message.student.situacao_academica && (
                <p><strong>Situação Acadêmica:</strong> {message.student.situacao_academica}</p>
              )}
              {message.student.situacao_financeira && (
                <p><strong>Situação Financeira:</strong> {message.student.situacao_financeira}</p>
              )}
            </div>
          </div>
        );
        
      case 'error':
        return (
          <div className="ucm-message bot">
            <div className="ucm-message-bubble ucm-error">
              ❌ {message.content}
            </div>
          </div>
        );
        
      default:
        return null;
    }
  };
  
  return (
    <div className={`ucm-chatbot-react ${theme} ${className} ${isMinimized ? 'minimized' : ''}`} style={style}>
      {/* Header */}
      <div className="ucm-chatbot-header">
        <div className="ucm-chatbot-title">
          <h3>🎓 UCM Assistant</h3>
          <span>Assistente Virtual</span>
        </div>
        <div className="ucm-chatbot-actions">
          <button onClick={toggleMinimize} title={isMinimized ? 'Expandir' : 'Minimizar'}>
            {isMinimized ? '+' : '−'}
          </button>
        </div>
      </div>
      
      {!isMinimized && (
        <>
          {/* Messages */}
          <div className="ucm-chatbot-messages">
            {messages.map((message) => (
              <MessageComponent key={message.id} message={message} />
            ))}
            <div ref={messagesEndRef} />
          </div>
          
          {/* Loading */}
          {isLoading && (
            <div className="ucm-chatbot-loading">
              <div className="ucm-typing-indicator">
                <span></span>
                <span></span>
                <span></span>
              </div>
              <span>Digitando...</span>
            </div>
          )}
          
          {/* Input */}
          <div className="ucm-chatbot-input">
            <div className="ucm-input-group">
              <input
                ref={inputRef}
                type="text"
                value={messageInput}
                onChange={(e) => setMessageInput(e.target.value)}
                onKeyPress={(e) => e.key === 'Enter' && !isLoading && sendMessage()}
                placeholder="Digite sua mensagem..."
                disabled={isLoading || !awaitingStudentCode}
              />
              <button
                onClick={sendMessage}
                disabled={isLoading || !awaitingStudentCode || !messageInput.trim()}
              >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M2,21L23,12L2,3V10L17,12L2,14V21Z"/>
                </svg>
              </button>
            </div>
            
            <div className="ucm-action-buttons">
              <button onClick={goBack} disabled={!canGoBack || isLoading}>
                ⬅️ Voltar
              </button>
              <button onClick={restartChat} disabled={isLoading}>
                🔄 Reiniciar
              </button>
              <button onClick={endChat} disabled={isLoading}>
                🔚 Encerrar
              </button>
            </div>
          </div>
        </>
      )}
      
      <style jsx>{`
        .ucm-chatbot-react {
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
        
        .ucm-chatbot-react.minimized {
          height: auto;
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
          animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
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
        
        .ucm-message-bubble.ucm-error {
          background: #ffebee !important;
          color: #c62828 !important;
          border: 1px solid #f44336;
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
        
        .ucm-option-btn:hover:not(:disabled) {
          background: #2196f3;
          color: white;
          transform: translateY(-1px);
        }
        
        .ucm-option-btn:disabled {
          opacity: 0.6;
          cursor: not-allowed;
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
          animation: typing 1.4s infinite;
        }
        
        .ucm-typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
        .ucm-typing-indicator span:nth-child(3) { animation-delay: 0.4s; }
        
        @keyframes typing {
          0%, 60%, 100% { transform: translateY(0); }
          30% { transform: translateY(-8px); }
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
      `}</style>
    </div>
  );
};

export default UCMChatBotReact;

/*
Exemplo de uso:

import UCMChatBotReact from './UCMChatBotReact';

function App() {
  const handleMessageReceived = (data) => {
    console.log('Nova mensagem:', data);
  };

  const handleStudentInfo = (student) => {
    console.log('Informações do estudante:', student);
    // Integrar com estado da aplicação
  };

  const handleChatEnded = (history) => {
    console.log('Chat encerrado:', history);
    // Salvar no analytics
  };

  const handleError = (title, message) => {
    console.error('Erro no chat:', title, message);
    // Mostrar notificação de erro
  };

  return (
    <div className="App">
      <h1>Sistema UCM</h1>
      
      <UCMChatBotReact
        baseUrl="https://api.ucm.ac.mz/api/web-chatbot"
        userIdentifier="user@example.com"
        theme="ucm-theme"
        onMessageReceived={handleMessageReceived}
        onStudentInfoReceived={handleStudentInfo}
        onChatEnded={handleChatEnded}
        onError={handleError}
        className="meu-chatbot"
        style={{ position: 'fixed', bottom: '20px', right: '20px' }}
      />
    </div>
  );
}
*/