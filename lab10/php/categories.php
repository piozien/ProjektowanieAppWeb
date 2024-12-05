<?php
//----------------------------------------//
//          Categories.php                 //
//----------------------------------------//
// Opis: Moduł zarządzania kategoriami    //
//   - Wyświetlanie kategorii             //
//   - Operacje CRUD na kategoriach       //
//   - Hierarchia kategorii               //
//----------------------------------------//

class Category {
    /**
     * Wyświetla panel zarządzania kategoriami
     * 
     * @return void
     */
    function PokazKategorie() {
        $Admin = new Admin();
        $status_login = $Admin->CheckLogin();

        if ($status_login == 1) {
            echo '<h3 class="h3-admin">Panel Kategorii</h3>';
            echo '<div class="admin-links">';
            echo '<a href="?idp=admin" class="admin-link">Powrót do Panelu Admina</a>';
           // echo '<a href="?idp=produkty" class="admin-link">Przejdź do Produktów</a>';
            echo '</div>';
            echo $this->ListaKategorii();
        } else {
            echo $Admin->FormularzLogowania();
        }
    }

    /**
     * Wyświetla listę kategorii w formie tabeli i drzewa
     * 
     * @return void
     */
    function ListaKategorii() {
        global $conn;
        
        // Przygotowanie bezpiecznego zapytania
        $query = "SELECT id, matka, nazwa FROM category_list ORDER BY id ASC LIMIT 100";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();

        echo '<div class="category-panel">';
        echo '<div class="category-actions">';
        echo '<a href="?idp=nowa-kategoria" class="new-category-btn">Dodaj nową kategorię</a>';
        echo '</div>';

        // Generowanie tabeli
        echo '<table class="admin-table">
            <tr>
                <th>ID</th>
                <th>Kategoria Nadrzędna</th>
                <th>Nazwa Kategorii</th>
                <th>Akcje</th>
            </tr>';

        while($row = $result->fetch_assoc()) {
            echo '<tr>
                <td class="id-cell">'.htmlspecialchars($row['id']).'</td>
                <td>'.htmlspecialchars($row['matka']).'</td>
                <td>'.htmlspecialchars($row['nazwa']).'</td>
                <td class="action-cell">
                    <a href="?idp=edytuj-kategorie&id='.htmlspecialchars($row['id']).'" class="action-button edit">Edytuj</a>
                    <a href="?idp=usun-kategorie&id='.htmlspecialchars($row['id']).'" 
                       class="action-button delete" 
                       onclick="return confirm(\'Czy na pewno chcesz usunąć tę kategorię?\');">Usuń</a>
                </td>
            </tr>';
        }
        echo '</table>';

        // Generowanie drzewa kategorii
        $stmt->execute();
        $categories = [];
        $tree = [];

        while($row = $result->fetch_assoc()) {
            $categories[$row['id']] = $row;
            if ($row['matka'] == 0) {
                $tree[$row['id']] = &$categories[$row['id']];
            } else {
                $categories[$row['matka']]['children'][] = &$categories[$row['id']];
            }
        }

        echo '<div class="category-tree">';
        echo '<h3>Struktura kategorii</h3>';
        $this->displayCategoryTree($tree);
        echo '</div>';
        
        echo '</div>'; // Zamknięcie category-panel

        $stmt->close();
    }

    /**
     * Dodaje nową kategorię
     * 
     * @return string HTML formularza lub komunikat o wyniku operacji
     */
    function AddCategory() {
        $Admin = new Admin();
        $status_login = $Admin->CheckLogin();

        if ($status_login == 1) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nazwa'], $_POST['matka'])) {
                global $conn;
                
                // Walidacja i czyszczenie danych
                $nazwa = trim($_POST['nazwa']);
                $matka = filter_var($_POST['matka'], FILTER_VALIDATE_INT, [
                    "options" => ["default" => 0, "min_range" => 0]
                ]);

                if (empty($nazwa)) {
                    return "Nazwa kategorii nie może być pusta.";
                }

                // Bezpieczne dodawanie kategorii
                $stmt = $conn->prepare("INSERT INTO category_list (nazwa, matka) VALUES (?, ?)");
                $stmt->bind_param("si", $nazwa, $matka);
                
                if ($stmt->execute()) {
                    header("Location: ?idp=kategorie");
                    exit();
                } else {
                    return "Błąd podczas dodawania kategorii: " . htmlspecialchars($stmt->error);
                }
                $stmt->close();
            }
            
