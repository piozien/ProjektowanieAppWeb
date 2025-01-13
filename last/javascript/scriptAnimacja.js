$(document).ready(function () {
  console.log("jQuery załadowane");

  $("#animacjaTestowa1").on("click", function () {
    console.log("Element #animacjaTestowa1 kliknięty");
    $(this).animate(
      {
        width: "200px",
        height: "100px",
        opacity: 0.8,
        fontSize: "2em",
        borderWidth: "5px",
      },
      500
    );
  });
});
