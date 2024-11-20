<?php

// ustawienia do bazy danych
$host = 'localhost'; // adres bazy
$user = 'root'; // użytkownik bazy danych
$password = ''; // hasło do bazy danych
$dbname = 'moja_strona'; // nazwa bazy danych

// Dane logowania do panelu administracyjnego
if (!defined('ADMIN_LOGIN')) {
    define('ADMIN_LOGIN', 'admin'); // Login administratora
}
if (!defined('ADMIN_PASSWORD')) {
    define('ADMIN_PASSWORD', 'haslo'); // Hasło administratora
}

// Połączenie z bazą danych, new mysqli tworzy nowe połaczenie z baza danych 
$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) { // sprawdzenie czy wystapił błąd podczas łaczenia i ewentualne wyswietlenie błedu
    die("Connection failed: " . $conn->connect_error);
}
?>