<!DOCTYPE html>
<html lang="pl">
  <head>
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
    <meta http-equiv="Content-Language" content="pl" />
    <meta name="Author" content="Piotr Zienowicz" />
    <title>Historia lotów kosmicznych</title>
    <link rel="stylesheet" href="../css/style.css" />
  </head>
  <body>
    <div class="header">
      <h1 class="body-title">KONTAKT</h1>
    </div>

    <?php include('../stronaWWW/php/website/navbar.php'); ?>
    <div class="content">
      <h2>Kontakt</h2>

      <div class="text">
        <p>
          Jeśli masz pytania, sugestie lub chcesz się podzielić swoją opinią,
          napisz do mnie!
        </p>
        <form
          action="mailto:example@example.com"
          method="post"
          enctype="text/plain"
        >
          <label for="name">Imię:</label><br />
          <input type="text" id="name" name="name" required /><br /><br />

          <label for="email">E-mail:</label><br />
          <input type="email" id="email" name="email" required /><br /><br />

          <label for="message">Wiadomość:</label><br />
          <textarea id="message" name="message" rows="4" required></textarea
          ><br /><br />

          <button type="submit">Wyślij wiadomość</button>
        </form>
      </div>
    </div>
    <?php include('../stronaWWW/php/website/footer.php'); ?>
  </body>
</html>
