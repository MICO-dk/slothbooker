
function getJSON() {
    $.ajax("data/data.json", {
        dataType: "json",
        type: "GET",
        success : function(response) {

            $.each(response, function(index, timeslot) {

                // Isolate the start time of the timeslot from the JSON file
                var start_time = timeslot.start_time;
                start_time = start_time.substr(0, 2);

                var room = timeslot.room;
                //console.log(room);

                // Find id/class combo for timeslot
                var classID = "#" + start_time + ".time-slot";
                
                $("#"+room).children(classID).each(function() {
                    $( this ).css({
                      "background-color": "#D14F4F",
                    });
                });


            });  // End each
        }   // End sucess    

    }).fail(function(jqXHR){
        var errorMessage = "<p>Sorry there has been a problem! ";
        errorMessage += "Please try again later</p>";
        $(".day-container").html(errorMessage);
    }); // End ajax get
}
