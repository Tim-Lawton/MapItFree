jQuery(document).ready(function ($) {

    var osMap, postcodeService, gaz, markers, lonlat, options, marker, data, gridProjection;

    function init() {


        var options = {
            enableHighAccuracy: true,
            timeout: 5000,
            maximumAge: 0
        };

        navigator.geolocation.getCurrentPosition(show_map, handle_error, options);

        osMap = new OpenSpace.Map('map');

        postcodeService = new OpenSpace.Postcode();
        gaz = new OpenSpace.Gazetteer();
        gridProjection = new OpenSpace.GridProjection();
        markers = new OpenLayers.Layer.Markers("Markers");
        osMap.addLayer(markers);

        osMap.events.remove('dblclick');

        osMap.events.register("dblclick", this, addMarker);


    }


    setTimeout(function () {
        if (!lonlat) {
            code = 1;
            handle_error(code);
        }
    }, 5000); // Wait extra second


    function handle_error(err) {


        if (err.code == 1 || err == 1) {
            osMap.setCenter(new OpenSpace.MapPoint(400000, 400000), 7);
            $('.spinner').fadeOut();
        }
    }

    function show_map(position) {

        var latitude = position.coords.latitude,
            longitude = position.coords.longitude;

        lonlat = new OpenLayers.LonLat(longitude, latitude);

        var geomapPoint = gridProjection.getMapPointFromLonLat(lonlat);


        onResult(geomapPoint);

    }

    function searchGazetteer() {

        var query = document.getElementById("location");


        validPostcode = isValidPostcode(query.value);

        if (validPostcode) {
            postcodeService.getLonLat(query.value, onPCResult);
        } else {
            gaz.getLonLat($('#location').val(), onResult);
        }

        return false;
    }


    function onResult(mapPoint) {



        $('.spinner').fadeOut();

        if (mapPoint !== null) {

            var thisajax = $.ajax({
                url: '/data/postcode-lookup.php',
                data: {postcode: $('#location').val()},
                dataType: "json"
            });

            thisajax.done(function (data) {
                mainAjaxCall(data);
            });


            osMap.setCenter(mapPoint, 5);
            marker = new OpenLayers.Marker(mapPoint);
            markers.addMarker(marker);

        }
    }

    function onPCResult(mapPoint) {
        var postcode = $('#location').val();


        $.ajax({
            url: '/data/postcode-lookup.php',
            data: {postcode: postcode},
            dataType: "json",
            success: function (data) {
                $.ajax({
                    url: '/data/product-lookup.php',
                    data: {local: data.local, town: data.town, county: data.county},
                    success: function (products) {
                        if (products.length > 0) {
                            if ($('.products-holder').html() != '') {
                                $(".products-holder").data('owlCarousel').destroy();
                            }

                            $('.map-details').slideDown();

                            $('.product-bar').slideDown();

                            $('.close-button').addClass('fade-in active');

                            $('.products-holder').html(products);
                            $('.products-holder').owlCarousel({
                                items: 5,
                                navigation: true,
                                navigationText: ['<i class="fa fa-chevron-left"></i>', '<i class="fa fa-chevron-right"></i>'],
                                pagination: false
                            });
                        }
                    }
                });
            }
        });

        $('.spinner').fadeOut();

        if (mapPoint !== null) {

            osMap.setCenter(mapPoint, 5);
            marker = new OpenLayers.Marker(mapPoint);
            markers.addMarker(marker);

        }
    }

    function mainAjaxCall(data) {


        $.ajax({
            url: '/data/product-lookup.php',
            data: {location: true, local: data.local, town: data.town, county: data.county},
            cache: true,
            success: function (products) {
                if (products.length > 0) {
                    if ($('.products-holder').html() != '') {
                        $(".products-holder").data('owlCarousel').destroy();
                    }

                    $('.map-details').slideDown();

                    $('.product-bar').slideDown();

                    $('.close-button').addClass('fade-in active');
                    $('.products-holder').html(products);
                    $('.products-holder').owlCarousel({
                        items: 5,
                        navigation: true,
                        navigationText: ['<i class="fa fa-chevron-left"></i>', '<i class="fa fa-chevron-right"></i>'],
                        pagination: false
                    });
                }
            }
        });
    }

    function getPossibleResults(query, callback) {
        $.ajax({
            url: '/data/postcode-lookup.php',
            data: {checkup: true, postcode: query},
            dataType: "json"
        }).done(callback);
    }


    function isValidPostcode(p) {
        var postcodeRegEx = /^[A-Z]{1,2}[0-9]{1,2}[A-Z]{0,1} ?[0-9][A-Z]{2}$/i;
        return postcodeRegEx.test(p);
    }

    $('.search-form form').submit(function (e) {
        $('.location-resolver').hide();
        e.preventDefault();
        searchGazetteer();
        $('.location span').text($('#location').val());
    });


    init();


    function addMarker(evt) {

        osMap.clearMarkers();

        var posClick = osMap.getLonLatFromViewPortPx(evt.xy);
        var ptClick = osMap.getLonLatFromViewPortPx(evt.xy);

        var clicked_location = gridProjection.getLonLatFromMapPoint(posClick);

        var marker = osMap.createMarker(posClick);

        var postcode = clicked_location.lat + ',' + clicked_location.lon;



        var thisajax = $.ajax({
            url: '/data/postcode-lookup.php',
            data: {latlng: true, postcode: postcode},
            dataType: "json"
        });

        thisajax.done(function (data) {

            $('.location span').text(data.local);
            mainAjaxCall(data);
        });


        OpenLayers.Event.stop(evt);

    }


    $('#map').height(function () {
        headerheight = $('header').height();
        footerheight = $('footer').height();

        minus = headerheight + footerheight;

        windowHeight = $(window).height();
        return windowHeight - minus;
    });

    $(window).resize(function () {
        $('#map').height(function () {
            headerheight = $('header').height();
            footerheight = $('footer').height();

            minus = headerheight + footerheight;

            windowHeight = $(window).height();
            return windowHeight - minus;
        });
    });

    $('.close-button').click(function () {

        if($(this).hasClass('active')){

            $('.map-details,.product-bar').slideUp();

            $(this).find('i').removeClass('fa-chevron-up');
            $(this).find('i').addClass('fa-chevron-down');

            $(this).toggleClass('active');
        } else {
            $('.map-details,.product-bar').slideDown();

            $(this).find('i').removeClass('fa-chevron-down');
            $(this).find('i').addClass('fa-chevron-up');

            $(this).toggleClass('active');
        }

    });


    var supportService = new OpenSpace.SupportService();
    supportService.getTileCount(tileCountResults);


    function tileCountResults(tilesUsed, maxTiles) {
        var s = "Tiles Used: " + tilesUsed + " of " + maxTiles;

        $('.hidden-count').text(s);
    }


    $('#location').focus(function(){
       $('.modal-overlay').fadeOut();
    });

    $('.modal-overlay span').click(function(){
        $('.modal-overlay').fadeOut();
    });

    });