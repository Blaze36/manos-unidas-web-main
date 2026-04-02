/**
 * Script principal de ONG Manos Unidas
 * Maneja la carga de secciones y la lógica de la aplicación
 */

// Estado global de la aplicación
const AppState = {
    currentUser: null,
    tiposDonacion: [],
    isLoggedIn: false
};

/**
 * Cargar usuario desde localStorage
 */
function loadUserFromStorage() {
    const user = localStorage.getItem('manosunidas_user');
    if (user) {
        AppState.currentUser = JSON.parse(user);
        AppState.isLoggedIn = true;
        updateUIForLoggedInUser();
    }
}

/**
 * Guardar usuario en localStorage
 */
function saveUserToStorage(user) {
    localStorage.setItem('manosunidas_user', JSON.stringify(user));
    AppState.currentUser = user;
    AppState.isLoggedIn = true;
}

/**
 * Cerrar sesión
 */
function logout() {
    localStorage.removeItem('manosunidas_user');
    AppState.currentUser = null;
    AppState.isLoggedIn = false;
    showToast('Sesión cerrada', 'info');
    setTimeout(() => location.reload(), 1000);
}

/**
 * Actualizar UI para usuario logueado
 */
function updateUIForLoggedInUser() {
    if (AppState.isLoggedIn && AppState.currentUser) {
        const userName = AppState.currentUser.nombre || AppState.currentUser.nombre_completo;
        const userEmail = AppState.currentUser.email || '';
        console.log('Usuario logueado:', userName);
        
        // Obtener iniciales del nombre (primeras letras de las primeras dos palabras)
        const iniciales = userName.split(' ')
            .slice(0, 2)
            .map(word => word.charAt(0).toUpperCase())
            .join('');
        
        // Mostrar dropdown de perfil
        const perfilDropdown = document.getElementById('perfil-dropdown');
        if (perfilDropdown) {
            perfilDropdown.classList.remove('hidden');
            
            // Actualizar avatar con iniciales
            const perfilAvatar = document.getElementById('perfil-avatar');
            if (perfilAvatar) {
                perfilAvatar.textContent = iniciales;
            }
            
            // Actualizar nombre y email en el dropdown
            const perfilNombre = document.getElementById('perfil-nombre');
            const perfilEmail = document.getElementById('perfil-email');
            if (perfilNombre) perfilNombre.textContent = userName;
            if (perfilEmail) perfilEmail.textContent = userEmail;
        }
        
        // Ocultar paso de registro y mostrar formulario de donación
        const pasoRegistro = document.getElementById('donacion-paso-registro');
        const pasoFormulario = document.getElementById('donacion-paso-formulario');
        const mensajeBienvenida = document.getElementById('mensaje-bienvenida-donacion');
        
        if (pasoRegistro) pasoRegistro.classList.add('hidden');
        if (pasoFormulario) pasoFormulario.classList.remove('hidden');
        if (mensajeBienvenida) {
            mensajeBienvenida.textContent = `Bienvenido ${userName}! Selecciona los artículos que deseas donar:`;
        }
        
        // Ocultar CTA de voluntario en la sección de actividades
        const voluntarioCTA = document.getElementById('voluntario-cta');
        if (voluntarioCTA) {
            voluntarioCTA.classList.add('hidden');
        }
        
        // Ocultar la sección completa de registro
        const seccionRegistro = document.getElementById('registro');
        if (seccionRegistro) {
            seccionRegistro.classList.add('hidden');
        }
        
        // Ocultar el enlace de "Regístrate" en la navegación
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            if (link.getAttribute('href') === '#registro') {
                link.classList.add('hidden');
            }
        });
    }
}

/**
 * Mostrar toast/notificación
 */
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    const icon = type === 'success' ? 'fa-check-circle' : 
                 type === 'error' ? 'fa-exclamation-circle' : 
                 'fa-info-circle';
    
    toast.innerHTML = `
        <i class="fas ${icon}"></i>
        <span>${message}</span>
    `;
    
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 4000);
}

/**
 * Habilitar/deshabilitar campos de cantidad según checkbox
 */
function setupDonationCheckboxes() {
    const checkboxes = document.querySelectorAll('input[name="donation"]');
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const container = this.closest('.donation-item');
            const qtyField = container.querySelector('.qty-field');
            
            if (qtyField) {
                qtyField.disabled = !this.checked;
                if (this.checked) {
                    qtyField.focus();
                }
            }
        });
    });
}

/**
 * Manejar envío de formulario de donación
 */
