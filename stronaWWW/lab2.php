<!DOCTYPE html>
<html lang="pl">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="Content-Language" content="pl" />
    <meta name="Author" content="Piotr Zienowicz" />
    <title>Historia lotów kosmicznych</title>
    <link rel="stylesheet" href="css/style2.css" />
    <script src="jquery/jQuery3.7.1.js"></script>
    <script src="javascript/kolorujtlo.js" type="text/javascript"></script>
    <script src="javascript/timedate.js" type="text/javascript"></script>
  </head>
  <body onload="startclock()">
    <div class="header">
      <h1 class="body-title">HISTORIA LOTÓW KOSMICZNYCH</h1>
    </div>

    <?php include('php/website/navbar.php'); ?>
    
      <section class="content">
        <form method="POST" name="background">
          <input
            type="button"
            value="żółty"
            ONCLICK="changeBackground('#FFF000')"
          />
          <input
            type="button"
            value="czarny"
            ONCLICK="changeBackground('#000000')"
          />
          <input
            type="button"
            value="biały"
            ONCLICK="changeBackground('#FFFFFF')"
          />
          <input
            type="button"
            value="zielony"
            ONCLICK="changeBackground('#00FF00')"
          />
          <input
            type="button"
            value="niebieski"
            ONCLICK="changeBackground('#0000FF')"
          />
          <input
            type="button"
            value="pomarańczowy"
            ONCLICK="changeBackground('#FF8000')"
          />
          <input
            type="button"
            value="szary"
            ONCLICK="changeBackground('#c0c0c0')"
          />
          <input
            type="button"
            value="czerowny"
            ONCLICK="changeBackground('#FF0000')"
          />
        </form>
      </section>
    </div>
    <div class="content">
      <h2>Ćwiczenia 2 - JavaScript</h2>

      <div class="text">
        Lorem ipsum dolor sit amet, consectetur adipisicing elit. Eaque iste
        eligendi blanditiis maxime cumque esse porro nemo voluptatum sapiente
        aperiam?
        <br />
      </div>
      <div id="zegarek"></div>
      <div id="data"></div>
      <div class="container">
        <div class="left">
          <div id="animacjaTestowa1" class="test-block">
            Kliknij, a się powiększe
          </div>
        </div>
        <div class="center">
          <div id="animacjaTestowa2" class="test-block">
            Najedź kursorem, a się powiększe
          </div>
        </div>
        <div class="right">
          <div id="animacjaTestowa3" class="test-block">Klikaj, abym urósł</div>
        </div>
      </div>
    </div>

    <?php include('php/website/footer.php'); ?>
    <script src="javascript/scriptAnimacja.js"></script>
    <script src="javascript/scriptAnimacja2.js"></script>
    <script src="javascript/scriptAnimacja3.js"></script>
  </body>
</html>
