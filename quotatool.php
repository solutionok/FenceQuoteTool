<?php

/*
  Plugin Name: fence quota tool
  Version: 0.9.0
  Author: bestcoder
 */
/////////// frontend ordering  /////////////////////////////////////////////////////////////////////////////////////
class PageTemplater {

    /**
     * A reference to an instance of this class.
     */
    private static $instance;

    /**
     * The array of templates that this plugin tracks.
     */
    protected $templates;

    /**
     * Returns an instance of this class. 
     */
    public static function get_instance() {

        if (null == self::$instance) {
            self::$instance = new PageTemplater();
        }

        return self::$instance;
    }

    /**
     * Initializes the plugin by setting filters and administration functions.
     */
    private function __construct() {

        $this->templates = array();


        // Add a filter to the attributes metabox to inject template into the cache.
        if (version_compare(floatval(get_bloginfo('version')), '4.7', '<')) {

            // 4.6 and older
            add_filter(
                    'page_attributes_dropdown_pages_args',
                    array($this, 'register_project_templates')
            );
        } else {

            // Add a filter to the wp 4.7 version attributes metabox
            add_filter(
                    'theme_page_templates', array($this, 'add_new_template')
            );
        }

        // Add a filter to the save post to inject out template into the page cache
        add_filter(
                'wp_insert_post_data',
                array($this, 'register_project_templates')
        );


        // Add a filter to the template include to determine if the page has our 
        // template assigned and return it's path
        add_filter(
                'template_include',
                array($this, 'view_project_template')
        );


        // Add your templates to this array.
        $this->templates = array(
            'order-custom-template.php' => 'order-custom-page',
        );


        synctables();
        //admin panal product manage page
        // add_menu_page( 'dash', $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
    }

    /**
     * Adds our template to the page dropdown for v4.7+
     *
     */
    public function add_new_template($posts_templates) {
        $posts_templates = array_merge($posts_templates, $this->templates);
        return $posts_templates;
    }

    /**
     * Adds our template to the pages cache in order to trick WordPress
     * into thinking the template file exists where it doens't really exist.
     */
    public function register_project_templates($atts) {

        // Create the key used for the themes cache
        $cache_key = 'page_templates-' . md5(get_theme_root() . '/' . get_stylesheet());

        // Retrieve the cache list. 
        // If it doesn't exist, or it's empty prepare an array
        $templates = wp_get_theme()->get_page_templates();
        if (empty($templates)) {
            $templates = array();
        }

        // New cache, therefore remove the old one
        wp_cache_delete($cache_key, 'themes');

        // Now add our template to the list of templates by merging our templates
        // with the existing templates array from the cache.
        $templates = array_merge($templates, $this->templates);

        // Add the modified cache to allow WordPress to pick it up for listing
        // available templates
        wp_cache_add($cache_key, $templates, 'themes', 1800);

        return $atts;
    }

    /**
     * Checks if the template is assigned to the page
     */
    public function view_project_template($template) {

        // Get global post
        global $post;

        // Return template if post is empty
        if (!$post) {
            return $template;
        }

        // Return default template if we don't have a custom one defined
        if (!isset($this->templates[get_post_meta(
                                $post->ID, '_wp_page_template', true
                )])) {
            return $template;
        }

        $file = plugin_dir_path(__FILE__) . get_post_meta(
                        $post->ID, '_wp_page_template', true
        );

        // Just to be safe, we check if the file exist first
        if (file_exists($file)) {
            return $file;
        } else {
            echo $file;
        }

        // Return template
        return $template;
    }

}
add_action('plugins_loaded', array('PageTemplater', 'get_instance'));

function change_email(){

    if(isset($_REQUEST['change-email'])){
        $pr = $_REQUEST;
        global $wpdb;
        $row = array('email' => $pr['email']);
        $row['title'] = $pr['title'];
        $row['phone'] = $pr['phone'];
        $row['mobile'] = $pr['mobile'];
        $row['website'] = $pr['website'];
        $row['video'] = $pr['video'];
        $row['recommend'] = $pr['recommend'];
        $row['content'] = $pr['content'];
        $wpdb->update('quote_email', $row, array('id'=>1));

        exit('ok');
    }
}
add_action('init', 'change_email');

