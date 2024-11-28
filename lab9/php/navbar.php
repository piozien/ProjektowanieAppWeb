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


function loadNav() {
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
    
    // Generowanie HTML nawigacji
    $navHtml = '<nav class="main-nav">
        <ul class="nav-list">';
    
    // Dodawanie linków do podstron
    foreach ($navItems as $item) {
        $navHtml .= sprintf(
            '<li class="nav-item">
                <a href="?idp=%s" class="nav-link">%s</a>
            </li>',
            $item['alias'],
            $item['title']
        );
    }
    
    // Dodawanie linków administracyjnych
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
        // Menu dla zalogowanego użytkownika
        $navHtml .= '
            <li class="nav-item admin">
                <a class="nav-link logout" href="?idp=logout">WYLOGUJ</a>
            </li>';
    } else {
        // Menu dla niezalogowanego użytkownika
        $navHtml .= '
            <li class="nav-item">
                <a class="nav-link haslo" href="?idp=haslo">ODZYSKIWANIE HASŁA</a>
            </li>';
    }
    
    // Zamknięcie znaczników nawigacji
    $navHtml .= '
        </ul>
    </nav>';
    
    return $navHtml;
}
?>