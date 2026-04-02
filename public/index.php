<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ONG Manos Unidas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .marquee {
            white-space: nowrap;
            overflow: hidden;
            box-sizing: border-box;
            animation: marquee 15s linear infinite;
        }
        @keyframes marquee {
            0%   { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
        .donation-item { transition: all 0.3s ease; }
        .donation-item:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .toast {
            position: fixed; bottom: 24px; right: 24px; z-index: 9999;
            min-width: 300px; max-width: 420px;
            padding: 16px 20px; border-radius: 10px;
            box-shadow: 0 8px 24px rgba(0,0,0,.18);
            display: flex; align-items: center; gap: 12px;
            animation: slideIn .35s ease;
        }
        @keyframes slideIn { from { transform: translateY(80px); opacity:0; } to { transform: translateY(0); opacity:1; } }
        .toast.success { background:#d1fae5; border-left:5px solid #059669; color:#065f46; }
        .toast.error   { background:#fee2e2; border-left:5px solid #dc2626; color:#991b1b; }
        .toast.info    { background:#dbeafe; border-left:5px solid #2563eb; color:#1e3a8a; }
        .btn-loading { opacity:.7; pointer-events:none; }
    </style>
</head>
<body class="bg-gray-100 font-sans">

<!-- TOAST CONTAINER -->
<div id="toast-container"></div>

<!-- HEADER -->
<header class="bg-teal-700 text-white py-6">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between">
            <div class="flex-1"></div>
            <div class="text-center flex-1">
                <img src="https://www.obispadodeibiza.es/wp-content/uploads/2020/09/Manos-Unidas-rectangular-e1600849373647.png"
                     alt="Logo ONG" class="mx-auto h-24 mb-4">
                <h1 class="text-3xl md:text-4xl font-bold mb-2">Bienvenidos a ONG Manos Unidas</h1>
                <p class="text-lg md:text-xl">Comprometidos con ayudar a quienes más lo necesitan</p>
                <p class="text-lg md:text-xl">Donde tu donación hace la diferencia</p>
            </div>
            <div class="flex-1 flex justify-end">
                <!-- Boton de usuario logueado -->
                <div id="user-menu" class="hidden">
                    <div class="text-right">
                        <p class="text-sm opacity-90 mb-1">Bienvenido</p>
                        <p id="user-name-display" class="font-semibold mb-2"></p>
                        <button onclick="logout()" class="bg-teal-900 hover:bg-red-600 text-white text-sm px-4 py-2 rounded-lg transition duration-300 flex items-center gap-2">
                            <i class="fas fa-sign-out-alt"></i> Cerrar sesión
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- NAVIGATION -->
<nav class="bg-teal-800 text-white sticky top-0 z-10 shadow-lg">
    <div class="container mx-auto px-4">
        <div class="flex flex-wrap justify-center">
            <a href="#inicio" class="nav-link px-4 py-3 hover:bg-teal-600 transition duration-300">Inicio</a>
            <a href="#donaciones" class="nav-link px-4 py-3 hover:bg-teal-600 transition duration-300">Donaciones</a>
            <a href="#actividades" class="nav-link px-4 py-3 hover:bg-teal-600 transition duration-300">Actividades</a>
            <a href="#afectados" class="nav-link px-4 py-3 hover:bg-teal-600 transition duration-300">Afectados</a>
            <a href="#mision-vision" class="nav-link px-4 py-3 hover:bg-teal-600 transition duration-300">Misión y Visión</a>
            <a href="#registro" class="nav-link px-4 py-3 hover:bg-teal-600 transition duration-300">Regístrate</a>
            
            <!-- Perfil con dropdown (solo visible si está logueado) -->
            <div id="perfil-dropdown" class="relative hidden">
                <button id="perfil-btn" class="px-4 py-3 hover:bg-teal-600 transition duration-300 flex items-center gap-2">
                    <div id="perfil-avatar" class="w-8 h-8 rounded-full bg-teal-600 flex items-center justify-center text-sm font-bold border-2 border-white">
                        --
                    </div>
                    <span>Perfil</span>
                    <i class="fas fa-chevron-down text-xs"></i>
                </button>
                
                <!-- Dropdown menu -->
                <div id="perfil-menu" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl border border-gray-200 overflow-hidden">
                    <div class="bg-teal-50 px-4 py-3 border-b border-gray-200">
                        <p class="text-sm text-gray-600">Sesión iniciada como:</p>
                        <p id="perfil-nombre" class="font-semibold text-gray-800 truncate"></p>
                        <p id="perfil-email" class="text-xs text-gray-500 truncate"></p>
                    </div>
                    <div class="py-2">
                        <button onclick="logout()" class="w-full text-left px-4 py-2 hover:bg-gray-100 transition duration-200 flex items-center gap-3 text-gray-700">
                            <i class="fas fa-sign-out-alt text-red-500"></i>
                            <span>Cerrar sesión</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- MAIN -->
<main class="container mx-auto px-4 py-8">

    <!-- INICIO -->
    <section id="inicio" class="mb-16 bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl md:text-3xl font-bold text-teal-800 mb-4">¿Quiénes somos?</h2>
        <div class="grid md:grid-cols-2 gap-8 items-center">
            <div>
                <p class="text-gray-700 mb-4">Somos una organización sin fines de lucro especializada en ayudar y brindar apoyo a cada persona que lo necesite a través de donaciones. Recuerda que tú también puedes ser parte de nosotros, regístrate y ayúdanos a cambiar miles de vidas.</p>
                <p class="text-gray-700 mb-6">¡Tu ayuda puede cambiar miles de vidas! Explora nuestro sitio para conocer cómo puedes colaborar, recuerda que una pequeña donación hace una gran diferencia.</p>
                <div class="bg-teal-100 border-l-4 border-teal-500 p-4 mb-6">
                    <p class="font-semibold text-teal-800">"Nadie es tan pobre que no pueda dar, ni tan rico que no pueda recibir"</p>
                </div>
            </div>
            <div class="rounded-lg overflow-hidden shadow-lg">
                <img src="https://images.unsplash.com/photo-1521791055366-0d553872125f?auto=format&fit=crop&w=1470&q=80"
                     alt="Voluntarios ayudando" class="w-full h-auto">
            </div>
        </div>
        <div class="mt-6 bg-teal-700 text-white py-2 px-4 rounded">
            <div class="marquee">
                <span class="text-lg font-semibold">¡Recuerda registrarte y ser parte de nuestra gran comunidad desde cualquier parte del mundo!</span>
            </div>
        </div>
    </section>

    <!-- DONACIONES -->
    <section id="donaciones" class="mb-16 bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl md:text-3xl font-bold text-teal-800 mb-6">Haz tu Donación</h2>
        
        <!-- Paso 1: Registro o Login (solo visible si NO está logueado) -->
        <div id="donacion-paso-registro">
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                <p class="text-gray-700 mb-4">Para hacer una donación, primero debes registrarte o iniciar sesión si ya tienes cuenta.</p>
            </div>
            
            <div class="grid md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6 text-center">
                    <i class="fas fa-user-plus text-4xl text-teal-600 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">¿Primera vez?</h3>
                    <p class="text-gray-600 mb-6">Completa el formulario de registro con tus datos.</p>
                    <a href="#registro" class="bg-teal-700 hover:bg-teal-800 text-white font-bold py-3 px-8 rounded-lg shadow-lg transition duration-300 transform hover:scale-105 inline-block">
                        <i class="fas fa-user-check mr-2"></i> Ir a Registro
                    </a>
                </div>
                
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6 text-center">
                    <i class="fas fa-sign-in-alt text-4xl text-blue-600 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">¿Ya tienes cuenta?</h3>
                    <p class="text-gray-600 mb-6">Inicia sesión con tu correo electrónico.</p>
                    <button type="button" id="btn-mostrar-login" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg shadow-lg transition duration-300 transform hover:scale-105">
                        <i class="fas fa-envelope mr-2"></i> Iniciar Sesión
                    </button>
                </div>
            </div>
        </div>

        <!-- Paso 2: Formulario de Donación (solo visible después de login/registro) -->
        <div id="donacion-paso-formulario" class="hidden">
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
                <p class="text-gray-700">
                    <i class="fas fa-check-circle text-green-600 mr-2"></i>
                    <span id="mensaje-bienvenida-donacion">Bienvenido! Selecciona los artículos que deseas donar:</span>
                </p>
            </div>

            <p class="text-gray-700 mb-8">Tu contribución nos ayuda a continuar con nuestra labor. Selecciona los artículos que deseas donar:</p>

            <form id="formulario-donacion" class="mb-8" novalidate>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">

                <!-- Alimentos -->
                <div class="donation-item bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                    <div class="flex items-center mb-3">
                        <input type="checkbox" id="chk-alimentos" name="donation" value="alimentos" class="mr-3 h-5 w-5 text-teal-600">
                        <label for="chk-alimentos" class="font-semibold text-gray-800">Alimentos no perecederos</label>
                    </div>
                    <p class="text-gray-600 text-sm mb-3">Arroz, frijoles, pasta, aceite, azúcar, harina, etc.</p>
                    <div class="flex items-center">
                        <span class="text-teal-700 font-bold mr-2">Cantidad:</span>
                        <input type="number" min="1" value="1" name="alimentos_cantidad" class="qty-field w-20 border border-gray-300 rounded px-2 py-1" disabled>
                        <span class="ml-2 text-gray-600">unidades</span>
                    </div>
                </div>

                <!-- Ropa -->
                <div class="donation-item bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                    <div class="flex items-center mb-3">
                        <input type="checkbox" id="chk-ropa" name="donation" value="ropa" class="mr-3 h-5 w-5 text-teal-600">
                        <label for="chk-ropa" class="font-semibold text-gray-800">Ropa y calzado</label>
                    </div>
                    <p class="text-gray-600 text-sm mb-3">Ropa en buen estado para niños y adultos, zapatos, etc.</p>
                    <div class="flex items-center">
                        <span class="text-teal-700 font-bold mr-2">Cantidad:</span>
                        <input type="number" min="1" value="1" name="ropa_cantidad" class="qty-field w-20 border border-gray-300 rounded px-2 py-1" disabled>
                        <span class="ml-2 text-gray-600">unidades</span>
                    </div>
                </div>

                <!-- Medicamentos -->
                <div class="donation-item bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                    <div class="flex items-center mb-3">
                        <input type="checkbox" id="chk-medicamentos" name="donation" value="medicamentos" class="mr-3 h-5 w-5 text-teal-600">
                        <label for="chk-medicamentos" class="font-semibold text-gray-800">Medicamentos</label>
                    </div>
                    <p class="text-gray-600 text-sm mb-3">Analgésicos, antibióticos, vendas, primeros auxilios.</p>
                    <div class="flex items-center">
                        <span class="text-teal-700 font-bold mr-2">Cantidad:</span>
                        <input type="number" min="1" value="1" name="medicamentos_cantidad" class="qty-field w-20 border border-gray-300 rounded px-2 py-1" disabled>
                        <span class="ml-2 text-gray-600">unidades</span>
                    </div>
                </div>

                <!-- Útiles -->
                <div class="donation-item bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                    <div class="flex items-center mb-3">
                        <input type="checkbox" id="chk-utiles" name="donation" value="utiles" class="mr-3 h-5 w-5 text-teal-600">
                        <label for="chk-utiles" class="font-semibold text-gray-800">Útiles escolares</label>
                    </div>
                    <p class="text-gray-600 text-sm mb-3">Cuadernos, lápices, colores, mochilas, etc.</p>
                    <div class="flex items-center">
                        <span class="text-teal-700 font-bold mr-2">Cantidad:</span>
                        <input type="number" min="1" value="1" name="utiles_cantidad" class="qty-field w-20 border border-gray-300 rounded px-2 py-1" disabled>
                        <span class="ml-2 text-gray-600">unidades</span>
                    </div>
                </div>

                <!-- Juguetes -->
                <div class="donation-item bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                    <div class="flex items-center mb-3">
                        <input type="checkbox" id="chk-juguetes" name="donation" value="juguetes" class="mr-3 h-5 w-5 text-teal-600">
                        <label for="chk-juguetes" class="font-semibold text-gray-800">Juguetes</label>
                    </div>
                    <p class="text-gray-600 text-sm mb-3">Juguetes educativos, peluches, juegos de mesa.</p>
                    <div class="flex items-center">
                        <span class="text-teal-700 font-bold mr-2">Cantidad:</span>
                        <input type="number" min="1" value="1" name="juguetes_cantidad" class="qty-field w-20 border border-gray-300 rounded px-2 py-1" disabled>
                        <span class="ml-2 text-gray-600">unidades</span>
                    </div>
                </div>

                <!-- Dinero -->
                <div class="donation-item bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                    <div class="flex items-center mb-3">
                        <input type="checkbox" id="chk-dinero" name="donation" value="dinero" class="mr-3 h-5 w-5 text-teal-600">
                        <label for="chk-dinero" class="font-semibold text-gray-800">Donación monetaria</label>
                    </div>
                    <p class="text-gray-600 text-sm mb-3">Cualquier cantidad es bienvenida para apoyar nuestros proyectos.</p>
                    <div class="flex items-center">
                        <span class="text-teal-700 font-bold mr-2">Monto:</span>
                        <input type="number" min="1" value="100" name="dinero_monto" class="qty-field w-24 border border-gray-300 rounded px-2 py-1" disabled>
                        <span class="ml-2 text-gray-600">Lempiras</span>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <button type="submit" id="btn-enviar-donacion"
                    class="bg-teal-700 hover:bg-teal-800 text-white font-bold py-3 px-6 rounded-lg shadow-md transition duration-300">
                    <i class="fas fa-hand-holding-heart mr-2"></i> Enviar donación
                </button>
            </div>
        </form>
        </div>

        <!-- Otras formas -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="font-semibold text-blue-800 mb-2">Otras formas de donar</h3>
            <div class="grid md:grid-cols-3 gap-4">
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <div class="text-blue-600 mb-2"><i class="fas fa-mobile-alt text-2xl"></i></div>
                    <h4 class="font-semibold mb-1">Donación por teléfono</h4>
                    <p class="text-sm text-gray-600">Llama al +504 2234-5678 para donar con tarjeta</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <div class="text-blue-600 mb-2"><i class="fas fa-university text-2xl"></i></div>
                    <h4 class="font-semibold mb-1">Transferencia bancaria</h4>
                    <p class="text-sm text-gray-600">Cuenta: 1234567890 Banco Atlántida</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <div class="text-blue-600 mb-2"><i class="fas fa-calendar-alt text-2xl"></i></div>
                    <h4 class="font-semibold mb-1">Donación recurrente</h4>
                    <p class="text-sm text-gray-600">Programa donaciones mensuales automáticas</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ACTIVIDADES -->
    <section id="actividades" class="mb-16 bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl md:text-3xl font-bold text-teal-800 mb-6">Nuestras Actividades</h2>

        <div class="mb-8">
            <div class="flex items-center mb-4">
                <div class="bg-teal-100 p-2 rounded-full mr-3"><i class="fas fa-utensils text-teal-700 text-xl"></i></div>
                <h3 class="text-xl font-semibold text-gray-800">Programa de Alimentación</h3>
            </div>
            <div class="grid md:grid-cols-2 gap-6 mb-6">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-gray-700 mb-2">Desayunos escolares</h4>
                    <p class="text-gray-600 mb-3">Proporcionamos desayunos nutritivos a más de 500 niños en escuelas rurales cada día.</p>
                    <div class="flex items-center text-sm text-gray-500"><i class="fas fa-map-marker-alt mr-2"></i><span>Escuelas rurales de Tegucigalpa</span></div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-gray-700 mb-2">Comedores comunitarios</h4>
                    <p class="text-gray-600 mb-3">Operamos 12 comedores que sirven almuerzos completos a familias necesitadas.</p>
                    <div class="flex items-center text-sm text-gray-500"><i class="fas fa-clock mr-2"></i><span>Lunes a viernes, 12:00 pm - 2:00 pm</span></div>
                </div>
            </div>
            <div class="bg-teal-50 border-l-4 border-teal-500 p-4">
                <p class="text-teal-800"><span class="font-bold">Próxima actividad:</span> Campaña de recolección de alimentos - 15 de julio, 2023</p>
            </div>
        </div>

        <div class="mb-8">
            <div class="flex items-center mb-4">
                <div class="bg-teal-100 p-2 rounded-full mr-3"><i class="fas fa-book text-teal-700 text-xl"></i></div>
                <h3 class="text-xl font-semibold text-gray-800">Educación y Capacitación</h3>
            </div>
            <div class="grid md:grid-cols-2 gap-6 mb-6">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-gray-700 mb-2">Clases de alfabetización</h4>
                    <p class="text-gray-600 mb-3">Programas para adultos que no han tenido acceso a educación básica.</p>
                    <div class="flex items-center text-sm text-gray-500"><i class="fas fa-users mr-2"></i><span>120 adultos beneficiados este año</span></div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-gray-700 mb-2">Talleres vocacionales</h4>
                    <p class="text-gray-600 mb-3">Carpintería, costura, panadería y otros oficios para generar ingresos.</p>
                    <div class="flex items-center text-sm text-gray-500"><i class="fas fa-graduation-cap mr-2"></i><span>85% de graduados consiguen empleo</span></div>
                </div>
            </div>
            <div class="bg-teal-50 border-l-4 border-teal-500 p-4">
                <p class="text-teal-800"><span class="font-bold">Inscripciones abiertas:</span> Taller de panadería - Inicia 1 de agosto</p>
            </div>
        </div>

        <div class="mb-8">
            <div class="flex items-center mb-4">
                <div class="bg-teal-100 p-2 rounded-full mr-3"><i class="fas fa-medkit text-teal-700 text-xl"></i></div>
                <h3 class="text-xl font-semibold text-gray-800">Salud Comunitaria</h3>
            </div>
            <div class="grid md:grid-cols-2 gap-6 mb-6">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-gray-700 mb-2">Clínicas móviles</h4>
                    <p class="text-gray-600 mb-3">Atención médica básica y medicamentos en zonas remotas.</p>
                    <div class="flex items-center text-sm text-gray-500"><i class="fas fa-heartbeat mr-2"></i><span>1,200 consultas mensuales</span></div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-gray-700 mb-2">Campañas de vacunación</h4>
                    <p class="text-gray-600 mb-3">Prevención de enfermedades en niños y adultos mayores.</p>
                    <div class="flex items-center text-sm text-gray-500"><i class="fas fa-syringe mr-2"></i><span>Próxima campaña: octubre 2023</span></div>
                </div>
            </div>
            <div class="bg-teal-50 border-l-4 border-teal-500 p-4">
                <p class="text-teal-800"><span class="font-bold">Necesitamos:</span> Médicos voluntarios para jornada del 20 de julio</p>
            </div>
        </div>
    </section>

    <!-- AFECTADOS -->
    <section id="afectados" class="mb-16 bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl md:text-3xl font-bold text-teal-800 mb-6">Historias de los Afectados</h2>

        <div class="mb-8">
            <div class="grid md:grid-cols-2 gap-8 mb-8">
                <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
                    <div class="flex items-center mb-4">
                        <img src="https://images.unsplash.com/photo-1551650975-87deedd944c3?auto=format&fit=crop&w=1374&q=80" alt="Familia Martínez" class="w-16 h-16 rounded-full object-cover mr-4">
                        <div><h3 class="font-semibold text-gray-800">Familia Martínez</h3><p class="text-sm text-gray-600">Tegucigalpa, Honduras</p></div>
                    </div>
                    <p class="text-gray-700 mb-4">"Perdimos nuestra casa en el huracán Eta. Gracias a Manos Unidas recibimos alimentos, ropa y materiales para reconstruir nuestro hogar. Ahora nuestros hijos pueden volver a la escuela con útiles nuevos."</p>
                    <div class="flex flex-wrap gap-2">
                        <span class="bg-teal-100 text-teal-800 text-xs px-2 py-1 rounded">Vivienda</span>
                        <span class="bg-teal-100 text-teal-800 text-xs px-2 py-1 rounded">Educación</span>
                        <span class="bg-teal-100 text-teal-800 text-xs px-2 py-1 rounded">Alimentación</span>
                    </div>
                </div>
                <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
                    <div class="flex items-center mb-4">
                        <img src="https://images.unsplash.com/photo-1573497491765-dccce02b29df?auto=format&fit=crop&w=1374&q=80" alt="Doña Rosa" class="w-16 h-16 rounded-full object-cover mr-4">
                        <div><h3 class="font-semibold text-gray-800">Doña Rosa</h3><p class="text-sm text-gray-600">San Pedro Sula, Honduras</p></div>
                    </div>
                    <p class="text-gray-700 mb-4">"Aprendí a leer y escribir a mis 65 años gracias a los programas de alfabetización. Ahora puedo ayudar a mis nietos con sus tareas y leer la Biblia por mí misma. Nunca es tarde para aprender."</p>
                    <div class="flex flex-wrap gap-2">
                        <span class="bg-teal-100 text-teal-800 text-xs px-2 py-1 rounded">Educación</span>
                        <span class="bg-teal-100 text-teal-800 text-xs px-2 py-1 rounded">Adultos mayores</span>
                    </div>
                </div>
            </div>
            <div class="grid md:grid-cols-2 gap-8">
                <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
                    <div class="flex items-center mb-4">
                        <img src="https://images.unsplash.com/photo-1516589178581-6cd7833ae3b2?auto=format&fit=crop&w=1374&q=80" alt="Juan Carlos" class="w-16 h-16 rounded-full object-cover mr-4">
                        <div><h3 class="font-semibold text-gray-800">Juan Carlos</h3><p class="text-sm text-gray-600">La Ceiba, Honduras</p></div>
                    </div>
                    <p class="text-gray-700 mb-4">"Después del accidente perdí la movilidad en mis piernas. Manos Unidas me proporcionó una silla de ruedas y terapia física. Ahora tengo mi propio taller de reparación de celulares gracias al curso que tomé con ellos."</p>
                    <div class="flex flex-wrap gap-2">
                        <span class="bg-teal-100 text-teal-800 text-xs px-2 py-1 rounded">Discapacidad</span>
                        <span class="bg-teal-100 text-teal-800 text-xs px-2 py-1 rounded">Capacitación</span>
                        <span class="bg-teal-100 text-teal-800 text-xs px-2 py-1 rounded">Empleo</span>
                    </div>
                </div>
                <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
                    <div class="flex items-center mb-4">
                        <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=1376&q=80" alt="Comunidad El Porvenir" class="w-16 h-16 rounded-full object-cover mr-4">
                        <div><h3 class="font-semibold text-gray-800">Comunidad El Porvenir</h3><p class="text-sm text-gray-600">Santa Bárbara, Honduras</p></div>
                    </div>
                    <p class="text-gray-700 mb-4">"Gracias al pozo que construyó Manos Unidas, ya no tenemos que caminar kilómetros para conseguir agua potable. Nuestros niños están más sanos y las mujeres tienen más tiempo para otras actividades."</p>
                    <div class="flex flex-wrap gap-2">
                        <span class="bg-teal-100 text-teal-800 text-xs px-2 py-1 rounded">Agua potable</span>
                        <span class="bg-teal-100 text-teal-800 text-xs px-2 py-1 rounded">Salud</span>
                        <span class="bg-teal-100 text-teal-800 text-xs px-2 py-1 rounded">Desarrollo comunitario</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-8">
            <h3 class="font-semibold text-red-800 mb-2">Emergencia actual</h3>
            <p class="text-gray-700">Más de 200 familias afectadas por inundaciones en el Valle de Sula necesitan ayuda urgente con alimentos, agua y medicinas.</p>
            <a href="#donaciones" class="text-red-600 font-semibold inline-block mt-2 hover:underline">¿Cómo puedes ayudar?</a>
        </div>

        <div class="bg-teal-700 text-white rounded-lg p-6">
            <h3 class="text-xl font-bold mb-4">Impacto de tu ayuda</h3>
            <div class="grid md:grid-cols-4 gap-4 text-center">
                <div><div class="text-3xl font-bold mb-1">5,000+</div><div class="text-sm">Personas beneficiadas</div></div>
                <div><div class="text-3xl font-bold mb-1">120</div><div class="text-sm">Comunidades alcanzadas</div></div>
                <div><div class="text-3xl font-bold mb-1">35</div><div class="text-sm">Proyectos activos</div></div>
                <div><div class="text-3xl font-bold mb-1">250</div><div class="text-sm">Voluntarios activos</div></div>
            </div>
        </div>
    </section>

    <!-- MISION Y VISION -->
    <section id="mision-vision" class="mb-16 bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl md:text-3xl font-bold text-teal-800 mb-6">Misión y Visión</h2>

        <div class="grid md:grid-cols-2 gap-8 mb-8">
            <div class="bg-teal-50 rounded-lg p-6">
                <div class="flex items-center mb-4">
                    <div class="bg-teal-700 text-white p-3 rounded-full mr-4"><i class="fas fa-bullseye text-xl"></i></div>
                    <h3 class="text-xl font-semibold text-teal-800">Nuestra Misión</h3>
                </div>
                <p class="text-gray-700 mb-4">Nuestra misión es la lucha contra el hambre, el subdesarrollo y la falta de instrucción y trabajar para erradicar las causas estructurales que las producen: la injusticia, el desigual reparto de los bienes y las oportunidades entre las personas y los pueblos, la ignorancia, los prejuicios, la insolidaridad, la indiferencia y la crisis de valores humanos y cristianos.</p>
                <div class="bg-white p-4 rounded-lg border border-teal-200">
                    <p class="text-teal-700 italic">"No esperes a que otros actúen, sé el cambio que quieres ver en el mundo."</p>
                </div>
            </div>
            <div class="bg-blue-50 rounded-lg p-6">
                <div class="flex items-center mb-4">
                    <div class="bg-blue-700 text-white p-3 rounded-full mr-4"><i class="fas fa-eye text-xl"></i></div>
                    <h3 class="text-xl font-semibold text-blue-800">Nuestra Visión</h3>
                </div>
                <p class="text-gray-700 mb-4">Nuestra visión, cuyo fundamento es el Evangelio y la Doctrina Social de la Iglesia, es que cada persona, hombre y mujer, en virtud de su dignidad, sea capaz de ser, por sí mismo, agente responsable de su mejora material, de su progreso moral y de su desarrollo espiritual, y goce de una vida digna.</p>
                <div class="bg-white p-4 rounded-lg border border-blue-200">
                    <p class="text-blue-700 italic">"Soñamos con un mundo donde cada persona tenga acceso a lo necesario para vivir con dignidad."</p>
                </div>
            </div>
        </div>

        <div class="mb-8">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Nuestros Valores</h3>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="bg-gray-50 p-4 rounded-lg border-l-4 border-teal-500"><h4 class="font-semibold text-gray-800 mb-2">1. La dignidad de la persona</h4><p class="text-gray-600">Reconocemos el valor intrínseco de cada ser humano, independientemente de su condición.</p></div>
                <div class="bg-gray-50 p-4 rounded-lg border-l-4 border-teal-500"><h4 class="font-semibold text-gray-800 mb-2">2. El destino universal de los bienes</h4><p class="text-gray-600">Creemos que los recursos del mundo deben beneficiar a todos, no solo a unos pocos.</p></div>
                <div class="bg-gray-50 p-4 rounded-lg border-l-4 border-teal-500"><h4 class="font-semibold text-gray-800 mb-2">3. El bien común</h4><p class="text-gray-600">Trabajamos por condiciones que permitan a todos desarrollarse plenamente.</p></div>
                <div class="bg-gray-50 p-4 rounded-lg border-l-4 border-teal-500"><h4 class="font-semibold text-gray-800 mb-2">4. La solidaridad</h4><p class="text-gray-600">Nos comprometemos mutuamente como miembros de una sola familia humana.</p></div>
                <div class="bg-gray-50 p-4 rounded-lg border-l-4 border-teal-500"><h4 class="font-semibold text-gray-800 mb-2">5. La subsidiariedad</h4><p class="text-gray-600">Empoderamos a las comunidades para que sean protagonistas de su propio desarrollo.</p></div>
            </div>
        </div>

        <div class="mb-8">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Nuestra gestión se guía por:</h3>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="flex items-start"><div class="bg-teal-100 p-2 rounded-full mr-3 mt-1"><i class="fas fa-hands-helping text-teal-700"></i></div><div><h4 class="font-semibold text-gray-800">El voluntariado</h4><p class="text-gray-600">El corazón de nuestra organización.</p></div></div>
                <div class="flex items-start"><div class="bg-teal-100 p-2 rounded-full mr-3 mt-1"><i class="fas fa-hand-holding-usd text-teal-700"></i></div><div><h4 class="font-semibold text-gray-800">La austeridad</h4><p class="text-gray-600">Maximizamos el impacto de cada donación.</p></div></div>
                <div class="flex items-start"><div class="bg-teal-100 p-2 rounded-full mr-3 mt-1"><i class="fas fa-peace text-teal-700"></i></div><div><h4 class="font-semibold text-gray-800">La cultura de la paz</h4><p class="text-gray-600">Promovemos la reconciliación y la justicia.</p></div></div>
                <div class="flex items-start"><div class="bg-teal-100 p-2 rounded-full mr-3 mt-1"><i class="fas fa-handshake text-teal-700"></i></div><div><h4 class="font-semibold text-gray-800">La cooperación</h4><p class="text-gray-600">Trabajamos en red con otras organizaciones.</p></div></div>
                <div class="flex items-start"><div class="bg-teal-100 p-2 rounded-full mr-3 mt-1"><i class="fas fa-award text-teal-700"></i></div><div><h4 class="font-semibold text-gray-800">La calidad</h4><p class="text-gray-600">Buscamos la excelencia en todo lo que hacemos.</p></div></div>
                <div class="flex items-start"><div class="bg-teal-100 p-2 rounded-full mr-3 mt-1"><i class="fas fa-clipboard-check text-teal-700"></i></div><div><h4 class="font-semibold text-gray-800">La transparencia</h4><p class="text-gray-600">Rendimos cuentas claras de nuestro trabajo.</p></div></div>
            </div>
        </div>

        <div class="bg-gray-50 border-l-4 border-gray-500 p-4">
            <h3 class="font-semibold text-gray-800 mb-2">Reconocimientos</h3>
            <p class="text-gray-700">Hemos sido reconocidos por nuestra labor con el Premio Nacional de Solidaridad (2020) y el Sello de Transparencia y Buen Gobierno por tres años consecutivos.</p>
        </div>
    </section>

    <!-- REGISTRO -->
    <section id="registro" class="mb-16 bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl md:text-3xl font-bold text-teal-800 mb-6">Regístrate como voluntario o donador</h2>

        <div class="grid md:grid-cols-2 gap-8 mb-8">
            <div class="bg-teal-50 rounded-lg p-6">
                <h3 class="text-xl font-semibold text-teal-800 mb-4">Voluntariado</h3>
                <p class="text-gray-700 mb-4">Únete a nuestro equipo de voluntarios y contribuye con tu tiempo y habilidades.</p>
                <!-- Resto del contenido -->
            </div>
            <div class="bg-blue-50 rounded-lg p-6">
                <h3 class="text-xl font-semibold text-blue-800 mb-4">Donaciones</h3>
                <p class="text-gray-700 mb-4">Tu contribución económica nos permite mantener y ampliar nuestros programas.</p>
                <!-- Resto del contenido -->
            </div>
        </div>

        <!-- Formulario de Registro -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-6">Formulario de Registro</h3>

            <form id="formulario-registro" novalidate>
                <div class="grid md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="reg-nombre" class="block text-gray-700 mb-1">Nombre completo *</label>
                        <input type="text" id="reg-nombre" name="nombre-completo" required
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label for="reg-email" class="block text-gray-700 mb-1">Correo electrónico *</label>
                        <input type="email" id="reg-email" name="email" required
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label for="reg-password" class="block text-gray-700 mb-1">Contraseña *</label>
                        <input type="password" id="reg-password" name="password" required minlength="6"
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500"
                            placeholder="Minimo 6 caracteres">
                    </div>
                    <div>
                        <label for="reg-password-confirm" class="block text-gray-700 mb-1">Confirmar Contraseña *</label>
                        <input type="password" id="reg-password-confirm" name="password-confirm" required minlength="6"
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500"
                            placeholder="Repetir contraseña">
                    </div>
                    
                    <div>
                        <label for="reg-telefono" class="block text-gray-700 mb-1">Teléfono *</label>
                        <input type="tel" id="reg-telefono" name="telefono" required
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div>
                        <label for="reg-ciudad" class="block text-gray-700 mb-1">Ciudad *</label>
                        <input type="text" id="reg-ciudad" name="ciudad" required
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 mb-2">¿Cómo deseas ayudar? *</label>
                    <div class="grid md:grid-cols-3 gap-4">
                        <div class="flex items-center"><input type="radio" id="reg-voluntario" name="ayuda" value="voluntario" class="mr-2"><label for="reg-voluntario" class="text-gray-700">Voluntario</label></div>
                        <div class="flex items-center"><input type="radio" id="reg-donador" name="ayuda" value="donador" class="mr-2"><label for="reg-donador" class="text-gray-700">Donador</label></div>
                        <div class="flex items-center"><input type="radio" id="reg-ambos" name="ayuda" value="ambos" class="mr-2"><label for="reg-ambos" class="text-gray-700">Ambos</label></div>
                    </div>
                </div>

                <div class="mb-6">
                    <label for="reg-habilidades" class="block text-gray-700 mb-1">Habilidades o profesión</label>
                    <textarea id="reg-habilidades" name="habilidades" rows="3"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500"
                        placeholder="Describa sus habilidades, profesión o áreas de interés"></textarea>
                </div>

                <div class="mb-6">
                    <label for="reg-disponibilidad" class="block text-gray-700 mb-1">Disponibilidad *</label>
                    <select id="reg-disponibilidad" name="disponibilidad" required
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                        <option value="">Seleccione una opción</option>
                        <option value="completa">Tiempo completo</option>
                        <option value="parcial">Tiempo parcial</option>
                        <option value="fines">Fines de semana</option>
                        <option value="eventos">Solo eventos especiales</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label for="reg-conocio" class="block text-gray-700 mb-1">¿Cómo nos conoció?</label>
                    <textarea id="reg-conocio" name="como-conocio" rows="2"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500"
                        placeholder="Redes sociales, amigos, evento, etc."></textarea>
                </div>

                <div class="flex items-center mb-4">
                    <input type="checkbox" id="reg-privacidad" name="privacidad" required class="mr-2">
                    <label for="reg-privacidad" class="text-gray-700">Acepto la política de privacidad y el tratamiento de mis datos *</label>
                </div>

                <div class="flex items-center mb-6">
                    <input type="checkbox" id="reg-newsletter" name="newsletter" class="mr-2">
                    <label for="reg-newsletter" class="text-gray-700">Deseo recibir información sobre actividades y campañas</label>
                </div>

                <div class="text-center">
                    <button type="submit" id="btn-enviar-registro"
                        class="bg-teal-700 hover:bg-teal-800 text-white font-bold py-3 px-8 rounded-lg shadow-lg transition duration-300 transform hover:scale-105">
                        <i class="fas fa-paper-plane mr-2"></i> Enviar registro
                    </button>
                </div>
            </form>
        </div>
    </section>


</main>

<!-- FOOTER -->
<footer class="bg-teal-900 text-white py-8">
    <div class="container mx-auto px-4">
        <div class="grid md:grid-cols-4 gap-8 mb-8">
            <div>
                <h3 class="text-lg font-semibold mb-4">ONG Manos Unidas</h3>
                <p class="text-teal-300">Trabajando por un mundo más justo y solidario desde 1995.</p>
                <div class="flex mt-4 space-x-4">
                    <a href="#" class="text-teal-300 hover:text-white"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-teal-300 hover:text-white"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-teal-300 hover:text-white"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-teal-300 hover:text-white"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div>
                <h4 class="font-semibold mb-3">Enlaces rápidos</h4>
                <ul class="space-y-2 text-teal-300">
                    <li><a href="#inicio" class="hover:text-white">Inicio</a></li>
                    <li><a href="#donaciones" class="hover:text-white">Donar</a></li>
                    <li><a href="#registro" class="hover:text-white">Voluntariado</a></li>
                    <li><a href="#actividades" class="hover:text-white">Actividades</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold mb-3">Contacto</h4>
                <ul class="space-y-2 text-teal-300">
                    <li><i class="fas fa-map-marker-alt mr-2"></i>Tegucigalpa, Honduras</li>
                    <li><i class="fas fa-phone mr-2"></i>+504 2234-5678</li>
                    <li><i class="fas fa-envelope mr-2"></i>info@manosunidas.hn</li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold mb-3">Síguenos</h4>
                <p class="text-teal-300 mb-4">Mantente informado sobre nuestras actividades</p>
                <form class="flex gap-2">
                    <input type="email" placeholder="Tu email" class="flex-1 px-3 py-2 rounded text-gray-900">
                    <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
        <div class="text-center text-teal-300 border-t border-teal-800 pt-6">
            <p>&copy; 2024 ONG Manos Unidas. Todos los derechos reservados.</p>
        </div>
    </div>
</footer>

<!-- Modal de Login -->
<div id="modal-login" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-2xl font-bold text-teal-800">Iniciar Sesión</h3>
            <button id="btn-cerrar-login" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        <p class="text-gray-600 mb-6">Ingresa tus credenciales para continuar</p>

        <form id="formulario-login" novalidate>
            <div class="mb-4">
                <label for="login-email" class="block text-gray-700 mb-2">Correo electrónico *</label>
                <input type="email" id="login-email" name="email" required placeholder="ejemplo@correo.com"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
            </div>

            <div class="mb-6">
                <label for="login-password" class="block text-gray-700 mb-2">Contraseña *</label>
                <input type="password" id="login-password" name="password" required placeholder="Tu contraseña"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
            </div>

            <button type="submit" id="btn-login"
                class="w-full bg-teal-700 hover:bg-teal-800 text-white font-bold py-3 rounded-lg shadow-md transition duration-300">
                <i class="fas fa-sign-in-alt mr-2"></i> Iniciar Sesión
            </button>
        </form>

        <div class="mt-4 text-center text-sm text-gray-600">
            <p>¿No tienes cuenta? <a href="#registro" class="text-teal-600 hover:underline font-semibold">Regístrate aquí</a></p>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="js/api.js"></script>
<script src="js/main.js"></script>

</body>
</html>