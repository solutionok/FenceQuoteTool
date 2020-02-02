<?php
global $wpdb;

$types = array();
$styles = array();
$colors = array();
$heights = array();

$typeA = array();
$styleA = array();
$colorA = array();
$heightA = array();

foreach($wpdb->get_results('select * from quote_items', ARRAY_A) as $it){
    if($it['itype']=='type'){
        $types[$it['itemn']]=$it['item_image'];
        $typeA[$it['id']]=$it['itemn'];
    }
    if($it['itype']=='style'){
        $styles[$it['itemn']]=$it['item_image'];
        $styleA[$it['id']]=$it['itemn'];
    }
    if($it['itype']=='color'){
        $colors[$it['itemn']]=$it['item_image'];
        $colorA[$it['id']]=$it['itemn'];
    }
    if($it['itype']=='height'){
        $heights[$it['itemn']]=$it['item_image'];
        $heightA[$it['id']]=$it['itemn'];
    }
}

$materials = array();

foreach($wpdb->get_results('select * from quote_materials', ARRAY_A) as $it){
    $material = array(
        'id'    => $it['id'],
        'price' => $it['mprice'],
        'type'  => $typeA[$it['mtype']],
        'style'  => $styleA[$it['mstyle']],
        'color'  => $colorA[$it['mcolor']],
        'height'  => $heightA[$it['mheight']],
    );
    $materials[] = $material;
}

