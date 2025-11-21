// API ACCESS CONFIGURATION

const API_BASE_URL = 'http://localhost/structured-data-definitivo/public/api/v1';

// DOM element references
const schemaIdInput = document.getElementById('schema-id');
const loadSchemaBtn = document.getElementById('load-schema-btn');
const formContainer = document.getElementById('form-container');
const messageArea = document.getElementById('message-area');
const headerSearchInput = document.querySelector('.search-input');
const headerSearchButton = document.querySelector('.search-button');

let currentSchemaDefinition = null;
let currentItemId = null;

// ===================================================================
// MAIN FUNCTION: LOAD SCHEMA (GET /schema)
// ===================================================================
async function loadSchema(schemaId = null) {
    // Si no se proporciona schemaId, usar el del input principal
    const idToLoad = schemaId || schemaIdInput.value;
    
    if (!idToLoad) {
        showMessage('Por favor, introduce un ID de esquema válido.', 'error');
        return;
    }

    // Actualizar el input principal si se usó la búsqueda del header
    if (schemaId && schemaId !== schemaIdInput.value) {
        schemaIdInput.value = schemaId;
    }

    showMessage('Cargando esquema...', '');
    formContainer.innerHTML = '<p>Cargando esquema...</p>';

    const endpoint = `${API_BASE_URL}/schema/${idToLoad}`;

    try {
        const response = await fetch(endpoint);
        
        if (!response.ok) {
            const errorText = response.status === 404 ? 
                'Esquema no encontrado (Error 404).' : 
                `Error HTTP: ${response.status} ${response.statusText}`;
            throw new Error(errorText);
        }

        const schemaData = await response.json();
        currentSchemaDefinition = schemaData;

        showMessage(`✅ Esquema ID ${idToLoad} (${schemaData.label}) cargado correctamente.`, 'success');
        renderForm(schemaData);

        // Scroll suave a los resultados
        document.querySelector('.results-section').scrollIntoView({ 
            behavior: 'smooth',
            block: 'start'
        });

    } catch (error) {
        console.error('Error al cargar el esquema:', error);
        showMessage(`❌ Error: ${error.message}`, 'error');
        formContainer.innerHTML = '<p>Error al cargar el esquema. Verifica la consola para más detalles.</p>';
    }
}

// ===================================================================
// FUNCTION FOR DYNAMIC FORM GENERATION
// ===================================================================
function renderForm(schema) {
    formContainer.innerHTML = '';
    const form = document.createElement('form');
    form.id = 'dynamic-item-form';
    
    const title = document.createElement('h3');
    title.textContent = `Crear Nuevo Ítem: ${schema.label}`;
    title.style.color = '#2d3748';
    title.style.marginBottom = '20px';
    form.appendChild(title);

    const schemaIdHidden = document.createElement('input');
    schemaIdHidden.type = 'hidden';
    schemaIdHidden.name = 'schema_id';
    schemaIdHidden.value = schema.id;
    form.appendChild(schemaIdHidden);

    const properties = schema.properties || {};
    
    // Crear campos del formulario
    for (const key in properties) {
        if (properties.hasOwnProperty(key)) {
            const prop = properties[key];
            const propGroup = createPropertyInput(prop);
            form.appendChild(propGroup);
        }
    }

    const submitBtn = document.createElement('button');
    submitBtn.type = 'submit';
    submitBtn.textContent = '💾 Crear Nuevo Ítem';
    submitBtn.className = 'submit-button';
    submitBtn.style.background = 'linear-gradient(135deg, #48bb78 0%, #38a169 100%)';
    form.appendChild(submitBtn);

    formContainer.appendChild(form);
    
    // Crear sección CRUD
    createCRUDSection();
    
    // Event Listeners
    form.addEventListener('submit', handleFormSubmit);
}

