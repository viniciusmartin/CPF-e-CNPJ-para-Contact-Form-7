<?php
/*
Plugin Name: CPF e CNPJ para Contact Form 7
Description: Plugin criado para inserir, gerenciar e validar CPF e CNPJ nos formulários criados com Contact Form 7.
Version: 1.0
Author: Vinícius Martin
Author URI: http://www.viniciusmartin.com/
License: GPLv2
*/

defined('ABSPATH') or die();

require_once dirname(__FILE__) . '/classes/validateCPF.php';
require_once dirname(__FILE__) . '/classes/validateCNPJ.php';

function cf7vm_init(){
	add_action('wpcf7_init', 'cf7vm_add_shortcode_cpf');
	add_action('wpcf7_init', 'cf7vm_add_shortcode_cnpj');
	add_action('wp_enqueue_scripts', 'cf7vm_enqueue_scripts');
	add_filter('wpcf7_validate_cpf*', 'cf7vm_cpf_validation_filter', 10, 2);
	add_filter('wpcf7_validate_cnpj*', 'cf7vm_cnpj_validation_filter', 10, 2);
}
add_action('plugins_loaded', 'cf7vm_init' , 20);


function cf7vm_enqueue_scripts(){
	wp_enqueue_script('cf7vm-mask', plugin_dir_url(__FILE__) . 'assets/js/mask.min.js', array('jquery'), '1.0', true);
	wp_enqueue_script('cf7vm-main', plugin_dir_url(__FILE__) . 'assets/js/main.js', array('jquery'), '1.0', true);
}

/***********************************
*************** CPF ****************
***********************************/

function cf7vm_add_shortcode_cpf(){
	if( !function_exists('wpcf7_add_form_tag') ) return;    

	wpcf7_add_form_tag(
		array('cpf' , 'cpf*'),
		'cf7vm_cpf_shortcode_handler',
		true
	);
}


function cf7vm_cpf_shortcode_handler( $tag ) {
	if( !class_exists('WPCF7_FormTag') ) return;
	
	$tag = new WPCF7_FormTag($tag);

	if( empty($tag->name) )
		return '';

	$validation_error = wpcf7_get_validation_error($tag->name);

	$class = wpcf7_form_controls_class($tag->type);

	if( $validation_error ){
		$class .= ' wpcf7-not-valid';
	}

	$atts = array();

	$atts['size'] = $tag->get_size_option('40');
	$atts['maxlength'] = $tag->get_maxlength_option();
	$atts['minlength'] = $tag->get_minlength_option();

	if( $atts['maxlength'] && $atts['minlength'] && $atts['maxlength'] < $atts['minlength'] ){
		unset( $atts['maxlength'], $atts['minlength'] );
	}

	$atts['class'] = $tag->get_class_option($class);
	$atts['id'] = $tag->get_id_option();
	$atts['tabindex'] = $tag->get_option('tabindex', 'int', true);

	if( $tag->has_option('readonly') ){
		$atts['readonly'] = 'readonly';
	}

	if( $tag->is_required() ){
		$atts['aria-required'] = 'true';
	}

	$atts['aria-invalid'] = $validation_error ? 'true' : 'false';

	$value = (string) reset($tag->values);

	$value = $tag->get_default_option($value);

	$value = wpcf7_get_hangover($tag->name, $value);

	$atts['placeholder'] = $value;

	if( wpcf7_support_html5() ){
		$atts['type'] = 'tel';
	} else {
		$atts['type'] = 'text';
	}

	$atts['name'] = $tag->name;
	
	$atts = wpcf7_format_atts($atts);

	$html = sprintf(
		'<span class="wpcf7-form-control-wrap %1$s"><input %2$s />%3$s</span>',
		sanitize_html_class( $tag->name ), $atts, $validation_error );

	return $html;
}


function cf7vm_cpf_validation_filter($result, $tag){
	if( !class_exists('WPCF7_FormTag') ) return;
	
	$tag = new WPCF7_FormTag($tag);

	$name = $tag->name;

	$value = isset($_POST[$name])
		? trim( wp_unslash( strtr( (string) $_POST[$name], "\n", " " ) ) )
		: '';

	if( 'cpf' == $tag->basetype ){
		if( $tag->is_required() && '' == cf7vm_validate_cpf($value) ){
			$result->invalidate($tag, wpcf7_get_message('invalid_cpf'));
		}
	}

	if( !empty($value) ){
		$maxlength = $tag->get_maxlength_option();
		$minlength = $tag->get_minlength_option();

		if( $maxlength && $minlength && $maxlength < $minlength ){
			$maxlength = $minlength = null;
		}

		$code_units = wpcf7_count_code_units($value);

		if( false !== $code_units ){
			if( $maxlength && $maxlength < $code_units ){
				$result->invalidate($tag, wpcf7_get_message('invalid_too_long'));
			} elseif ( $minlength && $code_units < $minlength ) {
				$result->invalidate($tag, wpcf7_get_message( 'invalid_too_short'));
			}
		}
	}

	return $result;
}


