<?php
//----------------------------------------//
//              navbar.php                 //
//----------------------------------------//
// Autor:                                 //
// Data utworzenia: 2023                  //
// Opis: Moduł nawigacji strony          //
//   - Generowanie menu                   //
//   - Pobieranie aktywnych stron         //
//   - Obsługa linków admina             //
//----------------------------------------//


function loadNav()
{
    global $conn;

    // Sprawdzenie połączenia z bazą
    if (!$conn) {
        error_log("Błąd połączenia z bazą danych w navbar.php");
        return '<nav><ul><li>Menu tymczasowo niedostępne</li></ul></nav>';
    }

    // Przygotowanie zapytania SQL z limitem dla optymalizacji
    $query = "SELECT alias, page_title, id 
             FROM page_list 
             WHERE status = 1 
             ORDER BY id ASC
             LIMIT 50";

    // Wykonanie zapytania
    $result = $conn->query($query);
    if (!$result) {
        error_log("Błąd zapytania SQL w navbar.php: " . $conn->error);
        return '<nav><ul><li>Menu tymczasowo niedostępne</li></ul></nav>';
    }

    // Inicjalizacja kontenera nawigacji
    $navItems = array();

    // Pobranie i zabezpieczenie danych
    while ($row = $result->fetch_assoc()) {
        // Dodatkowa walidacja danych
        if (empty($row['alias']) || empty($row['page_title'])) {
            continue;
        }

        $navItems[] = array(
            'alias' => htmlspecialchars(trim($row['alias']), ENT_QUOTES, 'UTF-8'),
            'title' => htmlspecialchars(trim($row['page_title']), ENT_QUOTES, 'UTF-8')
        );
    }

    // Generowanie HTML dla nawigacji
    $nav = '<nav class="main-nav">';
    $nav .= '<ul class="nav-list">';

    foreach ($navItems as $item) {
        $active = ($_GET['idp'] ?? 'glowna') == $item['alias'] ? ' active' : '';
        $icon = getNavIcon($item['alias']); // Dodajemy funkcję dla ikon
        $nav .= sprintf(
            '<li class="nav-item%s"><a href="?idp=%s"><i class="%s"></i> %s</a></li>',
            $active,
            $item['alias'],
            $icon,
            $item['title']
        );
    }

    $nav .= '</ul>';
    $nav .= '</nav>';

    return $nav;
}

// Funkcja zwracająca odpowiednią ikonę dla danej strony
function getNavIcon($alias)
{
    $icons = [
        'glowna' => 'fas fa-home',
        'pierwszy' => 'fas fa-rocket',
        'podstrona2' => 'fas fa-satellite',
        'podstrona3' => 'fas fa-user-astronaut',
        'podstrona4' => 'fas fa-dog',
        'podstrona5' => 'fas fa-moon',
        'podstrona6' => 'fas fa-tools',
        'podstrona7' => 'fas fa-video',
        'kontakt' => 'fas fa-envelope',
        'admin' => 'fas fa-user-shield',
    ];

    return $icons[$alias] ?? 'fas fa-link'; // Domyślna ikona jeśli nie znaleziono
}

function loadAdminNav()
{
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        return '';
    }

    $currentPage = $_GET['idp'] ?? '';

    // Przycisk toggle dla panelu admina
    $adminNav = '<button class="admin-toggle" onclick="toggleAdminPanel()">
        <i class="fas fa-cog"></i> Panel Admina
    </button>';

    $adminNav .= '<nav class="admin-navbar">
        <div class="admin-nav-container">
            <div class="admin-nav-brand">
                <a href="?idp=glowna">
                    <i class="fas fa-home"></i> Strona Główna
                </a>
            </div>
            <ul class="admin-nav-links">
                <li class="admin-nav-item' . ($currentPage === 'admin' ? ' active' : '') . '">
                    <a href="?idp=admin"><i class="fas fa-tachometer-alt"></i> Panel Admina</a>
                </li>
                <li class="admin-nav-item' . ($currentPage === 'kategorie' ? ' active' : '') . '">
                    <a href="?idp=kategorie"><i class="fas fa-folder"></i> Zarządzanie Kategoriami</a>
                </li>
                <li class="admin-nav-item' . ($currentPage === 'produkty' ? ' active' : '') . '">
                    <a href="?idp=produkty"><i class="fas fa-box"></i> Zarządzanie Produktami</a>
                </li>
                <li class="admin-nav-item">
                    <a href="?idp=logout" class="logout-button">
                        <i class="fas fa-sign-out-alt"></i> Wyloguj
                    </a>
                </li>
                
            </ul>
        </div>
    </nav>';

    return $adminNav;
}
?>