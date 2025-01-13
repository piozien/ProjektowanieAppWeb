<?php

include('cfg.php');

//---------------------------------//
//         PokazStrone             //
//---------------------------------//
//   Wyświetla zawartość strony    //
//   na podstawie przekazanego     //
//   aliasu                        //
//                                 //
// Parametry:                      //
//   $alias - alias strony         //
//                                 //
// Zwraca:                         //
//   Zawartość strony lub         //
//   komunikat o błędzie          //
//---------------------------------//

function PokazStrone($alias) {
    global $conn;
    
    // Zabezpieczenie przed atakiem XSS
    $alias_clear = htmlspecialchars($alias);

    // Przygotowanie zapytania SQL z LIMIT 1 dla optymalizacji
    // oraz z prepared statement dla ochrony przed SQL Injection
    $query = "SELECT * FROM page_list WHERE alias = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $alias_clear);

    // Wykonanie zapytania i pobranie wyników
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // Zwolnienie zasobów
    $stmt->close();
    
    // Zwrócenie zawartości strony lub komunikatu o błędzie
    return empty($row['id']) ? '[nie_znaleziono_strony]' : $row['page_content'];
}

//---------------------------------//
//      Główny blok kodu          //
//---------------------------------//
//   Sprawdza parametr GET i       //
//   wyświetla odpowiednią stronę  //
//---------------------------------//

if (isset($_GET['idp'])) {
    
} else {
    echo '[nie_znaleziono_strony]';
}
?>