if( is_admin() ){
	add_action('wpcf7_admin_init' , 'cf7vm_add_cpf_generator_field', 100);
}


function cf7vm_add_cpf_generator_field(){

	if( !class_exists('WPCF7_TagGenerator') ) return;

	$tag_generator = WPCF7_TagGenerator::get_instance();
	$tag_generator->add( 
		'cpf', 
		__( 'cpf', 'cf7vm-cpf-field'),
		'cf7vm_cpf_generator_field'
	);
}


function cf7vm_cpf_generator_field($contact_form , $args = ''){
	$args = wp_parse_args($args, array());
	$type = $args['id'];

?>
<div class="control-box">
	<fieldset>
		<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><?php _e( 'Field type', 'contact-form-7' ); ?></th>
				<td>
					<fieldset>
					<legend class="screen-reader-text"><?php _e( 'Field type', 'contact-form-7' ); ?></legend>
					<label><input type="checkbox" name="required" /> <?php _e( 'Required field', 'contact-form-7' ); ?></label>
					</fieldset>
				</td>
			</tr>
		
			<tr>
				<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-name' ); ?>"><?php _e( 'Name', 'contact-form-7' ); ?></label></th>
				<td><input type="text" name="name" class="tg-name oneline" id="<?php echo esc_attr( $args['content'] . '-name' ); ?>" /></td>
			</tr>

			<tr>
				<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-values' ); ?>"><?php echo esc_html( __( 'Default value', 'contact-form-7' ) ); ?></label></th>
				<td><input type="text" name="values" class="oneline" id="<?php echo esc_attr( $args['content'] . '-values' ); ?>" /></td>
			</tr>
		
			<tr>
				<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-id' ); ?>"><?php _e( 'Id attribute', 'contact-form-7' ); ?></label></th>
				<td><input type="text" name="id" class="idvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-id' ); ?>" /></td>
			</tr>
		
			<tr>
				<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-class' ); ?>"><?php _e( 'Class attribute', 'contact-form-7' ); ?></label></th>
				<td><input type="text" name="class" class="classvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-class' ); ?>" /></td>
			</tr>
			
		</tbody>
		</table>
	
	</fieldset>
</div>

<div class="insert-box">
	<input type="text" name="<?php echo $type; ?>" class="tag code" readonly="readonly" onfocus="this.select()" />

	<div class="submitbox">
	   <input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr( __( 'Insert Tag', 'contact-form-7' ) ); ?>" />
	</div>

	<br class="clear" />
</div>
<?php
}


/***********************************
*************** CNPJ ***************
***********************************/

function cf7vm_add_shortcode_cnpj(){
	if( !function_exists('wpcf7_add_form_tag') ) return;    

	wpcf7_add_form_tag(
		array('cnpj' , 'cnpj*'),
		'cf7vm_cnpj_shortcode_handler',
		true
	);
}


function cf7vm_cnpj_shortcode_handler( $tag ) {
	if( !class_exists('WPCF7_FormTag') ) return;
	
	$tag = new WPCF7_FormTag($tag);

	if( empty($tag->name) )
		return '';

	$validation_error = wpcf7_get_validation_error($tag->name);

	$class = wpcf7_form_controls_class($tag->type);

	if( $validation_error ){
		$class .= ' wpcf7-not-valid';
	}

	$atts = array();

	$atts['size'] = $tag->get_size_option('50');
	$atts['maxlength'] = $tag->get_maxlength_option();
	$atts['minlength'] = $tag->get_minlength_option();

	if( $atts['maxlength'] && $atts['minlength'] && $atts['maxlength'] < $atts['minlength'] ){
		unset( $atts['maxlength'], $atts['minlength'] );
	}

	$atts['class'] = $tag->get_class_option($class);
	$atts['id'] = $tag->get_id_option();
	$atts['tabindex'] = $tag->get_option('tabindex', 'int', true);

	if( $tag->has_option('readonly') ){
		$atts['readonly'] = 'readonly';
	}

	if( $tag->is_required() ){
		$atts['aria-required'] = 'true';
	}

	$atts['aria-invalid'] = $validation_error ? 'true' : 'false';
	
	$value = (string) reset($tag->values);

	$value = $tag->get_default_option($value);

	$value = wpcf7_get_hangover($tag->name, $value);

	$atts['placeholder'] = $value;

	if( wpcf7_support_html5() ){
		$atts['type'] = 'tel';
	} else {
		$atts['type'] = 'text';
	}

	$atts['name'] = $tag->name;
	
	$atts = wpcf7_format_atts($atts);

	$html = sprintf(
		'<span class="wpcf7-form-control-wrap %1$s"><input %2$s />%3$s</span>',
		sanitize_html_class( $tag->name ), $atts, $validation_error );

	return $html;
}