function register_order(){

    if(isset($_REQUEST['do-order'])){
        $pr = $_REQUEST;

        date_default_timezone_set(get_option('timezone_string'));
        $row = array('order_date' => date('Y-m-d H:i:s'));

        if(filter_var($pr['uname'])) $row['uname'] = $pr['uname'];
        else exit('bad name');

        if(filter_var($pr['uemail'], FILTER_VALIDATE_EMAIL))$row['uemail'] = $pr['uemail'];
        else exit('bad email');

        $row['uphone'] = $pr['uphone'];

        $row['address'] = $pr['address'];
        
        if(filter_var($pr['length'])) $row['fence_length'] = round($pr['length'], 1);
        else exit('bad fence length');

        $row['fence_lines'] = "";
        if($pr['measure_type'] == 'true') {
            if(count($pr['map_lines'])) 
                $row['fence_lines'] = json_encode($pr['map_lines']);
        }

        if(!empty($pr['material'])) $row['material_info'] = json_encode($pr['material']);
        else exit('bad material info');

        if(!empty($pr['gates'])) $row['gates'] = json_encode($pr['gates']);

        if(!empty($pr['total_price'])) $row['total_price'] = round($pr['total_price'], 1);
        else exit('bad price');

        global $wpdb;
        $wpdb->insert('quote_orders', $row);
        $email_info= $wpdb->get_results('select * from quote_email where id = 1')[0];

        $to = $pr['uemail'];

        //sender
        $from = $email_info->email;
        $fromName = $email_info->title;

        //email subject
        $subject = $email_info->title;

        //attachment file path
        $file = plugin_dir_path(__FILE__)."1.pdf";

        //email body content$
        $htmlContent = '<html><body>';
        $htmlContent .= '<p>Dear '.$pr['uname'].'</p>';
        $htmlContent .= '<p>'. $email_info->content .'</p>';
        $htmlContent .= '<p>Length : '.$pr['length'].'m</p>';
        $htmlContent .= '<p>Type : '.$pr['material']['style'].','.$pr['cur_pull'].'</p>';
        $htmlContent .= '<p style="color:red;">Cost  : $'.$pr['total_price'].'</p>';
        $htmlContent .= '<p>Contact Number : '.$pr['uphone'].'</p>';
        $htmlContent .= '<p>Email Address : '.$pr['uemail'].'</p>';

        $htmlContent .= '<h3>'.$email_info->title.'</h3>';
        if($email_info->phone != '')
            $htmlContent .= '<p>Office: ' . $email_info->phone . '</p>';
        if($email_info->mobile != '')
            $htmlContent .= '<p>Mobile: ' . $email_info->mobile . '</p>';
        if($email_info->website != '')
            $htmlContent .= '<p>Website: ' . $email_info->website . '</p>';
        if($email_info->video != '')
            $htmlContent .= '<p>Watch Our Video <a href="' . $email_info->video . '">Click Here</a></p>';
        if($email_info->recommend != '')    
            $htmlContent .= '<p>Recommendations: <a href="' . $email_info->recommend . '">Click Here</a></p>';
        $htmlContent .= "</body></html>";

        //header for sender info
        $headers = "From: $from";

        //boundary 
        $semi_rand = md5(time()); 
        $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x"; 

        //headers for attachment 
        $headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\""; 

        //multipart boundary 
        $message = "--{$mime_boundary}\n" . "Content-Type: text/html; charset=\"UTF-8\"\n" .
        "Content-Transfer-Encoding: 7bit\n\n" . $htmlContent . "\n\n"; 

        // //preparing attachment
        // if(!empty($file) > 0){
        //     if(is_file($file)){
        //         $message .= "--{$mime_boundary}\n";
        //         $fp =    @fopen($file,"rb");
        //         $data =  @fread($fp,filesize($file));

        //         @fclose($fp);
        //         $data = chunk_split(base64_encode($data));
        //         $new_file_name = "Lot 504 Ceremony Drive & Lot 503 Ceremony Drive, Tarniet.pdf";
        //         $message .= "Content-Type: application/octet-stream; name=\"".$new_file_name."\"\n" . 
        //         "Content-Description: ".$new_file_name."\n" .
        //         "Content-Disposition: attachment;\n" . " filename=\"".$new_file_name."\"; size=".filesize($file).";\n" . 
        //         "Content-Transfer-Encoding: base64\n\n" . $data . "\n\n";
        //     }
        // }
        $message .= "--{$mime_boundary}--";
        $returnpath = "-f" . $from;

        //send email
        mail($to, $subject, $message, $headers, $returnpath);

        $to = $email_info->email;

        //sender
        $from = $email_info->email;
        $fromName = 'Orders From Quotatool';

        //email subject
        $subject = 'Orders From Quotatool'; 

        //attachment file path
        $file = plugin_dir_path(__FILE__)."1.pdf";

        //email body content
        $htmlContent = '<html><body>';
        $htmlContent .= '<p>Order Date : '.date('Y-m-d H:i:s').'</p>';
        $htmlContent .= '<p>Name : '.$pr['uname'].'</p>';
        $htmlContent .= '<p>Email : '.$pr['uemail'].'</p>';
        $htmlContent .= '<p>Phone : '.$pr['uphone'].'</p>';
        $htmlContent .= '<p>Address : '.$pr['address'].'</p>';
        $htmlContent .= '<p style="color:red;">Price  : $'.$pr['total_price'].'</p>';
        $htmlContent .= '<p style="color:red;">Length  : '.$pr['length'].'</p>';
        $htmlContent .= '<p>Material : '.$pr['material']['style'].','.$pr['cur_pull'].'</p>';
        if($pr['gates'][0]['name'])
            $htmlContent .= '<p>Gate : '.$pr['gates'][0]['name'].'</p>';
        
    
        $htmlContent .= "</body></html>";

        //header for sender info
        $headers = "From: $from";

        //boundary 
        $semi_rand = md5(time()); 
        $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x"; 

        //headers for attachment 
        $headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\""; 

        //multipart boundary 
        $message = "--{$mime_boundary}\n" . "Content-Type: text/html; charset=\"UTF-8\"\n" .
        "Content-Transfer-Encoding: 7bit\n\n" . $htmlContent . "\n\n"; 

        $message .= "--{$mime_boundary}--";
        $returnpath = "-f" . $from;

        //send email
        mail($to, $subject, $message, $headers, $returnpath);

        exit('ok');
    }

}
add_action('init', 'register_order');

