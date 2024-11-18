<?php
class Contact {
    public function WyslijMailKontakt() {
        // Sprawdzenie, czy formularz został wysłany
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Zbieranie danych z formularza
            $name = htmlspecialchars($_POST['name']);
            $email = htmlspecialchars($_POST['email']);
            $message = htmlspecialchars($_POST['message']);

            // Logika wysyłania mailto... w moim wypadku tylko komunikat
            echo "<div class='alert'>Dziękuję za kontakt, $name! Twoja wiadomość została wysłana.</div>";
        }

        // Formularz kontaktowy
        return "
        <form action='' method='post'>
            <label for='name'>Imię:</label><br>
            <input type='text' id='name' name='name' required><br>
            <label for='email'>Email:</label><br>
            <input type='email' id='email' name='email' required><br>
            <label for='message'>Wiadomość:</label><br>
            <textarea id='message' name='message' required></textarea><br>
            <button type='submit'>Wyślij</button>
        </form>";
    }
}

?>
