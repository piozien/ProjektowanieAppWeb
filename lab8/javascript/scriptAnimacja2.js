$(document).ready(function () {
  console.log("jQuery załadowane");

  $("#animacjaTestowa2").hover(
    function () {
      $(this).animate(
        {
          width: "200px",
          height: "100px",
          opacity: 0.8,
          borderWidth: "5px",
          fontSize: "1.5em",
        },
        300
      );
    },
    function () {
      $(this).animate(
        {
          width: "150px",
          height: "80px",
          opacity: 1,
          borderWidth: "2px",
          fontSize: "1em",
        },
        300
      );
    }
  );
});
