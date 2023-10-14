<?php 
/**
 * Plugin Name: Simple Contact Form
 * Description: Simple contact form
 * Author: Mr Digital
 * Author URI: mrdigital.com
 * Version: 1.0.0
 * Text Domain: simple-contact-form
 */

if( !defined('ABSPATH')){
    echo "Do not even dare hacking this site";
    exit;
}

class simpleContactForm {
public function __construct(){
    /* this is the 1st method that will be activated*/
    //init hook and a callback = a promise to load when fucnis created
    /*plugin will load and will load the construct and then will add all of these hooks and the init hook and then will run the create_c post tupe*/
    add_action('init',array($this,'create_custom_post_type'));


    // add assets

    add_action('wp_enqueue_scripts', array($this, 'load_assets'));

    //shortcode

    add_shortcode( 'contact-form', array($this,'load_shortcode' ));
//load js
    add_action('wp_footer', array($this,'load_scripts'));

    //hook to create endpoint REGISTER REST API

    add_action( 'rest_api_init', array($this, 'register_rest_api') );
}

public function create_custom_post_type(){
$args = array(
    'public' => true,
    'has_archive' => true,
    'supports' => array('title'),
    'exclude_from_search' => true,
    'publicly_queryable' => false,
    'capability' => 'manage_options',
    'labels' => array(
        'name' => 'Contact Form',
'singular_name' => 'Contact form Entry',
'menu_icon' => 'dashicons-editor-ltr',
    )
    );
    register_post_type( 'simple_contact_form', $args );
}


public function load_assets(){
wp_enqueue_style( 'simple-contact-form', plugin_dir_url(__FILE__) . '/css/simple-contact-form.css', array(), '1', 'all' );
wp_enqueue_script( 'simple-contact-form',plugin_dir_url(__FILE__) .  '/js/simple-contact-form.js',array(), '1', true);
}

public function load_shortcode(){
?>
<div class="simple-contact-form">
<h1>Send us an email</h1>
<p>Please fill the below form</p>

<form id="simple-contact-form_form" >
<div class="form-group">
<input name="u-name" type="text" placeholder="Name" class="form-control mb-2">
</div>
<div class="form-group">
<input name="email" type="email" placeholder="Email" class="form-control mb-2">
</div>
<div class="form-group">
<input name="tel" type="tel" placeholder="Phone" class="form-control mb-2" >
</div>
<div class="form-group">
<textarea  name="text" placeholder="type your message" class="form-control mb-2"></textarea>
</div>
<button class="btn btn-success  btn-block w-100">Submit</button>


</form>
</div>
<?php }

public function load_scripts(){?>
<script>
var nonce = '<?php echo wp_create_nonce('wp_rest'); ?>';
(function($){
    $('#simple-contact-form_form').submit(function(event){
event.preventDefault();

var  form = $(this).serialize();


//AJAX Setting up the AJAX REQUEST
$.ajax({
    method: 'post',
    url: '<?php echo get_rest_url( null, 'simple-contact-form/v1/send-email');?>',
    headers: {'X-WP-Nonce':  nonce },
    data:form
})


});

})(jQuery);





</script>
<?php
}

public function register_rest_api(){
    register_rest_route('simple-contact-form/v1', 'send-email', array(
    'methods' => 'POST',
    'callback' => array($this,'handle_contact_form')
    ));
}
public function handle_contact_form($data){
$headers = $data-> get_headers();
$param = $data->get_params();
echo json_encode($headers);
$nonce = $headers['x_wp_nonce'][0];
if(!wp_verify_nonce($nonce,'wp_rest')){
    return new WP_REST_Response('Message not sent',422);
}

$post_id = wp_insert_post([
    'post_type' => 'simple_contact_form',
    'post_title' => 'Contact enquiry',
    'post_status' => 'publish'
]);


if($post_id){
    return new WP_REST_Response('Thank you for your message',200);
}
}
}

/* Instantiating the simpleContactForm class*/
new simpleContactForm;

/* Create a custom post type*/