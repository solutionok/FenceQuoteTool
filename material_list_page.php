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
    table td.col-x{vertical-align: top;padding: 10px 20px;}
    .meta-type-selector label{
        font-weight: bold;
        font-size: 20px;
        margin-right: 20px;
    }
    #footer-thankyou,#footer-upgrade{display: none!important;}
</style>

<?php 
    global $wpdb;
    $material_types = $wpdb->get_results('select * from quote_items where itype="type"', ARRAY_A);
    $mtype = isset($_GET['mtype']) ? $_GET['mtype'] : $material_types[0]['id'];
    
    $styles = array();
    $colors = array();
    $heights = array();
    $states = array();
    
    $items = array();
    
    foreach($wpdb->get_results('select * from quote_items', ARRAY_A) as $it){
        if($it['itype']=='style')$styles[$it['id']]=$it['itemn'];
        if($it['itype']=='color')$colors[$it['id']]=$it['itemn'];
        if($it['itype']=='height')$heights[$it['id']]=$it['itemn'];
        if($it['itype']=='state')$states[$it['id']]=$it['itemn'];
        
        $items[$it['id']]=$it['itemn'];
    }
?>

<p class="meta-type-selector">
    <?php foreach($material_types as $_t):?>
    <label><input type="radio" name="mtype" value="<?php echo $_t['id']?>" <?php if($mtype==$_t['id'])echo 'checked'?> form="meta-form"> <?php echo $_t['itemn']?> </label>
    <?php endforeach;?>
</p>
<hr>
<table style="width:100%">
    <tr>
        <td style="width:70%" class="col-x">
            <table class="quota-data-table">
                <tr>
                    <td>No</td>
                    <td>Style</td>
                    <td>Pull Down Fence</td>
                    <td>Height</td>
                    <td>State</td>
                    <td>Price</td>
                    <td style="width:80px;">Action</td>
                </tr>
                <?php
                $query = "SELECT * FROM quote_materials where mtype='{$mtype}'";
                $total_query = "SELECT COUNT(1) FROM (${query}) AS combined_table";
                $total = $wpdb->get_var($total_query);
                $items_per_page = 10;
                $page = isset($_GET['cpage']) ? abs((int) $_GET['cpage']) : 1;
                $offset = ( $page * $items_per_page ) - $items_per_page;
                $result = $wpdb->get_results($query . " ORDER BY mstyle,mcolor,mheight LIMIT ${offset}, ${items_per_page}", ARRAY_A);
                $totalPage = ceil($total / $items_per_page);

                $dataHTML = '';
                foreach ($result as $i => $r) {
                    $dataHTML .= '<tr>';
                    $dataHTML .= '<td>' . ($i + ($page - 1) * $items_per_page + 1) . '</td>';
                    $dataHTML .= '<td>' . $items[$r['mstyle']] . '</td>';
                    $dataHTML .= '<td>' . $items[$r['mcolor']] . '</td>';
                    $dataHTML .= '<td>' . $items[$r['mheight']] . '</td>';
                    $dataHTML .= '<td>' . $items[$r['mstate']] . '</td>';
                    $dataHTML .= '<td>' . $r['mprice'] . '</td>';
                    
                    $dataHTML .= '<td>' .
                            '<a href="javascript:doDelete('.$r['id'].')"><span class="dashicons dashicons-dismiss"></span></a> ' .
                            '<a href="javascript:;" onclick=\'viewDetail('.json_encode($r,true).')\'><span class="dashicons dashicons-welcome-write-blog"></span></a>' .
                            '</td>';

                    $dataHTML .= '</tr>';
                }

                echo $dataHTML;
                ?>
            </table>
        </td>
        <td style="width:30%;border-left: solid 1px #ccc" class="col-x">
            <h1 class="edit-h1"><span class="dashicons dashicons-plus-alt"></span> New Material</h1>
            <hr>
            <form method="post" id="meta-form" name="meta-form" enctype="multipart/form-data" target="_hideen_frame">
                <div>
                    <h3>Style</h3>
                    <select name="mstyle" required>
                    <?php foreach($styles as $key=>$label)echo '<option value="'.$key.'">'.$label.'</option>';?>
                    </select>
                </div>
                <div>
                    <h3>Pull Down Fence</h3>
                    <select name="mcolor" required>
                    <?php foreach($colors as $key=>$label)echo '<option value="'.$key.'">'.$label.'</option>';?>
                    </select>
                </div>
                <div>
                    <h3>Height</h3>
                    <select name="mheight" required>
                    <?php foreach($heights as $key=>$label)echo '<option value="'.$key.'">'.$label.'</option>';?>
                    </select>
                </div>
                <div>
                    <h3>State</h3>
                    <select name="mstate" required>
                    <?php foreach($states as $key=>$label)echo '<option value="'.$key.'">'.$label.'</option>';?>
                    </select>
                </div>
                <div>
                    <h3>Price</h3>
                    <input name="mprice" required="true" autocomplete="false">
                </div>
                <div>
                    <hr>
                    <input type="hidden" name="mtype" value="<?php echo $mtype;?>" required>
                    <input type="hidden" name="do-save-material-action" value="insert" required>
                    <input type="hidden" name="material_id">
                    <input type="submit" value="  Save  ">
                    <input type="button" value="  New  " onclick="resetForm()">
                </div>
            </form>
        </td>
    </tr>
