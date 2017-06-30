<?php
/*
Plugin Name:	CRM System
Description:	A CRM system that creates a custom post type called Customers. This custom post type is populated via form submission
				generated with shortcode or through wp-admin area to users with Administator status
Version:		1.0
Author:			Thom Kitchen
Author URI: 	https://github.com/thomkitchen
License:     	GPL2
License URI: 	https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: 	wporg

CRM System is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
CRM System is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with CRM System. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class CRM_System {

	public function __construct() {
		//Register post types, enqueue scripts
		add_action( 'init', array( $this, 'setup_post_type' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_custom_wp_admin_style' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'load_front_facing_style' ) );

		//Customer creation/edit page controls
		add_action( 'admin_menu', array( $this, 'remove_publish_box' ) );
		add_action( 'admin_menu', array( $this, 'remove_posttype_non_admin' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box') );
		add_action( 'save_post', array( $this, 'save_post') );

		//Admin menu display controls
		add_filter( 'post_row_actions', array( $this, 'remove_quick_edit' ) );
		add_filter( 'manage_edit-crm_customer_columns', array( $this, 'custom_post_type_columns' ) );
		add_action( 'manage_crm_customer_posts_custom_column', array( $this, 'add_columns') );
		add_action( 'manage_crm_customer_posts_column',  array( $this, 'add_columns') );

		//Activation/deactivation controls
		register_activation_hook( __FILE__, array( $this, 'activation' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );

		//Register shortcode
		add_shortcode( 'customer_form', array( $this, 'shortcode' ) );

		//Ajax controls
		add_action('wp_ajax_add_customer', array( $this, 'addCustomer') );
		add_action('wp_ajax_add_customer', array( $this, 'addCustomer') );
		add_action('wp_ajax_nopriv_add_customer', array( $this, 'addCustomer') );
		wp_enqueue_script( 'add-customer-ajax',plugin_dir_url( __FILE__ ) . '/js/addCustomer.js', array( 'jquery' ) );
		wp_localize_script( 'add-customer-ajax', 'AddCustAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

		add_action('admin_head', array( $this, 'customize_edit_page') );

    }

	// register the "customer" custom post type
	function setup_post_type() {
	    register_post_type( 'crm_customer', array(
				'labels' => array(
					'name' => 'Customers',
					'singular_name' => 'Customer',
					'add_new_item' => 'Add New Customer',
					'edit_item' => 'Edit Customer',
					'new_item' => 'New Customer',
					'view_item' => 'View Customer',
					'search_items' => 'Search Customers',
					'not_found' => 'No customer found'
				),
				'show_ui' => true,
				'public' => false,
				'menu_position' => 11,
				'menu_icon' => plugin_dir_url( __FILE__ ) . 'img/cust-menu-icon.png',
				'supports' => array(
					'title'
				),
				'hierarchical' => false,
				'has_archive' => false,
				'capability_type' => 'post',
				'capabilities' => array(
				    'edit_post'          => 'manage_options',
				    'manage_categories'	 => 'manage_options',
				    'read_post'          => 'manage_options',
				    'delete_post'        => 'manage_options',
				    'edit_posts'         => 'manage_options',
				    'edit_others_posts'  => 'manage_options',
				    'delete_posts'       => 'manage_options',
				    'publish_posts'      => 'manage_options',
				    'read_private_posts' => 'manage_options'
				),
				'taxonomies' => array('post_tag','category'),
				'supports' => array(
					'name',
					'email',
					'budget'
					),
		  ) );


	}

	function activation() {
	    // trigger our function that registers the custom post type
	    $this->setup_post_type();
	 
	    // clear the permalinks after the post type has been registered
	    flush_rewrite_rules();
	}

	function deactivation() {
		flush_rewrite_rules();
	}

	function add_meta_box() {
	    $screens = ['crm_customer'];
	    foreach ($screens as $screen) {
	        add_meta_box(
	            'crm-customer-box',           // Unique ID
	            'Customer Information',  // Box title
	            array( $this, 'customer_meta_box_html'),  // Content callback, must be of type callable
	            $screen                   // Post type
	        );
	    }
	}

	//Create form fields for creating/editting customers
	function customer_meta_box_html($post) {
		//Grab customer meta data. Force as an array for easy processing
		$customer_info = (array)$this->get_customer_data();
	    ?>
	    <div class="crm-customer-box-container">
		    <p>
		    	<h3>Name</h3>
			    <label for="crm_name">Enter customer name below:</label></br>
			    <input id="customer-name" class="crm-input-field" value="<?php echo esc_html( $customer_info['name'] ); ?>" name="customer[name]" name="post_title"/>
			</p>

			<p>
				<h3>Phone</h3>
			    <label for="crm_phone">Enter customer phone number below: Ex (555-555-5555)</label></br>
			    <input class="crm-input-field" value="<?php echo esc_html( $customer_info['phone'] ); ?>" name="customer[phone]"/>
			</p>
			<p>
				<h3>Email</h3>
			    <label for="crm_email">Enter customer email address below:</label></br>
			    <input class="crm-input-field" value="<?php echo esc_html( $customer_info['email'] ); ?>" name="customer[email]"/>
			</p>
			<p>
				<h3>Budget</h3>
			    <label for="crm_budget">Enter desired customer budget below:</label></br>
			    <input class="crm-input-field" value="<?php echo esc_html( $customer_info['budget'] ); ?>" name="customer[budget]"/>
			</p>
			<p>
				<h3>Message</h3>
			    <label for="crm_message">Leave any kind of note about the customer below:</label></br>
			    <textarea class="crm-input-field crm-text-field" name="customer[message]"><?php echo esc_html( $customer_info['message'] ); ?></textarea>
			</p>
			<p>
				<?php if ( get_post_status() != 'publish' ) {?>
					<input name="publish" type="submit" class="button button-primary button-large" style="float: right; width: auto;" id="publish" value="Save Customer">
				<?php } else { ?>
					<input name="save" type="submit" class="button button-primary button-large" style="float: right; width: auto;" id="publish" value="Update">
					<?php }?>
				<div style="clear: both;"></div>
			</p>
			<input type="hidden" name="post_title" id="post-title">
			<input type="hidden" name="post_date" id="post-date">
		</div>
	    <?php
	}

	function remove_publish_box() {
		remove_meta_box( 'submitdiv', 'crm_customer', 'side' );
	}

	function load_custom_wp_admin_style() {
        wp_register_style( 'custom_wp_admin_css', plugin_dir_url( __FILE__ ) . '/css/app-admin.css', false, '1.0.0' );
        wp_enqueue_style( 'custom_wp_admin_css' );
        wp_enqueue_script( 'crm-app-js',plugin_dir_url( __FILE__ ) . '/js/app.js', array( 'jquery' ) );
        wp_enqueue_script( 'crm-app-js',plugin_dir_url( __FILE__ ) . '/js/app.js', array( 'jquery' ) );
	}
	function load_front_facing_style() {
		wp_register_style( 'crm-app', plugin_dir_url(__FILE__) . '/css/app.css' );
		wp_register_style( 'crm-bootstrap', plugin_dir_url( __FILE__ ) . '/css/bootstrap.min.css' );
		wp_enqueue_style( 'crm-app' );
		wp_enqueue_style( 'crm-bootstrap' );
	}

	function shortcode( $atts ) {
		$atts = shortcode_atts(array(
				'name' => 'Name:',
				'phone' => 'Phone number:',
				'email' => 'Email address:',
				'budget' => 'Desired budget:',
				'message' => 'Message:',
				'name-max-length' => 524288,
				'phone-max-length' => 524288,
				'email-max-length' => 524288,
				'budget-max-length' => 524288,
				'message-max-length' => 524288,
				'message-rows' => 5,
				'message-cols' => 10
			), $atts);

		?>
		<div id="add-customer-container">
			<form method="post" id="add-customer-form">
				<div class="form-group">
					<label for="name"><?php echo $atts['name'] ?></label>
					<input type="text" name="name" name="post_title" id="customer-name" class="form-control" maxlength="<?php echo (int)$atts['name-max-length'];?>">
				</div>
				<div class="form-group">
					<label><?php echo $atts['phone'] ?></label>
					<input type="text" name="phone" class="form-control" maxlength="<?php echo (int)$atts['phone-max-length'];?>">
				</div>
				<div class="form-group">
					<label><?php echo $atts['email']?></label>
					<input type="text" name="email" class="form-control" maxlength="<?php echo (int)$atts['email-max-length'];?>">
				</div>
				<div class="form-group">
					<label><?php echo $atts['budget']?></label>
					<input type="text" name="budget" class="form-control" maxlength="<?php echo (int)$atts['budget-max-length'];?>">
				</div>
				<div class="form-group">
					<label><?php echo $atts['message']?></label>
					<textarea name="message" class="form-control" maxlength="<?php echo (int)$atts['message-max-length'];?>" rows="<?php echo (int)$atts['message-rows'];?>" cols="<?php echo (int)$atts['message-cols'];?>"></textarea>
				</div>
				<input name="add-customer" id="add-customer" type="submit" class="button button-primary button-large" value="Add Customer">
			</form>

			<div id="add-customer-results"></div>
		</div>
		<?php
	}

	function custom_post_type_columns( $columns ) {
		//unset( $columns['title'] );
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => __( 'Name' ),
			'email' => __( 'Email' ),
			'budget' => __( 'Budget' ),
		);

		return $columns;
	}

	function add_columns( $name) {
	    $customer = (array)$this->get_customer_data();
	    switch ($name) {
	    	case 'name':
	    		$name = $customer['name'];
	    		echo $name;
	        case 'email':
	            $email = $customer['email'];
	            echo $email;
	            break;
	        case 'budget':
	        	$budget = $customer['budget'];
	        	break;
	    }
	}

	function save_post() {
		global $post;
		if ( $_POST['customer'] ) {

			$customer_data = array();
			foreach ( $_POST['customer'] as $key => $value ) {
				$customer_data[$key] = $value;
				if ( $key === 'name' ) {
					$post->post_title = $value;
				}
			}
			if ( $this->validate_input( $customer_data ) ) {
				$this->set_customer_data( $customer_data );
			}
		}


	}

	function addCustomer(){
	    global $wpdb;

	    if ( !$_POST["customer_data"] )
	    	echo '<span class="submit-error">Error finding data</span>';

	    $customer_data = array();
		foreach ( $_POST['customer_data'] as $key => $value ) {
			$customer_data[$key] = $value;
		}

		if ( $this->validate_input( $customer_data ) ) {
			$post_id = wp_insert_post(array(
			    'post_type' => 'crm_customer',
			    'post_title' => $name,
			    'post_status' => 'publish',
			    'comment_status' => 'closed',
			    'ping_status' => 'closed',
			));
			$this->set_customer_data( $customer_data, $post_id );
			echo '<span class="submit-success">Success!</span>';
		}
		else {
			echo '<span class="submit-error">Error with one or more fields. Please try again.</span>';
		}

	    exit();
	}

	//Helper get/set functions
	function get_customer_data( $post_id = false ) {
		if ( !$post_id ) {
			global $post;
			$post_id = $post->ID;
		}

		return get_post_meta( $post_id, 'customer_data', true );

	}
	function set_customer_data( $data, $post_id = false ) {
		if ( !$post_id ) {
			global $post;
			$post_id = $post->ID;
		}
		if ( is_array( $data ) && !empty( $data ) ) {
			if ( !add_post_meta( $post_id, 'customer_data', $data, true ) ) { 
			   update_post_meta( $post_id, 'customer_data', $data );
			}
		} else {
			return false;
		}
	}

	function remove_quick_edit( $actions ) {
		unset($actions['inline hide-if-no-js']);
		return $actions;
	}

	function remove_posttype_non_admin() { 
		if( !current_user_can( 'administrator' ) ) {
			remove_menu_page( 'edit.php?post_type=crm_customer' ); 
			remove_menu_page( 'edit-tags.php?taxonomy=post_tag&post_type=crm_customer' );
			remove_menu_page( 'edit-tags.php?taxonomy=category&post_type=crm_customer' );
		}
	}

	function validate_input( $customer_data ) {
		if ( !is_array($customer_data) ) {
			return false;
		}

		foreach ( $customer_data as $key => $value ) {
			switch ( $key ){
				case 'name':
					if ( empty($value) || !preg_match("/^[a-zA-Z \.]*$/", $value) ) {
						wp_die( '<span class="submit-error">Invalid name entered. Please try again</span>', 'ERROR', array( 'back_link' => true ) );
					}
					break;
				case 'email':
					if ( !filter_var( $value, FILTER_VALIDATE_EMAIL ) ) {
						wp_die( '<span class="submit-error">Invalid email address entered. Please try again</span>', 'ERROR', array( 'back_link' => true ) );
					}
					break;
				case 'phone':
					if ( !preg_match("/[0-9]{3}-[0-9]{3}-[0-9]{4}/", $value ) ) {
						wp_die( '<span class="submit-error">Invalid phone number entered. Please try again</span>', 'ERROR', array( 'back_link' => true ) );
					}
					break;
				case 'budget':
					if ( !preg_match("/\b\d{1,3}(?:,?\d{3})*(?:\.\d{2})?\b/", $value) ) {
						wp_die( '<span class="submit-error">Invalid budget entered. Please try again</span>', 'ERROR', array( 'back_link' => true ) );
					}
					break;
				case 'message':
					$value = filter_var( $value, FILTER_SANITIZE_STRING );
					break;
				default:
					break;

			}
		}
		//Cool. We didn't die. Input is safe
		return true;
	}

	function customize_edit_page() {
		$screen = get_current_screen();
		if ( 'crm_customer' === $screen->post_type ) {
	    	add_filter( 'wp_dropdown_cats', '__return_false' );
	    	add_filter('months_dropdown_results', '__return_empty_array');
	    	echo '<style>#posts-filter #post-query-submit{ display: none; } </style>';
	    }
	}
}
$crm = new CRM_system();