async function handleDonationSubmit(event) {
    event.preventDefault();
    
    const form = event.target;
    const submitBtn = document.getElementById('btn-enviar-donacion');
    
    // Verificar que el usuario esté logueado
    if (!AppState.isLoggedIn || !AppState.currentUser) {
        showToast('Debes iniciar sesión o registrarte primero', 'error');
        return;
    }
    
    // Obtener artículos seleccionados
    const checkboxes = form.querySelectorAll('input[name="donation"]:checked');
    
    if (checkboxes.length === 0) {
        showToast('Selecciona al menos un artículo para donar', 'error');
        return;
    }
    
    // Preparar detalles
    const detalles = [];
    for (const checkbox of checkboxes) {
        const container = checkbox.closest('.donation-item');
        const qtyField = container.querySelector('.qty-field');
        const tipoCode = checkbox.value;
        
        // Buscar el ID del tipo de donación
        const tipo = AppState.tiposDonacion.find(t => t.codigo === tipoCode);
        if (!tipo) continue;
        
        const cantidad = parseFloat(qtyField.value) || 1;
        
        detalles.push({
            id_tipo_donacion: tipo.id,
            cantidad: cantidad,
            monto_lempiras: tipo.es_monetaria ? cantidad : null
        });
    }
    
    try {
        submitBtn.classList.add('btn-loading');
        submitBtn.disabled = true;
        
        // Crear la donación con el ID del usuario logueado
        const donacionData = {
            id_persona: AppState.currentUser.id,
            detalles: detalles,
            observaciones: `Donación desde formulario web`
        };
        
        const response = await API.createDonacion(donacionData);
        
        if (response.success) {
            showToast('¡Gracias por tu donación! Te contactaremos pronto.', 'success');
            form.reset();
            // Deshabilitar todos los campos de cantidad
            form.querySelectorAll('.qty-field').forEach(field => field.disabled = true);
        }
        
    } catch (error) {
        console.error('Error:', error);
        showToast(error.message || 'Error al procesar la donación', 'error');
    } finally {
        submitBtn.classList.remove('btn-loading');
        submitBtn.disabled = false;
    }
}

/**
 * Manejar envío de formulario de registro
 */
async function handleRegistroSubmit(event) {
    event.preventDefault();
    
    const form = event.target;
    const submitBtn = document.getElementById('btn-enviar-registro');
    
    // Validar campos requeridos
    const nombre = form.querySelector('#reg-nombre').value.trim();
    const email = form.querySelector('#reg-email').value.trim();
    const password = form.querySelector('#reg-password').value;
    const passwordConfirm = form.querySelector('#reg-password-confirm').value;
    const telefono = form.querySelector('#reg-telefono').value.trim();
    const ciudad = form.querySelector('#reg-ciudad').value.trim();
    const ayuda = form.querySelector('input[name="ayuda"]:checked');
    const disponibilidad = form.querySelector('#reg-disponibilidad').value;
    const privacidad = form.querySelector('#reg-privacidad').checked;
    
    if (!nombre || !email || !password || !passwordConfirm || !telefono || !ciudad || !ayuda || !disponibilidad) {
        showToast('Por favor completa todos los campos requeridos (*)', 'error');
        return;
    }
    
    if (password.length < 6) {
        showToast('La contraseña debe tener al menos 6 caracteres', 'error');
        return;
    }
    
    if (password !== passwordConfirm) {
        showToast('Las contraseñas no coinciden', 'error');
        return;
    }
    
    if (!privacidad) {
        showToast('Debes aceptar la política de privacidad', 'error');
        return;
    }
    
    try {
        submitBtn.classList.add('btn-loading');
        submitBtn.disabled = true;
        
        const tipoAyuda = ayuda.value;
        
        // Si es voluntario o ambos, registrar como voluntario
        if (tipoAyuda === 'voluntario' || tipoAyuda === 'ambos') {
            const voluntarioData = {
                nombre_completo: nombre,
                email: email,
                password: password,
                telefono: telefono,
                ciudad: ciudad,
                direccion: null,
                disponibilidad: disponibilidad,
                habilidades: form.querySelector('#reg-habilidades').value,
                como_conocio: form.querySelector('#reg-conocio').value,
                acepta_privacidad: true,
                recibe_newsletter: form.querySelector('#reg-newsletter')?.checked || false
            };
            
            const response = await API.createVoluntario(voluntarioData);
            
            if (response.success) {
                showToast('¡Registro exitoso! Bienvenido a Manos Unidas', 'success');
                
                // Auto-login después del registro
                const user = {
                    id: response.id_persona,
                    nombre_completo: nombre,
                    email: email
                };
                saveUserToStorage(user);
                updateUIForLoggedInUser();
                
                form.reset();
                
                // Redirigir a donaciones después de un momento
                setTimeout(() => {
                    window.location.hash = '#donaciones';
                }, 1500);
            }
        } else {
            // Solo donador - registrar como persona
            const personaData = {
                nombre_completo: nombre,
                email: email,
                password: password,
                telefono: telefono,
                ciudad: ciudad,
                acepta_privacidad: true,
                recibe_newsletter: form.querySelector('#reg-newsletter')?.checked || false
            };
            
            const response = await API.createPersona(personaData);
            
            if (response.success) {
                showToast('¡Registro exitoso! Ahora puedes hacer donaciones', 'success');
                
                // Auto-login después del registro
                const user = {
                    id: response.id,
                    nombre_completo: nombre,
                    email: email
                };
                saveUserToStorage(user);
                updateUIForLoggedInUser();
                
                form.reset();
                
                // Redirigir a donaciones después de un momento
                setTimeout(() => {
                    window.location.hash = '#donaciones';
                }, 1500);
            }
        }
        
    } catch (error) {
        console.error('Error:', error);
        showToast(error.message || 'Error al procesar el registro', 'error');
    } finally {
        submitBtn.classList.remove('btn-loading');
        submitBtn.disabled = false;
    }
}