function activate_serial(){
    $promise_keys = array('8','3','0','6','h','e','a','l','e','r');
    global $wpdb;

    if(isset($_REQUEST['do-activate'])){
        $pr = $_REQUEST;

        $email = $pr['email'];
        $serial = $pr['serial'];
        $expire_date_code = "";
        $hash_code = "";
        $expire_date = "";
        for($i = 0; $i < strlen($serial); $i++) {
            if($i < (strlen($serial) - 8))
                $hash_code .= $serial[$i];
            else
                $expire_date_code .= $serial[$i];
        }

        for($i = 0; $i < strlen($expire_date_code); $i++) {
            for($j = 0; $j < count($promise_keys); $j++) {
                if($promise_keys[$j] == $expire_date_code[$i]) {
                    if(strlen($expire_date) == 4 || strlen($expire_date) == 7) {
                        $expire_date .= '-';
                    }
                    $expire_date .= $j;
                    break;
                }
            }
        }
        $from = ($email . "/fencing/" . $expire_date);
        $hash_code1 = md5($from);
        $curDate = date("Y-m-d");
        if($hash_code == $hash_code1 && $curDate < $expire_date) {
            $exist= $wpdb->get_var('select itype from quote_items where itemn = "serial"');

            if( !$exist ) {
                $row = array('itemn' => 'serial');
                $row['itype'] = $serial;
                $wpdb->insert('quote_items', $row);
            } else {
                $wpdb->update('quote_items', array('itype' => $serial), array('itemn' => 'serial'));
            }
            exit('success');
        }
        else {
            exit('failed');
        }
    }

}
add_action('init', 'activate_serial');

function check_serial(){
    global $wpdb;

    if(isset($_REQUEST['check-activate'])){
        $pr = $_REQUEST;

        $exist= $wpdb->get_var('select itype from quote_items where itemn = "serial"');

        if( $exist )
            exit('success');
        else
            exit('failed');
    }

}
add_action('init', 'check_serial');

/////////// backend:order list  /////////////////////////////////////////////////////////////////////////////////////
function delete_order(){
    if(!isset($_GET['delete'])){
        return;
    }
    
    global $wpdb;
    $id = $_GET['delete'];
    $wpdb->delete('quote_orders', array('id'=>$id));
    exit('<script>parent.location.reload();</script>');
}
add_action('init', 'delete_order');

