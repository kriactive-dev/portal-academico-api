<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Nota publicado</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            line-height: 1.6;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .header {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        
        .header .icon {
            font-size: 48px;
            margin-bottom: 10px;
            display: block;
        }
        
        .header .logo {
            max-width: 80px;
            height: auto;
            margin-bottom: 15px;
            border-radius: 8px;
            background: white;
            padding: 5px;
        }
        
        .content {
            padding: 40px 30px;
        }
        
        .greeting {
            font-size: 18px;
            color: #333;
            margin-bottom: 20px;
        }
        
        .publication-card {
            background: #f8f9fa;
            border-left: 5px solid #1e40af;
            padding: 25px;
            margin: 25px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .publication-title {
            font-size: 20px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 15px;
            line-height: 1.4;
        }
        
        .publication-description {
            color: #555;
            margin-bottom: 20px;
            text-align: justify;
        }
        
        .publication-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .meta-item {
            background: white;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 14px;
            color: #666;
            border: 1px solid #e0e0e0;
        }
        
        .meta-item strong {
            color: #333;
        }
        
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .cta-button:hover {
            transform: translateY(-2px);
            text-decoration: none;
            color: white;
        }
        
        .university-info {
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            color: white;
            padding: 20px;
            margin: 30px 0;
            border-radius: 8px;
            text-align: center;
        }
        
        .university-info h3 {
            margin: 0 0 10px 0;
            font-size: 18px;
        }
        
        .footer {
            background: #2c3e50;
            color: white;
            padding: 30px 20px;
            text-align: center;
            font-size: 14px;
        }
        
        .footer a {
            color: #1e40af;
            text-decoration: none;
        }
        
        .divider {
            height: 2px;
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            margin: 30px 0;
            border-radius: 1px;
        }
        
        @media (max-width: 600px) {
            .email-container {
                margin: 0;
                border-radius: 0;
            }
            
            .content {
                padding: 20px 15px;
            }
            
            .publication-card {
                padding: 20px 15px;
            }
            
            .publication-meta {
                flex-direction: column;
                gap: 10px;
            }
            
            .header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <img src="{{ config('app.url') }}/logo-ucm.png" alt="Logo UCM" class="logo">
            <h1>Nova Livro Disponível</h1>
            <p style="margin: 10px 0 0 0; opacity: 0.9; font-size: 16px;">
                Universidade Católica de Moçambique
            </p>
        </div>
        
        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Olá
            </div>
            
            <p style="color: #555; margin-bottom: 25px;">
                Temos um novo livro que foi adicionado no portal académico.
            </p>
            
            <!-- Publication Card -->
            <div class="publication-card">
                
                <a href="{{ config('app.frontend_url', 'http://localhost:3000') }}" class="cta-button">
                    📖 Ver Livro Completa
                </a>
            </div>
            
            <div class="divider"></div>
            
            <!-- University Info -->
            <div class="university-info">
                <h3>🎓 Universidade Católica de Moçambique</h3>
                <p style="margin: 0; opacity: 0.9;">
                    Esta publicação foi direcionada para você com base no seu perfil académico.
                </p>
            </div>
            
            <p style="color: #666; font-size: 14px; margin-top: 30px;">
                <strong>💡 Dica:</strong> Mantenha o seu perfil actualizado para receber notificações mais relevantes.
            </p>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p style="margin: 0 0 10px 0;">
                <strong>Portal Académico - Universidade Católica de Moçambique</strong>
            </p>
            <p style="margin: 0; opacity: 0.8;">
                Este email foi enviado automaticamente. Se não deseja mais receber estas notificações, 
                <a href="#">clique aqui para cancelar</a>.
            </p>
            <p style="margin: 15px 0 0 0; opacity: 0.6; font-size: 12px;">
                © {{ date('Y') }} Portal Académico UCM. Todos os direitos reservados.
            </p>
        </div>
    </div>
</body>
</html>