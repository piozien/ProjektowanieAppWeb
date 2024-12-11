<?php
//----------------------------------------//
//              Version.php                //
//----------------------------------------//
// Autor: Piotr Zienowicz                 //
// Data utworzenia: 2024                  //
// Opis: Klasa wyświetlająca informacje   //
//       o wersji aplikacji               //
//----------------------------------------//

/**
 * Klasa Version
 * 
 * Odpowiada za wyświetlanie informacji o wersji
 * aplikacji, dacie aktualizacji oraz autorze
 */
class Version {
    // Stałe konfiguracyjne
    private const VERSION = '1.9.0';
    private const LAST_UPDATED = '05-12-2024';
    private const NR_INDEKSU = '169399';
    
    /**
     * Wyświetla informacje o wersji aplikacji
     *
     * @param string $str Numer grupy
     * @return string Sformatowany HTML z informacjami
     */
    public function DisplayVersion($str) {
        // Walidacja parametru wejściowego
        $nrGrupy = filter_var(
            $str,
            FILTER_SANITIZE_STRING,
            FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH
        );
        
        // Zabezpieczenie danych wyjściowych
        $version = htmlspecialchars(self::VERSION, ENT_QUOTES, 'UTF-8');
        $lastUpdated = htmlspecialchars(self::LAST_UPDATED, ENT_QUOTES, 'UTF-8');
        $nrIndeksu = htmlspecialchars(self::NR_INDEKSU, ENT_QUOTES, 'UTF-8');
        $nrGrupy = htmlspecialchars($nrGrupy, ENT_QUOTES, 'UTF-8');
        
        // Formatowanie wyjścia HTML
        return sprintf(
            '<div class="version-info">
                <p class="version">
                    Wersja: %s | Ostatnia aktualizacja: %s
                </p>
                <p class="author">
                    Autor: Piotr Zienowicz; indeks: %s; grupa %s
                </p>
            </div>',
            $version,
            $lastUpdated,
            $nrIndeksu,
            $nrGrupy
        );
    }
}
?>