function order_list_page(){
    require_once 'order_list.php';
}

function quota_menu(){
    add_menu_page( 'Fence Orders', 'Fence Orders', 'manage_options', 'the-order-main-page', 'order_list_page', 'dashicons-admin-page' );
    add_submenu_page( 'the-order-main-page', __('Orders', 'the-order-main-page'), __('Orders', 'the-order-main-page'), 'manage_options', 'the-order-main-page', 'order_list_page' );
}
add_action('admin_menu', 'quota_menu');


/////////// backend:item list  /////////////////////////////////////////////////////////////////////////////////////
function material_meta_page(){
    require_once 'material_meta_page.php' ;
}

function material_meta_page_menu(){
    add_submenu_page('the-order-main-page', 'Material Meta', 'Material Meta', 'manage_options', 'material-meta-page', 'material_meta_page');
}
add_action('admin_menu', 'material_meta_page_menu');

function material_meta_save(){
    if(!isset($_POST['do_quote_meta_save'])){
        return;
    }
    
    global $wpdb;
    $meta_id = $_POST['do_quote_meta_save'];

    if($_POST['do_quote_meta_type']=='delete'){
        $item_image= $wpdb->get_var('select item_image from quote_items where id=' . $meta_id);
        $type= $wpdb->get_var('select itype from quote_items where id=' . $meta_id);
        
        @unlink(ABSPATH.'/'.$item_image);
        $wpdb->delete('quote_items', array('id'=>$meta_id));
        $wpdb->delete('quote_materials', array('m'.$type=>$meta_id));
    }else{
        $row = array('itemn' => $_POST['itemn']);

        if(intval($meta_id)==0){
            if(empty($_POST['itemn'])) die('<script>alert("A item name required!");</script>');
            if((!isset($_FILES['item_image']) || !$_FILES['item_image']['tmp_name']) && $_POST['meta_type'] != 'state')die('<script>alert("Choose an item image!");</script>');
            $row['itype'] = $_POST['meta_type'];
            if($_POST['meta_type'] != 'state') {
                $ext = check_validate_image_uploaded($_FILES['item_image']);

                $row['item_image'] = image_move($_FILES['item_image']);
            } else {
                $row['item_image'] = " ";
            }

            $wpdb->insert('quote_items', $row);
        }else if(intval($meta_id) && $_POST['do_quote_meta_type']=='update'){

            if(isset($_FILES['item_image']) && $_FILES['item_image']['tmp_name']){
                $item_image= $wpdb->get_var('select item_image from quote_items where id=' . $meta_id);
                @unlink(ABSPATH.'/'.$item_image);
                
                $ext = check_validate_image_uploaded($_FILES['item_image']);
                $row['item_image'] = image_move($_FILES['item_image']);
      
            }
            $wpdb->update('quote_items', $row, array('id'=>$meta_id));
        }

    }
    
    die('<script>parent.location.reload();</script>');
}
add_action('init', 'material_meta_save');

/////////// backend:Email_Content  /////////////////////////////////////////////////////////////////////////////////////
function email_manage_page(){
    require_once 'email_manage_page.php' ;
}
function email_manage_page_menu(){
    add_submenu_page('the-order-main-page', 'Email', 'Email', 'manage_options', 'email-manage-page', 'email_manage_page');
}
add_action('admin_menu', 'email_manage_page_menu');
/////////// backend:gate list  /////////////////////////////////////////////////////////////////////////////////////
function gate_manage_page(){
    require_once 'gate_manage_page.php' ;
}

function gate_manage_page_menu(){
    add_submenu_page('the-order-main-page', 'Gate', 'Gate', 'manage_options', 'gate-manage-page', 'gate_manage_page');
}
add_action('admin_menu', 'gate_manage_page_menu');

