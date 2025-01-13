<?php
//----------------------------------------//
//          Products.php                   //
//----------------------------------------//
// Opis: Moduł zarządzania produktami     //
//   - Wyświetlanie listy produktów       //
//   - Dodawanie nowych produktów         //
//   - Edycja istniejących produktów      //
//   - Usuwanie produktów                 //
//   - Sprawdzanie dostępności            //
//----------------------------------------//

class Product
{
    /**
     * Wyświetla panel zarządzania produktami
     * 
     * @return void
     */
    function PokazProdukty()
    {
        $Admin = new Admin();
        $status_login = $Admin->CheckLogin();

        if ($status_login == 1) {
            echo '<h3 class="h3-admin">Panel Produktów</h3>';
            echo '<div class="admin-links">';
            echo '<a href="?idp=admin" class="admin-link">Powrót do Panelu Admina</a>';
            echo '</div>';
            echo $this->ListaProduktow();
        } else {
            echo $Admin->FormularzLogowania();
        }
    }

    /**
     * Wyświetla listę produktów w formie tabeli
     * 
     * @return void
     */
    function ListaProduktow()
    {
        global $conn;
        
        $query = "SELECT p.*, 
                  CASE 
                    WHEN c2.nazwa IS NOT NULL THEN CONCAT(c2.nazwa, ' → ', c1.nazwa)
                    ELSE c1.nazwa 
                  END as kategoria_nazwa,
                  CAST(p.ilosc_dostepnych AS SIGNED) as ilosc_dostepnych,
                  p.zdjecie,
                  p.tytul,
                  p.opis,
                  p.cena_netto,
                  p.podatek_vat,
                  p.data_utworzenia,
                  p.data_wygasniecia,
                  p.status_dostepnosci,
                  p.gabaryt_produkty
                  FROM product_list p
                  LEFT JOIN category_list c1 ON p.kategoria = c1.id
                  LEFT JOIN category_list c2 ON c1.matka = c2.id
                  ORDER BY p.data_utworzenia DESC";
        
        error_log("SQL Query: " . $query);
        $result = $conn->query($query);

        if (!$result) {
            error_log("SQL Error: " . $conn->error);
        }

        echo '<div class="product-panel">';
        echo '<div class="product-actions">';
        echo '<a href="?idp=nowy-produkt" class="new-product-btn">Dodaj nowy produkt</a>';
        echo '</div>';

        if ($result && $result->num_rows > 0) {
            echo '<table class="admin-table">
                    <tr>
                        <th>ID</th>
                        <th>Zdjęcie</th>
                        <th>Tytuł</th>
                        <th>Cena netto</th>
                        <th>VAT</th>
                        <th>Ilość</th>
                        <th>Kategoria</th>
                        <th>Status</th>
                        <th>Akcje</th>
                    </tr>';

            while ($row = $result->fetch_assoc()) {
                $dostepnosc = $this->SprawdzDostepnosc($row);
                $status_class = $dostepnosc ? 'status-available' : 'status-unavailable';
                $status_text = $dostepnosc ? 'Dostępny' : 'Niedostępny';
                
                echo '<tr>
                        <td>' . $row['id'] . '</td>
                        <td class="product-image-cell">';
                if ($row['zdjecie'] && file_exists($row['zdjecie'])) {
                    echo '<img src="' . $row['zdjecie'] . '" alt="Produkt" class="product-thumbnail">';
                } else {
                    echo '<img src="images/products/no-image.png" alt="Brak zdjęcia" class="product-thumbnail">';
                }
                echo '</td>
                        <td>' . htmlspecialchars($row['tytul']) . '</td>
                        <td>' . number_format($row['cena_netto'], 2) . ' zł</td>
                        <td>' . $row['podatek_vat'] . '%</td>
                        <td>' . $row['ilosc_dostepnych'] . '</td>
                        <td>' . htmlspecialchars($row['kategoria_nazwa'] ?? 'Brak kategorii') . '</td>
                        <td><span class="status-badge ' . $status_class . '">' . $status_text . '</span></td>
                        <td class="action-cell">
                            <a href="?idp=edytuj-produkt&id=' . $row['id'] . '" class="action-edit">Edytuj</a>
                            <a href="?idp=usun-produkt&id=' . $row['id'] . '" 
                               class="action-delete" 
                               onclick="return confirm(\'Czy na pewno chcesz usunąć ten produkt?\');">Usuń</a>
                        </td>
                    </tr>';
            }
            echo '</table>';
        } else {
            echo '<p class="no-results">Brak produktów w bazie.</p>';
        }
        echo '</div>';
    }

