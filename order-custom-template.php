<?php
get_header();
global $wpdb;

$types = array();
$styles = array();
$colors = array();
$heights = array();
$states = array();

$typeA = array();
$styleA = array();
$colorA = array();
$heightA = array();
$stateA = array();

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
    if($it['itype']=='state'){
      $stateA[$it['id']]=$it['itemn'];
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
        'state'  => $stateA[$it['mstate']],
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
  <title>QUOTATOOL</title>
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
  <script>
  function initMap() {
        var melborne = new google.maps.LatLng(-37.8136, 144.9631);

        map = new google.maps.Map(document.getElementById('map'), {
            center: melborne,
            zoom: 10,
            streetViewControl: false,
            mapTypeId: 'satellite',
            tilt: 0,
            zoomControl: false,
            mapTypeControl: false,
            scaleControl: false,
            streetViewControl: false,
            rotateControl: false,
            fullscreenControl: false
        });

        geocoder = new google.maps.Geocoder;

        autocomplete = new google.maps.places.Autocomplete(document.getElementById('zipcode'));

        autocomplete.addListener('place_changed', function () {
            searchPosition();
        });

        drawingManager = new google.maps.drawing.DrawingManager({
            drawingMode: 'polyline',
            drawingControlOptions: {
                drawingModes: ['polyline'],
                position: null,
                // position: google.maps.ControlPosition.TOP_LEFT,
            },
            polylineOptions: {
                strokeColor: '#1E90FF',
                strokeWeight: 6,
                clickable: false,
                editable: false,
            },
            map: map,
        });

        google.maps.event.addListener(drawingManager, 'polylinecomplete', function (polyline) {
          
            if (polyline.getPath().getArray().length < 2) {
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

                    // if (diff >= 0.3) {
                    //     continue;
                    // }

                    var goldStar = {
                        path: 'M0-48c-9.8 0-17.7 7.8-17.7 17.4 0 15.5 17.7 30.6 17.7 30.6s17.7-15.4 17.7-30.6c0-9.6-7.9-17.4-17.7-17.4z',
                        fillColor: 'yellow',
                        fillOpacity: 0.8,
                        scale: .7,
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
                    //fenceInfo.gates.push(workingGate);
                    workingGate = null;
                    drawingManager.setDrawingMode('polyline');
                    isGateSeleted = true;

                    onStateChanged();
                    return;
                }

            }

        });

        google.maps.event.addListener(map, 'dragend', function (event) {
            if(current_measure != 1) {
                isMapDrawingMode = -1
                drawingManager.setDrawingMode(null)
                return;
            }
            isMapDrawingMode = 1
            drawingManager.setDrawingMode('polyline')
        })
    }
  </script>
    <style>
        .gmnoprint{
            display:none;
        }
    </style>
</head>
<body>
  <div style="position: relative; width: 1920px;">
    <div class="top-bubble d-flex align-items-center justify-content-between">
      <h2 class="text-white top-bar-text" id="top-text">ONLINE FENCING QUOTE CALCULATOR</h2>
      <img src="/wp-content/plugins/quotatool/images/logo.png" class="mr-5 img-responsive">
    </div>
    <button type="button" class="btn btn-danger custom-back-button hide-section">Back</button>
    <div class="mt-5 d-flex flex-column justify-content-center align-items-center" id="section1">
      <div class="p-3 w-100 text-center">
        <select class="custom-select w-50" onchange="set_current_state(this)">
          <option value="" disabled selected>State</option>
          <?php foreach($stateA as $key=>$label)echo '<option value="'.$label.'">'.$label.'</option>';?>
        </select>
      </div>
      <div class="p-3 w-100 text-center">
        <input class="custom-input w-50" type="text" placeholder="Enter Your Address..." id="zipcode">
      </div>
      <div class="p-5">
        <button type="button" class="btn btn-danger btn-lg custom-start-button" onclick="goSection2()">START</button>
      </div>
      <div class="p-5 text-center">
         Save time and effort by comparing different fence quotes online
         <h5>Best used on Laptop or Computer</h5>
      </div>
    </div>

    <div class="row mt-5 hide-section" id="section2">
      <div class="col-md-8 col-sm-12 text-center" style="min-height: 300px; position: relative;">
        <div id="map" style="min-height: 300px;"></div>
        <div style="width: 60px; background-color: rgba(50,50,50,0.8); text-align:center; position: absolute; left : 15px; top: 0px;line-height: 1;">
          <button style="width: 50px; height: 50px;line-height: 1; margin-top: 10px;" id="zoom_in">
            <i class="fa fa-plus" style="font-size: 24px"></i>
          </button>
          <button style="width: 50px; height: 50px;line-height: 1;" id="zoom_out">
            <i class="fa fa-minus" style="font-size: 24px"></i>
          </button>
          <button style="width: 50px; height: 50px;line-height: 1;margin-top: 20px; margin-bottom: 10px;" id="set_position_first">
            <i class="fa fa-crosshairs" style="font-size: 24px"></i>
          </button>
        </div>
      </div>
      <div class="d-flex flex-column col-md-4 col-sm-12 text-center" id="measure-fence-text">
        <div class="p-4">
          <h6>Confirm your fence line on the map, click where you 
            would like the fence to start and then click where you
            would like the fence to finish.</h6>
        </div>
        <div class="p-4">
          <button type="button" class="btn btn-danger btn-lg custom-start-button custom-form-row w-75" id="resetFenceline">UNDO & REPICK</button>
        </div>
        <div class="p-4">
          <h4>Your Fence Length(m):</h4>
          <input class="custom-input text-center length" type="text" onkeypress="return isNumberKey(event)" disabled>
          <div>
            <input type="checkbox" id="measure-type">Input FenceLength by yourself.
          </div>
        </div>
        <div class="p-4">
          <button type="button" class="btn btn-danger btn-lg custom-start-button custom-form-row w-100 go-section3">CHOOSE A FENCE STYLE</button>
        </div>
      </div>
      <div class="d-flex flex-column col-md-4 col-sm-12 hide-section text-center" id="measure-gate-text">
        <div class="p-5">
          <h3 class="text-danger">Click one point on the fence line to place the gate!</h3>
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
          <h5>Press skip if no gate required.</h5>
          <button type="button" class="btn btn-danger btn-lg custom-start-button custom-form-row w-100 go-section11">I DON'T NEED GATE</button>
        </div>
      </div>
    </div>

    <div class="content-field mt-5 hide-section" id="section3">
      <div class="d-flex flex-wrap" id="material-list">
      </div>
    </div>

    <div class="mt-5 text-center hide-section" id="section6">
      <div class="d-flex flex-wrap" id="style-list">
      </div>
    </div>

    <div class="row mt-5 hide-section" id="section7">
      <div class="d-flex flex-column p-5 col-md-6 col-sm-12 flex-wrap">
        <h4>WHAT HEIGHT YOU LIKE FENCE</h4>
        <div class="d-flex flex-wrap" id="height-list">
        </div>
      </div>
      <div class="d-flex flex-column p-2 p-5 col-md-6 col-sm-12 text-center">
        <h4 class="w-100">DO YOU NEED YOUR EXISTING FENCE REMOVED</h4>
        <div class="mt-5">
          <button type="button" class="btn btn-danger btn-lg custom-start-button" id="pull_yes">YES</button>
        </div>
        <div class="mt-5">
        <button type="button" class="btn btn-danger btn-lg custom-start-button" id="pull_no">NO</button>
        </div>
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

    <div class="row mt-5 hide-section" id="section11">
      <div class="d-flex flex-column align-items-center col-md-4 col-xs-12">
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
      <div class="col-md-8 col-sm-12 text-center">
          <img src="/wp-content/plugins/quotatool/images/11-1.png" width="90%" height="100%">
      </div>
    </div>

    <div class="d-flex flex-column mt-5 align-items-center justify-content-center hide-section" id="section12">
      <div class="custom-gradient-circle-box d-flex align-items-center justify-content-center">
        <i class="fa fa-check" style="font-size: 120px;"></i>
      </div>
      <h1 class="display-1 font-weight-bold p-2 text-center">THANK YOU</h1>
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

    var isMapDrawingMode = 1;
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
    var curState = -1;
    var totalPrice = 0;
    var isGateSeleted = false;
    var lineStart = 0;
    var curPull = "";

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
        // "WHAT TYPE OF PICKET FENCE WOULD YOU LIKE?",
        "WHAT TYPE OF FENCE WOULD YOU LIKE?",
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
        $("#section6").removeClass('hide-animation');
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
                      +     '<div class="h-100 d-flex align-items-center justify-content" onclick="go_height_list(\'' + showStyleList[i] + '\')">'
                      +       '<img src="' + styleList[showStyleList[i]] + '" class="custom-blackborder-image" width="250px" height="250px">'
                      +     '</div>'
                      +     '<button type="button" class="btn btn-danger btn-lg custom-start-button custom-form-row" onclick="go_height_list(\'' + showStyleList[i] + '\')">CHOOSE STYLE</button>'
                      + '</div>');
        }
        $("#style-list").html(htmlText);
      },500);
    }

    function go_height_list(styleText) {
      curStyle = styleText;

      var av_heights = [];
      for(var i = 0; i < materialList.length; i++) {
        if(materialList[i].type == curType && materialList[i].style == curStyle && materialList[i].state == curState ) {
          av_heights.push(materialList[i].height);
        }
      }
      htmlText = '';
      for(var x in heightList) {
      if(!av_heights.includes(x)) continue;
      htmlText += ('<div class="align-self-end p-1" id="' + x + '" onclick="set_height(\'' + x + '\')">'
                  +   '<img src="' + heightList[x] + '">'
                  +'</div>');
      }
      $("#height-list").html(htmlText);

      $("#section" + curIndex).addClass('hide-animation');
      setTimeout(function() {
        $("#section" + curIndex).addClass('hide-section');
        $("#section7").removeClass('hide-section');
        $("#section7").removeClass('hide-animation');
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

    function set_gate(index) {
      var priceParam = gateList[index].price;
      curGate = gateList[index];
      curGatePrice = (priceParam["1.6"] * 1);
      $("#section" + curIndex).addClass('hide-animation');
      setTimeout(function() {
        $("#section" + curIndex).addClass('hide-section');
        $("#section11").removeClass('hide-section');
        $("#section11").removeClass('hide-animation');
        curIndex = 11;
        changeTopText(topText[curIndex - 1]);
      },500);
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
            jQuery('.action-guide').text('Click one point on the fence line to place the gate!');
        }

        fenceLength  = 0;

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

    function isNumberKey(evt){
      if(evt.key == ".")
        return true;
      var charCode = (evt.which) ? evt.which : event.keyCode
      if (charCode > 31 && (charCode < 48 || charCode > 57))
        return false;
      return true;
    }

    function set_current_state(selectObject){
      var value = selectObject.value;
      curState = value;
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
        
        if(!(isNaN(fenceInfo['uphone']) == false && fenceInfo['uphone'].length >= 5)) {
          alert("Please Input the phone number correctly!");
          return false;
        }

        if(!(fenceInfo['total_price']>0) || fenceInfo['material'] == "") {
          alert("There is no matching Item!");
          return false;
        }

        if(measureType && !(fenceInfo['map_lines'].length > 0)){
          alert("Please draw your Fence Line!");
          return false;
        }
        
        var valid = (  fenceInfo['length']>0
                    && fenceInfo['material']
                    && fenceInfo['total_price']>0);
                    
        return valid;
    }

    function removeMarker() {
      for (var i = 0; i < markers.length; i++) {
        markers[i].setMap(null);
      }
      markers = [];
    }

    function removePolyline() {
      fenceLength = 0;
      if(mag.length<1)  return;
      while(mag.pop()=='polyline'){
          polylines.pop().setMap(null);
          fenceInfo.map_lines.pop();
      }
      onStateChanged();
      drawingManager.setDrawingMode(null);
      drawingManager.setDrawingMode('polyline');
    }

    function goSection2() {
      if(!$("#zipcode").val() && curIndex == 1) {
        alert("Please input your address!");
        return;
      }
      if(curState == -1 && curIndex == 1) {
        alert("Please select the state!");
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
            removePolyline();
            drawingManager.setDrawingMode('polyline');
            changeTopText(topText[curIndex - 1]);
            $(".custom-back-button").removeClass('hide-section');
            current_measure = 1;
            lineStart = 0;
        } else {
            removeMarker();
            current_measure = 2;
            drawingManager.setDrawingMode(null);
            window.workingGate = gateList[0];
            $("#measure-fence-text").addClass('hide-section');
            $("#measure-gate-text").removeClass('hide-section');
            changeTopText(topText[7]);
        }
      },500);
    }

    var htmlText = '';
    for(var x in typeList) {
      htmlText += ('<div class="d-flex flex-column align-items-center p-2" style="width : 350px">'
                      +     '<h5 class="font-weight-bold">' + x + '</h5>'
                      +     '<div class="h-100 d-flex align-items-center justify-content" onclick="show_image_list(\'' + x + '\')">'
                      +       '<img src="' + typeList[x] + '" class="custom-blackborder-image" style="width:250px;height:250px;">'
                      +     '</div>'
                      +     '<button type="button" class="btn btn-danger btn-lg custom-start-button custom-form-row" onclick="show_image_list(\'' + x + '\')">CHOOSE STYLE</button>'
                      + '</div>');
    }
    $("#material-list").html(htmlText);
    
    htmlText = '';
    for(var x in heightList) {
      htmlText += ('<div class="align-self-end p-1" id="' + x + '" onclick="set_height(\'' + x + '\')">'
                  +   '<img src="' + heightList[x] + '">'
                  +'</div>');
    }
    $("#height-list").html(htmlText);
    htmlText = '' , htmlText1 = '';
    console.log(gateList);
    for(var i = 0 ; i < gateList.length; i++) {
      if(gateList[i].sg_type == "0") {
        htmlText += ('<div class="d-flex flex-column align-items-center p-2" style="width : 450px">'
                  +     '<h5 class="font-weight-bold">' + gateList[i].name + '</h5>'
                  +     "<div class='h-100 d-flex align-items-center justify-content' onclick='set_gate("+ i + ")'>"
                  +       '<img src="' + gateList[i].image + '">'
                  +     '</div>'
                  +     "<button type='button' class='btn btn-danger btn-lg custom-start-button custom-form-row'  onclick='set_gate("+ i + ")'>CHOOSE STYLE</button>"
                  + '</div>');
      } else {
        htmlText1 += ('<div class="d-flex flex-column align-items-center p-2" style="width : 450px">'
                  +     '<h5 class="font-weight-bold">' + gateList[i].name + '</h5>'
                  +     "<div class='h-100 d-flex align-items-center justify-content' onclick='set_gate("+ i + ")'>"
                  +       '<img src="' + gateList[i].image + '">'
                  +     '</div>'
                  +     "<button type='button' class='btn btn-danger btn-lg custom-start-button custom-form-row'  onclick='set_gate("+ i + ")'>CHOOSE STYLE</button>"
                  + '</div>');
      }
    }
    $("#single-gate-list").html(htmlText);
    $("#double-gate-list").html(htmlText);
    
    $(document).ready(function () {
        $("#pull_yes").click(function () {
          curPull = "Yes";
          goSection2();
        });
        $("#pull_no").click(function () {
          curPull = "No";
          goSection2();
        });
        $(".go-section2").click(function () {
            if(!$("#zipcode").val() && curIndex == 1) {
              alert("Please input your address!");
              return;
            }
            if(curState == -1 && curIndex == 1) {
              alert("Please select the state!");
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
                  removePolyline();
                  changeTopText(topText[curIndex - 1]);
                  $(".custom-back-button").removeClass('hide-section');
                  current_measure = 1;
                  lineStart = 0;
              } else {
                  removeMarker();
                  current_measure = 2;
                  drawingManager.setDrawingMode(null);
                  window.workingGate = gateList[0];
                  $("#measure-fence-text").addClass('hide-section');
                  $("#measure-gate-text").removeClass('hide-section');
                  changeTopText(topText[7]);
              }
            },500);
        });
        $(".go-section3").click(function () {
            if(!$('.length').val()) {
              alert("Please measure the fencline!");
              return;
            }
            fenceLength = ($('.length').val() * 1);
            $("#section" + curIndex).addClass('hide-animation');
            setTimeout(function() {
              $("#section" + curIndex).addClass('hide-section');
              $("#section3").removeClass('hide-section');
              $("#section3").removeClass('hide-animation');
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
              $("#section9").removeClass('hide-animation');
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
              $("#section10").removeClass('hide-animation');
              curIndex = 10;
              changeTopText(topText[curIndex - 1]);
            },500);
        });
        $(".go-section11").click(function () {
            $("#section" + curIndex).addClass('hide-animation');
            setTimeout(function() {
              $("#section" + curIndex).addClass('hide-section');
              $("#section11").removeClass('hide-section');
              $("#section11").removeClass('hide-animation');
              curIndex = 11;
              changeTopText(topText[curIndex - 1]);
            },500);
        });
        $("#go-section12").click(function () {
            $(".custom-back-button").addClass('hide-section');
            var totalPrice = curGatePrice;
            for(var i = 0; i < materialList.length; i++) {
              if(materialList[i].type == curType && materialList[i].style == curStyle && materialList[i].height == curHeight && materialList[i].state == curState && materialList[i].color == curPull) {
                totalPrice += ((materialList[i].price * 1) * fenceLength);
                fenceInfo.material = materialList[i];
                fenceInfo.material.height = curHeight;
                fenceInfo.material.state = curState;
              }
            }

            fenceInfo['uname'] = jQuery.trim(jQuery('#username').val());
            fenceInfo['uemail'] = jQuery.trim(jQuery('#useremail').val());
            fenceInfo['uphone'] = jQuery.trim(jQuery('#userphone').val());
            fenceInfo['length'] = jQuery('.length').val() * 1;
            if(fenceInfo['length'] < 25)
              totalPrice += 275;
            if(totalPrice < 1000)
              totalPrice = 1000.00;
            fenceInfo['total_price'] = '' + Math.round(totalPrice * 100)/100;
            fenceInfo['measure_type'] = measureType;

            fenceInfo['cur_pull'] = curPull;

            if(!checkOrderSendAble(true)) {
              return;
            }
            
            fenceInfo['address'] = document.getElementById('zipcode').value;
            if(curGatePrice != 0) {
              fenceInfo.gates.push(curGate);
            }
            jQuery.post('?do-order=1', fenceInfo, function(r){
                if(r=='ok'){
                    $("#total-price").text('$' + Math.round(totalPrice * 100)/100 + " AUD");
                    $("#section" + curIndex).addClass('hide-animation');
                    setTimeout(function() {
                      $("#section" + curIndex).addClass('hide-section');
                      $("#section12").removeClass('hide-section');
                      $("#section12").removeClass('hide-animation');
                      curIndex = 12;
                      changeTopText(topText[curIndex - 1]);
                    },500);
                }else{
                    bootbox.alert('Denied your request');
                }
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
        $("#resetFenceline").click(function () {
          fenceLength = 0;
          if(mag.length<1)  return;
          while(mag.pop()=='polyline'){
              polylines.pop().setMap(null);
              fenceInfo.map_lines.pop();
          }
          onStateChanged();
          drawingManager.setDrawingMode(null);
          drawingManager.setDrawingMode('polyline');
        });
        $("#map").on('click touchstart', function(){
            if(current_measure != 1) {
                drawingManager.setDrawingMode(null);
                isMapDrawingMode = -1
                return;
            }

            if(isMapDrawingMode === 1) {
                isMapDrawingMode = 0
            } else if (isMapDrawingMode === 0) {
                drawingManager.setDrawingMode(null);
                drawingManager.setDrawingMode('polyline');
                isMapDrawingMode = 1
            }
        });
        $("#zoom_in").click(function () {
          map.setZoom(map.getZoom() + 1);
        });
        $("#zoom_out").click(function () {
          map.setZoom(map.getZoom() - 1);
        });
        $("#set_position_first").click(function () {
          if (!place || typeof(place.place_id)=='undefined' || !place.place_id) {
            var address = document.getElementById('zipcode').value;
            var param = { 'address': address};
          }else{
              var place = autocomplete.getPlace();
              var param = {'placeId': place.place_id};
          }
          
          geocoder.geocode(param, function(results, status) {
          map.setCenter(results[0].geometry.location);
          });
        });
        $(".custom-back-button").click(function () {
            switch(curIndex) {
              case 2:
                if(current_measure == 1) {
                  $("#section" + curIndex).addClass('hide-animation');
                  setTimeout(function() {
                    $("#section" + curIndex).addClass('hide-section');
                    $("#section1").removeClass('hide-section');
                    $("#section1").removeClass('hide-animation');
                    $(".custom-back-button").addClass('hide-section');
                    curIndex = 1;
                    current_measure = 0;
                    changeTopText(topText[curIndex - 1]);
                  },500);
                } else {
                  $("#section" + curIndex).addClass('hide-animation');
                  setTimeout(function() {
                    $("#section" + curIndex).addClass('hide-section');
                    $("#section7").removeClass('hide-section');
                    $("#section7").removeClass('hide-animation');
                    curIndex = 7;
                    current_measure = 1;
                    changeTopText(topText[curIndex - 1]);
                  },500);
                }
                break;
              case 3:
                $("#section" + curIndex).addClass('hide-animation');
                setTimeout(function() {
                  $("#section" + curIndex).addClass('hide-section');
                  $("#section2").removeClass('hide-section');
                  $("#section2").removeClass('hide-animation');
                  curIndex = 2;
                  changeTopText(topText[curIndex - 1]);
                },500);
                break;
              case 6:
                $("#section" + curIndex).addClass('hide-animation');
                setTimeout(function() {
                  $("#section" + curIndex).addClass('hide-section');
                  $("#section3").removeClass('hide-section');
                  $("#section3").removeClass('hide-animation');
                  curIndex = 3;
                  changeTopText(topText[curIndex - 1]);
                },500);
                break;
              case 7:
                $("#section" + curIndex).addClass('hide-animation');
                setTimeout(function() {
                  $("#section" + curIndex).addClass('hide-section');
                  $("#section6").removeClass('hide-section');
                  $("#section6").removeClass('hide-animation');
                  curIndex = 6;
                  changeTopText(topText[curIndex - 1]);
                },500);
                break;
              case 9:
                $("#section" + curIndex).addClass('hide-animation');
                setTimeout(function() {
                  $("#section" + curIndex).addClass('hide-section');
                  $("#section2").removeClass('hide-section');
                  $("#section2").removeClass('hide-animation');
                  curIndex = 2;
                  removeMarker();
                  current_measure = 2;
                  drawingManager.setDrawingMode(null);
                  window.workingGate = gateList[0];
                  $("#measure-fence-text").addClass('hide-section');
                  $("#measure-gate-text").removeClass('hide-section');
                  changeTopText(topText[7]);
                },500);
                break;
              case 10:
                $("#section" + curIndex).addClass('hide-animation');
                setTimeout(function() {
                  $("#section" + curIndex).addClass('hide-section');
                  $("#section2").removeClass('hide-section');
                  $("#section2").removeClass('hide-animation');
                  curIndex = 2;
                  removeMarker();
                  current_measure = 2;
                  drawingManager.setDrawingMode(null);
                  window.workingGate = gateList[0];
                  $("#measure-fence-text").addClass('hide-section');
                  $("#measure-gate-text").removeClass('hide-section');
                  changeTopText(topText[7]);
                },500);
                break;
              case 11:
                $("#section" + curIndex).addClass('hide-animation');
                setTimeout(function() {
                  $("#section" + curIndex).addClass('hide-section');
                  $("#section2").removeClass('hide-section');
                  $("#section2").removeClass('hide-animation');
                  curIndex = 2;
                  removeMarker();
                  current_measure = 2;
                  drawingManager.setDrawingMode(null);
                  window.workingGate = gateList[0];
                  $("#measure-fence-text").addClass('hide-section');
                  $("#measure-gate-text").removeClass('hide-section');
                  changeTopText(topText[7]);
                },500);
                break;
            }
        });
    });
  </script>
</body>
</html>
