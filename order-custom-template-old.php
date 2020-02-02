<?php 
    get_header(); 
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
        );
    }
?>
<style>
    #content.site-content{ padding: 0px; }
    #content.site-content > .container{ width: 100%;max-width: 100%;}
    .hide{display: none;}
    body { margin:0; padding:0; }
    .site-content{background: #f4f4f4;}
    
    .site-content h3{margin-top: 30px;}
    .site-content .text-instructions{color: #666;}
    label[for=contact-info-address]{margin-top: 30px;}
    #zipcode{width: 100%;}
    
    .gmnoprint{display: none!important;}
    .quota-tool-sidebar{padding: 10px ;}
    
    table.order-info-table{max-height: 500px;overflow: auto;line-height: 50px;font-size: 18px;color: #000;}
    table.order-info-table tr td:last-child{text-align: left;}
    table.order-info-table tr td:first-child{width: 100px;text-align: left;vertical-align: top;}

    .quota-tool-tipbar{position: absolute;left: 0;top: 0;z-index: 1;width: 100%;padding-left: 30px;padding-top: 10px;padding-bottom: 10px;min-height: 50px;}
    
    .quota-tool-tipbar .action-guide{position: absolute;right: 10px;color: #ff0000;font-weight: bold;}
    .quote-material-list .modal-dialog{max-width: 95%;margin: 50px auto;}
    
    .quote-material-list .modal-body li, .quote-gate-list .modal-body li{list-style: none;width: 100px;margin: 0 0 0 15px;padding: 0;display: inline-block;cursor: pointer;vertical-align: top;transition: margin 250ms cubic-bezier(.215,.61,.355,1);}
    .quote-material-list .modal-body li label.item-name,.quote-gate-list .modal-body li label.item-name{display: block;overflow: hidden;white-space: normal;text-align: center;color: #1a1a1a;font-weight: bold;line-height: 14px;margin-top: 2px;font-size: 13px;}
    .quote-material-list .modal-body li image.item-image,.quote-gate-list .modal-body li image.item-image{height: 100px;width: 100px;vertical-align: middle;}
    .quote-gate-list .modal-body li{margin-bottom: 10px;}
    
    .quote-material-list .modal-body li.pin{width: auto;padding: 10px;line-height:80px;font-size: 1.3em;}
    input.validate-error{border-color: #ff4400;}
    .quote-material-list .modal-body label.item{margin-left: 20px; }
    #map { width:100%;min-height: 600px;}
</style>

<div class="col-sm-3">
    <div class="quota-tool-sidebar">
        <div class="position-search-box">
            <h3>Find your home</h3>
            <div class="text-instructions">
                Enter your address, and find to locate your home on the map.
            </div>
            <div class="example"></div>
            <hr>
            <input type="text" id="zipcode" class="form-controll">
            <hr>
            <button onclick="searchPosition()" class="btn btn-success">Find Your Home</button>
        </div>
        
        <label for="contact-info-address">all prices exc gst, any jobs less than 25m will incure a small job fee</label>
        
        <table class="order-info-table hide">
            <tr><td>Length</td><td class="length"> - </td></tr>
            <tr><td>Height</td><td class="height"> - </td></tr>
            <tr><td>Material</td><td class="material"> - </td></tr>
            <tr><td>Gates</td><td class="gates"> -  </td></tr>
            <tr class="price-tr "><td>Price</td><td class="price"> - </td></tr>
            <tr class="user-form "><td>Full Name</td><td><input type="text" class="form-control" id="user-name" aria-describedby="emailHelp" placeholder="Enter full Name"></td></tr>
            <tr class="user-form "><td>Email</td><td><input type="email" class="form-control" id="user-email" aria-describedby="emailHelp" placeholder="Enter email"></td></tr>
            <tr class="user-form "><td>Phone</td><td><input type="text" class="form-control" id="user-phone" placeholder="Enter phone number"></td></tr>
            
            <tr class="user-form "><td></td><td> <button id="sendorder" class="btn btn-success">Get a quote</button> </td></tr>
        </table>
    </div>
</div>

<div class="col-sm-9" style="padding-right: 0px;">
    <div class="quota-tool-tipbar">
        <button id="choose-material" class="btn btn-primary btn-sm" data-toggle="modal" data-target=".quote-material-list">Choose Material</button>
        <button id="add-gate" class="btn btn-primary btn-sm hide" data-toggle="modal" data-target=".quote-gate-list">Add Gate</button>
        <button id="undo" class="btn btn-primary btn-sm hide">Undo</button>

        <span class="action-guide">Draw your fence by clicking on the map, then double click to finish the fence line to get a measurment.</span>
    </div>
    <div id="map"></div>
</div>

<div class="modal fade quote-gate-list" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title"><i class="fa fa-paw"></i> Choose Gate</h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                
            </div>
            <div class="modal-footer">
                <span id="gate-privce" style="color:#ff4400;margin-right: 25px;"></span>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade quote-material-list" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title"><i class="fa fa-paw"></i> Choose fence material</h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding-bottom:0;">
                
            </div>
            
            <div style="position:relative;">
                <div style="position:absolute;right:20px;top: -50px;">
                    <span id="privceval" style="color:#ff4400;margin-right: 25px;"></span>
                    <button type="button" class="btn btn-primary setmaterial">Select this</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var map, drawingManager, poly, geocoder,workingGate, autocomplete;
    var polylines = [];
    var markers = [];
    var mag = [];
    var typeList = <?php echo json_encode($types);?>;
    var styleList = <?php echo json_encode($styles);?>;
    var colorList = <?php echo json_encode($colors);?>;
    var heightList = <?php echo json_encode($heights);?>;
    var materialList = <?php echo json_encode($materials);?>;
    var gateList = <?php echo json_encode($gates)?>;
    var ts = ['type','style','color','height'];
    var fenceInfo = {
        map_lines:[],
        length:0,
        material:'',
        gates:[],
        total_price:0,
    };
    
    function initMap() {
        var melborne = new google.maps.LatLng(-37.8136, 144.9631);

        map = new google.maps.Map(document.getElementById('map'), {
            center: melborne,
            zoom: 10,
            streetViewControl: false,
            mapTypeId: 'satellite',
            tilt:0,
            zoomControl: true,
            mapTypeControl: false,
            scaleControl: false,
            streetViewControl: false,
            rotateControl: false,
            fullscreenControl: false
        });
        
        geocoder = new google.maps.Geocoder;
        
        autocomplete = new google.maps.places.Autocomplete(document.getElementById('zipcode'), {
          componentRestrictions: {'country': 'aus'},
        });
        
        autocomplete.addListener('place_changed', function() {
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
                clickable:false,
                editable:false,
            },
            map: map,
        });
        
        google.maps.event.addListener(drawingManager, 'polylinecomplete', function (polyline) {
            if(polyline.getPath().getArray().length<2){
                polyline.map(null);
                return;
            }
            
            mag.push('polyline');
            polylines.push(polyline);
            fenceInfo.map_lines.push(eval('['+polyline.getPath().getArray().toString().replace(/ /g,'').replace(/\(/g,'[').replace(/\)/g,']')+']'));
            
            onStateChanged();
        });
        
        google.maps.event.addListener(map, 'click', function(event) {
            if(!window.workingGate || !window.workingGate.name || !polylines.length)return false;
            
            for (var i = 0; i < polylines.length; i++) {
                var pp = polylines[i].getPath().getArray();
                
                for(var j=1; j<pp.length; j++){
                    var distance = google.maps.geometry.spherical.computeDistanceBetween(event.latLng, pp[j-1]);
                    var distance1 = google.maps.geometry.spherical.computeDistanceBetween(event.latLng, pp[j]);
                    var linelength = google.maps.geometry.spherical.computeDistanceBetween(pp[j], pp[j-1]);
                    var diff = (distance+distance1)-linelength;

                    if(diff >= 0.3){
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
                    if(!workingGate.price[materialHeight]){
                        for(iii in workingGate.price){
                            if(gateprice<parseFloat(workingGate.price[iii])){
                                gateprice = parseFloat(workingGate.price[iii]);
                            }
                        }
                    }else{
                        var gateprice = workingGate.price[materialHeight];
                    }
                    
                    workingGate.map_mark = event.latLng.toString().replace(/\(/g,'[').replace(/\)/g,']');
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
    
    function initMaterialList(){
        jQuery('.quote-material-list .modal-body').empty();

        for(ii in ts){
            var html = '<b>'+(ts[ii]=='color'?'pull down old fence':ts[ii])+'</b><ul>' ;
            var list = eval(ts[ii].toLowerCase()+'List');
            var iid = '';
            for(iid in list){
                html += '<li class="item" dtype="'+ts[ii]+'">' +
                            '<img class="item-img" src="'+list[iid]+'">' +
                            '<label class="item-name">'+iid+'</label>' +
                        '</li>';
            }
            html += '</ul>' ;
            jQuery('.quote-material-list .modal-body').append(html);
        }
        jQuery('.setmaterial').hide();
    }
    
    function initGateList(){
        var _h = parseFloat(fenceInfo.material.height);

        var html='<ul>';
        var count = 0;
        for(iid in gateList){
            html += '<li class="item" gate-iid="'+iid+'" title="$('+gateList[iid]['price'][_h]+') AUD">' +
                        '<img class="item-img" src="'+gateList[iid]['image']+'" style="width:100px;height:100px;">' +
                        '<label class="item-name">'+gateList[iid]['name']+'</label>' +
                    '</li>';
            count++;
        }

        jQuery('.quote-gate-list .modal-body').empty().append(html+'</ul>');

        if(count)jQuery('.quote-gate-list').modal()
    }
    
    function syncMaterialAttrs(){
        var fixitems = [],
            evalstr = '',
            curtype = null,
            curitems = null,
            ele = null,
            curUL = null,
            html = null,
            curVal = null;

        jQuery('li.pin').each(function(i, el){
            fixitems.push(['ele["'+jQuery(el).attr('dtype')+'"]=="'+jQuery(el).prev().children('label').text()+'"']);
        });

        if(!fixitems.length){
            initMaterialList();
            return;
        }

        evalstr = fixitems.join(' && ');
      
        for(var i=0; i<ts.length; i++){
            curtype = ts[i];

            if(jQuery('li.pin[dtype=' + curtype + ']').length>0)
                continue;

            curUL = jQuery('li.item[dtype=' + curtype + ']').parent();
            curUL.empty();
            
            curitems = [];
            for(j in materialList){
                ele = materialList[j];
                if((eval(evalstr)) && curitems.indexOf(ele[curtype])<0){
                    curitems.push(ele[curtype]);
                    html = '<li class="item" dtype="'+curtype+'">' +
                                '<img class="item-img" src="'+eval(curtype+'List["'+ele[curtype]+'"]')+'">' +
                                '<label class="item-name">'+ele[curtype]+'</label>' +
                            '</li>';

                    jQuery(curUL).append(html);
                }
            }
        }

        jQuery('.setmaterial').hide();
        jQuery('#privceval').text('');
        if(jQuery('li.item[dtype=type]').length==1 && jQuery('li.item[dtype=style]').length==1 && jQuery('li.item[dtype=color]').length==1 && jQuery('li.item[dtype=height]').length==1){
            jQuery('.setmaterial').show();

            var typev = jQuery('li.item[dtype=type] label').text(),
                stylev = jQuery('li.item[dtype=style] label').text(),
                colorv = jQuery('li.item[dtype=color] label').text(),
                heightv = jQuery('li.item[dtype=height] label').text();
            var ele;

            for(j in materialList){
                ele = materialList[j];
                if(ele['type']==typev && ele['style']==stylev && ele['color']==colorv && ele['height']==heightv){
                    jQuery('#privceval').text('Price : $' + ele['price'] + ' AUD');
                    fenceInfo.material = ele;
                    return;
                }
            }
        }
    }
    
    function checkOrderSendAble(domark){
        fenceInfo['uname'] = jQuery.trim(jQuery('#user-name').val());
        fenceInfo['uemail'] = jQuery.trim(jQuery('#user-email').val());
        fenceInfo['uphone'] = jQuery.trim(jQuery('#user-phone').val());
        
        jQuery('#user-name,#user-email,#user-phone').removeClass('validate-error');
        
        if(!fenceInfo['uname']){
            jQuery('#user-name').addClass('validate-error');
            return false;
        }
        
        if(!check_email(fenceInfo['uemail'])){
            jQuery('#user-email').addClass('validate-error');
            return false;
        }
        
        if(!(/^(1\s|1|)?((\(\d{3}\))|\d{3})(\-|\s)?(\d{3})(\-|\s)?(\d{4})$/.test(fenceInfo['uphone']))){
            jQuery('#user-phone').addClass('validate-error');
            return false;
        }
        
        var valid = (  fenceInfo['length']>0
                    && fenceInfo['map_lines'].length>0
                    && fenceInfo['material']
                    && fenceInfo['total_price']>0);
                    
        return valid;
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
        jQuery('.length').text(fenceLength ? (fenceLength.toFixed(1) + 'M') : ' - ');
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
    
    jQuery('#sendorder').click(function(){
        if(!checkOrderSendAble(true))return;
        
        fenceInfo['uname'] = jQuery.trim(jQuery('#user-name').val());
        fenceInfo['uemail'] = jQuery.trim(jQuery('#user-email').val());
        fenceInfo['uphone'] = jQuery.trim(jQuery('#user-phone').val());
        
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

            console.log(fenceInfo);
            
            jQuery.post('?do-order=1', fenceInfo, function(r){
                if(r=='ok'){
                    bootbox.alert('<strong>Thank you for your request!<strong><br>We have received your quote request and a sales representative will be contacting you shortly.')
                }else{
                    bootbox.alert('Denied your request');
                }
//                location.reload();
            });
        });
    })
    
    jQuery('#undo').click(function(){
        if(mag.length<1)return;
        
        if(mag.pop()=='polyline'){
            polylines.pop().setMap(null);
            fenceInfo.map_lines.pop();
        }else{
            markers.pop().setMap(null);
            fenceInfo.gates.pop();
        }
        onStateChanged();
    });

    jQuery('body').on('click', 'li.item[dtype]', function(){
        var dtype = jQuery(this).attr('dtype');
        var that = this;

        jQuery('.item[dtype=' + dtype + ']')
            .not(that)
            .fadeOut('fast')
            .promise()
            .done(function(){ 
                jQuery(this).remove();
                if(jQuery('.item[dtype=' + dtype + ']').length<=1 && !jQuery(that).next('li.pin').length){
                    jQuery(that).after('<li class="pin" dtype="'+dtype+'"><i class="fa fa-minus-circle"></i> Clear Selection</li>');
                }
            }).promise().done(function(){
                syncMaterialAttrs();
            });
    });

    jQuery('body').on('click', 'li.pin', function(){
        jQuery(this)
            .fadeOut('fast')
            .promise()
            .done(function(){
                jQuery(this).remove();
                syncMaterialAttrs();
            });
    });

    jQuery('.setmaterial').click(function(){
        jQuery('button[data-dismiss="modal"]').click();
        
        onStateChanged();
    })
    
    jQuery('#add-gate').click(function(){
        initGateList();
    })

    jQuery('body').on('click', 'li[gate-iid]', function(){
        jQuery('button[data-dismiss="modal"]').click();

        jQuery('.action-guide').text('Place the door on the fence on the map with a mouse click.');
        window.workingGate = gateList[jQuery(this).attr('gate-iid')];
        drawingManager.setDrawingMode(null);

    });
    
    initMaterialList();
</script>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDA4-Xd76eSbG9v6HzevULyNYvJoykTD5c&libraries=drawing,geometry,places&callback=initMap" async defer></script>
<script src="/wp-content/plugins/quotatool/bootbox.min.js"></script>
<?php get_footer(); ?>