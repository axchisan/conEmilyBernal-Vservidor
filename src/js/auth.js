import { supabase } from "./supabase.js";
import { registerUser } from "./api.js";

// Iniciar sesión con Google
export async function loginWithGoogle() {
    try {
        const { data, error } = await supabase.auth.signInWithOAuth({
            provider: "google",
            options: { 
                redirectTo: "https://odontologiaemilybernal.com/index.php" // Redirige a index.php tras autenticación
            },
        });

        if (error) return;
    } catch (error) {}
}

// Verificar si hay una sesión PHP activa
async function checkPHPSession() {
    try {
        const response = await fetch('/check_session.php');
        const data = await response.json();
        return data.isLoggedIn;
    } catch (error) {
        console.error('Error al verificar la sesión PHP:', error);
        return false;
    }
}

// Verificar sesión activa y procesar usuario autenticado
export async function checkUserSession() {
    try {
        // Verificar sesión de Supabase
        const { data, error } = await supabase.auth.getSession();
        if (error) return;

        // Verificar sesión PHP
        const isPHPSessionActive = await checkPHPSession();

        if (data?.session) {
            const user = data.session.user;
            // Registrar usuario en el backend
            await registerUser({
                email: user.email,
                user_metadata: {
                    full_name: user.user_metadata.full_name || user.email.split('@')[0]
                }
            });
        } else if (!isPHPSessionActive && !window.location.pathname.includes('index.php')) {
            // Redirigir a index.php solo si no hay sesión de Supabase NI sesión PHP
            window.location.href = '/index.php';
        }
    } catch (error) {
        console.error('Error en checkUserSession:', error);
    }
}

// Escuchar cambios en la autenticación
let hasChecked = false; // Control para evitar verificaciones múltiples por evento
supabase.auth.onAuthStateChange(async (event, session) => {
    if (hasChecked) return;
    if (event === "SIGNED_IN" && session) {
        hasChecked = true;
        await checkUserSession();
    } else if (event === "SIGNED_OUT") {
        hasChecked = false;
        // Verificar sesión PHP antes de redirigir
        const isPHPSessionActive = await checkPHPSession();
        if (!isPHPSessionActive) {
            window.location.href = '/index.php';
        }
    }
});

// Verificar sesión al cargar cualquier página
document.addEventListener("DOMContentLoaded", () => {
    hasChecked = false; // Resetear control para cada carga de página
    checkUserSession();
});