function cf7vm_cnpj_validation_filter($result, $tag){
	if( !class_exists('WPCF7_FormTag') ) return;
	
	$tag = new WPCF7_FormTag($tag);

	$name = $tag->name;

	$value = isset($_POST[$name])
		? trim( wp_unslash( strtr( (string) $_POST[$name], "\n", " " ) ) )
		: '';

	if( 'cnpj' == $tag->basetype ){
		if( $tag->is_required() && '' == cf7vm_validate_cnpj($value) ){
			$result->invalidate($tag, wpcf7_get_message('invalid_cnpj'));
		}
	}

	if( !empty($value) ){
		$maxlength = $tag->get_maxlength_option();
		$minlength = $tag->get_minlength_option();

		if( $maxlength && $minlength && $maxlength < $minlength ){
			$maxlength = $minlength = null;
		}

		$code_units = wpcf7_count_code_units($value);

		if( false !== $code_units ){
			if( $maxlength && $maxlength < $code_units ){
				$result->invalidate($tag, wpcf7_get_message('invalid_too_long'));
			} elseif ( $minlength && $code_units < $minlength ) {
				$result->invalidate($tag, wpcf7_get_message( 'invalid_too_short'));
			}
		}
	}

	return $result;
}


if( is_admin() ){
	add_action('wpcf7_admin_init' , 'cf7vm_add_cnpj_generator_field', 100);
}


function cf7vm_add_cnpj_generator_field(){

	if( !class_exists('WPCF7_TagGenerator') ) return;

	$tag_generator = WPCF7_TagGenerator::get_instance();
	$tag_generator->add( 
		'cnpj', 
		__( 'cnpj', 'cf7vm-cnpj-field'),
		'cf7vm_cnpj_generator_field'
	);
}


function cf7vm_cnpj_generator_field($contact_form , $args = ''){
	$args = wp_parse_args($args, array());
	$type = $args['id'];

?>
<div class="control-box">
	<fieldset>
		<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><?php _e( 'Field type', 'contact-form-7' ); ?></th>
				<td>
					<fieldset>
					<legend class="screen-reader-text"><?php _e( 'Field type', 'contact-form-7' ); ?></legend>
					<label><input type="checkbox" name="required" /> <?php _e( 'Required field', 'contact-form-7' ); ?></label>
					</fieldset>
				</td>
			</tr>
		
			<tr>
				<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-name' ); ?>"><?php _e( 'Name', 'contact-form-7' ); ?></label></th>
				<td><input type="text" name="name" class="tg-name oneline" id="<?php echo esc_attr( $args['content'] . '-name' ); ?>" /></td>
			</tr>

			<tr>
				<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-values' ); ?>"><?php echo esc_html( __( 'Default value', 'contact-form-7' ) ); ?></label></th>
				<td><input type="text" name="values" class="oneline" id="<?php echo esc_attr( $args['content'] . '-values' ); ?>" /></td>
			</tr>
		
			<tr>
				<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-id' ); ?>"><?php _e( 'Id attribute', 'contact-form-7' ); ?></label></th>
				<td><input type="text" name="id" class="idvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-id' ); ?>" /></td>
			</tr>
		
			<tr>
				<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-class' ); ?>"><?php _e( 'Class attribute', 'contact-form-7' ); ?></label></th>
				<td><input type="text" name="class" class="classvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-class' ); ?>" /></td>
			</tr>
			
		</tbody>
		</table>
	
	</fieldset>
</div>

<div class="insert-box">
	<input type="text" name="<?php echo $type; ?>" class="tag code" readonly="readonly" onfocus="this.select()" />

	<div class="submitbox">
	   <input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr( __( 'Insert Tag', 'contact-form-7' ) ); ?>" />
	</div>

	<br class="clear" />
</div>
<?php
}


add_filter('wpcf7_messages', 'cf7vm_text_messages', 1000, 2);
function cf7vm_text_messages($messages){
	return array_merge($messages, array(
		'invalid_cpf' => array(
			'description' => __('Quanto o CPF for inválido', 'contact-form-7'),
			'default' => __('O CPF informado é inválido.', 'contact-form-7')
		),
		'invalid_cnpj' => array(
			'description' => __('Quanto o CNPJ for inválido', 'contact-form-7'),
			'default' => __('O CNPJ informado é inválido.', 'contact-form-7')
		)
	));
}