$(document).ready(function () {
  console.log("jQuery załadowane");

  let count = 0;
  $("#animacjaTestowa3").on("click", function () {
    count++;
    $(this).animate(
      {
        fontSize: "+=2em",
        height: "+=20px",
        width: "+=20px",
      },
      500
    );
    console.log("Kliknięć: " + count);
  });
});