    /**
     * Sprawdza dostępność produktu na podstawie statusu, ilości i daty wygaśnięcia
     * 
     * @param array $produkt Dane produktu
     * @return bool True jeśli produkt jest dostępny, false w przeciwnym razie
     */
    function SprawdzDostepnosc($produkt)
    {
        // Sprawdzenie statusu dostępności
        if (!isset($produkt['status_dostepnosci']) || $produkt['status_dostepnosci'] != 1) {
            return false;
        }

        // Sprawdzenie ilości w magazynie
        if (!isset($produkt['ilosc_dostepnych']) || intval($produkt['ilosc_dostepnych']) <= 0) {
            return false;
        }

        // Sprawdzenie daty wygaśnięcia
        if (isset($produkt['data_wygasniecia']) && $produkt['data_wygasniecia'] !== null) {
            $data_wygasniecia = new DateTime($produkt['data_wygasniecia']);
            $teraz = new DateTime();
            if ($data_wygasniecia < $teraz) {
                return false;
            }
        }

        return true;
    }

    /**
     * Wyświetla formularz dodawania nowego produktu i obsługuje jego zapis
     * 
     * @return void
     */
    function DodajProdukt()
    {
        global $conn;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tytul = filter_input(INPUT_POST, 'tytul', FILTER_SANITIZE_STRING);
            $opis = filter_input(INPUT_POST, 'opis', FILTER_SANITIZE_STRING);
            $cena_netto = filter_input(INPUT_POST, 'cena_netto', FILTER_VALIDATE_FLOAT);
            $podatek_vat = filter_input(INPUT_POST, 'podatek_vat', FILTER_VALIDATE_FLOAT);
            $ilosc = filter_input(INPUT_POST, 'ilosc_dostepnych', FILTER_VALIDATE_INT);
            $kategoria_id = filter_input(INPUT_POST, 'kategoria', FILTER_VALIDATE_INT);
            $gabaryt = filter_input(INPUT_POST, 'gabaryt_produkty', FILTER_SANITIZE_STRING);
            $data_wygasniecia = filter_input(INPUT_POST, 'data_wygasniecia', FILTER_SANITIZE_STRING);

            if (!$tytul || !$cena_netto || !$podatek_vat || !$ilosc || !$kategoria_id || !$gabaryt) {
                echo '<div class="error-message">Wszystkie pola są wymagane!</div>';
                return;
            }

            // Walidacja daty wygaśnięcia
            $data_wygasniecia_sql = null;
            if (!empty($data_wygasniecia)) {
                $data_wygasniecia_sql = date('Y-m-d H:i:s', strtotime($data_wygasniecia));
            }

            // Sprawdź czy kategoria istnieje
            $query = "SELECT * FROM category_list WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $kategoria_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 0) {
                echo '<div class="error-message">Wybrana kategoria nie istnieje!</div>';
                return;
            }
            $kategoria = $result->fetch_assoc();

            // Obsługa zdjęcia
            $zdjecie_path = 'images/products/no-image.png';
            if (isset($_FILES['zdjecie']) && $_FILES['zdjecie']['error'] === UPLOAD_ERR_OK) {
                $file_tmp = $_FILES['zdjecie']['tmp_name'];
                $file_name = $_FILES['zdjecie']['name'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                // Sprawdź rozszerzenie pliku
                $allowed = array('jpg', 'jpeg', 'png', 'gif');
                if (!in_array($file_ext, $allowed)) {
                    echo '<div class="error-message">Niedozwolony format pliku!</div>';
                    return;
                }

                // Generuj unikalną nazwę pliku
                $new_file_name = uniqid() . '.' . $file_ext;
                $upload_path = 'images/products/' . $new_file_name;

                if (move_uploaded_file($file_tmp, $upload_path)) {
                    $zdjecie_path = $upload_path;
                } else {
                    echo '<div class="error-message">Błąd podczas uploadu zdjęcia!</div>';
                    return;
                }
            }

            $query = "INSERT INTO product_list (tytul, opis, cena_netto, podatek_vat, ilosc_dostepnych, 
                     kategoria, gabaryt_produkty, zdjecie, data_wygasniecia) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($query);
            $stmt->bind_param(
                "ssddissss",
                $tytul,
                $opis,
                $cena_netto,
                $podatek_vat,
                $ilosc,
                $kategoria_id, 
                $gabaryt,
                $zdjecie_path,
                $data_wygasniecia_sql
            );

