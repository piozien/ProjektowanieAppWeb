console.log("Skrypt animacji 3 załadowany");
$("#animacjaTestowa3").on("click", function(){
    if (!$(this).is("animated")) {
        $(this).animate({
            width: "+-" + 50,
            height: "+=" + 10,
            opacity: "-=" + 0.1,
            duration: 3000
        });
    }
});