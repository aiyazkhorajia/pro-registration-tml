<?php
/**
 * Plugin Name: Reg-Pro Addons for Theme My Login
 * Plugin URI: ayaz.khorajia@gmail.com
 * Description: Theme My Login Addons for registration fields, Just drag and drop the fields from list and create a registration form with user meta information.
 * Version: 1.0
 * Author: Aiyaz
 * Author URI: ayaz.khorajia@gmail.com
 * License: GPL2
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !is_plugin_active( 'theme-my-login/theme-my-login.php' ) ) {
	deactivate_plugins( plugin_basename( __FILE__ ) );
	add_action('admin_notices', 'tml_activation_warning');
}

function tml_activation_warning() {
    echo '<div id="message" class="error">';
    echo '  <p>Reg-Pro Addons is deactivated, because this plugin requires Theme My Login plugin installed and activated.</p>';
    echo '</div>';
}
/* Register style and script */

function rpdd_wp_enqueue() {
	wp_register_script( 'jquery', plugins_url( 'js/jquery-3.2.1.min.js', __FILE__ ) );
}

add_action( 'wp_enqueue_scripts', 'rpdd_wp_enqueue' );

function rpdd_admin_enqueue() {

	if ( is_admin() && ( isset( $_GET['page'] ) && $_GET['page'] === 'rpdd-setting-page' ) ) {
		// Register Scripts
		wp_register_script( 'jquery', plugins_url( 'js/jquery-3.2.1.min.js', __FILE__ ) );
		wp_register_script( 'rpdd-jquery-ui-min', plugins_url( 'js/jquery-ui.min.js', __FILE__ ) );
		wp_register_script( 'rpdd-popper-min', plugins_url( 'js/popper.min.js', __FILE__ ) );
		wp_register_script( 'rpdd-bootstrap-min', plugins_url( 'js/bootstrap.min.js', __FILE__ ) );
		wp_register_script( 'rpdd-form-builder', plugins_url( 'js/form_builder.js', __FILE__ ) );
		wp_register_script( 'rpdd-ui-touch-punch', plugins_url( 'js/jquery.ui.touch-punch.min.js', __FILE__ ) );
		// Localize the script with new data
		$form_fields = rpdd_get_form_fields();
		$rpdd_ajax   = array(
			'form_fields' => $form_fields,
			'ajax_url'    => admin_url( 'admin-ajax.php' ),
		);
		wp_localize_script( 'rpdd-form-builder', 'rpdd_ajax', $rpdd_ajax );

		// Register Styles
		wp_register_style( 'form_builder-css', plugins_url( '/css/form_builder.css', __FILE__ ) );
		wp_register_style( 'rpdd-bootstrap-min-css', plugins_url( '/css/bootstrap.min.css', __FILE__ ) );
		wp_register_style( 'rpdd-font-awesome-min-css', 'https://netdna.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css' );
		// Enqueue Scripts and Styles
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'rpdd-jquery-ui-min' );
		wp_enqueue_script( 'rpdd-popper-min' );
		wp_enqueue_script( 'rpdd-bootstrap-min' );
		wp_enqueue_script( 'rpdd-form-builder' );
		wp_enqueue_script( 'rpdd-ui-touch-punch' );
		wp_enqueue_style( 'form_builder-css' );
		wp_enqueue_style( 'rpdd-bootstrap-min-css' );
		wp_enqueue_style( 'rpdd-font-awesome-min-css' );
	}
}
add_action( 'admin_enqueue_scripts', 'rpdd_admin_enqueue' );

/* create admin settings menu */
add_action( 'admin_menu', 'rpdd_create_sub_menu' );

function rpdd_create_sub_menu() {
	add_submenu_page(
		'theme_my_login',
		__( 'Registration fields', 'theme-my-login' ),
		__( 'Registration fields', 'theme-my-login' ),
		'manage_options',
		'rpdd-setting-page',
		'rpdd_settings_page'
	);
}

function rpdd_settings_page() {
?>
<div class="container">
<div class = "panel panel-default">
  <div class = "panel-heading">
	  <h5 class = "panel-title">
		Theme My Login - Registration Meta Fields
	  </h5>
	  <p>Just Drag and drop the fields and edit Label, Name, Placeholder text. You can retrive fields value using get_user_meta() function.</p> 
   </div>
   
   <div class = "panel-body">
   <div class="form_builder" style="margin-top: 25px">
  <div class="row">
  <!-- <div class="jumbotron">
	<h1>Theme My Login - Registration Meta Fields</h1> 
	<p>Just Drag and drop the fields and edit Label, Name, Placeholder text. You can retrive fields value using get_user_meta() function.</p> 
  </div> -->
	<div class="col-sm-2">
	  <nav class="nav-sidebar">
		<ul class="nav nav-pills nav-stacked">
		  <li class="form_bal_textfield"> <a href="javascript:;">Text Field <i class="fa fa-arrows-alt pull-right"></i></a> </li>
		  <li class="form_bal_textarea"> <a href="javascript:;">Text Area <i class="fa fa-arrows-alt pull-right"></i></a> </li>
		  <li class="form_bal_select"> <a href="javascript:;">Select <i class="fa fa-arrows-alt pull-right"></i></a> </li>
		  <li class="form_bal_radio"> <a href="javascript:;">Radio Button <i class="fa fa-arrows-alt pull-right"></i></a> </li>
		  <li class="form_bal_checkbox"> <a href="javascript:;">Checkbox <i class="fa fa-arrows-alt pull-right"></i></a> </li>
		  <li class="form_bal_number"> <a href="javascript:;">Number <i class="fa fa-arrows-alt pull-right"></i></a> </li>
		  <li class="form_bal_date"> <a href="javascript:;">Date <i class="fa fa-arrows-alt pull-right"></i></a> </li>
		</ul>
	  </nav>
	  <button style="cursor: pointer;" class="btn btn-info save_reg_pro_form mt-2 pull-right">Save</button>
	  </div>
	<div class="col-md-5 bal_builder">
	  <div class="form_builder_area"></div>
	</div>
  </div>
</div>
   </div>
</div>
</div>
<?php
}

