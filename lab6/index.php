<!DOCTYPE html>
<html lang="pl">
<head>
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
    <meta http-equiv="Content-Language" content="pl" />
    <meta name="Author" content="Piotr Zienowicz" />
    <title>Historia lotów kosmicznych</title>
    <?php
    include('cfg.php');

    // Ładowanie odpowiednich arkuszy stylów
    if ($_GET['idp'] == 'podstrona6') {
        echo '<link rel="stylesheet" href="css/style2.css" />'; //lab2
    } else {
        echo '<link rel="stylesheet" href="css/style.css" />'; //reszta
    }
   
    
    ?>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="javascript/kolorujtlo.js" type="text/javascript"></script>
    <script src="javascript/timedate.js" type="text/javascript"></script>
    <script src="javascript/scriptAnimacja.js"></script>
    <script src="javascript/scriptAnimacja2.js"></script>
    <script src="javascript/scriptAnimacja3.js"></script>

</head>
<body onload="startclock()">

<div class="header">
    <h1 class="body-title">HISTORIA LOTÓW KOSMICZNYCH</h1>
</div>

<div class="navbar">
    <ul>
        <li><a href="index.php?idp=">STRONA GŁÓWNA</a></li>
        <li><a href="index.php?idp=podstrona1">PIERWSZY LOT W KOSMOS</a></li>
        <li><a href="index.php?idp=podstrona2">PIERWSZY LOT ORBITALNY</a></li>
        <li><a href="index.php?idp=podstrona3">CZŁOWIEK W KOSMOSIE</a></li>
        <li><a href="index.php?idp=podstrona4">PSI ASTRONAUTA</a></li>
        <li><a href="index.php?idp=podstrona5">KSIĘŻYC</a></li>
        <li><a href="index.php?idp=podstrona6">ĆWICZENIA 2 - JS</a></li>
        <li><a href="index.php?idp=podstrona7">FILMY</a></li>
        <li><a href="index.php?idp=kontakt">KONTAKT</a></li>
    </ul>
</div>


<div class="content">
    <?php
    if (isset($_GET['idp']) && $_GET['idp'] !== '') {
        include('showpage.php'); 
    } else {
        include('html/glowna.html'); // Domyślny plik
    }
    ?>
</div>

<footer>
    <div class="footer-content">
    <?php
        /* Autor i informacje o projekcie */
        $nr_indeksu = '169399';
        $nrGrupy = '4';
        echo 'Autor: Piotr Zienowicz ' . $nr_indeksu . ' grupa ' . $nrGrupy . '<br /><br />';
    ?>
        <ul class="footer-links">
            <li><a href="index.php?idp=kontakt">Kontakt</a></li>
        </ul>
    </div>
</footer>

</body>
</html>