function gate_save(){
    if(!isset($_POST['do_gate_manage_action'])){
        return;
    }
    
    global $wpdb;
    $meta_id = $_POST['do_gate_manage_id'];

    if($_POST['do_gate_manage_action']=='delete'){
        $item_image= $wpdb->get_var('select item_image from quote_gates where id=' . $meta_id);
        @unlink(ABSPATH.'/'.$item_image);
        $wpdb->delete('quote_gates', array('id'=>$meta_id));
    }else{
        $row = array('itemn' => $_POST['itemn'], 'price' => $_POST['price']);

        if(intval($meta_id)==0){
            if(empty($_POST['itemn'])) die('<script>alert("A item name required!");</script>');
            if(!isset($_FILES['item_image']) || !$_FILES['item_image']['tmp_name'])die('<script>alert("Choose an item image!");</script>');
            $ext = check_validate_image_uploaded($_FILES['item_image']);

            $row['item_image'] = image_move($_FILES['item_image']);

            $wpdb->insert('quote_gates', $row);
    
        }else if(intval($meta_id) && $_POST['do_quote_meta_type']=='update'){

            if(isset($_FILES['item_image']) && $_FILES['item_image']['tmp_name']){
                $item_image= $wpdb->get_var('select item_image from quote_gates where id=' . $meta_id);
                @unlink(ABSPATH.'/'.$item_image);
                
                $ext = check_validate_image_uploaded($_FILES['item_image']);
                $row['item_image'] = image_move($_FILES['item_image']);
      
            }
            $wpdb->update('quote_gates', $row, array('id'=>$meta_id));
        }

    }
    die('<script>parent.location.reload();</script>');
}
add_action('init', 'gate_save');


/////////// backend:material list  /////////////////////////////////////////////////////////////////////////////////////
function material_list_page(){
    require_once 'material_list_page.php' ;
}

function material_list_page_menu(){
    add_submenu_page('the-order-main-page', 'Material List', 'Material List', 'manage_options', 'material-list-page', 'material_list_page');
}
add_action('admin_menu', 'material_list_page_menu');

function material_save(){
    if(!isset($_POST['do-save-material-action']))return;
    
    global $wpdb;
    
    if($_POST['do-save-material-action']=='insert'){
        $re = $wpdb->insert('quote_materials', array(
            'mtype' => $_POST['mtype'],
            'mstyle' => $_POST['mstyle'],
            'mcolor' => $_POST['mcolor'],
            'mheight' => $_POST['mheight'],
            'mstate' => $_POST['mstate'],
            'mprice' => $_POST['mprice'],
        ));
        if(false === $re){
            die('<script>alert("The same material is already registered.")</script>');
        }
    }
    
    if($_POST['do-save-material-action']=='update'){
        $wpdb->update('quote_materials', array('mprice' => $_POST['mprice']), array('id' => $_POST['material_id']));
    }
    
    else if($_POST['do-save-material-action']=='delete'){
        $re = $wpdb->delete('quote_materials', array('id' => $_POST['material_id']));
    }
    
    die('<script>parent.location.reload(); alert("Save Success!");</script>');
}
add_action('init', 'material_save');



//////////////////////////////////////////////////////////////////////
function check_validate_image_uploaded($upload_file){
    if ($upload_file['error']){
        die('<script>alert("Fail image upload! Picture files are not larger than '.ini_get('upload_max_filesize').'?");</script>');
    }
    $validext = array('jpg', 'png', 'gif');
    $ext = strtolower(end(explode('.', $upload_file['name'])));
    if(!$ext || !in_array($ext, $validext)){
        die('<script>alert("Invalid image! Only jpg, png, gif are available.");</script>');
    }
    return $ext;
}

function image_move($upload_file){
    $dir = wp_upload_dir(null, true);
    @chmod($dir['path'], 0777);
    $fpath = $dir['path'].DIRECTORY_SEPARATOR.$upload_file['name'];
    @unlink($fpath);
    @move_uploaded_file($upload_file['tmp_name'], $fpath);

    $image = wp_get_image_editor($fpath);
    
    if (!is_wp_error($image)) {
        $image->resize( 100, 100 );
        $final_image = $image->save( $fpath );
    }else{
        die('');
    }
    
    $urls = parse_url($dir['url']);
    
    return $urls['path'].'/'.$upload_file['name'];
}

function validate_phone_number($phone){
     $filtered_phone_number = filter_var($phone, FILTER_SANITIZE_NUMBER_INT);
     $phone_to_check = str_replace("-", "", $filtered_phone_number);
     if (strlen($phone_to_check) < 10 || strlen($phone_to_check) > 14) {
        return false;
     } else {
       return true;
     }
}

