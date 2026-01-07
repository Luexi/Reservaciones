// bot-messenger/index.js
require('dotenv').config();
const express = require('express');
const bodyParser = require('body-parser');
const axios = require('axios');

const app = express();
const PORT = process.env.PORT || 3000;

// Messenger credentials
const PAGE_ACCESS_TOKEN = process.env.FB_PAGE_TOKEN;
const VERIFY_TOKEN = process.env.FB_VERIFY_TOKEN || 'rosa_mezcal_2026';

app.use(bodyParser.json());

// Webhook verification
app.get('/webhook', (req, res) => {
    const mode = req.query['hub.mode'];
    const token = req.query['hub.verify_token'];
    const challenge = req.query['hub.challenge'];

    if (mode && token === VERIFY_TOKEN) {
        console.log('Webhook verified');
        res.status(200).send(challenge);
    } else {
        res.sendStatus(403);
    }
});

// Webhook handler
app.post('/webhook', (req, res) => {
    const body = req.body;

    if (body.object === 'page') {
        body.entry.forEach(entry => {
            const webhookEvent = entry.messaging[0];
            console.log('Received event:', webhookEvent);

            const senderPsid = webhookEvent.sender.id;

            if (webhookEvent.message) {
                handleMessage(senderPsid, webhookEvent.message);
            } else if (webhookEvent.postback) {
                handlePostback(senderPsid, webhookEvent.postback);
            }
        });

        res.status(200).send('EVENT_RECEIVED');
    } else {
        res.sendStatus(404);
    }
});

// Conversational state (in production, use Redis or database)
const userStates = new Map();

async function handleMessage(senderPsid, receivedMessage) {
    const text = receivedMessage.text?.toLowerCase() || '';

    // Get or initialize user state
    let state = userStates.get(senderPsid) || { step: 0, data: {} };

    let response;

    // Simple menu-based conversation
    if (text.includes('hola') || text.includes('ayuda') || state.step === 0) {
        response = {
            text: `¡Hola! Bienvenido a Rosa Mezcal 🍹\n\n¿Qué te gustaría hacer?\n\n1️⃣ Hacer una reservación\n2️⃣ Ver horarios\n3️⃣ Hablar con un agente\n\nEscribe el número de tu opción.`
        };
        state.step = 1;
    } else if (state.step === 1) {
        if (text.includes('1') || text.includes('reserva')) {
            response = { text: '✅ Perfecto! ¿Para cuántas personas será la reserva?' };
            state.step = 2;
        } else if (text.includes('2') || text.includes('horario')) {
            response = { text: 'Nuestros horarios:\n🕐 Lunes-Jueves: 18:00 - 23:00\n🕐 Viernes-Domingo: 18:00 - 01:00' };
            state.step = 0;
        } else {
            response = { text: 'Por favor, escribe 1, 2 o 3 según lo que necesites.' };
        }
    } else if (state.step === 2) {
        const numPersonas = parseInt(text);
        if (numPersonas && numPersonas > 0 && numPersonas <= 20) {
            state.data.num_personas = numPersonas;
            response = { text: `Reserva para ${numPersonas} personas. ¿Para qué fecha? (formato: DD/MM/YYYY)` };
            state.step = 3;
        } else {
            response = { text: 'Por favor, indica un número válido de personas (1-20).' };
        }
    } else if (state.step === 3) {
        // Parse date (simplified)
        state.data.fecha = text; // In production, validate format
        response = { text: '¿A qué hora? (formato: HH:MM, ej: 19:30)' };
        state.step = 4;
    } else if (state.step === 4) {
        state.data.hora = text;
        response = { text: '¿Cuál es tu nombre completo?' };
        state.step = 5;
    } else if (state.step === 5) {
        state.data.nombre = text;
        response = { text: '¿Cuál es tu número de teléfono?' };
        state.step = 6;
    } else if (state.step === 6) {
        state.data.telefono = text;

        // Create reservation via API
        try {
            const apiResponse = await createReservation({
                ...state.data,
                origen: 'messenger',
                email: null
            });

            if (apiResponse.success) {
                response = {
                    text: `🎉 ¡Reservación confirmada!\n\n` +
                        `📅 Fecha: ${state.data.fecha}\n` +
                        `🕐 Hora: ${state.data.hora}\n` +
                        `👥 Personas: ${state.data.num_personas}\n` +
                        `📱 Código: #${apiResponse.reservation_id.substring(0, 8)}\n\n` +
                        `¡Te esperamos en Rosa Mezcal! 🍹`
                };
            } else {
                response = { text: `❌ Error: ${apiResponse.error}\n\nPor favor, intenta nuevamente o contacta al restaurante.` };
            }
        } catch (error) {
            console.error('API Error:', error);
            response = { text: '❌ Error al procesar tu reservación. Por favor, intenta más tarde.' };
        }

        // Reset state
        state = { step: 0, data: {} };
    } else {
        response = { text: 'Escribe "hola" para comenzar.' };
    }

    userStates.set(senderPsid, state);
    await callSendAPI(senderPsid, response);
}

async function handlePostback(senderPsid, receivedPostback) {
    const payload = receivedPostback.payload;

    // Handle postback actions
    let response = { text: `Postback recibido: ${payload}` };
    await callSendAPI(senderPsid, response);
}

async function callSendAPI(senderPsid, response) {
    const requestBody = {
        recipient: { id: senderPsid },
        message: response
    };

    try {
        await axios.post(
            `https://graph.facebook.com/v18.0/me/messages?access_token=${PAGE_ACCESS_TOKEN}`,
            requestBody
        );
        console.log('Message sent successfully');
    } catch (error) {
        console.error('Unable to send message:', error.response?.data || error.message);
    }
}

async function createReservation(data) {
    try {
        const response = await axios.post(
            'http://web/reservaciones/api/create_reservation.php',
            data
        );
        return response.data;
    } catch (error) {
        console.error('Reservation API error:', error);
        return { success: false, error: 'Error de conexión' };
    }
}

app.listen(PORT, () => {
    console.log(`Messenger bot listening on port ${PORT}`);
});
