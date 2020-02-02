<style>
    .quota-data-table{
        width: 100%;
    }
    .quota-data-table tr{
        color: #888;
        font-size: 18px;
        line-height: 30px;
    }

    .quota-data-table tr:first-child{
        font-weight: bold;
    }

    .quota-data-table td{
        padding: 5px;
        border-bottom: solid 1px #888;
    }
    .order-paginator{
        font-size: 18px;
        margin-top: 10px;
    }
    a{    text-decoration: none;}
    .hide{
        display: none;
    }
    
    .fence-detail{
        display: none;
        position: absolute;
        right: 25px;
        top: -200px;
        
    }
    .fence-detail button{
        position:absolute;
        right:-27px;
        top:195px;
        z-index: 2222;
    }
    .fence-detail #map{
        width: 600px;
        height: 450px;
        border: solid 1px #444;
    }
</style>
<table class="quota-data-table">
    <tr>
        <td>No</td>
        <td>Date</td>
        <td>Name</td>
        <td>Email</td>
        <td>Phone</td>
        <td>Address</td>
        <td>Price</td>
        <td>Length</td>
        <td>Material</td>
        <td>Gate Count</td>
        <td style="width:80px;">Action</td>
    </tr>
    <?php
    global $wpdb;
    $query = "SELECT * FROM quote_orders";
    $total_query = "SELECT COUNT(1) FROM (${query}) AS combined_table";
    $total = $wpdb->get_var($total_query);
    $items_per_page = 15;
    $page = isset($_GET['cpage']) ? abs((int) $_GET['cpage']) : 1;
    $offset = ( $page * $items_per_page ) - $items_per_page;
    $result = $wpdb->get_results($query . " ORDER BY order_date DESC LIMIT ${offset}, ${items_per_page}", ARRAY_A);
    $totalPage = ceil($total / $items_per_page);

    $dataHTML = '';
    foreach ($result as $i => $r) {
        $dataHTML .= '<tr>';
        $dataHTML .= '<td>' . ($i + ($page - 1) * $items_per_page + 1) . '</td>';
        $dataHTML .= '<td>' . date('n/j/y H:i', strtotime($r['order_date'])) . '</td>';
        $dataHTML .= '<td>' . $r['uname'] . '</td>';
        $dataHTML .= '<td>' . $r['uemail'] . '</td>';
        $dataHTML .= '<td>' . $r['uphone'] . '</td>';
        $dataHTML .= '<td>' . $r['address'] . '</td>';
        $dataHTML .= '<td>$' . round($r['total_price'], 1) . '</td>';
        $dataHTML .= '<td>' . round($r['fence_length'], 1) . 'm</td>';

        $mt = json_decode($r['material_info'], true);
        $dataHTML .= '<td>' . ($mt['style'] . ', ' . $mt['color'] . ', ' . $mt['height']) . '</td>';
        if($r['gates']){
            $gt = array_column(json_decode($r['gates'], true), 'name');
            $dataHTML .= '<td>' . implode(', ', $gt) . '</td>';
            
        }else{
            $dataHTML .= '<td></td>';
        }
        
        $dataHTML .= '<td>' .
                '<a href="?delete='.$r['id'].'" target="_hiden_frame"><span class="dashicons dashicons-dismiss"></span></a> ' .
                '<a href="javascript:;" onclick=\'viewDetail('.$r['fence_lines'].','.($r['gates']?$r['gates']:('""')).', this)\'><span class="dashicons dashicons-visibility"></span></a>' .
                '</td>';

        $dataHTML .= '</tr>';
    }

    echo $dataHTML;
    ?>
</table>
<iframe name="_hiden_frame" style="display: none;"></iframe>
<?php
if ($totalPage > 1) {
    echo '<div class="order-paginator"><span>Page ' . $page . ' of ' . $totalPage . '</span>' .
    paginate_links(array(
        'base' => add_query_arg('cpage', '%#%'),
        'format' => '',
        'prev_text' => __('&laquo;'),
        'next_text' => __('&raquo;'),
        'total' => $totalPage,
        'current' => $page
    )) . '</div>';
}

?>


<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDA4-Xd76eSbG9v6HzevULyNYvJoykTD5c&libraries=drawing,geometry,places&callback=initMap" async defer></script>
<script>
    var map, 
        polylines=[], 
        markers=[];
    function viewDetail(ls,gs,el){
        jQuery(el).css('position','relative');
        
        jQuery('.fence-detail')
                .appendTo(el)
                .show();
        
        var offset = jQuery(el).offset();
        
        jQuery('.fence-detail').css('top', -200);
        jQuery('.fence-detail button').css('top', 195);
        
        if(offset.top<200){
            jQuery('.fence-detail').css('top', -5);
            jQuery('.fence-detail button').css('top', 0);
        }else if((window.innerHeight-offset.top)<200){
            jQuery('.fence-detail').css('top', -425);
            jQuery('.fence-detail button').css('top', 422);
        }
        
        cleanMap();
        drawPolyAndMarkers(ls,gs);
    }
    
    function drawPolyAndMarkers(ls,gs){
        polylines = [];
        markers = [];
        
        for(var i=0; i<ls.length; i++){
            var poly = [];
            
            for(var j=0; j<ls[i].length; j++){
                poly.push({lat: ls[i][j][0]*1, lng:ls[i][j][1]*1});
            }
            
            polylines.push(new google.maps.Polyline({
                path: poly,
                geodesic: true,
                strokeColor: 'darkred',
                strokeWeight: 5,
                clickable:false,
                editable:false,
                map:map,
            }));
        }
        
        var goldStar = {
          path: 'M0-48c-9.8 0-17.7 7.8-17.7 17.4 0 15.5 17.7 30.6 17.7 30.6s17.7-15.4 17.7-30.6c0-9.6-7.9-17.4-17.7-17.4z',
          fillColor: 'yellow',
          fillOpacity: 0.8,
          scale: .3,
          strokeColor: 'gold',
        };
        
        if(gs){
            for(var i=0; i<gs.length; i++){
                var mpos = eval(gs[i]['map_mark']);
                markers.push(new google.maps.Marker({
                    position: {lat:mpos[0], lng:mpos[1]}, 
                    icon: goldStar,
                    map: map
                }));
            }
        }
        
        map.panTo({lat:ls[0][0][0]*1, lng:ls[0][0][1]*1});
    }
    
    function cleanMap(){
        for(i=0;i<polylines.length;i++)polylines[i].setMap(null);
        for(i=0;i<markers.length;i++)markers[i].setMap(null);
        polylines=[]; 
        markers=[];
    }
    
    function initMap() {
        var melborne = new google.maps.LatLng(-37.8136, 144.9631);

        map = new google.maps.Map(document.getElementById('map'), {
            center: melborne,
            zoom: 20,
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
        
    }
    
    function hideme(){
        setTimeout(function(){
            jQuery('.fence-detail').hide()
        },100)
    }
</script>

<div class="fence-detail hide">
    <button onclick="hideme()" style=""><strong>X</strong></button>
    <div id="map"></div>
</div>