/**
 * Cargar dashboard
 */
async function loadDashboard() {
    const updateTime = document.getElementById('dash-updated');
    if (updateTime) {
        updateTime.textContent = 'Cargando...';
    }
    
    try {
        const response = await API.getDashboard();
        
        if (response.success) {
            const data = response.data;
            
            // Actualizar KPIs
            document.getElementById('kpi-personas').textContent = 
                data.estadisticas.total_personas.toLocaleString();
            document.getElementById('kpi-voluntarios').textContent = 
                data.estadisticas.total_voluntarios.toLocaleString();
            document.getElementById('kpi-donaciones').textContent = 
                data.estadisticas.total_donaciones.toLocaleString();
            document.getElementById('kpi-lempiras').textContent = 
                'L ' + parseFloat(data.estadisticas.total_lempiras_donados).toLocaleString('es-HN', {minimumFractionDigits: 2});
            document.getElementById('kpi-beneficiarios').textContent = 
                data.estadisticas.total_beneficiarios.toLocaleString();
            document.getElementById('kpi-actividades').textContent = 
                data.estadisticas.actividades_activas.toLocaleString();
            document.getElementById('kpi-pendientes').textContent = 
                data.estadisticas.donaciones_pendientes.toLocaleString();
            document.getElementById('kpi-suscriptores').textContent = 
                data.estadisticas.total_suscriptores.toLocaleString();
            
            // Actualizar tabla de donaciones
            renderDonacionesTable(data.ultimas_donaciones);
            
            // Actualizar tabla de personas
            renderPersonasTable(data.ultimas_personas);
            
            // Actualizar gráficos
            renderDonacionesPorTipo(data.donaciones_por_tipo);
            renderDisponibilidadChart(data.voluntarios_por_disponibilidad);
            renderEstadosChart(data.donaciones_por_estado);
            renderNewsletterTable(data.ultimos_newsletter);
            
            if (updateTime) {
                const fecha = new Date(data.ultima_actualizacion);
                updateTime.textContent = `Actualizado: ${fecha.toLocaleTimeString('es-HN')}`;
            }
            
            showToast('Dashboard actualizado', 'success');
        }
        
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al cargar dashboard', 'error');
    }
}

/**
 * Renderizar tabla de donaciones
 */
function renderDonacionesTable(donaciones) {
    const tbody = document.getElementById('dash-tabla-donaciones');
    if (!tbody) return;
    
    if (!donaciones || donaciones.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-gray-400 py-6 text-sm">No hay donaciones registradas</td></tr>';
        return;
    }
    
    tbody.innerHTML = donaciones.map(d => {
        const fecha = new Date(d.fecha_donacion);
        const pillClass = d.estado === 'pendiente' ? 'pill-yellow' :
                         d.estado === 'confirmada' ? 'pill-blue' :
                         d.estado === 'entregada' ? 'pill-green' : 'pill-gray';
        
        return `
            <tr>
                <td class="py-2 px-2 text-sm">${d.nombre_completo}</td>
                <td class="py-2 px-2 text-xs text-gray-600">${d.tipos}</td>
                <td class="py-2 px-2 text-sm font-semibold">${d.total_items}</td>
                <td class="py-2 px-2"><span class="pill ${pillClass}">${d.estado}</span></td>
                <td class="py-2 px-2 text-xs text-gray-500">${fecha.toLocaleDateString('es-HN')}</td>
            </tr>
        `;
    }).join('');
}

