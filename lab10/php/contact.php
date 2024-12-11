
<?php
//---------------------------------//
//         Contact.php             //
//---------------------------------//
//  Klasa obsługująca:            //
//  - Formularz kontaktowy        //
//  - Wysyłanie wiadomości        //
//  - Odzyskiwanie hasła          //
//---------------------------------//

class Contact {
    
    //---------------------------------//
    //      WyslijMailKontakt         //
    //---------------------------------//
    //  Wysyła wiadomość email        //
    //  Parametry:                    //
    //  - $odbiorca: email odbiorcy   //
    //  Zwraca:                       //
    //  - Komunikat o wysłaniu        //
    //---------------------------------//
    
    function WyslijMailKontakt($odbiorca) {
        // Walidacja danych wejściowych
        if (empty($_POST['email']) || 
            empty($_POST['title']) || 
            empty($_POST['content'])) {
            return $this->PokazKontakt();
        }
        
        // Zabezpieczenie danych przed atakami
        $mail = array(
            'sender'    => filter_var($_POST['email'], FILTER_SANITIZE_EMAIL),
            'subject'   => htmlspecialchars($_POST['title'], ENT_QUOTES, 'UTF-8'),
            'body'      => htmlspecialchars($_POST['content'], ENT_QUOTES, 'UTF-8'),
            'recipient' => filter_var($odbiorca, FILTER_SANITIZE_EMAIL)
        );
        
        // Walidacja adresu email
        if (!filter_var($mail['sender'], FILTER_VALIDATE_EMAIL)) {
            return '<div class="alert">Nieprawidłowy adres email!</div>';
        }
        
        // Przygotowanie nagłówków
        $headers = array(
            'From: Formularz kontaktowy <' . $mail['sender'] . '>',
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=utf-8'
        );
        
        // Próba wysłania emaila
        if (mail(
            $mail['recipient'],
            $mail['subject'],
            $mail['body'],
            implode("\n", $headers)
        )) {
            return '<div class="alert">Wiadomość została wysłana!</div>';
        } else {
            return '<div class="alert">Wystąpił błąd podczas wysyłania wiadomości.</div>';
        }
    }
    
    //---------------------------------//
    //        PokazKontakt            //
    //---------------------------------//
    //  Wyświetla formularz           //
    //  kontaktowy                    //
    //  Zwraca:                       //
    //  - HTML formularza             //
    //---------------------------------//
    
    function PokazKontakt() {
        // Zabezpieczenie ścieżki formularza
        $action = htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8');
        
        return '
        <form method="post" action="' . $action . '">
            <table class="form_email">
                <tr>
                    <td>Email:</td>
                    <td>
                        <input type="email" 
                               name="email" 
                               required 
                               style="width: 100%;" />
                    </td>
                </tr>
                <tr>
                    <td>Tytuł:</td>
                    <td>
                        <input type="text" 
                               name="title" 
                               required 
                               style="width: 100%;" />
                    </td>
                </tr>
                <tr>
                    <td>Zawartość:</td>
                    <td>
                        <textarea name="content" 
                                  required 
                                  style="width: 100%; height: 150px;">
                        </textarea>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <input type="submit" 
                               value="Wyślij" 
                               class="submit-button" />
                    </td>
                </tr>
            </table>
            <div class="buttons2">
                <a class="contact-button" 
                   href="?idp=haslo">
                    Odzyskiwanie hasła
                </a>
            </div>
        </form>';
    }
    
    //---------------------------------//
    //      PrzypomnijHaslo           //
    //---------------------------------//
    //  Obsługuje proces              //
    //  odzyskiwania hasła            //
    //  Parametry:                    //
    //  - $odbiorca: email odbiorcy   //
    //  Zwraca:                       //
    //  - Komunikat o wysłaniu        //
    //---------------------------------//
    
    function PrzypomnijHaslo($odbiorca) {
        if (empty($_POST['email_recov'])) {
            return $this->PokazKontaktHaslo();
        }
        
        // Zabezpieczenie danych przed atakami
        $mail = array(
            'sender'    => filter_var($_POST['email_recov'], FILTER_SANITIZE_EMAIL),
            'subject'   => 'Odzyskanie hasła',
            'body'      => 'Twoje hasło to: test',
            'recipient' => filter_var($odbiorca, FILTER_SANITIZE_EMAIL)
        );
        
        // Walidacja adresu email
        if (!filter_var($mail['sender'], FILTER_VALIDATE_EMAIL)) {
            return '<div class="alert">Nieprawidłowy adres email!</div>';
        }
        
        // Przygotowanie nagłówków
        $headers = array(
            'From: Formularz odzyskiwania hasła <' . $mail['sender'] . '>',
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=utf-8'
        );
        
        // Próba wysłania emaila
        if (mail(
            $mail['recipient'],
            $mail['subject'],
            $mail['body'],
            implode("\n", $headers)
        )) {
            return '<div class="alert">Hasło zostało wysłane na podany adres e-mail!</div>';
        } else {
            return '<div class="alert">Wystąpił błąd podczas wysyłania hasła.</div>';
        }
    }
    
    //---------------------------------//
    //     PokazKontaktHaslo          //
    //---------------------------------//
    //  Wyświetla formularz           //
    //  odzyskiwania hasła            //
    //  Zwraca:                       //
    //  - HTML formularza             //
    //---------------------------------//
    
    function PokazKontaktHaslo() {
        // Zabezpieczenie ścieżki formularza
        $action = htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8');
        
        return '
        <div class="form_passrecov">
            <form method="post" action="' . $action . '">
                <table class="form_passrecov">
                    <tr>
                        <td>Email:</td>
                        <td>
                            <input type="email" 
                                   name="email_recov" 
                                   required 
                                   style="width: 100%;" />
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <input type="submit" 
                                   value="Wyślij" 
                                   class="submit-button" />
                        </td>
                    </tr>
                </table>
            </form>
            <div class="buttons2">
                <a class="contact-button" 
                   href="?idp=kontakt">
                    Kontakt
                </a>
            </div>
        </div>';
    }
}
?>