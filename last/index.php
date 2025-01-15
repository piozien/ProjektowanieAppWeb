<!--
//---------------------------------//
//         index.php               //
//---------------------------------//
//  Główny plik wejściowy         //
//  strony Historia Lotów         //
//  Kosmicznych                   //
//                                //
//  Funkcjonalności:              //
//  - Routing                     //
//  - Wyświetlanie stron         //
//  - Panel administracyjny       //
//---------------------------------//
-->

<?php
session_start();
ob_start();
include('cfg.php');
include('php/navbar.php');
include('showpage.php');
include('admin/admin.php');
include('php/contact.php');
include('php/categories.php');
include('php/products.php');
include('php/store.php');
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
    <meta http-equiv="Content-Language" content="pl" />
    <meta name="Author" content="Piotr Zienowicz" />
    <meta name="Keywords" content="Sklep internetowy, Produkty, Koszyk" />
    <title>Sklep internetowy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- JavaScript -->
    <script src="javascript/admin-panel.js" defer></script>
    <script src="javascript/cart.js" defer></script>

    <?php
    //---------------------------------//
    //    Inicjalizacja aplikacji     //
    //---------------------------------//
    //  Konfiguracja początkowa:      //
    //  - Start sesji                 //
    //  - Zabezpieczenia XSS         //
    //  - Ładowanie komponentów       //
    //---------------------------------//
    
    // Regeneracja ID sesji dla bezpieczeństwa
    if (!isset($_SESSION['initialized'])) {
        session_regenerate_id(true);
        $_SESSION['initialized'] = true;
    }

    // Ustawienie domyślnej strony
    if (!isset($_GET['idp'])) {
        $_GET['idp'] = 'glowna';
    }

    // Zabezpieczenie parametru idp przed atakami
    $_GET['idp'] = filter_var(
        $_GET['idp'],
        FILTER_SANITIZE_STRING,
        FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH
    );

    //----------------------------------------
    // Zarządzanie stylami CSS
    //----------------------------------------
    if (isset($_GET['idp']) && $_GET['idp'] === 'podstrona6') {
        echo '<link rel="stylesheet" href="css/style2.css">';
    } else {
        echo '<link rel="stylesheet" href="css/style.css">';
    }
    ?>

    <!-- Skrypty zewnętrzne -->
    <script src="jquery/jQuery3.7.1.js"></script>
    <script src="javascript/kolorujtlo.js"></script>
    <script src="javascript/timedate.js"></script>
    <script src="javascript/scriptAnimacja.js"></script>
    <script src="javascript/scriptAnimacja2.js"></script>
    <script src="javascript/scriptAnimacja3.js"></script>
</head>