            if ($stmt->execute()) {
                header("Location: ?idp=produkty");
                exit();
            } else {
                echo '<div class="error-message">Wystąpił błąd podczas dodawania produktu!</div>';
            }
        }

        // Pobierz listę kategorii
        $query = "SELECT c1.*, c2.nazwa as parent_name 
                 FROM category_list c1 
                 LEFT JOIN category_list c2 ON c1.matka = c2.id 
                 ORDER BY c1.matka, c1.nazwa";
        $result = $conn->query($query);
        $kategorie = [];
        while ($row = $result->fetch_assoc()) {
            $kategorie[] = $row;
        }

        // Wyświetl formularz
        echo '<div class="product-form">
            <h3>Dodaj nowy produkt</h3>
            <form method="POST" enctype="multipart/form-data">
                <label for="tytul">Tytuł</label>
                <input type="text" id="tytul" name="tytul" required>
                
                <label for="opis">Opis</label>
                <textarea id="opis" name="opis" required></textarea>
                
                <label for="cena_netto">Cena netto</label>
                <input type="number" id="cena_netto" name="cena_netto" step="0.01" required>
                
                <label for="podatek_vat">VAT (%)</label>
                <input type="number" id="podatek_vat" name="podatek_vat" step="0.01" required>
                
                <label for="ilosc_dostepnych">Ilość dostępnych</label>
                <input type="number" id="ilosc_dostepnych" name="ilosc_dostepnych" required>
                
                <label for="kategoria">Kategoria</label>
                <select id="kategoria" name="kategoria" required>
                    <option value="">Wybierz kategorię</option>';
        foreach ($kategorie as $kat) {
            $nazwa_kategorii = $kat['nazwa'];
            if ($kat['parent_name']) {
                $nazwa_kategorii = $kat['parent_name'] . ' → ' . $kat['nazwa'];
            }
            echo '<option value="' . $kat['id'] . '">' . htmlspecialchars($nazwa_kategorii) . '</option>';
        }
        echo '</select>
                
                <label for="gabaryt_produkty">Gabaryt</label>
                <input type="text" id="gabaryt_produkty" name="gabaryt_produkty" required>
                
                <label for="data_wygasniecia">Data wygaśnięcia (opcjonalne)</label>
                <input type="datetime-local" id="data_wygasniecia" name="data_wygasniecia">
                
                <label for="zdjecie">Zdjęcie</label>
                <input type="file" id="zdjecie" name="zdjecie" accept="image/*">
                
                <button type="submit" class="new-product-btn">Dodaj produkt</button>
            </form>
        </div>';
    }

    /**
     * Wyświetla formularz edycji produktu i obsługuje jego aktualizację
     * 
     * @return void
     */
    function EdytujProdukt()
    {
        global $conn;

        if (!isset($_GET['id'])) {
            header("Location: ?idp=produkty");
            exit();
        }

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tytul = filter_input(INPUT_POST, 'tytul', FILTER_SANITIZE_STRING);
            $opis = filter_input(INPUT_POST, 'opis', FILTER_SANITIZE_STRING);
            $cena_netto = filter_input(INPUT_POST, 'cena_netto', FILTER_VALIDATE_FLOAT);
            $podatek_vat = filter_input(INPUT_POST, 'podatek_vat', FILTER_VALIDATE_FLOAT);
            $ilosc = filter_input(INPUT_POST, 'ilosc_dostepnych', FILTER_VALIDATE_INT);
            $kategoria_id = filter_input(INPUT_POST, 'kategoria', FILTER_VALIDATE_INT);
            $gabaryt = filter_input(INPUT_POST, 'gabaryt_produkty', FILTER_SANITIZE_STRING);
            $data_wygasniecia = filter_input(INPUT_POST, 'data_wygasniecia', FILTER_SANITIZE_STRING);

            if (!$tytul || !$cena_netto || !$podatek_vat || !isset($ilosc) || !$kategoria_id || !$gabaryt) {
                echo '<div class="error-message">Wszystkie pola są wymagane!</div>';
                return;
            }

            // Sprawdź czy kategoria istnieje
            $query = "SELECT * FROM category_list WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $kategoria_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 0) {
                echo '<div class="error-message">Wybrana kategoria nie istnieje!</div>';
                return;
            }
            $kategoria = $result->fetch_assoc();

            // Obsługa zdjęcia
            $zdjecie_path = null;
            if (isset($_FILES['zdjecie']) && $_FILES['zdjecie']['size'] > 0) {
                $file_tmp = $_FILES['zdjecie']['tmp_name'];
                $file_name = $_FILES['zdjecie']['name'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                // Sprawdź rozszerzenie pliku
                $allowed = array('jpg', 'jpeg', 'png', 'gif');
                if (!in_array($file_ext, $allowed)) {
                    echo '<div class="error-message">Niedozwolony format pliku!</div>';
                    return;
                }

                // Generuj unikalną nazwę pliku
                $new_file_name = uniqid() . '.' . $file_ext;
                $upload_path = 'images/products/' . $new_file_name;

                if (move_uploaded_file($file_tmp, $upload_path)) {
                    // Pobierz stare zdjęcie
                    $query = "SELECT zdjecie FROM product_list WHERE id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $produkt = $result->fetch_assoc();

                    // Usuń stare zdjęcie jeśli istnieje i nie jest domyślnym
                    if (
                        $produkt && $produkt['zdjecie'] && file_exists($produkt['zdjecie']) &&
                        $produkt['zdjecie'] !== 'images/products/no-image.png'
                    ) {
                        unlink($produkt['zdjecie']);
                    }

                    $zdjecie_path = $upload_path;
                } else {
                    echo '<div class="error-message">Błąd podczas uploadu zdjęcia!</div>';
                    return;
                }
            }

            // Walidacja daty wygaśnięcia
            $data_wygasniecia_sql = null;
            if (!empty($data_wygasniecia)) {
                $data_wygasniecia_sql = date('Y-m-d H:i:s', strtotime($data_wygasniecia));
            }

            if ($zdjecie_path) {
                $query = "UPDATE product_list SET 
                         tytul = ?, 
                         opis = ?, 
                         cena_netto = ?, 
                         podatek_vat = ?, 
                         ilosc_dostepnych = ?, 
                         kategoria = ?, 
                         gabaryt_produkty = ?,
                         data_wygasniecia = ?,
                         zdjecie = ? 
                         WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ssddissssi", 
                    $tytul, 
                    $opis, 
                    $cena_netto, 
                    $podatek_vat, 
                    $ilosc, 
                    $kategoria_id, 
                    $gabaryt,
                    $data_wygasniecia_sql,
                    $zdjecie_path,
                    $id
                );
            } else {
                $query = "UPDATE product_list SET 
                         tytul = ?, 
                         opis = ?, 
                         cena_netto = ?, 
                         podatek_vat = ?, 
                         ilosc_dostepnych = ?, 
                         kategoria = ?, 
                         gabaryt_produkty = ?,
                         data_wygasniecia = ? 
                         WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ssdisissi", 
                    $tytul, 
                    $opis, 
                    $cena_netto, 
                    $podatek_vat, 
                    $ilosc, 
                    $kategoria_id, 
                    $gabaryt,
                    $data_wygasniecia_sql,
                    $id
                );
            }

            if ($stmt->execute()) {
                ob_end_clean(); // Clear any output before redirect
                header("Location: ?idp=produkty");
                exit();
            } else {
                echo '<div class="error-message">Wystąpił błąd podczas aktualizacji produktu!</div>';
            }
        }

        // Pobierz dane produktu
        $query = "SELECT * FROM product_list WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $produkt = $result->fetch_assoc();

        if (!$produkt) {
            header("Location: ?idp=produkty");
            exit();
        }

        // Pobierz listę kategorii
        $query = "SELECT c1.*, c2.nazwa as parent_name 
                 FROM category_list c1 
                 LEFT JOIN category_list c2 ON c1.matka = c2.id 
                 ORDER BY c1.matka, c1.nazwa";
        $result = $conn->query($query);
        $kategorie = [];
        while ($row = $result->fetch_assoc()) {
            $kategorie[] = $row;
        }

        // Wyświetl formularz edycji
        echo '<div class="product-form">
            <h3>Edytuj produkt</h3>
            <form method="POST" enctype="multipart/form-data">
                <label for="tytul">Tytuł</label>
                <input type="text" id="tytul" name="tytul" value="' . htmlspecialchars($produkt['tytul']) . '" required>
                
                <label for="opis">Opis</label>
                <textarea id="opis" name="opis" required>' . htmlspecialchars($produkt['opis']) . '</textarea>
                
                <label for="cena_netto">Cena netto</label>
                <input type="number" id="cena_netto" name="cena_netto" step="0.01" value="' . $produkt['cena_netto'] . '" required>
                
                <label for="podatek_vat">VAT (%)</label>
                <input type="number" id="podatek_vat" name="podatek_vat" step="0.01" value="' . $produkt['podatek_vat'] . '" required>
                
                <label for="ilosc_dostepnych">Ilość dostępnych</label>
                <input type="number" id="ilosc_dostepnych" name="ilosc_dostepnych" value="' . $produkt['ilosc_dostepnych'] . '">
                
                <label for="kategoria">Kategoria</label>
                <select id="kategoria" name="kategoria" required>
                    <option value="">Wybierz kategorię</option>';
        foreach ($kategorie as $kat) {
            $nazwa_kategorii = $kat['nazwa'];
            if ($kat['parent_name']) {
                $nazwa_kategorii = $kat['parent_name'] . ' → ' . $kat['nazwa'];
            }
            $selected = ($kat['id'] == $produkt['kategoria']) ? ' selected' : '';
            echo '<option value="' . $kat['id'] . '"' . $selected . '>' . htmlspecialchars($nazwa_kategorii) . '</option>';
        }
        echo '</select>
                
                <label for="gabaryt_produkty">Gabaryt</label>
                <input type="text" id="gabaryt_produkty" name="gabaryt_produkty" value="' . htmlspecialchars($produkt['gabaryt_produkty']) . '" required>
                
                <label for="data_wygasniecia">Data wygaśnięcia (opcjonalne)</label>
                <input type="datetime-local" id="data_wygasniecia" name="data_wygasniecia" 
                       value="' . ($produkt['data_wygasniecia'] ? date('Y-m-d\TH:i', strtotime($produkt['data_wygasniecia'])) : '') . '">
                
                <label for="zdjecie">Aktualne zdjęcie:</label>';

        if ($produkt['zdjecie'] && file_exists($produkt['zdjecie'])) {
            echo '<img src="' . $produkt['zdjecie'] . '" alt="Produkt" class="preview">';
        } else {
            echo '<img src="images/products/no-image.png" alt="Brak zdjęcia" class="preview">';
        }

        echo '<label for="zdjecie">Zmień zdjęcie (opcjonalnie):</label>
                <input type="file" id="zdjecie" name="zdjecie" accept="image/*">
                
                <button type="submit" class="new-product-btn">Zapisz zmiany</button>
            </form>
        </div>';
    }

    /**
     * Obsługuje usuwanie produktu
     * 
     * @return void
     */
    function UsunProdukt()
    {
        global $conn;

        if (!isset($_GET['id'])) {
            header("Location: ?idp=produkty");
            exit();
        }

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        // Najpierw pobierz informacje o zdjęciu
        $query = "SELECT zdjecie FROM product_list WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $produkt = $result->fetch_assoc();

        // Usuń zdjęcie jeśli istnieje i nie jest domyślnym
        if (
            $produkt && $produkt['zdjecie'] && file_exists($produkt['zdjecie']) &&
            $produkt['zdjecie'] !== 'images/products/no-image.png'
        ) {
            unlink($produkt['zdjecie']);
        }

        // Usuń rekord z bazy
        $query = "DELETE FROM product_list WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            header("Location: ?idp=produkty");
            exit();
        } else {
            echo '<div class="error-message">Wystąpił błąd podczas usuwania produktu!</div>';
        }
    }
}
?>