/**
 * Renderizar tabla de personas
 */
function renderPersonasTable(personas) {
    const tbody = document.getElementById('dash-tabla-personas');
    if (!tbody) return;
    
    if (!personas || personas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-gray-400 py-6 text-sm">No hay personas registradas</td></tr>';
        return;
    }
    
    tbody.innerHTML = personas.map(p => {
        const fecha = new Date(p.fecha_registro);
        const pillClass = p.tipo === 'Voluntario' ? 'pill-blue' : 'pill-green';
        
        return `
            <tr>
                <td class="py-2 px-2 text-sm">${p.nombre_completo}</td>
                <td class="py-2 px-2 text-xs text-gray-600">${p.ciudad || 'N/A'}</td>
                <td class="py-2 px-2"><span class="pill ${pillClass}">${p.tipo}</span></td>
                <td class="py-2 px-2 text-xs text-gray-500">${fecha.toLocaleDateString('es-HN')}</td>
            </tr>
        `;
    }).join('');
}

/**
 * Renderizar gráfico de donaciones por tipo
 */
function renderDonacionesPorTipo(datos) {
    const container = document.getElementById('dash-chart-tipos');
    if (!container || !datos || datos.length === 0) return;
    
    const html = datos.map(d => {
        const porcentaje = datos.reduce((sum, item) => sum + parseInt(item.total_donaciones), 0);
        const pct = porcentaje > 0 ? Math.round((d.total_donaciones / porcentaje) * 100) : 0;
        
        return `
            <div class="bar-wrap">
                <div class="bar-label">${d.nombre}</div>
                <div class="bar-track">
                    <div class="bar-fill bg-teal-600" style="width: ${pct}%">${d.total_donaciones}</div>
                </div>
            </div>
        `;
    }).join('');
    
    container.innerHTML = html;
}

/**
 * Renderizar gráfico de disponibilidad
 */
function renderDisponibilidadChart(datos) {
    const container = document.getElementById('dash-chart-disponibilidad');
    if (!container || !datos || datos.length === 0) return;
    
    const labels = {
        'completa': 'Tiempo completo',
        'parcial': 'Tiempo parcial',
        'fines': 'Fines de semana',
        'eventos': 'Solo eventos'
    };
    
    const html = datos.map(d => {
        const total = datos.reduce((sum, item) => sum + parseInt(item.cantidad), 0);
        const pct = total > 0 ? Math.round((d.cantidad / total) * 100) : 0;
        
        return `
            <div class="bar-wrap">
                <div class="bar-label">${labels[d.disponibilidad] || d.disponibilidad}</div>
                <div class="bar-track">
                    <div class="bar-fill bg-blue-600" style="width: ${pct}%">${d.cantidad}</div>
                </div>
            </div>
        `;
    }).join('');
    
    container.innerHTML = html;
}

/**
 * Renderizar gráfico de estados
 */
function renderEstadosChart(datos) {
    const container = document.getElementById('dash-chart-estados');
    if (!container || !datos || datos.length === 0) return;
    
    const total = datos.reduce((sum, item) => sum + parseInt(item.cantidad), 0);
    
    const html = datos.map(d => {
        const pct = total > 0 ? Math.round((d.cantidad / total) * 100) : 0;
        const color = d.estado === 'pendiente' ? 'bg-yellow-500' :
                     d.estado === 'confirmada' ? 'bg-blue-500' :
                     d.estado === 'entregada' ? 'bg-green-500' : 'bg-gray-500';
        
        return `
            <div class="bar-wrap">
                <div class="bar-label capitalize">${d.estado}</div>
                <div class="bar-track">
                    <div class="bar-fill ${color}" style="width: ${pct}%">${d.cantidad}</div>
                </div>
            </div>
        `;
    }).join('');
    
    container.innerHTML = html;
}

/**
 * Renderizar tabla de newsletter
 */
function renderNewsletterTable(suscriptores) {
    const tbody = document.getElementById('dash-tabla-newsletter');
    if (!tbody) return;
    
    if (!suscriptores || suscriptores.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" class="text-center text-gray-400 py-6 text-sm">No hay suscriptores</td></tr>';
        return;
    }
    
    tbody.innerHTML = suscriptores.map(s => {
        const fecha = new Date(s.fecha_suscripcion);
        
        return `
            <tr>
                <td class="py-2 px-2 text-sm">${s.email}</td>
                <td class="py-2 px-2 text-xs">${s.vinculado}</td>
                <td class="py-2 px-2 text-xs text-gray-500">${fecha.toLocaleDateString('es-HN')}</td>
            </tr>
        `;
    }).join('');
}

