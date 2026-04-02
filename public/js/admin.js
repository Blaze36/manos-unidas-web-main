/**
 * Panel de Administración - ONG Manos Unidas
 * Gestión de donaciones, voluntarios y personas
 */

// Estado global
let currentSection = 'dashboard';
let dashboardData = null;

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    console.log('Panel de Administración - Inicializando...');
    
    setupTabs();
    setupPerfilDropdown();
    setupModals();
    
    // Cargar dashboard inicial
    loadDashboard();
});

/**
 * Configurar tabs de navegación
 */
function setupTabs() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const section = this.dataset.section;
            switchSection(section);
        });
    });
}

/**
 * Cambiar sección activa
 */
function switchSection(section) {
    currentSection = section;
    
    // Actualizar botones
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active', 'border-b-2', 'border-teal-700', 'text-teal-700');
        btn.classList.add('text-gray-600');
    });
    const activeBtn = document.querySelector(`[data-section="${section}"]`);
    activeBtn.classList.add('active', 'border-b-2', 'border-teal-700', 'text-teal-700');
    activeBtn.classList.remove('text-gray-600');
    
    // Ocultar todas las secciones
    document.querySelectorAll('.admin-section').forEach(sec => {
        sec.classList.add('hidden');
    });
    
    // Mostrar sección activa
    const activeSection = document.getElementById(`section-${section}`);
    activeSection.classList.remove('hidden');
    
    // Cargar datos según la sección
    switch(section) {
        case 'dashboard':
            loadDashboard();
            break;
        case 'donaciones':
            loadDonaciones();
            break;
        case 'voluntarios':
            loadVoluntarios();
            break;
        case 'personas':
            loadPersonas();
            break;
    }
}

/**
 * Cargar Dashboard
 */
async function loadDashboard() {
    try {
        const response = await API.getDashboard();
        
        if (response.success) {
            dashboardData = response.data;
            renderDashboard(dashboardData);
        }
    } catch (error) {
        console.error('Error al cargar dashboard:', error);
        showToast('Error al cargar estadísticas', 'error');
    }
}

/**
 * Renderizar Dashboard
 */
function renderDashboard(data) {
    // Actualizar stats desde data.estadisticas
    const stats = data.estadisticas || {};
    
    document.getElementById('stat-total-donaciones').textContent = stats.total_donaciones || 0;
    document.getElementById('stat-donaciones-recibidas').textContent = stats.donaciones_pendientes || 0;
    document.getElementById('stat-voluntarios-activos').textContent = stats.total_voluntarios || 0;
    document.getElementById('stat-total-personas').textContent = stats.total_personas || 0;
    
    // Renderizar actividad reciente (usar ultimas_donaciones)
    renderActividadReciente(data.ultimas_donaciones || []);
}

/**
 * Renderizar actividad reciente
 */
function renderActividadReciente(donaciones) {
    const container = document.getElementById('actividad-reciente');
    
    if (!donaciones || donaciones.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center py-4">No hay actividad reciente</p>';
        return;
    }
    
    container.innerHTML = donaciones.slice(0, 10).map(don => {
        const estadoPill = getEstadoPill(don.estado);
        return `
            <div class="flex items-center gap-3 p-3 hover:bg-gray-50 rounded-lg">
                <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-box-open text-blue-600"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-800">${don.nombre_completo}</p>
                    <p class="text-xs text-gray-500">${don.tipos || 'Donacion'} - ${estadoPill}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500">${formatearFecha(don.fecha_donacion)}</p>
                </div>
            </div>
        `;
    }).join('');
}

/**
 * Cargar Donaciones
 */
async function loadDonaciones() {
    try {
        const filtroEstado = document.getElementById('filtro-estado-donacion').value;
        const params = filtroEstado ? { estado: filtroEstado } : {};
        
        const response = await API.getDonaciones(params);
        
        if (response.success) {
            renderTablaDonaciones(response.data);
        }
    } catch (error) {
        console.error('Error al cargar donaciones:', error);
        showToast('Error al cargar donaciones', 'error');
    }
}

