<?php
include 'cfg.php'; // Ładowanie cfg 

class Admin
{
    // Funkcja do wyświetlania formularza logowania
    // $_SERVER['REQUEST_URI']. Formularz przesyła dane do bieżącego adresu URL w celu ich przetwarzania
    function FormularzLogowania()
    {
        return '
        <div class="logowanie">
            <h3 class="heading">Panel CMS:</h3>
            <form method="post" name="LoginForm" enctype="multipart/form-data" action="' . $_SERVER['REQUEST_URI'] . '">
                <table class="logowanie">
                    <tr><td class="log4_t">[login]</td><td><input type="text" name="login" class="logowanie" required /></td></tr>
                    <tr><td class="log4_t">[haslo]</td><td><input type="password" name="login_pass" class="logowanie" required /></td></tr>
                    <tr><td></td><td><input type="submit" name="x1_submit" class="login-link" value="zaloguj" /></td></tr>
                    
                </table>
            </form>
        </div>';
    }

    // Funkcja do sprawdzania logowania
    function CheckLogin()
    {
        // Sprawdź, czy użytkownik jest już zalogowany
        if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']) {
            return 1; // Użytkownik jest już zalogowany
        }

        // Sprawdź, czy formularz przekazał login i hasło
        if (isset($_POST['login']) && isset($_POST['login_pass'])) {
            return $this->CheckLoginCred($_POST['login'], $_POST['login_pass']); // Sprawdzenie danych logowania
        }