$gates = array();
foreach($wpdb->get_results('select * from quote_gates', ARRAY_A) as $gt){
    $gates[] = array(
        'id'=>$gt['id'],
        'name'=>$gt['itemn'],
        'price'=>array('1.6'=>$gt['price']),
        'image'=>$gt['item_image'],
        'sg_type'=>$gt['sg_type'],
    );
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Design</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="/wp-content/plugins/quotatool/custom.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDA4-Xd76eSbG9v6HzevULyNYvJoykTD5c&libraries=drawing,geometry,places&callback=initMap" async defer></script>
  <script src="/wp-content/plugins/quotatool/bootbox.min.js"></script>
 
</head>
<body>
  <div>
    <div class="top-bubble d-flex align-items-center justify-content-between">
      <h1 class="text-white top-bar-text" id="top-text">ONLINE FENCING QUOTE CALCULATOR</h1>
      <img src="/wp-content/plugins/quotatool/images/logo.png" class="mr-5">
    </div>
    <div class="mt-5 d-flex flex-column justify-content-center align-items-center" id="section1">
      <div class="p-3 w-100 text-center">
        <select class="custom-select w-50" onchange="set_current_state(this)">
            <option value="" disabled selected>State</option>
            <option value="NSW">New South Wales</option>
            <option value="QLD">Queensland</option>
            <option value="SA">South Australia</option>
            <option value="TAS">Tasmania</option>
            <option value="VIC">Victoria</option>
            <option value="WA">Western Australia</option>
          </select>
      </div>
      <div class="p-3 w-100 text-center">
        <input class="custom-input w-50" type="text" placeholder="Enter Your Address..." id="zipcode">
      </div>
      <div class="p-5">
        <button type="button" class="btn btn-danger btn-lg custom-start-button go-section2">START</button>
      </div>
      <div class="p-5">
         Save time and effort by comparing different fence quotes online
      </div>
    </div>

    <div class="d-flex mt-5 text-center hide-section h-100" id="section2">
      <div class="d-flex flex-column col-4" id="measure-fence-text">
        <div class="p-5">
          <h6>Confirm your fence line on the map, click where you 
            would like the fence to start and then click where you
            would like the fence to finish.</h6>
        </div>
        <div class="p-5">
          <h4>Your Fence Length(m):</h4>
          <input class="custom-input text-center length" type="text" onkeypress="return isNumberKey(event)" disabled>
          <div>
            <input type="checkbox" id="measure-type">Input FenceLength by yourself.
          </div>
        </div>
        <div class="p-5">
          <button type="button" class="btn btn-danger btn-lg custom-start-button custom-form-row w-75" id="go-section3">CHOOSE A FENCE STYLE</button>
        </div>
      </div>
      <div class="d-flex flex-column col-4 hide-section" id="measure-gate-text">
        <div class="p-5">
          <h5>Click on the map where you would like your gate or gates.</h5>
        </div>
        <div class="p-2">
          <div class="p-3">
            <button type="button" class="custom-gradient-button w-100" id="go-section9">SINGLE GATE</button>
          </div>
          <div class="p-3">
            <button type="button" class="custom-gradient-button w-100" id="go-section10">DOUBLE GATES</button>
          </div>
        </div>
        <div class="p-5">
          <h5>Press next if no gate required.</h5>
          <button type="button" class="btn btn-danger btn-lg custom-start-button custom-form-row w-100 go-section11">NEXT</button>
        </div>
      </div>
      <div class="col-8">
        <div class="p-5" id="map"></div>
      </div>
    </div>

    <div class="content-field mt-5 hide-section" id="section3">
      <div class="custom-grey-bar d-flex justify-content-around" id="fence-type-text">
      </div>
      <div class="d-flex justify-content-around custom-form-row" id="fence-type-image">
      </div>
    </div>

    <div class="mt-5 text-center hide-section" id="section6">
      <div class="d-flex flex-wrap" id="style-list">
      </div>
    </div>

    <div class="d-flex hide-section" id="section7">
      <div class="d-flex flex-column p-5 w-50 flex-wrap">
        <h3>WHAT HEIGHT YOU LIKE FENCE</h3>
        <div class="d-flex flex-wrap" id="height-list">
        </div>
      </div>
      <div class="d-flex flex-column p-5 w-50 text-center">
        <h3 class="w-100">DO YOU NEED YOUR EXISTING FENCE REMOVED</h3>
        <i class="fa fa-check-circle mt-5 go-section2" style="color : red; font-size: 160px;"></i>
        <i class="fa fa-times-circle mt-5 go-section3" style="color : red; font-size: 160px;"></i>
      </div>
    </div>

    <div class="mt-5 text-center hide-section" id="section9">
      <div class="d-flex flex-wrap" id="single-gate-list">
      </div>
    </div>

    <div class="mt-5 text-center hide-section" id="section10">
      <div class="d-flex flex-wrap" id="double-gate-list">
      </div>
    </div>

    <div class="d-flex mt-5 hide-section" id="section11">
      <div class="d-flex flex-column col-4 align-items-center">
        <div class="d-flex flex-column p-2 w-100">
          <h4>Name:</h4>
          <div class="d-flex flex-row">
            <div class="input-bubble justify-content-center align-items-center d-flex">
                <i class="fa fa-user" style="font-size: 40px"></i>
            </div>
            <input class="custom-bubble-input w-100" type="text" id="username">
          </div>
        </div>
        <div class="d-flex flex-column p-2 w-100">
          <h4>Email Address:</h4>
          <div class="d-flex flex-row">
            <div class="input-bubble justify-content-center align-items-center d-flex">
                <i class="fa fa-envelope" style="font-size: 40px"></i>
            </div>
            <input class="custom-bubble-input w-100" type="text" id="useremail">
          </div>
        </div>
        <div class="d-flex flex-column p-2 w-100">
          <h4>Contact Number:</h4>
          <div class="d-flex flex-row">
            <div class="input-bubble justify-content-center align-items-center d-flex">
                <i class="fa fa-phone" style="font-size: 40px"></i>
            </div>
            <input class="custom-bubble-input w-100" type="text" id="userphone">
          </div>
        </div>
        <div class="p-5">
          <button type="button" class="btn btn-danger btn-lg custom-start-button custom-form-row w-100" id="go-section12">SEND ME MY FENCING ESTIMATE</button>
        </div>
      </div>
      <div class="col-8">
          <img src="/wp-content/plugins/quotatool/images/11-1.png" width="90%" height="100%">
      </div>
    </div>

    <div class="d-flex flex-column mt-5 align-items-center justify-content-center hide-section" id="section12">
      <div class="custom-gradient-circle-box d-flex align-items-center justify-content-center">
        <i class="fa fa-check" style="font-size: 120px;"></i>
      </div>
      <h1 class="display-1 font-weight-bold p-2">THANK YOU</h1>
      <h4 class="w-50 text-center p-2">
          Thank you for using our online instant fencing cost estimator, we have just emailed your estimate to your email, please feel free to contact us on 
          03 9028 7557 if you have any questions
      </h4>
      <h4 class="p-2">The Team At Fencing Quotes Online</h4>
      <h2 class="p-3">Your Fence Cost</h2>
      <h3 class="text-danger" id="total-price"></h3>
    </div>
  <div> 
  <script>
    var materialList = <?php echo json_encode($materials);?>;
    var gateList = <?php echo json_encode($gates)?>;
    var typeList = <?php echo json_encode($types);?>;
    var styleList = <?php echo json_encode($styles);?>;
    var heightList = <?php echo json_encode($heights);?>;
    var showStyleList = [];
    var curType = '';
    var curStyle = '';
    var curHeight = '';
    var curGate = '';
    var curGatePrice = 0;
    var totalPrice = 0;
    var isGateSeleted = false;

    var map, drawingManager, poly, geocoder,workingGate, autocomplete;
    var markers = [];
    var curIndex = 1;
    var mag = [];
    var polylines = [];
    var fenceLength = 0;
    var fenceInfo = {
        map_lines:[],
        length:0,
        material:'',
        gates:[],
        total_price:0,
    };
    var measureType = true;
    var current_measure = 0;
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

    function show_image_list(typeText) {
      curType = typeText;
      $("#section" + curIndex).addClass('hide-animation');
      setTimeout(function() {
        $("#section" + curIndex).addClass('hide-section');
        $("#section6").removeClass('hide-section');
        curIndex = 6;
        changeTopText(topText[curIndex - 1]);

        showStyleList = [];
        for(var i = 0; i < materialList.length; i++) {
          if(materialList[i].type == typeText) {
            var isExist = 0;
            for(var j = 0; j < showStyleList.length; j++) {
              if(showStyleList[j] == materialList[i].style)
                isExist = 1;
            }
            if(isExist == 0)
              showStyleList.push(materialList[i].style);
          }
        }

        var htmlText = '';
        for(var i = 0 ; i < showStyleList.length; i++) {
          htmlText += ('<div class="d-flex flex-column align-items-center p-2" style="width : 450px">'
                      +     '<h5 class="font-weight-bold">' + showStyleList[i] + '</h5>'
                      +     '<div class="h-100 d-flex align-items-center justify-content">'
                      +       '<img src="' + styleList[showStyleList[i]] + '" class="custom-blackborder-image mw-100">'
                      +     '</div>'
                      +     '<button type="button" class="btn btn-danger btn-lg custom-start-button custom-form-row" onclick="go_height_list(\'' + showStyleList[i] + '\')">CHOOSE STYLE</button>'
                      + '</div>');
        }
        $("#style-list").html(htmlText);
      },500);
    }

    function go_height_list(styleText) {
      curStyle = styleText;
      $("#section" + curIndex).addClass('hide-animation');
      setTimeout(function() {
        $("#section" + curIndex).addClass('hide-section');
        $("#section7").removeClass('hide-section');
        curIndex = 7;
        changeTopText(topText[curIndex - 1]);
      },500);
    }

    function changeTopText(tText) {
        $("#top-text").text(tText);
    }

    function set_height(heightText) {
      curHeight = heightText;

      for(var x in heightList) {
        var tempId = x.replace("." , "\\.");
        $('#'+tempId).removeClass('red-border');
      }
      var tempId = curHeight.replace("." , "\\.");
      $('#' + tempId).addClass("red-border");
    }

    function set_gate(priceParam) {
      curGatePrice = (priceParam["1.6"] * 1);
      $("#section" + curIndex).addClass('hide-animation');
      setTimeout(function() {
        $("#section" + curIndex).addClass('hide-section');
        $("#section11").removeClass('hide-section');
        curIndex = 11;
        changeTopText(topText[curIndex - 1]);
      },500);
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
            // componentRestrictions: { 'country': 'aus' },
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

        google.maps.event.addListener(drawingManager, 'overlaycomplete', function (polyline) {
            console.log(polyline)
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
                    isGateSeleted = true;

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
        
        for(var i=0; i<polylines.length; i++){
            fenceLength += google.maps.geometry.spherical.computeLength(polylines[i].getPath().getArray());
        }
        jQuery('.length').val(fenceLength ? (fenceLength.toFixed(1)) : '');
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
            var address = document.getElementById('zipcode').value;
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

    function isNumberKey(evt)
    {
      if(evt.key == ".")
        return true;
      var charCode = (evt.which) ? evt.which : event.keyCode
      if (charCode > 31 && (charCode < 48 || charCode > 57))
        return false;
      return true;
    }

    function set_current_state(selectObject){
      var value = selectObject.value;

      autocomplete.setComponentRestrictions(
        {"country": "aus"}
      ); 
    }

    function check_email(val){
      if(!val.match(/\S+@\S+\.\S+/)){
          return false;
      }
      if( val.indexOf(' ')!=-1 || val.indexOf('..')!=-1){
          return false;
      }
      return true;
    }

    function checkOrderSendAble(domark){
        fenceInfo['uname'] = jQuery.trim(jQuery('#username').val());
        fenceInfo['uemail'] = jQuery.trim(jQuery('#useremail').val());
        fenceInfo['uphone'] = jQuery.trim(jQuery('#userphone').val());
        
        if(!fenceInfo['uname']) {
          alert("Please Input the name correctly!");
          return false;
        }
        
        if(!check_email(fenceInfo['uemail'])) {
          alert("Please Input the email address correctly!");
          return false;
        }
        
        if(!(/^(1\s|1|)?((\(\d{3}\))|\d{3})(\-|\s)?(\d{3})(\-|\s)?(\d{4})$/.test(fenceInfo['uphone']))) {
          alert("Please Input the phone number correctly!");
          return false;
        }
        
        var valid = (  fenceInfo['length']>0
                    && fenceInfo['map_lines'].length>0
                    && fenceInfo['material']
                    && fenceInfo['total_price']>0);

        console.log("FENCE INFO",fenceInfo);
        console.log("valie" , valid);
                    
        return valid;
    }

    var htmlText = '' , htmlText1 = '' , cnt = 0;
    for(var x in typeList) {
      cnt++;
      htmlText += ("<p class='custom-grey-bar-text'>"+x+"</p>");
      htmlText1 += ("<div class='d-flex flex-column justify-content-center align-items-center'>"
                  +   "<img src='" + typeList[x] + "' class='custom-blackborder-image' width='80%' height='300px'>"
                  +   "<button type='button' class='btn btn-danger btn-lg custom-start-button custom-form-row' onclick='show_image_list(\"" + x + "\")'>CHOOSE STYLE</button>"
                  +"</div>");
    }
    $("#fence-type-text").html(htmlText);
    $("#fence-type-image").html(htmlText1);
    
    htmlText = '';
    for(var x in heightList) {
      htmlText += ('<div class="align-self-end p-1" id="' + x + '" onclick="set_height(\'' + x + '\')">'
                  +   '<img src="' + heightList[x] + '">'
                  +'</div>');
    }
    $("#height-list").html(htmlText);
    htmlText = '' , htmlText1 = '';
    for(var i = 0 ; i < gateList.length; i++) {
      if(gateList[i].sg_type == "0") {
        htmlText += ('<div class="d-flex flex-column align-items-center p-2" style="width : 450px">'
                  +     '<h5 class="font-weight-bold">' + gateList[i].name + '</h5>'
                  +     '<div class="h-100 d-flex align-items-center justify-content">'
                  +       '<img src="' + gateList[i].image + '">'
                  +     '</div>'
                  +     "<button type='button' class='btn btn-danger btn-lg custom-start-button custom-form-row'  onclick='set_gate("+ JSON.stringify(gateList[i].price) + ")'>CHOOSE STYLE</button>"
                  + '</div>');
      } else {
        htmlText1 += ('<div class="d-flex flex-column align-items-center p-2" style="width : 450px">'
                  +     '<h5 class="font-weight-bold">' + gateList[i].name + '</h5>'
                  +     '<div class="h-100 d-flex align-items-center justify-content">'
                  +       '<img src="' + gateList[i].image + '">'
                  +     '</div>'
                  +     "<button type='button' class='btn btn-danger btn-lg custom-start-button custom-form-row'  onclick='set_gate("+ JSON.stringify(gateList[i].price) + ")'>CHOOSE STYLE</button>"
                  + '</div>');
      }
    }
    $("#single-gate-list").html(htmlText);
    $("#double-gate-list").html(htmlText);

    $(document).ready(function () {
        $(".go-section2").click(function () {
            if(!$("#zipcode").val() && curIndex == 1) {
              alert("Please input your address!");
              return;
            }
            if(!curHeight && curIndex == 7) {
              alert("Please choose the height of the fence!");
              return;
            }
            $("#section" + curIndex).addClass('hide-animation');
            setTimeout(function() {
              $("#section" + curIndex).addClass('hide-section');
              curIndex = 2;
              $("#section2").removeClass('hide-animation');
              $("#section2").removeClass('hide-section');
              if(current_measure == 0) {
                  changeTopText(topText[curIndex - 1]);
                  current_measure = 1;
              } else {
                  drawingManager.setDrawingMode(null);
                  window.workingGate = gateList[0];
                  $("#measure-fence-text").addClass('hide-section');
                  $("#measure-gate-text").removeClass('hide-section');
                  changeTopText(topText[7]);
              }
            },500);
        });
        $("#go-section3").click(function () {
            if(!$('.length').val()) {
              alert("Please measure the fencline!");
              return;
            }
            fenceLength = ($('.length').val() * 1);
            $("#section" + curIndex).addClass('hide-animation');
            setTimeout(function() {
              $("#section" + curIndex).addClass('hide-section');
              $("#section3").removeClass('hide-section');
              curIndex = 3;
              changeTopText(topText[curIndex - 1]);
            },500);
        });
        $("#go-section9").click(function () {
            if(isGateSeleted == false && polylines.length) {
              alert("Please select the gate position.");
              return;
            }
            $("#section" + curIndex).addClass('hide-animation');
            setTimeout(function() {
              $("#section" + curIndex).addClass('hide-section');
              $("#section9").removeClass('hide-section');
              curIndex = 9;
              changeTopText(topText[curIndex - 1]);
            },500);
        });
        $("#go-section10").click(function () {
            if(isGateSeleted == false && polylines.length) {
              alert("Please select the gate position.");
              return;
            }
            $("#section" + curIndex).addClass('hide-animation');
            setTimeout(function() {
              $("#section" + curIndex).addClass('hide-section');
              $("#section10").removeClass('hide-section');
              curIndex = 10;
              changeTopText(topText[curIndex - 1]);
            },500);
        });
        $(".go-section11").click(function () {
            if(isGateSeleted == false && polylines.length) {
              alert("Please select the gate position.");
              return;
            }
            $("#section" + curIndex).addClass('hide-animation');
            setTimeout(function() {
              $("#section" + curIndex).addClass('hide-section');
              $("#section11").removeClass('hide-section');
              curIndex = 11;
              changeTopText(topText[curIndex - 1]);
            },500);
        });
        $("#go-section12").click(function () {
            var totalPrice = curGatePrice;
            for(var i = 0; i < materialList.length; i++) {
              if(materialList[i].type == curType && materialList[i].style == curStyle && materialList[i].height == curHeight) {
                totalPrice += ((materialList[i].price * 1) * fenceLength);
                fenceInfo.material = materialList[i];
                fenceInfo.material.height = curHeight;
              }
            }

            fenceInfo['uname'] = jQuery.trim(jQuery('#username').val());
            fenceInfo['uemail'] = jQuery.trim(jQuery('#useremail').val());
            fenceInfo['uphone'] = jQuery.trim(jQuery('#userphone').val());
            fenceInfo['total_price'] = totalPrice;

            if(!checkOrderSendAble(true)) {
              return;
            }
            
            var lg = fenceInfo['map_lines'][0][0];
            var latlng = new google.maps.LatLng(Number(lg[0]), Number(lg[1]));
            
            geocoder.geocode({'latLng': latlng}, function (results, status) {
                fenceInfo['address'] = '';
                if (status === google.maps.GeocoderStatus.OK) {
                    for(var i=0; i<results.length; i++){
                        if(results[i]['formatted_address']){
                            fenceInfo['address'] = results[i]['formatted_address'];
                            break;
                        }
                        if(results[i]['long_name']){
                            fenceInfo['address'] = results[i]['long_name'];
                            break;
                        }
                    }
                }
                
                jQuery.post('?do-order=1', fenceInfo, function(r){
                    if(r=='ok'){
                        $("#total-price").text(Math.round(totalPrice * 100)/100 + " AUD");
                        $("#section" + curIndex).addClass('hide-animation');
                        setTimeout(function() {
                          $("#section" + curIndex).addClass('hide-section');
                          $("#section12").removeClass('hide-section');
                          curIndex = 12;
                          changeTopText(topText[curIndex - 1]);
                        },500);
                    }else{
                        bootbox.alert('Denied your request');
                    }
    
                });
            });
        });
        $("#measure-type").click(function () {
          measureType = !measureType;
          $(".length").prop('disabled', measureType);
          if(measureType == false) {
            fenceLength = 0;
            if(mag.length<1)return;
            while(mag.pop()=='polyline'){
                polylines.pop().setMap(null);
                fenceInfo.map_lines.pop();
            }
            onStateChanged();
            drawingManager.setDrawingMode(null);
          } else {
            drawingManager.setDrawingMode('polyline');
          }
        });
    });
  </script>
</body>
</html>
