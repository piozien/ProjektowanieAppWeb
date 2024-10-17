console.log("Skrypt animacji załadowany");
$("#animacjaTestowa1").on("click", function () {
  console.log("cos");
  $(this).animate(
    {
      width: "500px",
      opacity: 0.4,
      fontSize: "3em",
      borderWidth: "10px",
    },
    1500
  );
});
