/**
 * Cliente API para ONG Manos Unidas
 * Maneja todas las llamadas al backend PHP en Railway
 */

// Usar ruta relativa para que funcione en cualquier entorno
const API_BASE_URL = '/api';

class API {
    /**
     * Realizar petición HTTP
     */
    static async request(endpoint, options = {}) {
        const url = `${API_BASE_URL}/${endpoint}`;
        
        const config = {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        };

        try {
            const response = await fetch(url, config);
            
            // Verificar si la respuesta es JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                // Si no es JSON, leer como texto para debug
                const text = await response.text();
                console.error('Respuesta no-JSON del servidor:', text.substring(0, 500));
                throw new Error('El servidor devolvió una respuesta inválida. Verifica los logs del servidor.');
            }
            
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Error en la petición');
            }
            
            return data;
        } catch (error) {
            console.error('Error en API:', error);
            
            // Si el error es de parsing JSON, dar un mensaje más claro
            if (error instanceof SyntaxError) {
                throw new Error('Error del servidor: respuesta inválida. Contacta al administrador.');
            }
            
            throw error;
        }
    }

    // ============ LOGIN ============
    
    /**
     * Login de usuario con email
     */
    static async login(credentials) {
        return this.request('login.php', {
            method: 'POST',
            body: JSON.stringify(credentials)
        });
    }

    // ============ PERSONAS ============
    static async getPersonas(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`personas.php${queryString ? '?' + queryString : ''}`);
    }

    static async getPersona(id) {
        return this.request(`personas.php?id=${id}`);
    }

    static async createPersona(data) {
        return this.request('personas.php', {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    static async updatePersona(id, data) {
        return this.request('personas.php', {
            method: 'PUT',
            body: JSON.stringify({ id, ...data })
        });
    }

    static async deletePersona(id) {
        return this.request(`personas.php?id=${id}`, {
            method: 'DELETE'
        });
    }

    // ============ DONACIONES ============
    static async getDonaciones(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`donaciones.php${queryString ? '?' + queryString : ''}`);
    }

    static async getDonacion(id) {
        return this.request(`donaciones.php?id=${id}`);
    }

    static async createDonacion(data) {
        return this.request('donaciones.php', {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    static async updateDonacion(id, data) {
        return this.request('donaciones.php', {
            method: 'PUT',
            body: JSON.stringify({ id, ...data })
        });
    }

    // ============ VOLUNTARIOS ============
    static async getVoluntarios(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`voluntarios.php${queryString ? '?' + queryString : ''}`);
    }

    static async getVoluntario(id) {
        return this.request(`voluntarios.php?id=${id}`);
    }

    static async createVoluntario(data) {
        return this.request('voluntarios.php', {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    static async updateVoluntario(id, data) {
        return this.request('voluntarios.php', {
            method: 'PUT',
            body: JSON.stringify({ id, ...data })
        });
    }

    // ============ TIPOS DE DONACIÓN ============
    static async getTiposDonacion() {
        return this.request('tipos-donacion.php');
    }

    // ============ DASHBOARD ============
    static async getDashboard() {
        return this.request('dashboard.php');
    }
}

// Exportar para uso global
window.API = API;