<!DOCTYPE html>
<html lang="pl">
<head>
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
    <meta http-equiv="Content-Language" content="pl" />
    <meta name="Author" content="Piotr Zienowicz" />
    <title>Historia lotów kosmicznych</title>
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>

<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

/* Ustawienie domyślnej strony */
if ($_GET['idp'] == '') {
    $strona = 'html/glowna.html';
} elseif ($_GET['idp'] == 'podstrona1') {
    $strona = 'html/first.html';
} elseif ($_GET['idp'] == 'podstrona2') {
    $strona = 'html/orbit.html';
} elseif ($_GET['idp'] == 'podstrona3') {
    $strona = 'html/human.html';
} elseif ($_GET['idp'] == 'podstrona4') {
    $strona = 'html/lajka.html';
} elseif ($_GET['idp'] == 'podstrona5') {
    $strona = 'html/moon.html';
} elseif ($_GET['idp'] == 'podstrona6') {
    $strona = 'html/lab2.html';
} elseif ($_GET['idp'] == 'kontakt') {
    $strona = 'html/contact.html';
} else {
    $strona = 'html/404.html';  // Plik z informacją o błędzie 
}

/* Autor i informacje o projekcie */
$nr_indeksu = '169399';
$nrGrupy = '4';
echo 'Autor: Piotr Zienowicz ' . $nr_indeksu . ' grupa ' . $nrGrupy . '<br /><br />';
?>

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
        <li><a href="index.php?idp=kontakt">KONTAKT</a></li>
    </ul>
</div>

<div class="content">
    <?php
    if (file_exists($strona)) {
        include($strona);
    } else {
        echo 'Przepraszamy, nie znaleziono strony.';
    }
    ?>
</div>

<footer>
    <div class="footer-content">
        <p>&copy; 2024 Piotr Zienowicz, 169399, ISI4.</p>
        <ul class="footer-links">
            <li><a href="index.php?idp=kontakt">Kontakt</a></li>
        </ul>
    </div>
</footer>

</body>
</html>
