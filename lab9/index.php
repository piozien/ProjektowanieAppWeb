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

<!DOCTYPE html>
<html lang="pl">

<head>
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
    <meta http-equiv="Content-Language" content="pl" />
    <meta name="Author" content="Piotr Zienowicz" />
    <title>Historia lotów kosmicznych</title>

    <?php
    //---------------------------------//
    //    Inicjalizacja aplikacji     //
    //---------------------------------//
    //  Konfiguracja początkowa:      //
    //  - Start sesji                 //
    //  - Zabezpieczenia XSS         //
    //  - Ładowanie komponentów       //
    //---------------------------------//
    
    session_start();
    
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

    // Dołączenie wymaganych plików
    include('cfg.php');
    include('php/navbar.php');
    include('showpage.php');
    include('admin/admin.php');
    include('php/contact.php');

    //----------------------------------------
    // Zarządzanie stylami CSS
    //----------------------------------------
    if (filter_var($_GET['idp'], FILTER_SANITIZE_STRING) === 'podstrona6') {
        echo '<link rel="stylesheet" href="css/style2.css" />'; 
    } else {
        echo '<link rel="stylesheet" href="css/style.css" />'; 
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