/**
 * Cargar tipos de donación al inicio
 */
async function loadTiposDonacion() {
    try {
        const response = await API.getTiposDonacion();
        if (response.success) {
            AppState.tiposDonacion = response.data;
        }
    } catch (error) {
        console.error('Error al cargar tipos de donación:', error);
    }
}

/**
 * Inicialización al cargar la página
 */
document.addEventListener('DOMContentLoaded', async function() {
    // Cargar usuario guardado
    loadUserFromStorage();
    
    // Cargar tipos de donación
    await loadTiposDonacion();
    
    // Setup de checkboxes de donación
    setupDonationCheckboxes();
    
    // Event listeners de formularios
    const formDonacion = document.getElementById('formulario-donacion');
    if (formDonacion) {
        formDonacion.addEventListener('submit', handleDonationSubmit);
    }
    
    const formRegistro = document.getElementById('formulario-registro');
    if (formRegistro) {
        formRegistro.addEventListener('submit', handleRegistroSubmit);
    }
    
    // Smooth scroll para links de navegación
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                
                // Dashboard solo disponible en indexAdmin.php
                /*
                if (this.getAttribute('href') === '#dashboard') {
                    setTimeout(() => loadDashboard(), 500);
                }
                */
            }
        });
    });
    
    // Dashboard solo disponible en indexAdmin.php
    /*
    if (window.location.hash === '#dashboard') {
        setTimeout(() => loadDashboard(), 500);
    }
    */
    
    console.log('✅ ONG Manos Unidas - Sistema inicializado');
    
    // Event listeners adicionales
    setupModalLogin();
});

/**
 * Configurar modal de login
 */
function setupModalLogin() {
    const modal = document.getElementById('modal-login');
    const btnMostrarLogin = document.getElementById('btn-mostrar-login');
    const btnCerrarLogin = document.getElementById('btn-cerrar-login');
    const formLogin = document.getElementById('formulario-login');
    
    // Abrir modal
    if (btnMostrarLogin) {
        btnMostrarLogin.addEventListener('click', function() {
            modal.classList.remove('hidden');
        });
    }
    
    // Cerrar modal
    if (btnCerrarLogin) {
        btnCerrarLogin.addEventListener('click', function() {
            modal.classList.add('hidden');
        });
    }
    
    // Cerrar modal al hacer click fuera
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.classList.add('hidden');
        }
    });
    
    // Manejar formulario de login
    if (formLogin) {
        formLogin.addEventListener('submit', async function(event) {
            event.preventDefault();
            
            const email = document.getElementById('login-email').value.trim();
            const password = document.getElementById('login-password').value;
            
            if (!email || !password) {
                showToast('Por favor ingresa tu correo y contraseña', 'error');
                return;
            }
            
            const submitBtn = document.getElementById('btn-login');
            
            try {
                submitBtn.classList.add('btn-loading');
                submitBtn.disabled = true;
                
                const response = await API.login({ email, password });
                
                if (response.success) {
                    // Guardar usuario y actualizar UI
                    saveUserToStorage(response.user);
                    updateUIForLoggedInUser();
                    
                    // Verificar si es admin y redirigir
                    if (response.user.rol === 'admin') {
                        showToast('Bienvenido Administrador! Redirigiendo...', 'success');
                        setTimeout(() => {
                            window.location.href = 'indexAdmin.php';
                        }, 1500);
                        return;
                    }
                    
                    showToast('¡Bienvenido de vuelta!', 'success');
                    modal.classList.add('hidden');
                    formLogin.reset();
                }
                
            } catch (error) {
                console.error('Error:', error);
                showToast(error.message || 'Correo o contraseña incorrectos', 'error');
            } finally {
                submitBtn.classList.remove('btn-loading');
                submitBtn.disabled = false;
            }
        });
    }
    
    // Configurar dropdown de perfil
    setupPerfilDropdown();
}

/**
 * Configurar dropdown de perfil
 */
function setupPerfilDropdown() {
    const perfilBtn = document.getElementById('perfil-btn');
    const perfilMenu = document.getElementById('perfil-menu');
    
    if (perfilBtn && perfilMenu) {
        // Toggle dropdown al hacer click en el botón
        perfilBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            perfilMenu.classList.toggle('hidden');
        });
        
        // Cerrar dropdown al hacer click fuera
        document.addEventListener('click', function(e) {
            if (!perfilMenu.contains(e.target) && !perfilBtn.contains(e.target)) {
                perfilMenu.classList.add('hidden');
            }
        });
    }
}

// Hacer funciones globalmente accesibles
window.loadDashboard = loadDashboard;
window.logout = logout;