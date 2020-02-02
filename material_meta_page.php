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
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>

<?php 
    $meta_type = isset($_GET['meta_type']) ? $_GET['meta_type'] : 'type';
?>

<p class="meta-type-selector">
    <label><input type="radio" name="meta_type" value="type" <?php if($meta_type=='type')echo 'checked'?> form="meta-form"> Type </label>
    <label><input type="radio" name="meta_type" value="style" <?php if($meta_type=='style')echo 'checked'?> form="meta-form"> Style </label>
    <label><input type="radio" name="meta_type" value="color" <?php if($meta_type=='color')echo 'checked'?> form="meta-form"> Pull Down Fence </label>
    <label><input type="radio" name="meta_type" value="height" <?php if($meta_type=='height')echo 'checked'?> form="meta-form"> Height </label>
    <label><input type="radio" name="meta_type" value="state" <?php if($meta_type=='state')echo 'checked'?> form="meta-form"> State </label>
</p>
<hr>
<table style="width:100%">
    <tr>
        <td style="width:70%" class="col-x">
            <table class="quota-data-table">
                <tr>
                    <td>No</td>
                    <td>Name</td>
                    <?php if($meta_type != 'state') { ?>
                        <td>Image</td>
                    <?php } ?>
                    <td style="width:80px;">Action</td>
                </tr>
                <?php
                global $wpdb;
                $query = "SELECT * FROM quote_items where itype='{$meta_type}'";
                $result = $wpdb->get_results($query, ARRAY_A);

                $dataHTML = '';
                foreach ($result as $i => $r) {
                    $dataHTML .= '<tr>';
                    $dataHTML .= '<td>' . ($i + 1) . '</td>';
                    $dataHTML .= '<td>' . $r['itemn'] . '</td>';
                    if($meta_type != 'state') {
                        $dataHTML .= '<td><img src="' . $r['item_image'] . '"></td>';
                    }
                    
                    $dataHTML .= '<td>' .
                            '<a href="javascript:doDelete('.$r['id'].')"><span class="dashicons dashicons-dismiss"></span></a> ' .
                            '<a href="javascript:;" onclick=\'viewDetail('.$r['id'].',"'.$r['itemn'].'")\'><span class="dashicons dashicons-welcome-write-blog"></span></a>' .
                            '</td>';

                    $dataHTML .= '</tr>';
                }

                echo $dataHTML;
                ?>
            </table>
        </td>
        <td style="width:30%;border-left: solid 1px #ccc" class="col-x">
            <h1 class="edit-h1"><span class="dashicons dashicons-plus-alt"></span> New Item</h1>
            <hr>
            <form method="post" id="meta-form" name="meta-form" enctype="multipart/form-data" target="_hideen_frame">
                <div>
                    <h3>Name</h3>
                    <input type="text" name="itemn" required>
                </div>
                <?php if($meta_type != 'state') { ?>
                    <div>
                        <h3>Image</h3>
                        <input type="file" name="item_image">
                    </div>
                <?php } ?>
                <div>
                    <hr>
                    <input type="hidden" name="do_quote_meta_save" value="">
                    <input type="hidden" name="do_quote_meta_type" value="<?php echo $meta_type;?>" required>
                    <input type="button" value="  Save  " id = "save1">
                    <input type="button" value="  New  " onclick="resetForm()">
                </div>
            </form>
        </td>
    </tr>
</table>

<!-- Modal -->
<div class="modal fade" id="myModal" role="dialog" style="margin-top: 100px;">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Activate</h4>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="exampleInputEmail1">Email address</label>
                <input type="email" class="form-control" id="cemail" aria-describedby="emailHelp" placeholder="Enter email">
            </div>
            <div class="form-group">
                <label for="exampleInputPassword1">Serial Number</label>
                <input type="input" class="form-control" id="cserial" placeholder="Serial Number">
            </div>
            <button id="activate" class="btn btn-primary">Activate</button>
        </div>
      </div>
    </div>
</div>

<script>
    function doDelete(id){
        console.log("delete");
        if(!confirm('Are you sure? will remove linked materials.'))return ;
        jQuery('input[name=do_quote_meta_save]').val(id)
        jQuery('input[name=do_quote_meta_type]').val('delete');
        jQuery('input[name=itemn]').val('-');
        jQuery('#meta-form').submit();
    }
    function viewDetail(id,name){
        jQuery('h1.edit-h1').html('<span class="dashicons dashicons-welcome-write-blog"></span> Edit Item');
        jQuery('input[name=do_quote_meta_save]').val(id)
        jQuery('input[name=do_quote_meta_type]').val('update');
        jQuery('input[name=itemn]').val(name);
    }
    function resetForm(){
        document.getElementById('meta-form').reset();
        jQuery('h1.edit-h1').html('<span class="dashicons dashicons-plus-alt"></span> New Item')
    }
    $("#save1").click(function(){
        var checkInfo = {};
        checkInfo['type'] = 'checkonly';
        $.ajax({
            type: "POST",
            url: '?check-activate=1',
            data: checkInfo,
            async: false,
            success: function(r){
                console.log(123);
                if(r=='success'){
                    $('#meta-form').submit();
                } else {
                    $("#myModal").modal();
                }
            },
        });
    });
    
    jQuery('input[name="meta_type"]').change(function(){
        location.href = '?page=material-meta-page&meta_type=' + this.value;
    })
    $(document).ready(function(){
        $("#activate").click(function(){
            var serialInfo = {};
            serialInfo['email'] = $('#cemail').val();
            serialInfo['serial'] = $('#cserial').val();
            jQuery.post('?do-activate=1', serialInfo, function(r){
                if(r=='success'){
                    if(jQuery('input[name=do_quote_meta_type]').val()=='delete') $('#meta-form').submit();
                    var name = jQuery.trim(jQuery('input[name=itemn]').val());
                    if(!name)return;

                    var ncount = 0;
                    jQuery('table.quota-data-table tr').each(function(i,tr){
                        if(jQuery('table.quota-data-table tr').index(tr)==0)return;
                        
                        if(jQuery.trim(jQuery('td:nth-child(2)', tr).text())==name)ncount++;
                    });
                        
                    if(ncount>(jQuery('input[name=do_quote_meta_type]').val()=='update'?1:0)){
                        //alert('Exists an equal name item already!');
                        return false;
                    }
                    $('#meta-form').submit();
                }else{
                    //alert('Acivation Falied');
                }

            });
        });
    });
</script>

<iframe name="_hideen_frame" style="display: none;width:100%;"></iframe>