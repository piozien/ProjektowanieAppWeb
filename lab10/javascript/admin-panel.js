// Funkcja do obsługi wysuwanego panelu admina
function toggleAdminPanel() {
    const navbar = document.querySelector(".admin-navbar");
    const toggle = document.querySelector(".admin-toggle");
    navbar.classList.toggle("active");
    toggle.classList.toggle("active");
}

// Dodaj klasę do body gdy strona się załaduje
document.addEventListener('DOMContentLoaded', function() {
    document.body.classList.add("admin-logged-in");
});
