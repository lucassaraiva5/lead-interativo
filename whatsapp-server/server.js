const express = require('express');
const { Client, LocalAuth, MessageMedia } = require('whatsapp-web.js');
const qrcode = require('qrcode');
const qrcodeTerminal = require('qrcode-terminal');
const http = require('http');
const socketIO = require('socket.io');
const cors = require('cors');
const fetch = (...args) => import('node-fetch').then(({default: fetch}) => fetch(...args));

// Bot agora responde a qualquer número

const app = express();
app.use(cors());
app.use(express.json({ limit: "50mb" }));
app.use(express.urlencoded({ limit: "50mb", extended: true }));

const server = http.createServer(app);
const io = socketIO(server, {
    cors: {
        origin: "http://localhost:8000", // Seu domínio Laravel
        methods: ["GET", "POST"]
    }
});

const client = new Client({
    authStrategy: new LocalAuth(),
    puppeteer: {
        args: ['--no-sandbox']
    }
});

let qrCodeData = null;
let connectionStatus = 'disconnected';

client.on('qr', async (qr) => {
    console.log('\n\nQR Code received. Scan it with your WhatsApp app:\n');
    
    // Gerar QR code no terminal
    qrcodeTerminal.generate(qr, { small: true });
    
    try {
        // Gerar QR code para a interface web também
        qrCodeData = await qrcode.toDataURL(qr);
        io.emit('qr', qrCodeData);
        connectionStatus = 'qr_received';
        
        console.log('\nAguardando leitura do QR Code...');
    } catch (err) {
        console.error('Error generating QR code:', err);
    }
});

client.on('ready', async () => {
    console.log('\n=================================');
    console.log('🟢 WhatsApp conectado com sucesso!');
    console.log('📱 Bot pronto para receber mensagens de qualquer número');
    console.log('=================================\n');
    
    connectionStatus = 'connected';
    io.emit('ready');
});

client.on('authenticated', () => {
    console.log('Authenticated');
    connectionStatus = 'authenticated';
    io.emit('authenticated');
});

client.on('auth_failure', () => {
    console.log('Auth failure');
    connectionStatus = 'auth_failure';
    io.emit('auth_failure');
});

client.on('disconnected', () => {
    console.log('Client was disconnected');
    connectionStatus = 'disconnected';
    io.emit('disconnected');
});

// Receber mensagem do WhatsApp
client.on('message', async msg => {
    console.log('\n🔔 Mensagem recebida de:', msg.from);
    console.log('📝 Conteúdo:', msg.body);
    
    try {
        let mediaUrl = null;
        let mediaData = null;

        if (msg.hasMedia) {
            console.log('📎 Processando mídia...');
            const mediaData = await msg.downloadMedia();
            if (mediaData) {
                mediaUrl = `data:${mediaData.mimetype};base64,${mediaData.data.replace(/\s/g, '')}`;
            }
        }

        const webhookData = {
            data: {
                from: msg.from,
                to: msg.to,
                body: msg.body,
                media: mediaUrl,
                received_at: new Date().toISOString()
            }
        };

        console.log('Enviando para o Laravel:', {
            from: webhookData.data.from,
            body: webhookData.data.body,
            hasMedia: !!mediaUrl
        });

        const response = await fetch('https://whats.ajudabr.com/webhook', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(webhookData)
        });
        
        const responseData = await response.text();
        console.log('Resposta do Laravel:', response.status, responseData);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}, response: ${responseData}`);
        }
    } catch (error) {
        console.error('Error sending message to Laravel:', error);
        console.error('Error details:', error.message);
    }
});

// Rota para obter o status atual
app.get('/status', (req, res) => {
    res.json({ status: connectionStatus });
});

// Rota para enviar mensagem
app.post('/send-message', async (req, res) => {
    const { number, message, image } = req.body;
    
    if (!number) {
        return res.status(400).json({ success: false, message: 'Number is required' });
    }
    
    // Formatar número para o formato do WhatsApp (adicionar @c.us se não tiver)
    const formattedNumber = number.includes('@c.us') ? number : number + '@c.us';
    
    try {
        if (image) {
            // Se houver imagem em base64, criar objeto MessageMedia
            const messageMedia = new MessageMedia('image/jpeg', image.split(',')[1], 'image.jpg');
            await client.sendMessage(formattedNumber, messageMedia, { caption: message });
        } else {
            // Se não houver imagem, enviar apenas texto
            await client.sendMessage(formattedNumber, message);
        }
        console.log(`📤 Mensagem enviada para ${formattedNumber}:`, message);
        res.json({ success: true, message: 'Message sent' });
    } catch (error) {
        console.error('❌ Erro ao enviar mensagem:', error.message);
        res.status(500).json({ success: false, message: error.message });
    }
});

// Rota para desconectar
app.post('/logout', (req, res) => {
    client.destroy();
    res.json({ success: true, message: 'Disconnected' });
});

// Inicializar o cliente WhatsApp
client.initialize();

const PORT = process.env.PORT || 4004;
server.listen(PORT, () => {
    console.log(`Server is running on port ${PORT}`);
});