/**
 * Renderizar tabla de donaciones
 */
function renderTablaDonaciones(donaciones) {
    const tbody = document.getElementById('tabla-donaciones-body');
    
    if (!donaciones || donaciones.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-8 text-gray-500">No hay donaciones registradas</td></tr>';
        return;
    }
    
    tbody.innerHTML = donaciones.map(don => {
        const estadoPill = getEstadoPill(don.estado);
        const totalItems = don.total_items || 0;
        
        return `
            <tr>
                <td class="font-mono text-sm">#${don.id}</td>
                <td>
                    <div class="font-semibold text-gray-800">${don.nombre_completo || 'N/A'}</div>
                    <div class="text-xs text-gray-500">${don.email || ''}</div>
                </td>
                <td class="text-sm">${totalItems} articulo(s)</td>
                <td class="text-sm">${formatearFecha(don.fecha_donacion)}</td>
                <td>${estadoPill}</td>
                <td>
                    <button class="btn-cambiar-estado text-teal-600 hover:text-teal-800 mr-2" data-id="${don.id}" data-estado="${don.estado}">
                        <i class="fas fa-edit mr-1"></i> Cambiar
                    </button>
                </td>
            </tr>
        `;
    }).join('');
    
    // Agregar event listeners a botones
    document.querySelectorAll('.btn-cambiar-estado').forEach(btn => {
        btn.addEventListener('click', function() {
            abrirModalCambiarEstado(this.dataset.id, this.dataset.estado);
        });
    });
}

/**
 * Cargar Voluntarios
 */
async function loadVoluntarios() {
    try {
        const filtroEstado = document.getElementById('filtro-estado-voluntario').value;
        const params = filtroEstado ? { estado: filtroEstado } : {};
        
        const response = await API.getVoluntarios(params);
        
        if (response.success) {
            renderTablaVoluntarios(response.data);
        }
    } catch (error) {
        console.error('Error al cargar voluntarios:', error);
        showToast('Error al cargar voluntarios', 'error');
    }
}

/**
 * Renderizar tabla de voluntarios
 */