<body onload="startclock()">
    <header>
        <h1 class="body-title">HISTORIA LOTÓW KOSMICZNYCH</h1>
    </header>

    <?php
    // Wyświetl navbar admina tylko dla stron administracyjnych
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
        $adminPages = ['admin', 'edit', 'delete', 'create', 'haslo', 'kategorie', 'edytuj-kategorie', 'nowa-kategoria', 'usun-kategorie', 'produkty', 'nowy-produkt', 'edytuj-produkt', 'usun-produkt', 'sklep'];
        if (in_array($_GET['idp'] ?? '', $adminPages)) {
            echo loadAdminNav();
        }
    }
    ?>

    <div class="navbar">
        <?php echo loadNav(); ?>
    </div>

    <div class="content">
        <?php
        //---------------------------------//
        //    Router aplikacji            //
        //---------------------------------//
        //  Obsługa ścieżek:             //
        //  - kontakt                    //
        //  - panel admina               //
        //  - zarządzanie stronami       //
        //  - wyświetlanie treści        //
        //---------------------------------//
        
        // Pobranie i zabezpieczenie aliasu strony
        $alias = filter_var(
            $_GET['idp'],
            FILTER_SANITIZE_STRING,
            FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH
        );

        // Inicjalizacja instancji Admin
        static $Admin = null;

        switch ($alias) {
            case 'kontakt':
                $contact = new Contact();
                echo "<h2>Kontakt</h2>";
                echo $contact->WyslijMailKontakt("169399@student.uwm.edu.pl");
                echo "<br></br>";
                break;

            case 'admin':
                if ($Admin === null) {
                    $Admin = new Admin();
                }
                echo $Admin->LoginAdmin();
                break;

            case 'logout':
                if ($Admin === null) {
                    $Admin = new Admin();
                }
                $Admin->logout();
                break;

            case 'edit':
                if ($Admin === null) {
                    $Admin = new Admin();
                }
                if (!isset($_SESSION['loggedin'])) {
                    header('Location: ?idp=admin');
                    exit();
                }
                echo $Admin->EditPage();
                break;

            case 'delete':
                if ($Admin === null) {
                    $Admin = new Admin();
                }
                if (!isset($_SESSION['loggedin'])) {
                    header('Location: ?idp=admin');
                    exit();
                }
                echo $Admin->DeletePage();
                break;

            case 'create':
                if ($Admin === null) {
                    $Admin = new Admin();
                }
                if (!isset($_SESSION['loggedin'])) {
                    header('Location: ?idp=admin');
                    exit();
                }
                echo $Admin->StworzPodstrone();
                break;

            case 'haslo':
                $contact = new Contact();
                echo "<h2>Odzyskanie hasła</h2>";
                echo $contact->PrzypomnijHaslo("169399@student.uwm.edu.pl");
                echo "<br></br>";
                break;

            case 'kategorie':
                if ($Admin === null) {
                    $Admin = new Admin();
                }
                if (!isset($_SESSION['loggedin'])) {
                    header('Location: ?idp=admin');
                    exit();
                }
                $Category = new Category();
                echo $Category->PokazKategorie(); 					// Login do strony administracyjnej,  wyświetlenie tabeli Kategorii
                break;

            case 'nowa-kategoria':
                if ($Admin === null) {
                    $Admin = new Admin();
                }
                if (!isset($_SESSION['loggedin'])) {
                    header('Location: ?idp=admin');
                    exit();
                }
                $Category = new Category();
                echo $Category->AddCategory();						// Login do strony administracyjnej  utworzenie nowej kategorii w bazie danych
                break;

            case 'edytuj-kategorie':
                if ($Admin === null) {
                    $Admin = new Admin();
                }
                if (!isset($_SESSION['loggedin'])) {
                    header('Location: ?idp=admin');
                    exit();
                }
                $Category = new Category();
                echo $Category->EditCategory();						// Login do strony administracyjnej  edycja istniejącej kategorii w bazie danych
                break;

            case 'usun-kategorie':
                if ($Admin === null) {
                    $Admin = new Admin();
                }
                if (!isset($_SESSION['loggedin'])) {
                    header('Location: ?idp=admin');
                    exit();
                }
                $Category = new Category();
                echo $Category->DeleteCategory();					// Login do strony administracyjnej  usunięcie wybranej kategorii z bazy danych
                break;

            case 'produkty':
                if ($Admin === null) {
                    $Admin = new Admin();
                }
                if (!isset($_SESSION['loggedin'])) {
                    header('Location: ?idp=admin');
                    exit();
                }
                $produkty = new Product();
                $produkty->ListaProduktow();					// Wyświetlenie listy produktów w panelu administracyjnym
                break;

            case 'nowy-produkt':
                if ($Admin === null) {
                    $Admin = new Admin();
                }
                if (!isset($_SESSION['loggedin'])) {
                    header('Location: ?idp=admin');
                    exit();
                }
                $produkty = new Product();
                $produkty->DodajProdukt();					// Wyświetlenie formularza dodawania nowego produktu
                break;

            case 'edytuj-produkt':
                if ($Admin === null) {
                    $Admin = new Admin();
                }
                if (!isset($_SESSION['loggedin'])) {
                    header('Location: ?idp=admin');
                    exit();
                }
                    $produkty = new Product();
                    $produkty->EdytujProdukt();					// Wyświetlenie formularza edycji wybranego produktu
                break;

            case 'usun-produkt':
                if ($Admin === null) {
                    $Admin = new Admin();
                }
                if (!isset($_SESSION['loggedin'])) {
                    header('Location: ?idp=admin');
                    exit();
                }
                    $produkty = new Product();
                    $produkty->UsunProdukt();					// Usunięcie wybranego produktu z bazy danych
                break;

            case 'sklep':
                if ($Admin === null) {
                    $Admin = new Admin();
                }
                if (!isset($_SESSION['loggedin'])) {
                    header('Location: ?idp=admin');
                    exit();
                } 
                    $Store = new Store();
                    echo $Store->StorePage();											// Wyświetlenie produktów oraz możliwość dodania do koszyka i zakupu
                    break;
                    
            case 'sklep2':
                if ($Admin === null) {
                    $Admin = new Admin();
                }
                if (!isset($_SESSION['loggedin'])) {
                    header('Location: ?idp=admin');
                    exit();
                } 
                    $Store = new Store();
                    echo $Store->ShowCart();											// Wyświetlenie produktów oraz możliwość dodania do koszyka i zakupu
                break;
                

            default:
                echo PokazStrone($alias);
                break;
        }
        ?>
    </div>

    <footer>
        <?php
        //---------------------------------//
        //    Wyświetlanie wersji         //
        //---------------------------------//
        include('php/Version.php');
        $version = new Version();
        echo $version->DisplayVersion('4');
        ?>
    </footer>

</body>

</html>