function rpdd_register_shortcode_function() {
	$form_json   = get_option( 'reg-pro-registration-fields' );
	$form_fields = json_decode( $form_json );
	$html        = '';
	foreach ( $form_fields as $field ) {
		$data_type = $field->type;
		$name      = $field->name;
		if ( $data_type === 'text' ) {
			$label       = $field->label;
			$placeholder = $field->placeholder;
			$required    = $field->required;
			$html       .= '<p class="tml-user-login-wrap"><label class="control-label">' . $label . '</label><input type="text" name="' . $name . '" placeholder="' . $placeholder . '" class="form-control" ' . $required . '/></p>';
		}
		if ( $data_type === 'number' ) {
			$label       = $field->label;
			$placeholder = $field->placeholder;
			$required    = $field->required;
			$html       .= '<p class="tml-user-login-wrap"><label class="control-label">' . $label . '</label><input type="number" name="' . $name . '" placeholder="' . $placeholder . '" class="form-control" ' . $required . '/></p>';
		}
		if ( $data_type === 'date' ) {
			$label    = $field->label;
			$required = $field->required;
			$html    .= '<p class="tml-user-login-wrap"><label class="control-label">' . $label . '</label><input type="date" name="' . $name . '" placeholder="' . $placeholder . '" class="form-control" ' . $required . '/></p>';
		}
		if ( $data_type === 'radio' ) {
			$options     = $field->options;
			$label       = $field->label;
			$option_html = '';
			foreach ( $options as $option ) {
				$option_html .= '<label class="form-check-label"><input type="radio" class="form-check-input" name="' . $name . '" value="' . $option->value . '">' . $option->label . '</label>';
			}
			$html .= '<p class="tml-user-login-wrap"><label class="control-label">' . $label . '</label>' . $option_html . '</p>';
		}
		if ( $data_type === 'select' ) {
			$options     = $field->options;
			$label       = $field->label;
			$option_html = '';
			foreach ( $options as $option ) {
				$option_html .= '<option value="' . $option->value . '">' . $option->label . '</option>';
			}
			$html .= '<p class="tml-user-login-wrap"><label class="control-label">' . $label . '</label><select class="form-control" name="' . $name . '">' . $option_html . '</select></p>';
		}
		if ( $data_type === 'checkbox' ) {
			$options     = $field->options;
			$label       = $field->label;
			$option_html = '';
			foreach ( $options as $option ) {
				$option_html .= '<label class="form-check-label"><input type="checkbox" class="form-check-input" name="' . $name . '[]" value="' . $option->value . '">' . $option->label . '</label>';
			}
			$html .= '<p class="tml-user-login-wrap"><label class="control-label">' . $label . '</label>' . $option_html . '</p>';
		}
		if ( $data_type === 'textarea' ) {
			$label       = $field->label;
			$placeholder = $field->placeholder;
			$required    = $field->required;
			$html       .= '<p class="tml-user-login-wrap"><label class="control-label">' . $label . '</label><textarea rows="5" name="' . $name . '" placeholder="' . $placeholder . '" class="form-control" ' . $required . '></textarea></p>';
		}
	}
	echo $html;
}
add_action( 'register_form', 'rpdd_register_shortcode_function' );

function rpdd_user_register( $user_id ) {
	$form_fields = rpdd_get_form_fields();
	foreach ( $form_fields as $field ) {
		$data_type = $field->type;
		$name      = $field->name;

		if ( isset( $_POST[ $name ] ) && ! empty( $_POST[ $name ] ) ) {
			if ( $field->type == 'checkbox' ) {
				update_user_meta( $user_id, $name, $_POST[ $name ] );
			} else {
				update_user_meta( $user_id, $name, sanitize_text_field( $_POST[ $name ] ) );
			}
		}
	}
}
add_action( 'user_register', 'rpdd_user_register' );

function rpdd_get_form_fields() {
	$form_fields = array();
	$form_json   = get_option( 'reg-pro-registration-fields' );
	if ( ! empty( $form_json ) ) {
		$form_fields = json_decode( $form_json );
	}
	return $form_fields;
}

function save_regpro_settings() {
	//var_dump( $_POST['form_fields'] ); die;
	if ( isset( $_POST['form_fields'] ) && ! empty( $_POST['form_fields'] ) ) {
		update_option( 'reg-pro-registration-fields', wp_json_encode( $_POST['form_fields'] ) );
		echo true;
	} else {
		echo false;
	}
	wp_die();
}
add_action( 'wp_ajax_save_regpro_settings', 'save_regpro_settings' );
?>