function createPropertyInput(prop) {
    const propGroup = document.createElement('div');
    propGroup.className = 'property-group';
    
    const label = document.createElement('label');
    label.textContent = `${prop.label}`;
    
    if (prop.min_cardinality > 0) {
        label.innerHTML += ` <span style="color: #e53e3e;">*</span>`;
    }
    
    const typeInfo = prop.types[0]; 
    const input = document.createElement('input');

    input.dataset.typeId = typeInfo.id; 
    input.name = prop.label; 
    input.required = prop.min_cardinality > 0;
    input.placeholder = `Ingrese ${prop.label.toLowerCase()}`;

    if (typeInfo.type === 'Text' || typeInfo.type === 'Boolean') {
        input.type = 'text';
    } else if (typeInfo.type === 'Number') {
        input.type = 'number';
    } else if (typeInfo.type === 'Date') {
        input.type = 'date';
    } else if (typeInfo.type === 'Thing') {
        input.type = 'number'; 
        input.placeholder = `ID del ítem relacionado (${typeInfo.schema_label})`;
    } else {
        input.type = 'text'; 
    }

    propGroup.appendChild(label);
    propGroup.appendChild(input);

    if (prop.comment) {
        const comment = document.createElement('small');
        comment.textContent = prop.comment;
        propGroup.appendChild(comment);
    }
    
    return propGroup;
}

function createCRUDSection() {
    const crudSection = document.createElement('div');
    crudSection.id = 'crud-section';
    crudSection.innerHTML = `
        <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e2e8f0;">
            <h3 style="color: #2d3748; margin-bottom: 20px;">🔧 Operaciones CRUD</h3>
            
            <!-- Leer Ítem -->
            <div class="crud-operation">
                <h4>🔎 Leer Ítem Existente</h4>
                <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 15px;">
                    <input type="number" id="read-item-id" placeholder="ID del ítem" 
                           style="padding: 8px 12px; border: 1px solid #cbd5e0; border-radius: 4px; width: 120px;">
                    <button id="read-item-btn" class="crud-btn read-btn">Buscar Ítem</button>
                </div>
                <div id="item-read-output" style="display: none;"></div>
            </div>

            <!-- Actualizar Ítem -->
            <div class="crud-operation" id="update-section" style="display: none;">
                <h4>✏️ Actualizar Ítem</h4>
                <div id="update-form-container"></div>
            </div>

            <!-- Eliminar Ítem -->
            <div class="crud-operation">
                <h4>🗑️ Eliminar Ítem</h4>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="number" id="delete-item-id" placeholder="ID del ítem" 
                           style="padding: 8px 12px; border: 1px solid #cbd5e0; border-radius: 4px; width: 120px;">
                    <button id="delete-item-btn" class="crud-btn delete-btn">Eliminar Ítem</button>
                </div>
            </div>

            <!-- Listar Todos los Ítems -->
            <div class="crud-operation">
                <h4>📋 Listar Todos los Ítems</h4>
                <button id="list-items-btn" class="crud-btn list-btn">Listar Ítems</button>
                <div id="items-list-output" style="display: none; margin-top: 15px;"></div>
            </div>
        </div>
    `;

    formContainer.appendChild(crudSection);

    // Event Listeners para CRUD
    document.getElementById('read-item-btn').addEventListener('click', readItem);
    document.getElementById('delete-item-btn').addEventListener('click', deleteItem);
    document.getElementById('list-items-btn').addEventListener('click', listAllItems);
}

// ===================================================================
// CREATE - Crear Nuevo Ítem (POST /item)
// ===================================================================
async function handleFormSubmit(event) {
    event.preventDefault(); 
    showMessage('Creando nuevo ítem...', '');

    const formData = new FormData(event.target);
    const schemaId = formData.get('schema_id');
    const propertiesPayload = {};

    event.target.querySelectorAll('input[type="text"], input[type="number"], input[type="date"]').forEach(input => {
        if (input.name === 'schema_id' || !input.value.trim()) return; 

        propertiesPayload[input.name] = {
            "type": parseInt(input.dataset.typeId), 
            "values": [input.value.trim()] 
        };
    });

    const payload = {
        schema_id: parseInt(schemaId),
        properties: propertiesPayload
    };
    
    if (Object.keys(propertiesPayload).length === 0) {
        showMessage('❌ No hay valores para crear el Ítem.', 'error');
        return;
    }

    const endpoint = `${API_BASE_URL}/item`;

    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const result = await response.json();

        if (!response.ok) {
            const errorMsg = result.errors ? JSON.stringify(result.errors, null, 2) : result.message;
            throw new Error(`Error en la API (${response.status}): ${errorMsg}`);
        }

        currentItemId = result.id;
        showMessage(`🎉 Éxito: Ítem ID ${result.id} creado!`, 'success');
        
        // Actualizar campos de ID en las operaciones CRUD
        document.getElementById('read-item-id').value = result.id;
        document.getElementById('delete-item-id').value = result.id;

        // Mostrar el resultado
        const resultDisplay = document.createElement('div');
        resultDisplay.innerHTML = `
            <div style="background: #f0fff4; padding: 15px; border-radius: 6px; border: 1px solid #9ae6b4; margin-top: 15px;">
                <h4 style="color: #276749; margin-bottom: 10px;">✅ Ítem Creado Exitosamente</h4>
                <pre style="background: white; padding: 10px; border-radius: 4px; border: 1px solid #cbd5e0; font-size: 12px; overflow-x: auto;">${JSON.stringify(result, null, 2)}</pre>
            </div>
        `;
        formContainer.appendChild(resultDisplay);

    } catch (error) {
        console.error('Error al crear el Ítem:', error);
        showMessage(`❌ Fallo en la creación: ${error.message}`, 'error');
    }
}

