// reservaciones/assets/js/table-map.js

const tableMap = document.getElementById('tableMap');
const tables = document.querySelectorAll('.table-item');

// Make tables draggable
tables.forEach(table => {
    let isDragging = false;
    let currentX, currentY, initialX, initialY;
    let xOffset = 0, yOffset = 0;

    table.addEventListener('mousedown', dragStart);
    table.addEventListener('touchstart', dragStart);

    function dragStart(e) {
        if (e.type === 'touchstart') {
            initialX = e.touches[0].clientX - xOffset;
            initialY = e.touches[0].clientY - yOffset;
        } else {
            initialX = e.clientX - xOffset;
            initialY = e.clientY - yOffset;
        }

        if (e.target === table || table.contains(e.target)) {
            isDragging = true;
        }

        document.addEventListener('mousemove', drag);
        document.addEventListener('touchmove', drag);
        document.addEventListener('mouseup', dragEnd);
        document.addEventListener('touchend', dragEnd);
    }

    function drag(e) {
        if (isDragging) {
            e.preventDefault();

            if (e.type === 'touchmove') {
                currentX = e.touches[0].clientX - initialX;
                currentY = e.touches[0].clientY - initialY;
            } else {
                currentX = e.clientX - initialX;
                currentY = e.clientY - initialY;
            }

            xOffset = currentX;
            yOffset = currentY;

            setTranslate(currentX, currentY, table);
        }
    }

    function dragEnd(e) {
        if (isDragging) {
            // Update the position in the style
            const rect = table.getBoundingClientRect();
            const mapRect = tableMap.getBoundingClientRect();

            const newLeft = rect.left - mapRect.left;
            const newTop = rect.top - mapRect.top;

            table.style.left = newLeft + 'px';
            table.style.top = newTop + 'px';
            table.style.transform = 'none';

            xOffset = 0;
            yOffset = 0;
        }

        initialX = 0;
        initialY = 0;
        isDragging = false;

        document.removeEventListener('mousemove', drag);
        document.removeEventListener('touchmove', drag);
        document.removeEventListener('mouseup', dragEnd);
        document.removeEventListener('touchend', dragEnd);
    }

    function setTranslate(xPos, yPos, el) {
        el.style.transform = `translate3d(${xPos}px, ${yPos}px, 0)`;
    }
});

function saveLayout() {
    const positions = [];

    tables.forEach(table => {
        const id = table.dataset.id;
        const left = parseFloat(table.style.left) || 0;
        const top = parseFloat(table.style.top) || 0;

        positions.push({
            id: id,
            x: left,
            y: top
        });
    });

    // Send to server
    fetch('../api/update_table_positions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ positions: positions })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ Disposición guardada exitosamente');
            } else {
                alert('❌ Error: ' + data.error);
            }
        })
        .catch(error => {
            alert('❌ Error al guardar');
            console.error(error);
        });
}

function addNewTable() {
    const numero = prompt('Número de mesa:');
    const capacidad = prompt('Capacidad (personas):');

    if (numero && capacidad) {
        // TODO: Add API call to create new table
        alert('Función en desarrollo');
    }
}