function renderTablaVoluntarios(voluntarios) {
    const tbody = document.getElementById('tabla-voluntarios-body');
    
    if (!voluntarios || voluntarios.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-8 text-gray-500">No hay voluntarios registrados</td></tr>';
        return;
    }
    
    tbody.innerHTML = voluntarios.map(vol => {
        const estadoPill = vol.estado === 'activo' ? 
            '<span class="pill pill-green">Activo</span>' : 
            '<span class="pill pill-gray">Inactivo</span>';
        
        return `
            <tr>
                <td class="font-mono text-sm">#${vol.id}</td>
                <td class="font-semibold text-gray-800">${vol.nombre_completo}</td>
                <td class="text-sm">${vol.email}</td>
                <td class="text-sm">${vol.telefono || 'N/A'}</td>
                <td class="text-sm">${vol.disponibilidad || 'N/A'}</td>
                <td>${estadoPill}</td>
                <td>
                    <button class="text-teal-600 hover:text-teal-800" title="Ver detalles">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

/**
 * Cargar Personas
 */
async function loadPersonas() {
    try {
        const response = await API.getPersonas();
        
        if (response.success) {
            renderTablaPersonas(response.data);
        }
    } catch (error) {
        console.error('Error al cargar personas:', error);
        showToast('Error al cargar personas', 'error');
    }
}

/**
 * Renderizar tabla de personas
 */
function renderTablaPersonas(personas) {
    const tbody = document.getElementById('tabla-personas-body');
    
    if (!personas || personas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-8 text-gray-500">No hay personas registradas</td></tr>';
        return;
    }
    
    tbody.innerHTML = personas.map(per => {
        const estadoPill = per.activo === 1 ? 
            '<span class="pill pill-green">Activo</span>' : 
            '<span class="pill pill-red">Inactivo</span>';
        
        return `
            <tr>
                <td class="font-mono text-sm">#${per.id}</td>
                <td class="font-semibold text-gray-800">${per.nombre_completo}</td>
                <td class="text-sm">${per.email}</td>
                <td class="text-sm">${per.telefono || 'N/A'}</td>
                <td class="text-sm">${per.ciudad || 'N/A'}</td>
                <td class="text-sm">${formatearFecha(per.fecha_registro)}</td>
                <td>${estadoPill}</td>
            </tr>
        `;
    }).join('');
}

/**
 * Modal para cambiar estado
 */
function setupModals() {
    const modal = document.getElementById('modal-cambiar-estado');
    const btnCerrar = document.getElementById('btn-cerrar-modal-estado');
    const form = document.getElementById('formulario-cambiar-estado');
    
    // Cerrar modal
    if (btnCerrar) {
        btnCerrar.addEventListener('click', () => {
            modal.classList.add('hidden');
        });
    }
    
    // Cerrar al hacer click fuera
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.add('hidden');
        }
    });
    
    // Manejar submit
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            await cambiarEstadoDonacion();
        });
    }
    
    // Refrescar botones
    document.getElementById('btn-refrescar-donaciones')?.addEventListener('click', loadDonaciones);
    document.getElementById('btn-refrescar-voluntarios')?.addEventListener('click', loadVoluntarios);
    document.getElementById('btn-refrescar-personas')?.addEventListener('click', loadPersonas);
    
    // Filtros
    document.getElementById('filtro-estado-donacion')?.addEventListener('change', loadDonaciones);
    document.getElementById('filtro-estado-voluntario')?.addEventListener('change', loadVoluntarios);
}

/**
 * Abrir modal para cambiar estado
 */
function abrirModalCambiarEstado(donacionId, estadoActual) {
    const modal = document.getElementById('modal-cambiar-estado');
    document.getElementById('modal-donacion-id').value = donacionId;
    document.getElementById('modal-nuevo-estado').value = estadoActual;
    modal.classList.remove('hidden');
}

/**
 * Cambiar estado de donación
 */
async function cambiarEstadoDonacion() {
    const donacionId = document.getElementById('modal-donacion-id').value;
    const nuevoEstado = document.getElementById('modal-nuevo-estado').value;
    
    try {
        const response = await API.updateDonacion(donacionId, { estado: nuevoEstado });
        
        if (response.success) {
            showToast('Estado actualizado correctamente', 'success');
            document.getElementById('modal-cambiar-estado').classList.add('hidden');
            loadDonaciones();
        }
    } catch (error) {
        console.error('Error al cambiar estado:', error);
        showToast(error.message || 'Error al actualizar estado', 'error');
    }
}

/**
 * Dropdown de perfil
 */
function setupPerfilDropdown() {
    const btn = document.getElementById('admin-perfil-btn');
    const menu = document.getElementById('admin-perfil-menu');
    
    if (btn && menu) {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            menu.classList.toggle('hidden');
        });
        
        document.addEventListener('click', () => {
            menu.classList.add('hidden');
        });
    }
}

/**
 * Utilidades
 */
function getEstadoPill(estado) {
    const pills = {
        'pendiente': '<span class="pill pill-yellow">Pendiente</span>',
        'confirmada': '<span class="pill pill-blue">Confirmada</span>',
        'recogida': '<span class="pill pill-blue">Recogida</span>',
        'entregada': '<span class="pill pill-green">Entregada</span>',
        'cancelada': '<span class="pill pill-red">Cancelada</span>'
    };
    return pills[estado] || '<span class="pill pill-gray">Desconocido</span>';
}

function formatearFecha(fecha) {
    if (!fecha) return 'N/A';
    const d = new Date(fecha);
    return d.toLocaleDateString('es-ES', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
        <span>${message}</span>
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideIn .35s ease reverse';
        setTimeout(() => toast.remove(), 350);
    }, 3000);
}