<?php
//---------------------------------//
//           cfg.php               //
//---------------------------------//
//  Konfiguracja aplikacji:       //
//  - Połączenie z bazą danych    //
//  - Dane dostępowe admina       //
//  - Inicjalizacja połączenia    //
//---------------------------------//

//---------------------------------//
//    Konfiguracja MySQL          //
//---------------------------------//
$host = 'localhost';      // Adres serwera bazy danych
$user = 'root';          // Nazwa użytkownika bazy danych
$password = '';          // Hasło do bazy danych (puste w środowisku lokalnym)
$dbname = 'moja_strona'; // Nazwa bazy danych

//---------------------------------//
//    Dane logowania admina       //
//---------------------------------//
// Używamy defined() aby uniknąć ponownego definiowania stałych
if (!defined('ADMIN_LOGIN')) {
    define('ADMIN_LOGIN', 'admin');      // Login do panelu administracyjnego
}

if (!defined('ADMIN_PASSWORD')) {
    define('ADMIN_PASSWORD', 'haslo');   // Hasło do panelu administracyjnego
}

//-----------------------------------------
// Połączenie z bazą danych
//-----------------------------------------
// Utworzenie połączenia
$conn = new mysqli($host, $user, $password, $dbname);

// Sprawdzenie połączenia
if ($conn->connect_error) {
    error_log("Błąd połączenia z bazą danych: " . $conn->connect_error);
    die("Przepraszamy, wystąpił problem z połączeniem do bazy danych.");
}

// Ustawienie kodowania UTF-8
if (!$conn->set_charset("utf8")) {
    error_log("Błąd ustawienia kodowania UTF-8: " . $conn->error);
    die("Przepraszamy, wystąpił problem z konfiguracją.");
}

// Ustawienie trybu ścisłego dla MySQL
$conn->query("SET SESSION sql_mode = 'STRICT_ALL_TABLES'");

// Automatyczne zamykanie połączenia na końcu skryptu
register_shutdown_function(function() use ($conn) {
    if ($conn instanceof mysqli) {
        $conn->close();
    }
});
?>