</table>

<script>
    function doDelete(id){
        if(!confirm('Are you sure?'))return ;
        jQuery('input[name=material_id]').val(id)
        jQuery('input[name=do-save-material-action]').val('delete');
        jQuery('input[name=mprice]').val('-');
        jQuery('#meta-form').submit();
    }
    function viewDetail(ma){
        jQuery('h1.edit-h1').html('<span class="dashicons dashicons-welcome-write-blog"></span> Edit Material');
        jQuery('input[name=material_id]').val(ma.id)
        jQuery('input[name=do-save-material-action]').val('update');
        jQuery('select[name=mstyle]').val(ma.mstyle).get(0).disabled=true;
        jQuery('select[name=mcolor]').val(ma.mcolor).get(0).disabled=true;
        jQuery('select[name=mheight]').val(ma.mheight).get(0).disabled=true;
        jQuery('select[name=mheight]').val(ma.mstate).get(0).disabled=true;
        jQuery('input[name=mprice]').val(ma.mprice);
    }
    function resetForm(){
        document.getElementById('meta-form').reset();
        localStorage.clear();
        
        jQuery('select[name=mstyle]').get(0).disabled = false;
        jQuery('select[name=mcolor]').get(0).disabled = false;
        jQuery('select[name=mheight]').get(0).disabled = false;
        jQuery('select[name=mstate]').get(0).disabled = false;
        jQuery('h1.edit-h1').html('<span class="dashicons dashicons-plus-alt"></span> New Item')
    }
    
    jQuery('input[name="mtype"]').change(function(){
        location.href = '?page=material-list-page&mtype=' + this.value;
    })
    
    jQuery('#meta-form select').change(function(){
        localStorage.setItem(this.name, this.value);
    }).each(function(i, el){
        if(localStorage.getItem(el.name))this.value = localStorage.getItem(el.name);
    });
    
    jQuery('select[name=mheight]').focus();
</script>

<iframe name="_hideen_frame" style="display: none;width:100%;"></iframe>
</div>
<?php
if ($totalPage > 1) {
    echo '<div class="order-paginator"><span>Page ' . $page . ' of ' . $totalPage . '</span>' .
    paginate_links(array(
        'base' => add_query_arg('cpage', '%#%'),
        'format' => '',
        'prev_text' => __('&nbsp;&nbsp;&nbsp;&laquo;&nbsp;&nbsp;&nbsp;'),
        'next_text' => __('&nbsp;&nbsp;&nbsp;&raquo;&nbsp;&nbsp;&nbsp;'),
        'total' => $totalPage,
        'current' => $page
    )) . '</div>';
}
?>