function synctables(){
    global $wpdb;
    if($wpdb->get_var("SHOW TABLES LIKE 'quote_items'") != 'quote_items') {

         $sql = "CREATE TABLE `quote_items` (                                                                                 
               `id` int(11) NOT NULL AUTO_INCREMENT,                                                                      
               `itemn` text,                                                          
               `item_image` text,                                                     
               `itype` text COLLATE utf8mb4_bin,                                                                          
               PRIMARY KEY (`id`)                                                                                         
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC  ";
         require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
         dbDelta( $sql );
    }
    

    if($wpdb->get_var("SHOW TABLES LIKE 'quote_gates'") != 'quote_gates') {

         $sql = "CREATE TABLE `quote_gates` (                                                                                                   
               `id` int(11) NOT NULL AUTO_INCREMENT,                                                                                        
               `itemn` text CHARACTER SET utf8 COLLATE utf8_bin,                                                                            
               `item_image` text CHARACTER SET utf8 COLLATE utf8_bin,                                                                       
               `price` double,  
               `sg_type` int(11) NOT NULL,                                                                                          
               PRIMARY KEY (`id`)                                                                                                           
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC  ";
         require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
         dbDelta( $sql );
    }

    if($wpdb->get_var("SHOW TABLES LIKE 'quote_email'") != 'quote_email') {

        $sql = "CREATE TABLE `quote_email` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `email` varchar(255) NOT NULL,
            `title` varchar(255) NOT NULL,
            `phone` varchar(255) NOT NULL,
            `mobile` varchar(255) NOT NULL,
            `website` varchar(255) NOT NULL,
            `video` varchar(255) NOT NULL,
            `recommend` varchar(255) NOT NULL,
            `content` text NOT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
          INSERT INTO `quote_email` (`id`, `email`, `title`, `phone`, `mobile`, `website`, `video`, `recommend`, `content`) 
          VALUES ('1', 'quotes@fencingquotesonline.com.au', 'Fencing Quote', '03 9028 7557', '0432 978 683', ' www.fencingquotesonline.com.au', 'https://www.youtube.com/watch?v=qzq5_dlC4cc', 'https://hipages.com.au/connect/fencingquotesonline', 'Be sure to check out our website for valuable information in the meantime, and we will have a formal written quote to you within 24 hours.\r\nIf you have any questions or if we can further assist you in any other way, please feel free to call us at 03 9028 7557 or email us at Quotes@FencingQuotesOnline.com.au\r\nWe hope to hear from you soon!\r\nFencing Quotes Online Australia');
        ";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
   }
    
    
    if($wpdb->get_var("SHOW TABLES LIKE 'quote_materials'") != 'quote_materials') {

         $sql = "CREATE TABLE `quote_materials` (                                                                             
                   `id` int(11) NOT NULL AUTO_INCREMENT,                                                                      
                   `mtype` int(11) DEFAULT NULL,                                                                              
                   `mstyle` int(11) DEFAULT NULL,                                                                             
                   `mcolor` int(11) DEFAULT NULL,                                                                             
                   `mheight` int(11) DEFAULT NULL,
                   `mstate` int(11) DEFAULT NULL,                                                                            
                   `mprice` double DEFAULT NULL,                                                                              
                   PRIMARY KEY (`id`),
                   UNIQUE KEY `NewIndex1` (`mtype`,`mstyle`,`mcolor`,`mheight`,`mstate`)
                 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC";
         require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
         dbDelta( $sql );
    }
    
    if($wpdb->get_var("SHOW TABLES LIKE 'quote_orders'") != 'quote_orders') {

         $sql = "CREATE TABLE `quote_orders` (                                                           
                `id` int(11) NOT NULL AUTO_INCREMENT,                                                 
                `order_date` datetime DEFAULT NULL,                                                   
                `uname` text,                                                                         
                `uemail` text,                                                                        
                `uphone` text,                                                                   
                `address` text,                                                                        
                `fence_length` double DEFAULT NULL,                                                   
                `fence_lines` text,                                                                   
                `material_info` text,                                                                 
                `gates` text,                                                                         
                `total_price` double DEFAULT NULL,                                                    
                `has_check` int(11) DEFAULT NULL,                                                     
                PRIMARY KEY (`id`)                                                                                        
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC";
         require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
         dbDelta( $sql );
    }
    
}

