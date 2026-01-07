// reservaciones/assets/js/booking.js

const API_BASE = './api';

// Form elements
const form = document.getElementById('reservationForm');
const fechaInput = document.getElementById('fecha');
const horaSelect = document.getElementById('hora');
const numPersonasSelect = document.getElementById('num_personas');
const comentariosTextarea = document.getElementById('comentarios');
const charCount = document.querySelector('.char-count');
const submitBtn = document.getElementById('submitBtn');
const loading = document.getElementById('loading');
const toast = document.getElementById('toast');

// Set minimum date to tomorrow
const tomorrow = new Date();
tomorrow.setDate(tomorrow.getDate() + 1);
fechaInput.min = tomorrow.toISOString().split('T')[0];

// Character counter
comentariosTextarea?.addEventListener('input', (e) => {
    const count = e.target.value.length;
    charCount.textContent = `${count}/500`;
});

// Check availability when date or num_personas changes
fechaInput.addEventListener('change', checkAvailability);
numPersonasSelect.addEventListener('change', checkAvailability);

async function checkAvailability() {
    const fecha = fechaInput.value;
    const numPersonas = numPersonasSelect.value;

    if (!fecha || !numPersonas) {
        horaSelect.disabled = true;
        horaSelect.innerHTML = '<option value="">Selecciona fecha y personas primero</option>';
        return;
    }

    // Generate time slots (example: 18:00 - 23:00, every 30 minutes)
    const timeSlots = generateTimeSlots('18:00', '23:00', 30);

    horaSelect.disabled = true;
    horaSelect.innerHTML = '<option value="">Verificando disponibilidad...</option>';

    try {
        // Check availability for each time slot
        const availabilityPromises = timeSlots.map(async (time) => {
            const response = await fetch(`${API_BASE}/check_availability.php?fecha=${fecha}&hora=${time}&num_personas=${numPersonas}`);
            const data = await response.json();
            return { time, available: data.available, count: data.tables_count };
        });

        const results = await Promise.all(availabilityPromises);

        // Populate time select
        horaSelect.innerHTML = '<option value="">Selecciona una hora</option>';

        results.forEach(result => {
            if (result.available) {
                const option = document.createElement('option');
                option.value = result.time;
                option.textContent = `${result.time} (${result.count} mesa${result.count > 1 ? 's' : ''} disponible${result.count > 1 ? 's' : ''})`;
                horaSelect.appendChild(option);
            }
        });

        horaSelect.disabled = false;

        // Show status
        const availableSlots = results.filter(r => r.available).length;
        const statusDiv = document.getElementById('availability-status');

        if (availableSlots === 0) {
            statusDiv.className = 'availability-status unavailable';
            statusDiv.textContent = '❌ No hay mesas disponibles para esta fecha';
        } else if (availableSlots <= 3) {
            statusDiv.className = 'availability-status limited';
            statusDiv.textContent = '⚠️ Pocas mesas disponibles';
        } else {
            statusDiv.className = 'availability-status available';
            statusDiv.textContent = '✅ Buena disponibilidad';
        }

    } catch (error) {
        showToast('Error al verificar disponibilidad', 'error');
        console.error(error);
    }
}

function generateTimeSlots(start, end, intervalMinutes) {
    const slots = [];
    let [startHour, startMin] = start.split(':').map(Number);
    let [endHour, endMin] = end.split(':').map(Number);

    let currentHour = startHour;
    let currentMin = startMin;

    while (currentHour < endHour || (currentHour === endHour && currentMin <= endMin)) {
        const timeString = `${String(currentHour).padStart(2, '0')}:${String(currentMin).padStart(2, '0')}`;
        slots.push(timeString);

        currentMin += intervalMinutes;
        if (currentMin >= 60) {
            currentHour++;
            currentMin -= 60;
        }
    }

    return slots;
}

// Form submission
form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = {
        nombre: document.getElementById('nombre').value,
        telefono: document.getElementById('telefono').value,
        email: document.getElementById('email').value || null,
        num_personas: parseInt(document.getElementById('num_personas').value),
        fecha: document.getElementById('fecha').value,
        hora: document.getElementById('hora').value,
        ocasion_especial: document.getElementById('ocasion').value || null,
        comentarios: document.getElementById('comentarios').value || null,
        origen: 'web'
    };

    // Show loading
    loading.classList.remove('hidden');
    submitBtn.disabled = true;

    try {
        const response = await fetch(`${API_BASE}/create_reservation.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (result.success) {
            showToast('¡Reservación creada exitosamente! 🎉', 'success');

            // Redirect to confirmation page after 2 seconds
            setTimeout(() => {
                window.location.href = `confirmation.php?id=${result.reservation_id}`;
            }, 2000);
        } else {
            showToast('Error: ' + result.error, 'error');
            submitBtn.disabled = false;
        }

    } catch (error) {
        showToast('Error al procesar la reservación', 'error');
        console.error(error);
        submitBtn.disabled = false;
    } finally {
        loading.classList.add('hidden');
    }
});

function showToast(message, type = 'success') {
    toast.textContent = message;
    toast.className = `toast ${type} show`;

    setTimeout(() => {
        toast.classList.remove('show');
    }, 4000);
}