// ===================================================================
// READ - Leer Ítem Existente (GET /item/{id})
// ===================================================================
async function readItem() {
    const itemId = document.getElementById('read-item-id').value;
    
    if (!itemId) {
        showMessage('❌ Por favor, introduce un ID de ítem válido.', 'error');
        return;
    }

    showMessage(`Buscando ítem ID ${itemId}...`, '');
    const outputArea = document.getElementById('item-read-output');

    const endpoint = `${API_BASE_URL}/item/${itemId}`;
    
    try {
        const response = await fetch(endpoint);

        if (!response.ok) {
            const errorText = response.status === 404 ? 
                'Ítem no encontrado (Error 404).' : 
                `Error HTTP: ${response.status} ${response.statusText}`;
            throw new Error(errorText);
        }

        const data = await response.json();
        
        outputArea.innerHTML = `
            <div style="background: #ebf8ff; padding: 15px; border-radius: 6px; border: 1px solid #90cdf4; margin-top: 10px;">
                <h4 style="color: #2b6cb0; margin-bottom: 10px;">✅ Ítem ID ${itemId} Encontrado</h4>
                <pre style="background: white; padding: 10px; border-radius: 4px; border: 1px solid #cbd5e0; font-size: 12px; overflow-x: auto;">${JSON.stringify(data, null, 2)}</pre>
                <button onclick="prepareUpdateForm(${itemId})" class="crud-btn update-btn" style="margin-top: 10px;">
                    ✏️ Actualizar Este Ítem
                </button>
            </div>
        `;
        outputArea.style.display = 'block';
        
        showMessage(`✅ Ítem ID ${itemId} encontrado correctamente.`, 'success');
        currentItemId = itemId;

    } catch (error) {
        console.error("Error al obtener el Ítem:", error);
        outputArea.innerHTML = `
            <div style="background: #fed7d7; padding: 15px; border-radius: 6px; border: 1px solid #feb2b2; margin-top: 10px;">
                <p style="color: #c53030;">❌ Error: ${error.message}</p>
            </div>
        `;
        outputArea.style.display = 'block';
        showMessage(`❌ Error al obtener el Ítem: ${error.message}`, 'error');
    }
}

// ===================================================================
// UPDATE - Preparar Formulario de Actualización
// ===================================================================
async function prepareUpdateForm(itemId) {
    const updateSection = document.getElementById('update-section');
    const updateFormContainer = document.getElementById('update-form-container');
    
    showMessage(`Preparando actualización para ítem ID ${itemId}...`, '');
    
    try {
        // Obtener datos actuales del ítem
        const response = await fetch(`${API_BASE_URL}/item/${itemId}`);
        if (!response.ok) throw new Error('No se pudo obtener el ítem para actualizar');
        
        const currentItem = await response.json();
        
        updateFormContainer.innerHTML = `
            <div style="background: #faf5ff; padding: 15px; border-radius: 6px; border: 1px solid #d6bcfa; margin-bottom: 15px;">
                <h5 style="color: #6b46c1; margin-bottom: 10px;">Actualizar Ítem ID ${itemId}</h5>
                <div id="update-fields-container"></div>
                <button onclick="updateItem(${itemId})" class="crud-btn update-btn" style="margin-top: 10px;">
                    💾 Guardar Cambios
                </button>
            </div>
        `;

        // Crear campos de actualización basados en el schema actual
        const fieldsContainer = document.getElementById('update-fields-container');
        const properties = currentSchemaDefinition.properties || {};
        
        for (const key in properties) {
            if (properties.hasOwnProperty(key)) {
                const prop = properties[key];
                const currentValue = currentItem.properties && currentItem.properties[prop.label] ? 
                    currentItem.properties[prop.label].values[0] : '';
                
                const fieldGroup = document.createElement('div');
                fieldGroup.style.marginBottom = '10px';
                fieldGroup.innerHTML = `
                    <label style="display: block; font-weight: 600; color: #4a5568; font-size: 13px; margin-bottom: 5px;">
                        ${prop.label}
                    </label>
                    <input type="text" 
                           id="update-${prop.label}" 
                           value="${currentValue}"
                           placeholder="${prop.label}"
                           style="width: 100%; padding: 8px; border: 1px solid #cbd5e0; border-radius: 4px; font-size: 13px;"
                           data-type-id="${prop.types[0].id}">
                `;
                fieldsContainer.appendChild(fieldGroup);
            }
        }
        
        updateSection.style.display = 'block';
        showMessage(`✅ Formulario de actualización listo para ítem ID ${itemId}`, 'success');
        
    } catch (error) {
        console.error('Error al preparar actualización:', error);
        showMessage(`❌ Error al preparar actualización: ${error.message}`, 'error');
    }
}

