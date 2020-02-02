var map, drawingManager, poly, geocoder,workingGate, autocomplete;
var markers = [];
var curIndex = 1;
var mag = [];
var polylines = [];
var fenceInfo = {
    map_lines:[],
    length:0,
    material:'',
    gates:[],
    total_price:0,
};
var current_measure = 0;
var fenceHeight = 0;
var topText = [
    "ONLINE FENCING QUOTE CALCULATOR",
    "MEASURE YOUR FENCELINE",
    "WHAT TYPE OF FENCE WOULD YOU LIKE?",
    "WHAT TYPE OF TIMBER FENCE WOULD YOU LIKE?",
    "WHAT TYPE OF COLORBOND FENCE WOULD YOU LIKE?",
    "WHAT TYPE OF PICKET FENCE WOULD YOU LIKE?",
    "CHOOSE THE HEIGHT OF YOUR FENCE.",
    "DO YOU NEED A GATE?",
    "WHAT TYPE OF SINGLE GATE WOULD YOU LIKE?",
    "WHAT TYPE OF DOUBLE GATE WOULD YOU LIKE?",
    "YOUR INFORMATION",
    "FENCING QUOTES ONLINE",
];
$(document).ready(function () {
    $(".go-section2").click(function () {
        $("#section" + curIndex).addClass('hide-section');
        curIndex = 2;
        $("#section2").removeClass('hide-section');
        if(current_measure == 0) {
            changeTopText(topText[curIndex - 1]);
            current_measure = 1;
        } else {
            drawingManager.setDrawingMode(null);
            window.workingGate = gateList[jQuery(this).attr('gate-iid')];
            $("#measure-fence-text").addClass('hide-section');
            $("#measure-gate-text").removeClass('hide-section');
            changeTopText(topText[7]);
        }
    });
    $("#go-section3").click(function () {
        $("#section" + curIndex).addClass('hide-section');
        $("#section3").removeClass('hide-section');
        curIndex = 3;
        changeTopText(topText[curIndex - 1]);
    });
    $("#go-section4").click(function () {
        $("#section" + curIndex).addClass('hide-section');
        $("#section4").removeClass('hide-section');
        curIndex = 4;
        changeTopText(topText[curIndex - 1]);
    });
    $("#go-section5").click(function () {
        $("#section" + curIndex).addClass('hide-section');
        $("#section5").removeClass('hide-section');
        curIndex = 5;
        changeTopText(topText[curIndex - 1]);
    });
    $("#go-section6").click(function () {
        $("#section" + curIndex).addClass('hide-section');
        $("#section6").removeClass('hide-section');
        curIndex = 6;
        changeTopText(topText[curIndex - 1]);
    });
    $(".go-section7").click(function () {
        $("#section" + curIndex).addClass('hide-section');
        $("#section7").removeClass('hide-section');
        curIndex = 7;
        changeTopText(topText[curIndex - 1]);
    });
    $("#go-section9").click(function () {
        $("#section" + curIndex).addClass('hide-section');
        $("#section9").removeClass('hide-section');
        curIndex = 9;
        changeTopText(topText[curIndex - 1]);
    });
    $("#go-section10").click(function () {
        $("#section" + curIndex).addClass('hide-section');
        $("#section10").removeClass('hide-section');
        curIndex = 10;
        changeTopText(topText[curIndex - 1]);
    });
    $(".go-section11").click(function () {
        $("#section" + curIndex).addClass('hide-section');
        $("#section11").removeClass('hide-section');
        curIndex = 11;
        changeTopText(topText[curIndex - 1]);
    });
    $("#go-section12").click(function () {
        $("#section" + curIndex).addClass('hide-section');
        $("#section12").removeClass('hide-section');
        curIndex = 12;
        changeTopText(topText[curIndex - 1]);
    });
    $("#1.0").click(function () {
        fenceHeight = 1.0;
    });
    $("#1.2").click(function () {
        fenceHeight = 1.2;
    });
    $("#1.5").click(function () {
        fenceHeight = 1.5;
    });
    $("#1.6").click(function () {
        fenceHeight = 1.6;
    });
    $("#1.8").click(function () {
        fenceHeight = 1.8;
    });
    $("#1.95").click(function () {
        fenceHeight = 1.95;
    });
    $("#2.0").click(function () {
        fenceHeight = 2.0;
    });
    $("#2.1").click(function () {
        fenceHeight = 2.1;
    });
    $("#2.3").click(function () {
        fenceHeight = 2.3;
    });
    $("#2.5").click(function () {
        fenceHeight = 2.5;
    });
});

function changeTopText(tText) {
    $("#top-text").text(tText);
}

