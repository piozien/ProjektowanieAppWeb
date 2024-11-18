<!DOCTYPE html>
<html lang="pl">

<head>
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
    <meta http-equiv="Content-Language" content="pl" />
    <meta name="Author" content="Piotr Zienowicz" />
    <title>Historia lotów kosmicznych</title>

    <?php
    session_start();
    // Ustawienie domyślnej wartości idp gdy nie jest jeszcze ustawiona
    if (!isset($_GET['idp'])) {
        $_GET['idp'] = 'glowna';
    }

    include('cfg.php');
    include('php/navbar.php');
    include('showpage.php');
    include('admin/admin.php');
    include('php/Contact.php');



    // Ładowanie odpowiednich arkuszy stylów
    if ($_GET['idp'] == 'podstrona6') {
        echo '<link rel="stylesheet" href="css/style2.css" />'; // lab2
    } else {
        echo '<link rel="stylesheet" href="css/style.css" />'; // reszta
    }
    ?>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="javascript/kolorujtlo.js" type="text/javascript"></script>
    <script src="javascript/timedate.js" type="text/javascript"></script>
    <script src="javascript/scriptAnimacja.js"></script>
    <script src="javascript/scriptAnimacja2.js"></script>
    <script src="javascript/scriptAnimacja3.js"></script>
</head>

<body onload="startClock()">

    <header>
        <h1 class="body-title">HISTORIA LOTÓW KOSMICZNYCH</h1>
    </header>
    <div class="navbar">
        <?php echo loadNav(); ?>
    </div>

    <div class="content">
        <?php

        // Wyświetlenie odpowiedniej strony na podstawie idp
        $alias = htmlspecialchars($_GET['idp']);

        static $Admin = null; // Tworzymy instancję Admin która będzie wykorzystywana przy wywołaniu odpowiedniego case
        
        switch ($alias) {
            // Wywołuje panel do kontaktu 
            case 'kontakt':
                $contact = new Contact();
                echo $contact->WyslijMailKontakt();
                break;
            // Wywołanie admin, sprawdzam czy instancja klasy Admin juz istnieje jesli nie to tworze nowa
            case 'admin':
                if ($Admin === null) {
                    $Admin = new Admin();
                }
                // wywołanie funkcji loginAdmin
                echo $Admin->LoginAdmin();
                break;

            case 'logout':
                if ($Admin === null) {
                    $Admin = new Admin();
                }
                $Admin->logout(); // Wywołanie metody logout
                break;

            case 'edit':
                if ($Admin === null) {
                    $Admin = new Admin();
                }
                echo $Admin->EditPage();
                break;

            case 'delete':
                if ($Admin === null) {
                    $Admin = new Admin();
                }
                echo $Admin->DeletePage(); // Wywołanie metody DeletePage
                break;
            case 'create': // Zmiana na 'create'
                if ($Admin === null) {
                    $Admin = new Admin();
                }
                echo $Admin->StworzPodstrone(); // Wywołanie metody StworzPodstrone
                break;

            default:
                echo PokazStrone($alias); /// 
                break;
        }
        ?>
    </div>


    <footer>

            <?php
            include('php/Version.php');
            $version = new Version();
            echo $version->DisplayVersion('4');
            ?>
        </div>
    </footer>

</body>

</html>