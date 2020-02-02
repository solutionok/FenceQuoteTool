<?php
    global $wpdb;

    $email_info= $wpdb->get_results('select * from quote_email where id = 1')[0];
?>
<style>
    table td.col-x{vertical-align: top;padding: 10px 20px;}
    input{width: 400px;}
    button{width: 200px;height:50px;}
</style>

<hr>
<table style="width:100%">
    <tr>
        <td style="width:30%;border-left: solid 1px #ccc" class="col-x">
            <h1 class="edit-h1">Customize Email Content</h1>
            <hr>
            <div>
                <div>
                    <h3>Email</h3>
                    <input type="text" id="c_email" required>
                </div>
                <div>
                    <h3>Company Name</h3>
                    <input type="text" id="c_title" required>
                </div>
                <div>
                    <h3>Email Content</h3>
                    <textarea rows="10" cols="80" id="c_content"></textarea>
                </div>
                <div>
                    <h3>Office</h3>
                    <input type="text" id="c_phone" required>
                </div>
                <div>
                    <h3>Mobile</h3>
                    <input type="text" id="c_mobile" required>
                </div>
                <div>
                    <h3>Website</h3>
                    <input type="text" id="c_website" required>
                </div>
                <div>
                    <h3>Video</h3>
                    <input type="text" id="c_video" required>
                </div>
                <div>
                    <h3>Recommendation</h3>
                    <input type="text" id="c_recommend" required>
                </div>
                <div>
                    <hr>
                    <button id="save">Save</button>
                </div>
            </div>
        </td>
    </tr>
</table>

<script>
    var prev_emailInfo = <?php echo json_encode($email_info);?>;
    jQuery('#c_email').val(prev_emailInfo['email']);
    jQuery('#c_title').val(prev_emailInfo['title']);
    jQuery('#c_phone').val(prev_emailInfo['phone']);
    jQuery('#c_mobile').val(prev_emailInfo['mobile']);
    jQuery('#c_website').val(prev_emailInfo['website']);
    jQuery('#c_video').val(prev_emailInfo['video']);
    jQuery('#c_recommend').val(prev_emailInfo['recommend']);
    jQuery('#c_content').val(prev_emailInfo['content']);
    jQuery("#save").click(function () {
        var emailInfo = {};
        emailInfo['email'] = jQuery.trim(jQuery('#c_email').val());
        emailInfo['title'] = jQuery.trim(jQuery('#c_title').val());
        emailInfo['phone'] = jQuery.trim(jQuery('#c_phone').val());
        emailInfo['mobile'] = jQuery.trim(jQuery('#c_mobile').val());
        emailInfo['website'] = jQuery.trim(jQuery('#c_website').val());
        emailInfo['video'] = jQuery.trim(jQuery('#c_video').val());
        emailInfo['recommend'] = jQuery.trim(jQuery('#c_recommend').val());
        emailInfo['content'] = jQuery.trim(jQuery('#c_content').val());
        if( !emailInfo['email'] ) {
           alert("Please input email correctly!");
           return; 
        }
        if( !emailInfo['title'] ) {
           alert("Please input title correctly!");
           return; 
        }
        // if( !emailInfo['phone'] ) {
        //    alert("Please input phone correctly!");
        //    return; 
        // }
        // if( !emailInfo['mobile'] ) {
        //    alert("Please input mobile correctly!");
        //    return; 
        // }
        // if( !emailInfo['website'] ) {
        //    alert("Please input website correctly!");
        //    return; 
        // }
        // if( !emailInfo['video'] ) {
        //    alert("Please input video correctly!");
        //    return; 
        // }
        // if( !emailInfo['recommend'] ) {
        //    alert("Please input recommendation correctly!");
        //    return; 
        // }
        if( !emailInfo['content'] ) {
           alert("Please input content correctly!");
           return; 
        }
        jQuery.post('?change-email=1', emailInfo, function(r){
            if(r=='ok'){
                alert("Saved successfully!")
            }else{
                alert('Denied your request');
            }

        });
    });
</script>