function initMap() {
    var melborne = new google.maps.LatLng(-37.8136, 144.9631);

    map = new google.maps.Map(document.getElementById('map'), {
        center: melborne,
        zoom: 10,
        streetViewControl: false,
        mapTypeId: 'satellite',
        tilt: 0,
        zoomControl: true,
        mapTypeControl: false,
        scaleControl: false,
        streetViewControl: false,
        rotateControl: false,
        fullscreenControl: false
    });

    geocoder = new google.maps.Geocoder;

    autocomplete = new google.maps.places.Autocomplete(document.getElementById('zipcode'), {
        componentRestrictions: { 'country': 'aus' },
    });

    autocomplete.addListener('place_changed', function () {
        searchPosition();
    });

    drawingManager = new google.maps.drawing.DrawingManager({
        drawingMode: 'polyline',
        drawingControlOptions: {
            drawingModes: ['polyline'],
            position: google.maps.ControlPosition.TOP_LEFT,
        },
        polylineOptions: {
            strokeColor: 'darkred',
            strokeWeight: 5,
            clickable: false,
            editable: false,
        },
        map: map,
    });

    google.maps.event.addListener(drawingManager, 'polylinecomplete', function (polyline) {
        if (polyline.getPath().getArray().length < 2) {
            polyline.map(null);
            return;
        }

        mag.push('polyline');
        polylines.push(polyline);
        fenceInfo.map_lines.push(eval('[' + polyline.getPath().getArray().toString().replace(/ /g, '').replace(/\(/g, '[').replace(/\)/g, ']') + ']'));

        onStateChanged();
    });

    google.maps.event.addListener(map, 'click', function (event) {
        if (!window.workingGate || !window.workingGate.name || !polylines.length) return false;

        for (var i = 0; i < polylines.length; i++) {
            var pp = polylines[i].getPath().getArray();

            for (var j = 1; j < pp.length; j++) {
                var distance = google.maps.geometry.spherical.computeDistanceBetween(event.latLng, pp[j - 1]);
                var distance1 = google.maps.geometry.spherical.computeDistanceBetween(event.latLng, pp[j]);
                var linelength = google.maps.geometry.spherical.computeDistanceBetween(pp[j], pp[j - 1]);
                var diff = (distance + distance1) - linelength;

                if (diff >= 0.3) {
                    continue;
                }

                var goldStar = {
                    path: 'M0-48c-9.8 0-17.7 7.8-17.7 17.4 0 15.5 17.7 30.6 17.7 30.6s17.7-15.4 17.7-30.6c0-9.6-7.9-17.4-17.7-17.4z',
                    fillColor: 'yellow',
                    fillOpacity: 0.8,
                    scale: .3,
                    strokeColor: 'gold',
                };

                var marker = new google.maps.Marker({
                    position: event.latLng,
                    icon: goldStar,
                    map: map,
                });

                markers.push(marker);
                mag.push('marker');

                var materialHeight = parseFloat(fenceInfo.material.height).toString();
                var gateprice = 0;
                if (!workingGate.price[materialHeight]) {
                    for (iii in workingGate.price) {
                        if (gateprice < parseFloat(workingGate.price[iii])) {
                            gateprice = parseFloat(workingGate.price[iii]);
                        }
                    }
                } else {
                    var gateprice = workingGate.price[materialHeight];
                }

                workingGate.map_mark = event.latLng.toString().replace(/\(/g, '[').replace(/\)/g, ']');
                workingGate.valid_price = gateprice;
                fenceInfo.gates.push(workingGate);
                workingGate = null;
                drawingManager.setDrawingMode('polyline');

                onStateChanged();
                return;
            }

        }

    });
}

function onStateChanged(){
    jQuery('.action-guide').text('');
    if(polylines.length||markers.length){
        jQuery('#undo').show();
        jQuery('.position-search-box').hide();
        jQuery('.order-info-table').show();
    }else{
        jQuery('#undo').hide();
        jQuery('.position-search-box').show();
        jQuery('.order-info-table').hide();
        jQuery('.action-guide').text('Confirm the fence position on the map. and draw your fence with the mouse dragging on the map.');
    }
    
    if(window.workingGate){
        jQuery('.action-guide').text('Click a point on fenceline, then will deploy with a gate.');
    }
    
    var fenceLength = 0;
    for(var i=0; i<polylines.length; i++){
        fenceLength += google.maps.geometry.spherical.computeLength(polylines[i].getPath().getArray());
    }
    console.log(fenceLength);
    jQuery('.length').val(fenceLength ? (fenceLength.toFixed(1) + 'M') : ' - ');
    fenceInfo['length'] = fenceLength;
    
    var materialPrice = 0;
    if(fenceInfo['material']){
        materialPrice = fenceInfo['material']['price'];
        jQuery('.material').text(fenceInfo['material']['style'] + ', ' + fenceInfo['material']['color'] + ', $' + fenceInfo['material']['price']);
        jQuery('.height').text(fenceInfo['material']['height']+'M');
    }else jQuery('.material,.height').text(' - ');
    
    
    var gatePrice = 0;
    var gatesLabel = '';
    for(var i=0; i<fenceInfo['gates'].length; i++){
        gatesLabel += '<div>'+fenceInfo['gates'][i]['name']+', $'+ fenceInfo['gates'][i]['valid_price'] +'</div>';
        gatePrice += fenceInfo['gates'][i]['valid_price']*1;
    }
    jQuery('.gates').html(gatesLabel?gatesLabel:'-');

    if(fenceInfo['gates'].length>=3)jQuery('#add-gate').hide();
    else jQuery('#add-gate').show();
    
    
    fenceInfo['total_price'] = materialPrice * fenceLength + gatePrice;
    jQuery('.price').text(fenceInfo['total_price']?('$' + Number(fenceInfo['total_price']).toFixed(0)):'-');
    
    if(fenceLength)jQuery('#add-gate').show();
    else jQuery('#add-gate').hide();
}

function searchPosition(){
        
    if (!place || typeof(place.place_id)=='undefined' || !place.place_id) {
        var address = document.getElementById('zipcode').value + ',australia';
        var param = { 'address': address};
    }else{
        var place = autocomplete.getPlace();
        var param = {'placeId': place.place_id};
    }
    
    geocoder.geocode(param, function(results, status) {
      if (status !== 'OK') {
        window.alert('Geocoder failed due to: ' + status);
        return;
      }
      map.setZoom(18);
      map.setCenter(results[0].geometry.location);
      var marker = new google.maps.Marker({
          map: map
        });
      marker.setPlace({
        placeId: results[0].place_id,
        location: results[0].geometry.location
      });
      marker.setVisible(true);
    });
}