        return 0; // Nie ma danych logowania
    }

    // Funkcja do sprawdzania danych logowania
    function CheckLoginCred($login, $pass)
    {
        if ($login == ADMIN_LOGIN && $pass == ADMIN_PASSWORD) { // Sprawdzenie zdefiniowanych danych logowania
            $_SESSION['loggedin'] = true; // Ustawienie zmiennej sesyjnej na true
            return 1; // Pomyślne sprawdzenie
        } else {
            echo "Logowanie się nie powiodło.";
            return 0; // Niepoprawne dane
        }
    }

    // Funkcja do wyświetlania panelu administracyjnego
    function LoginAdmin()
    {
        $status_login = $this->CheckLogin(); // Sprawdź dane logowania

        if ($status_login == 1) {
            echo '<h3 class="h3-admin">Lista Stron</h3>';
            echo $this->ListaPodstron(); // Wyświetl listę podstron
        } else {
            echo $this->FormularzLogowania(); // Wyświetlenie formularza logowania
        }
    }

    function logout()
    {
        // Sprawdzenie i usunięcie zmiennych sesyjnych
        if (isset($_SESSION['loggedin'])) {
            unset($_SESSION['loggedin']);
        }
        // Przy wylogowaniu przekierowywanie na główną strone
        header('Location: ?idp=glowna');
        exit;
    }

    // Funkcja do wyświetlania listy podstron
    function ListaPodstron()
    {
        // globalna zmienna $conn służaca za łaczenie sie z bazą danych (konfiguracja jest w cfg.php)
        global $conn;
        // zapytanie do bazy, które ma pobrać id i tytuł z tabeli page_list
        $query = "SELECT id, page_title FROM page_list";
        // wysłanie zapytania do bazy danych
        $result = $conn->query($query);

        // wywołanie tabeli w której znajda sie przetworzone dane z zapytania wyżej
        echo '<table class="admin-table">
        <tr>
            <th>ID Strony</th>
            <th>Tytuł Strony</th>
            <th>Edytuj</th>
            <th>Usuń</th>
        </tr>';

        // iteracja po wynikach zapytania i dodawanie każdej wartosci i przycisków
        while ($row = $result->fetch_assoc()) {
            echo '<tr>
                    <td style="color: gold;">' . $row['id'] . '</td>
                    <td style="color: #FFFFFF;">' . $row['page_title'] . '</td>
                    <td><a class="edit-button" href="?idp=edit&ide=' . $row['id'] . '">Edit</a></td>
                    <td><a class="delete-button" href="?idp=delete&idd=' . $row['id'] . '" onclick="return confirm(\'Czy na pewno chcesz usunąć tę stronę?\');">Delete</a></td>
                  </tr>';
        }

        echo '</table>';

        // wyświetlanie guzika do tworzenia nowej strony
        echo '<div class="buttons">';
        // przekierowanie do strony odpowiedzialnej za tworzenie nowych
        echo '<a class="create-link" href="?idp=create">Dodaj nową stronę</a>';
        echo '</div>';

        // wyświetlanie guzika odpowiedzialnego za wylogowywanie i przekierowywanie
        echo '<div class="logout-container">';
        echo '<a class="logout-link" href="?idp=logout">Wyloguj się</a>';
        echo '</div>';
    }

    // Funkcja odpowiedzialna za tworzenie nowej podstrony
    function StworzPodstrone()
    {
        // Sprawdź, czy użytkownik jest zalogowany
        $status_login = $this->CheckLogin();

        // jeśli użytkownik jest zalogowany
        if ($status_login == 1) {
            echo '<h3 class="h3-admin">Dodaj nową stronę</h3>';

            // sprawdzenie czy formularz jest wysłany metoda POST i czy wymagane dane sa wprowadzone
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_title'], $_POST['create_content'], $_POST['create_active'], $_POST['create_alias'])) {
                // Przygotowanie danych do wstawienia do bazy w odpowiednie miejsce
                // wykorzystuje tutaj real_escape_string który zabezpiecza przed SQL Injection
                $title = $GLOBALS['conn']->real_escape_string($_POST['create_title']);
                $content = $GLOBALS['conn']->real_escape_string($_POST['create_content']);
                // ustawienie strony jako aktywna 1 - lub 0 nieaktywna
                $active = isset($_POST['create_active']) ? 1 : 0;
                //ustawienie aliasu i zachowanie bezpieczenstwa 
                $alias = $GLOBALS['conn']->real_escape_string($_POST['create_alias']);

                // zapytanie SQL które doda nowa podstrone do bazy
                $query = "INSERT INTO page_list (page_title, page_content, status, alias) VALUES ('$title', '$content', '$active', '$alias')";

                // sprawdzenie czy jest polaczenie z baza i czy zapytanie zostalo przetworzone poprawnie
                if ($GLOBALS['conn']->query($query) === TRUE) {
                    echo "Nowa strona została dodana pomyślnie.";
                    // przekierowanie na panel admina po skonczonej operacji
                    header("Location: ?idp=admin");
                    exit;
                } else {
                    // wyswietlenie komunikatu błedu w przypadku niepowodzenia
                    echo "Błąd podczas dodawania: " . $GLOBALS['conn']->error;
                }
            } else {
                // Jeśli formularz nie został wysłany, wyświetl formularz do dodania nowej strony
                return '
    <div class="create-container">
        <h3 class="create-title">Tworzenie Strony</h3>
        <form method="post" action="' . $_SERVER['REQUEST_URI'] . '">
            <div class="form-group">
                <label for="create_title">Tytuł:</label>
                <input type="text" id="create_title" name="create_title" required />
            </div>
            <div class="form-group">
                <label for="create_content">Zawartość:</label>
                <textarea id="create_content" name="create_content" required></textarea>
            </div>
            <div class="form-group-inline">
                <label for="create_active">Aktywna:</label>
                <input type="checkbox" id="create_active" name="create_active" />
            </div>
            <div class="form-group">
                <label for="create_alias">Alias:</label>
                <input type="text" id="create_alias" name="create_alias" required />
            </div>
            <div class="form-group">
                <input type="submit" class="submit-button" value="Dodaj stronę" />
            </div>
        </form>
    </div>';
            }
        } else {
            return $this->FormularzLogowania(); // Jeśli nie jesteś zalogowany, wyświetl formularz logowania
        }
    }

    function EditPage()
    {
        // sprawdzenie czy uzytkownik jest zalogowanny
        $status_login = $this->CheckLogin();

        if ($status_login == 1) {
            echo '<h3 class="h3-admin">Strona edycji</h3>';

            // sprawdzenie czy w URL strony znajduje sie parametr ide który jest id edytowanej strony
            if (isset($_GET['ide'])) {

                // sprawdzenie czy formularz jest wysłany metoda POST i czy wymagane dane sa wprowadzone
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_title'], $_POST['edit_content'], $_POST['edit_active'], $_POST['edit_alias'])) {
                    // przygotwanie danych do zmiany: tytuł, zawartość, aktywność, alias lub id, zachowanie bezpieczenstwa po przez real_escape_string lub intval
                    $title = $GLOBALS['conn']->real_escape_string($_POST['edit_title']);
                    $content = $GLOBALS['conn']->real_escape_string($_POST['edit_content']);
                    $active = isset($_POST['edit_active']) ? 1 : 0;
                    $alias = $GLOBALS['conn']->real_escape_string($_POST['edit_alias']);
                    $id = intval($_GET['ide']);

                    // Zapytanie SQL aktualizujace dane podstrony
                    $query = "UPDATE page_list SET page_title='$title', page_content='$content', status='$active', alias='$alias' WHERE id='$id' LIMIT 1";

                    // sprawdzenie czy jest polaczenie z baza i czy zapytanie zostalo przetworzone poprawnie
                    if ($GLOBALS['conn']->query($query) === TRUE) {
                        echo "Strona została zaktualizowana pomyślnie.";
                        // przekierowanie na panel admina
                        header("Location: ?idp=admin");
                        exit;
                    } else {
                        // komunikat o błedzie podczas aktualizacji
                        echo "Błąd podczas aktualizacji: " . $GLOBALS['conn']->error;
                    }
                } else {
                    // jesli formularz nie został wysłany pobieram dane strony do edycji
                    $query = "SELECT * FROM page_list WHERE id='" . intval($_GET['ide']) . "' LIMIT 1";
                    $result = $GLOBALS['conn']->query($query);

                    // sprawdzam czy strona o wskazanym id istnieje
                    if ($result && $result->num_rows > 0) {
                        $row = $result->fetch_assoc();

                        return '
                                <div class="edit-container">
                                    <h3 class="edit-title">Edycja Strony</h3>
                                    <form method="post" action="' . $_SERVER['REQUEST_URI'] . '">
                                        <div class="form-group">
                                            <label for="edit_title">Tytuł:</label>
                                            <input type="text" id="edit_title" name="edit_title" value="' . htmlspecialchars($row['page_title']) . '" required />
                                        </div>
                                        <div class="form-group">
                                            <label for="edit_content">Zawartość:</label>
                                            <textarea id="edit_content" name="edit_content" required>' . htmlspecialchars($row['page_content']) . '</textarea>
                                        </div>
                                        <div class="form-group-inline">
                                            <label for="edit_active">Aktywna:</label>
                                            <input type="checkbox" id="edit_active" name="edit_active"' . ($row['status'] ? ' checked' : '') . ' />
                                        </div>
                                        <div class="form-group">
                                            <label for="edit_alias">Alias:</label>
                                            <input type="text" id="edit_alias" name="edit_alias" value="' . htmlspecialchars($row['alias']) . '" required />
                                        </div>
                                        <div class="form-group">
                                            <input type="submit" class="submit-button" value="Zapisz zmiany" />
                                        </div>
                                    </form>
                                </div>';
                    } else {
                        return "Nie znaleziono strony do edycji.";
                    }
                }
            } else {
                return "Nie podano ID strony do edycji.";
            }
        } else {
            return $this->FormularzLogowania(); // Jeśli nie jesteś zalogowany, wyświetl formularz logowania
        }
    }

    function DeletePage()
    {
        // Sprawdź, czy użytkownik jest zalogowany
        $status_login = $this->CheckLogin();

        if ($status_login == 1) { // jesli zalogowano to...
            // Sprawdź, czy podano ID do usunięcia
            if (isset($_GET['idd'])) {
                // intval słuzacy do zabezpieczenia przed SQL Injection
                $id = intval($_GET['idd']);

                // Zapytanie do usunięcia podstrony
                $query = "DELETE FROM page_list WHERE id='$id' LIMIT 1";

                // sprawdzenie czy jest polaczenie z baza i czy zapytanie zostalo przetworzone poprawnie
                if ($GLOBALS['conn']->query($query) === TRUE) {
                    echo "Strona została usunięta pomyślnie.";
                    header("Location: ?idp=admin"); // Przekierowanie po udanym usunięciu na panel admina
                    exit;
                } else {
                    echo "Błąd podczas usuwania: " . $GLOBALS['conn']->error;
                }
            } else {
                echo "Nie podano ID strony do usunięcia.";
            }
        } else {
            return $this->FormularzLogowania(); // Jeśli nie jesteś zalogowany, wyświetl formularz logowania
        }
    }


}

?>