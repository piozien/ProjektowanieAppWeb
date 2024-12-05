<?php
  // Start sesji
  session_start();

  $nr_indeksu = '169399';  
  $nrGrupy = '4';  
  echo 'Piotr Zienowicz '.$nr_indeksu.' grupa '.$nrGrupy.'<br /><br />';

  // a) Zastosowanie include()
  echo 'Zastosowanie metody include() <br />';
  include('footer.php');  
  echo 'Stopka załadowana poprzez metodę include() <br />';

  // Użycie require_once()
  echo 'Użycie metody require_once <br />';
  require_once('navbar.php');  
  echo "Navbar załadowany poprzez metodę require_once() <br /><br />";

  // b) Warunki if, else, elseif
  $zmienna = 10;
  echo "Zmienna wynosi: $zmienna <br />";
  if ($zmienna < 5) {
      echo "Zmienna jest mniejsza niż 5.<br />";
  } elseif ($zmienna == 10) {
      echo "Zmienna jest równa 10.<br />";
  } else {
      echo "Zmienna ma inną wartość.<br />";
  }

  // Przykład switch
  $kolor = "czerwony";
  echo "<br />Kolor: ";
  switch ($kolor) {
      case "niebieski":
          echo "Kolor to niebieski.<br />";
          break;
      case "czerwony":
          echo "Kolor to czerwony.<br />";
          break;
      case "zielony":
          echo "Kolor to zielony.<br />";
          break;
      default:
          echo "Kolor nie jest rozpoznany.<br />";
          break;
  }

  // c) Pętle while() i for()
  echo "<br />Przykład pętli while:<br />";
  $i = 0;
  while ($i < 5) {
      echo "Wartość i: $i<br />";
      $i++;
  }

  echo "<br />Przykład pętli for:<br />";
  for ($j = 0; $j < 5; $j++) {
      echo "Wartość j: $j<br />";
  }

  // d) Testowanie $_GET, $_POST, $_SESSION

  // Przykład $_GET http://localhost/lab4/php/labor_169399_ISI4.php?nazwa=Piotr
  echo "<br />Przykład zmiennej \$_GET:<br />";
  if (isset($_GET['nazwa'])) {
      $nazwa = $_GET['nazwa'];
      echo "Otrzymano z GET nazwę: $nazwa<br />";
  } else {
      echo "Brak wartości 'nazwa' w zapytaniu GET.<br />";
  }

  // Przykład do testowania $_POST
  echo "<br />Przykład zmiennej \$_POST:<br />";
  echo '<form action="labor_169399_ISI4.php" method="POST">
            <label for="email">Podaj email:</label>
            <input type="text" name="email" id="email">
            <input type="submit" value="Wyślij">
        </form>';

  if (isset($_POST['email'])) {
      $email = $_POST['email'];
      echo "Otrzymano z POST email: $email<br />";
  } else {
      echo "Brak wartości 'email' w zapytaniu POST.<br />";
  }

  // Przykład $_SESSION
  echo "<br />Przykład zmiennej \$_SESSION:<br />";
  if (isset($_SESSION['uzytkownik'])) {
      $uzytkownik = $_SESSION['uzytkownik'];
      echo "Otrzymano użytkownika z sesji: $uzytkownik<br />";
  } else {
      $_SESSION['uzytkownik'] = "lab4";
      echo "Zmienna sesji ustawiona na: lab4<br />";
  }
?>
