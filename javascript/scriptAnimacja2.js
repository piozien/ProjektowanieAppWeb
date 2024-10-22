console.log("Skrypt animacji 2 załadowany");
$("#animacjaTestowa2").on({
  mouseover: function () {
    $(this).animate(
      {
        width: 300,
      },
      800
    );
  },
  mouseout: function () {
    $(this).animate(
      {
        width: 200,
      },
      800
    );
  },
});