// ===================================================================
// UPDATE - Actualizar Ítem (PATCH /item/{id})
// ===================================================================
async function updateItem(itemId) {
    showMessage(`Actualizando ítem ID ${itemId}...`, '');
    
    const propertiesPayload = {};
    const updateFields = document.querySelectorAll('#update-fields-container input');
    
    updateFields.forEach(input => {
        const propName = input.id.replace('update-', '');
        if (input.value.trim()) {
            propertiesPayload[propName] = {
                "type": parseInt(input.dataset.typeId),
                "values": [input.value.trim()]
            };
        }
    });

    if (Object.keys(propertiesPayload).length === 0) {
        showMessage('❌ No hay cambios para actualizar.', 'error');
        return;
    }

    const payload = {
        properties: propertiesPayload
    };

    const endpoint = `${API_BASE_URL}/item/${itemId}`;
    
    try {
        const response = await fetch(endpoint, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const result = await response.json();

        if (!response.ok) {
            const errorMsg = result.errors ? JSON.stringify(result.errors, null, 2) : result.message;
            throw new Error(`Error en la API (${response.status}): ${errorMsg}`);
        }

        showMessage(`✅ Ítem ID ${itemId} actualizado correctamente.`, 'success');
        
        // Recargar los datos del ítem
        readItem();
        
    } catch (error) {
        console.error("Error al actualizar el Ítem:", error);
        showMessage(`❌ Fallo en la actualización: ${error.message}`, 'error');
    }
}

// ===================================================================
// DELETE - Eliminar Ítem (DELETE /item/{id})
// ===================================================================
async function deleteItem() {
    const itemId = document.getElementById('delete-item-id').value;
    
    if (!itemId) {
        showMessage('❌ Por favor, introduce un ID de ítem válido.', 'error');
        return;
    }
    
    if (!confirm(`¿Estás seguro de que quieres ELIMINAR el Ítem ID ${itemId}? Esta acción es irreversible.`)) {
        return;
    }

    showMessage(`Eliminando ítem ID ${itemId}...`, '');
    const endpoint = `${API_BASE_URL}/item/${itemId}`;
    
    try {
        const response = await fetch(endpoint, {
            method: 'DELETE',
        });

        if (response.status === 204 || response.ok) {
            showMessage(`✅ Ítem ID ${itemId} eliminado correctamente.`, 'success');
            
            // Limpiar campos
            document.getElementById('read-item-id').value = '';
            document.getElementById('delete-item-id').value = '';
            document.getElementById('item-read-output').style.display = 'none';
            document.getElementById('update-section').style.display = 'none';
            
        } else {
            const result = await response.json();
            const errorMsg = result.message || 'Error desconocido al eliminar.';
            throw new Error(errorMsg);
        }
        
    } catch (error) {
        console.error("Error al eliminar el Ítem:", error);
        showMessage(`❌ Fallo en la eliminación: ${error.message}`, 'error');
    }
}

// ===================================================================
// LIST - Listar Todos los Ítems (GET /items)
// ===================================================================
async function listAllItems() {
    showMessage('Cargando lista de ítems...', '');
    const outputArea = document.getElementById('items-list-output');

    const endpoint = `${API_BASE_URL}/items`;
    
    try {
        const response = await fetch(endpoint);

        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status} ${response.statusText}`);
        }

        const data = await response.json();
        
        if (data.length === 0) {
            outputArea.innerHTML = `
                <div style="background: #f7fafc; padding: 15px; border-radius: 6px; border: 1px solid #e2e8f0;">
                    <p style="color: #718096; text-align: center;">No hay ítems en la base de datos.</p>
                </div>
            `;
        } else {
            let itemsHTML = '<div style="max-height: 300px; overflow-y: auto;">';
            data.forEach(item => {
                itemsHTML += `
                    <div style="background: white; padding: 10px; margin-bottom: 8px; border-radius: 4px; border: 1px solid #e2e8f0;">
                        <div style="display: flex; justify-content: between; align-items: center;">
                            <div>
                                <strong>ID: ${item.id}</strong> - ${item.schema_label}
                            </div>
                            <button onclick="document.getElementById('read-item-id').value=${item.id}; readItem();" 
                                    class="crud-btn read-btn" style="padding: 4px 8px; font-size: 11px;">
                                Ver
                            </button>
                        </div>
                    </div>
                `;
            });
            itemsHTML += '</div>';
            outputArea.innerHTML = itemsHTML;
        }
        
        outputArea.style.display = 'block';
        showMessage(`✅ Se encontraron ${data.length} ítems.`, 'success');

    } catch (error) {
        console.error("Error al listar ítems:", error);
        outputArea.innerHTML = `
            <div style="background: #fed7d7; padding: 15px; border-radius: 6px; border: 1px solid #feb2b2;">
                <p style="color: #c53030;">❌ Error: ${error.message}</p>
            </div>
        `;
        outputArea.style.display = 'block';
        showMessage(`❌ Error al listar ítems: ${error.message}`, 'error');
    }
}

// ===================================================================
// HEADER SEARCH FUNCTIONALITY
// ===================================================================
function handleHeaderSearch() {
    const searchValue = headerSearchInput.value.trim();
    
    if (!searchValue) {
        showMessage('Por favor, introduce un término de búsqueda.', 'error');
        return;
    }

    // Limpiar el input de búsqueda después de capturar el valor
    headerSearchInput.value = '';

    // Determinar si es un ID numérico o un nombre de esquema
    if (/^\d+$/.test(searchValue)) {
        // Es un ID numérico - cargar esquema directamente
        loadSchema(searchValue);
    } else {
        // Es un nombre de esquema - buscar en los esquemas populares
        searchSchemaByName(searchValue);
    }
}

function searchSchemaByName(schemaName) {
    const normalizedSearch = schemaName.toLowerCase().trim();
    
    // Buscar en los esquemas populares
    const popularSchemas = [
        { id: '132', name: 'singlefamilyresidence', label: 'SingleFamilyResidence' },
        { id: '128', name: 'person', label: 'Person' },
        { id: '127', name: 'organization', label: 'Organization' },
        { id: '134', name: 'product', label: 'Product' }
    ];

    const foundSchema = popularSchemas.find(schema => 
        schema.name.includes(normalizedSearch) || 
        schema.label.toLowerCase().includes(normalizedSearch)
    );

    if (foundSchema) {
        showMessage(`Encontrado: ${foundSchema.label} (ID: ${foundSchema.id})`, 'success');
        loadSchema(foundSchema.id);
    } else {
        showMessage(`No se encontró el esquema "${schemaName}". Prueba con IDs: 132, 128, 127, 134`, 'error');
    }
}

// ===================================================================
// HELPER FUNCTIONS
// ===================================================================
function showMessage(message, type) {
    messageArea.textContent = message;
    messageArea.className = type ? `message-area ${type}` : 'message-area';
}

// ===================================================================
// EVENT LISTENERS
// ===================================================================
document.addEventListener('DOMContentLoaded', function() {
    // Botón principal de carga
    loadSchemaBtn.addEventListener('click', () => loadSchema());
    
    // Botones de esquemas populares
    document.querySelectorAll('.schema-load-btn').forEach(button => {
        button.addEventListener('click', function() {
            const schemaId = this.getAttribute('data-id');
            loadSchema(schemaId);
        });
    });
    
    // Enter en el input principal
    schemaIdInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            loadSchema();
        }
    });

    // Búsqueda del header - botón
    headerSearchButton.addEventListener('click', handleHeaderSearch);

    // Búsqueda del header - Enter
    headerSearchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            handleHeaderSearch();
        }
    });

    // Placeholder dinámico para la búsqueda del header
    headerSearchInput.placeholder = 'Buscar por ID (132) o nombre (Person)...';
});