            return $this->FormularzDodawaniaKategorii();
        }
        return $Admin->FormularzLogowania();
    }

    /**
     * Edytuje istniejącą kategorię
     * 
     * @return string HTML formularza lub komunikat o wyniku operacji
     */
    function EditCategory() {
        $Admin = new Admin();
        $status_login = $Admin->CheckLogin();

        if ($status_login == 1) {
            global $conn;
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['nazwa'], $_POST['matka'])) {
                // Walidacja i czyszczenie danych
                $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
                $nazwa = trim($_POST['nazwa']);
                $matka = filter_var($_POST['matka'], FILTER_VALIDATE_INT, [
                    "options" => ["default" => 0, "min_range" => 0]
                ]);

                if ($id === false || empty($nazwa)) {
                    return "Nieprawidłowe dane kategorii.";
                }

                // Sprawdzenie czy nie tworzymy cyklu w hierarchii
                if ($id == $matka) {
                    return "Kategoria nie może być swoim własnym rodzicem.";
                }

                // Bezpieczna aktualizacja kategorii
                $stmt = $conn->prepare("UPDATE category_list SET nazwa = ?, matka = ? WHERE id = ?");
                $stmt->bind_param("sii", $nazwa, $matka, $id);
                
                if ($stmt->execute()) {
                    header("Location: ?idp=kategorie");
                    exit();
                } else {
                    return "Błąd podczas aktualizacji kategorii: " . htmlspecialchars($stmt->error);
                }
                $stmt->close();
            }
            
            return isset($_GET['id']) ? 
                   $this->FormularzEdycjiKategorii($_GET['id']) : 
                   "Nie podano ID kategorii do edycji.";
        }
        return $Admin->FormularzLogowania();
    }

    /**
     * Usuwa kategorię
     * 
     * @return string Komunikat o wyniku operacji
     */
    function DeleteCategory() {
        $Admin = new Admin();
        $status_login = $Admin->CheckLogin();

        if ($status_login == 1) {
            if (isset($_GET['id'])) {
                global $conn;
                $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

                if ($id === false) {
                    return "Nieprawidłowe ID kategorii.";
                }

                // Sprawdzenie czy kategoria nie ma podkategorii
                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM category_list WHERE matka = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();

                if ($row['count'] > 0) {
                    return "Nie można usunąć kategorii, która posiada podkategorie.";
                }

                // Bezpieczne usuwanie kategorii
                $stmt = $conn->prepare("DELETE FROM category_list WHERE id = ?");
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    header("Location: ?idp=kategorie");
                    exit();
                } else {
                    return "Błąd podczas usuwania kategorii: " . htmlspecialchars($stmt->error);
                }
                $stmt->close();
            }
            header("Location: ?idp=kategorie");
            exit();
        }
        return $Admin->FormularzLogowania();
    }

    /**
     * Wyświetla drzewo kategorii
     * 
     * @param array $category_listTablica kategorii
     * @param int $level Poziom zagłębienia
     * @return void
     */
    private function displayCategoryTree($category_list, $level = 0) {
        foreach ($category_list as $category) {
            echo str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
            echo htmlspecialchars($category['nazwa']) . '<br>';
            
            if (!empty($category['children'])) {
                $this->displayCategoryTree($category['children'], $level + 1);
            }
        }
    }

    /**
     * Generuje formularz dodawania kategorii
     * 
     * @return string HTML formularza
     */
    private function FormularzDodawaniaKategorii() {
        return '<div class="create-container">
            <h3 class="create-title">Dodawanie Kategorii</h3>
            <form method="post" class="admin-form">
                <div class="form-group">
                    <label for="nazwa">Nazwa kategorii:</label>
                    <input type="text" id="nazwa" name="nazwa" required />
                </div>
                <div class="form-group">
                    <label for="matka">Kategoria nadrzędna (0 dla głównej):</label>
                    <input type="number" id="matka" name="matka" value="0" min="0" />
                </div>
                <div class="form-group">
                    <input type="submit" class="submit-button" value="Dodaj kategorię" />
                </div>
            </form>
        </div>';
    }

    /**
     * Generuje formularz edycji kategorii
     * 
     * @param int $id ID kategorii do edycji
     * @return string HTML formularza
     */
    private function FormularzEdycjiKategorii($id) {
        global $conn;
        $id = filter_var($id, FILTER_VALIDATE_INT);
        
        if ($id === false) {
            return "Nieprawidłowe ID kategorii.";
        }

        $stmt = $conn->prepare("SELECT * FROM category_list WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $category = $result->fetch_assoc();
        $stmt->close();

        if (!$category) {
            return "Nie znaleziono kategorii.";
        }

        return '<div class="edit-container">
            <h3 class="edit-title">Edycja Kategorii</h3>
            <form method="post" class="admin-form">
                <input type="hidden" name="id" value="'.htmlspecialchars($category['id']).'" />
                <div class="form-group">
                    <label for="nazwa">Nazwa kategorii:</label>
                    <input type="text" id="nazwa" name="nazwa" value="'.htmlspecialchars($category['nazwa']).'" required />
                </div>
                <div class="form-group">
                    <label for="matka">Kategoria nadrzędna (0 dla głównej):</label>
                    <input type="number" id="matka" name="matka" value="'.htmlspecialchars($category['matka']).'" min="0" />
                </div>
                <div class="form-group">
                    <input type="submit" class="submit-button" value="Zapisz zmiany" />
                </div>
            </form>
        </div>';
    }
}
?>