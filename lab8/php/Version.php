<?php
class Version {
    public function DisplayVersion($str) {
        $version = "1.7.0"; // Aktualna wersja strony
        $lastUpdated = "2024-11-20"; // Data ostatniej aktualizacji
        $nr_indeksu = '169399';
            $nrGrupy = $str;

        return "<div class='version-info'>Wersja: " . $version . " | Ostatnia aktualizacja: " . $lastUpdated . "<br />Autor: Piotr Zienowicz; indeks: ".$nr_indeksu."; grupa ".$nrGrupy."<br />  </div>";
        
    }
}
?>
