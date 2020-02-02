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

<hr>
<table style="width:100%">
    <tr>
        <td style="width:70%" class="col-x">
            <table class="quota-data-table">
                <tr>
                    <td>No</td>
                    <td>Name</td>
                    <td>Image</td>
                    <td>Price</td>
                    <td style="width:80px;">Action</td>
                </tr>
                <?php
                global $wpdb;
                $query = "SELECT * FROM quote_gates";
                $result = $wpdb->get_results($query, ARRAY_A);

                $dataHTML = '';
                foreach ($result as $i => $r) {
                    $dataHTML .= '<tr>';
                    $dataHTML .= '<td>' . ($i + 1) . '</td>';
                    $dataHTML .= '<td>' . $r['itemn'] . '</td>';
                    $dataHTML .= '<td><img src="' . $r['item_image'] . '"></td>';
                    $dataHTML .= '<td>' . $r['price'] . '</td>';
                    
                    $dataHTML .= '<td>' .
                            '<a href="javascript:doDelete('.$r['id'].')"><span class="dashicons dashicons-dismiss"></span></a> ' .
                            '<a href="javascript:;" onclick=\'viewDetail('.$r['id'].',"'.$r['itemn'].'","'.$r['price'].'")\'><span class="dashicons dashicons-welcome-write-blog"></span></a>' .
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
                <div>
                    <h3>Price</h3>
                    <input type="text" name="price" required>
                </div>
                <div>
                    <h3>Image</h3>
                    <input type="file" name="item_image">
                </div>
                <div>
                    <hr>
                    <input type="hidden" name="do_gate_manage_action" value="insert">
                    <input type="hidden" name="do_gate_manage_id" required>
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
        jQuery('input[name=do_gate_manage_id]').val(id)
        jQuery('input[name=do_gate_manage_action]').val('delete');
        jQuery('input[name=itemn]').val('-');
        jQuery('input[name=price]').val('11');
        jQuery('#meta-form').submit();
    }
    function viewDetail(id,name,price){
        jQuery('h1.edit-h1').html('<span class="dashicons dashicons-welcome-write-blog"></span> Edit Item');
        jQuery('input[name=do_gate_manage_id]').val(id)
        jQuery('input[name=do_gate_manage_action]').val('update');
        jQuery('input[name=itemn]').val(name);
        jQuery('input[name=price]').val(price);
    }
    function resetForm(){
        document.getElementById('meta-form').reset();
        jQuery('h1.edit-h1').html('<span class="dashicons dashicons-plus-alt"></span> New Item')
    }
    
    jQuery('#meta-form').submit(function(){
        var name = jQuery.trim(jQuery('input[name=itemn]').val());
        if(!name){
            jQuery('input[name=itemn]').focus();
            return false;
        }
        
        if(jQuery('input[name=do_gate_manage_action]').val()=='delete')return true;
        
        var price = jQuery.trim(jQuery('input[name=price]').val());
        if(!price || isNaN(price) || Number(price)<=0){
            jQuery('input[name=price]').focus();
            return false;
        }

        var ncount = 0;
        jQuery('table.quota-data-table tr').each(function(i,tr){
            if(jQuery('table.quota-data-table tr').index(tr)==0)return;
            
            if(jQuery.trim(jQuery('td:nth-child(2)', tr).text())==name)ncount++;
        });
        
        if(ncount>(jQuery('input[name=do_gate_manage_action]').val()=='update'?1:0)){
            alert('Exists an equal name item already!');
            return false;
        }
        return true;
        
    })
    
</script>

<iframe name="_hideen_frame" style="display: none;width:100%;"></iframe>