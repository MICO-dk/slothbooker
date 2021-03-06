jQuery.noConflict();

jQuery(document).ready(function($) {

    
    function createTimestamps() {

        var time = moment().hour(0);
        
        for(var i = 0; i < 24; i++) {
            time = moment().add(i, 'hours').format('HH');
            //console.log(time);
        }    
    }

    function createDay(name, title) {

        var time = "00";

        // Create container for room time slots
        jQuery(".timeslot-container").append("<div class='room' id=" + name + "></div>");

        // Room title
        jQuery("#" + name).append("<h2>" + title + "</h2>");

        // Create room time slots
        for (var i = 0; i < 24; i++) {
            jQuery("#" + name).append( "<div class='time-slot' data-room= '" + name + "' id='" + time + ":00'></div>" );
            time++;
            if(time < 10) {
                time = "0"+time;
            }
        }
    };

    function createWeek() {

        var dayArray = [];  // For weekdays
        var dateArray = []; // For dates

        var dateStampArray = []; // For datestamps, same format as JSON

        var weekLow = 0;
        var weekHigh = 7;

        // Add a month's worth of weekdays and dates to the arrays
        for(var i = 0; i < 28; i++) {

            day = moment().add(i, 'days').format('dddd');
            date = moment().add(i, 'days').format('DD MMM');

            dateStamp = moment().add(i, 'days').format('YYYY-MM-DD');

            dayArray.push(day);
            dateArray.push(date);
            dateStampArray.push(dateStamp);
        }

        // Create day blocks for the first week
        for (var j = weekLow; j < weekHigh; j++) {
            jQuery(".timeline").append("<div class='day-block' id='" + dateStampArray[j] + "'>" + '<p class="day">' + dayArray[j] + '</p>' + '<p class="date">' + dateArray[j] + '</p>' +  "</div>");
        }

        var firstDay = dateStampArray[0];

        jQuery(".day-block").first().addClass("active");

        // Display next week
        jQuery(".next-week").click(function() {

            // If less than 4 weeks has passed
            if(weekHigh < 28) {

                jQuery(".time-slot").each(function(){
                    jQuery(this).removeClass("booked");
                });

                jQuery(".day-block").each(function() {
                    // Clear
                    jQuery(this).removeClass("active");
                    jQuery(this).children("p").remove();

                    // Add the new weekday and date paragraphs
                    // NOTE: Should probably change this, so user decides what is printed in tthe day-block boxes?
                    jQuery(this).append('<p class="day">' + dayArray[weekHigh] + '</p>' + '<p class="date">' + dateArray[weekHigh] + '</p>');
                    
                    // Add datestamp ID
                    jQuery(this).attr('id', dateStampArray[weekHigh]);
                    
                    weekLow += 1;
                    weekHigh += 1;
                });

                jQuery(".day-block").first().addClass("active");

                loadBookings();


            }
            
        });

        // Display previous week
        jQuery(".prev-week").click(function() {
            
            if(weekLow > 0) {

                weekLow -= 7;
                weekHigh -= 7;

                jQuery(".day-block").each(function(){
                    jQuery(this).removeClass("active");
                    jQuery(this).children("p").remove();
                    jQuery(this).append('<p class="day">' + dayArray[weekLow] + '</p>' + '<p class="date">' + dateArray[weekLow] + '</p>');
                    jQuery(this).attr('id', dateStampArray[weekLow]);
                    weekLow += 1;
                    weekHigh += 1;
                });

                weekLow -= 7;
                weekHigh -= 7;

                jQuery(".day-block").first().addClass("active");

                loadBookings();
            }

        });
        
    }

    function createDays() {
        $.ajax({
            url: myAjax.ajaxurl,
            type: "GET",
            data: {
            },

            success: function(data) {
                
                rooms = $.parseJSON(wp_rooms);

                $.each(rooms, function(i, obj){

                    var post_name;
                    var post_title;

                    $.each(obj, function(key, val){

                        if(key == 'post_name') {
                            post_name = val;
                        }
                        if(key == 'post_title') {
                            post_title = val;
                        }
                        
                    });

                    createDay(post_name, post_title);
                });
            },
        })
    };

    function getUser() {
        $.ajax({
            url: myAjax.ajaxurl,
            type: "GET",
            data: {
                action : 'get_current_user'
            },

            success: function(data) {
                console.log(data);
            }
        });
    };

    function loadBookings() {

        self = this;
        /**
         * Delete a single event, calling a server side function.
         */
        $.ajax({
            url: myAjax.ajaxurl,
            type: "POST",
            data: {
                'action' : 'get_bookings_json',
            },

            beforeSend: function(jqXHR, textStatus) {
                //$('[data-id='+ id +']').addClass('_has_transition');
            },

            success: function(data) {
            
                //data = $($.parseHTML(data));
                //data = JSON.parse(data);

                events = $.parseJSON(wp_bookings);

                // Loop through the objects (bookings)
                $.each(events, function(i, obj){

                    var date = "";
                    var start_time = "";
                    var room = "";
                    
                    // Loop through each field in the object (booking)
                    $.each(obj, function(key, val) {


                        //console.log(key + " : " + val);

                        // Check date
                        if(val == get_selected_date()) {
                            date = val;
                        }

                        if(key == "start_time") {

                            $(".time-slot").each(function(){
                                if($(this).attr("id") == val) {
                                    start_time = val;
                                }
                            });
                        }

                        if(key == "room") {

                            $(".time-slot").each(function(){
                                if($(this).attr("data-room") == val) {
                                    room = val;
                                }
                            });
                        }

                    });

                    if(date != "" && start_time != "" && room != "") {
                        //console.log("Date: " + date + ". Time: " + start_time + ". Room: " + room);
                    }

                    if(date == get_selected_date()) {
                        //$(".time-slot").attr({"id": start_time, "data-room": room}).addClass("booked");

                        $('.time-slot[id="' + start_time + '"][data-room="' + room + '"]').addClass("booked");
                    }
                });
                

                //sel.removeClass("booked");
                

            },
  
            error: function(jqXHR, textStatus, errorThrown) {
                
                console.log("error");
                //console.log(jqXHR);
                console.log(textStatus);
                console.log(errorThrown);
            }

        }); //end $.ajax
    };



    moment.locale('dk');

    createTimestamps();

    createDays();

    createWeek();

    loadBookings();

    jQuery(".day-block").click(function() {


        jQuery(this).addClass("active").siblings().removeClass("active");

        jQuery(".time-slot").each(function(){
            jQuery(this).removeClass("booked");
        });

        loadBookings();
        
        //getJSON();

    });

});
