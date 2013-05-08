<?php
/*
Plugin Name: Gravity Forms - GA Parse Add-On
Plugin URI: http://example.com/
Description: Grab the Google Analytics data for form submissions including campaign source, campaign name, campaign medium among other data 
Version: 0.1
Author: De"Yonte W.
Author URI: http://example.com/
*/

/**
 * Copyright (c) 2013 De"Yonte W. All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * **********************************************************************
 */
require("admin/class.gaparse.php");

/**
 * Extends Gravity Forms to get the Google Analytics data for form submissions
 * @package WordPress
 * @subpackage Gravity Forms
 * @category Plugin   
 * @author De'Yonte W.
 */
class GravityGAParse extends GA_Parse{
		
	public $ga_fields;

	/**
	 * create the constructor for the class
	 * 
	 * create the constructor for the child class and call the parent constructor
	 */
	public function __construct(){
		parent::__construct($_COOKIE);

		$this->ga_fields = array(
			array(
				'id' => 'ga_campaign_source',
				'name' => 'Campaign Source'
			),
			array(
				'id' => 'ga_campaign_name',
				'name' => 'Campaign Name'
			),
			array(
				'id' => 'ga_campaign_medium',
				'name' => 'Campaign Medium'
			),
			array(
				'id' => 'ga_campaign_content',
				'name' => 'Campaign Content'
			),
			array(
				'id' => 'ga_campaign_term',
				'name' => 'Campaign Term'
			),
			array(
				'id' => 'ga_first_visit',
				'name' => 'First Visit'
			),
			 array(
				'id' => 'ga_previous_visit',
				'name' => 'Previous Visit'
			),
			array(
				'id' => 'ga_visit_started',
				'name' => 'Current Visit Started'
			),
			array(
				'id' => 'ga_times_visited',
				'name' => 'Times Visited'
			),
			array(
				'id' => 'ga_pages_viewed',
				'name' => 'Pages Viewed'
			)
		);
		
		add_action( "gform_field_input" , array($this,"render_field"), 10, 5 );

		if( is_admin() ):
			$this->admin_hooks();
		else:
			$this->front_hooks();
		endif;
	}

	/*================START ADMIN SECTION================*/

	/**
	 * load the WordPress hooks for the admin/backend side of WordPress
	 */
	public function admin_hooks(){
		add_filter("gform_add_field_buttons", array($this,"define_fields"));
		add_filter("gform_field_type_title" , array($this,"add_field_titles"));
		add_action("gform_editor_js", array($this,"field_settings" ));
	}
	
	/**
	 * add a new field group to Gravity Forms and the custom field types
	 * 
	 * add the Google Analytics field group to Gravity Forms and assign the new field types to display in the group 
	 * @param array $field_groups 
	 * @return array the field groups to display and the custom field types
	 */
	public function define_fields( $field_groups ) {

		$field_groups[] = array(
			"name" => "google_analytics",
			"label" => "Google Analytics",
			"tooltip_class" => "tooltip_bottomleft",
			"fields" => array()
		);

		//render all of the google analytic fields
		$all_fields = array();
		foreach( $this->ga_fields as $ga_field ):
			
			$all_fields[] = array(
				"class" => "button",
				"value" => $ga_field["name"],
				"onclick" => "StartAddField('".$ga_field["id"]."')",
			);
		
		endforeach;

		$field_groups[count($field_groups)-1]["fields"] = $all_fields;

		return $field_groups;
	}

	/**
	 * add field titles for the custom field types
	 * 
	 * add the field titles for each of the custom field types. This appears when you hover over the field on the backend of the Gravity Forms editor page
	 * @param string $type 
	 * @return string the title of the field
	 */
	public function add_field_titles( $type ) {

		$title = null;
		switch($type){
			case "ga_campaign_source": $title = "Campaign Source";
			break;
			case "ga_campaign_name": $title = "Campaign Name";
			break;
			case "ga_campaign_medium": $title = "Campaign Medium";
			break;
			case "ga_campaign_content": $title = "Campaign Content";
			break;
			case "ga_campaign_term": $title = "Campaign Term";
			break;
			case "ga_first_visit": $title = "First Visit";
			break;
			case "ga_previous_visit": $title = "Previous Visit";
			break;
			case "ga_visit_started": $title = "Current Visit Started";
			break;
			case "ga_times_visited": $title = "Times Visited";
			break;
			case "ga_pages_viewed": $title = "Pages Viewed";
			break;
		}

		if( !is_null($title) ){
			return __( $title , "gravityforms" );
		}
	}

	/**
	 * load the field on the backend and the frontend of the site
	 * @param string $input 
	 * @param object $field 
	 * @param string $value 
	 * @param int $lead_id 
	 * @param int $form_id 
	 * @return string how the field should be displayed
	 */
	public function render_field( $input, $field, $value, $lead_id, $form_id ){

		$type = $field["type"];
		$tabindex = GFCommon::get_tabindex();
		$input_name = $form_id ."_" . $field["id"];
		$ga_field = null;
		

		if( $this->in_array_r($type, $this->ga_fields) )
			return sprintf( "<div class='ginput_container'><input type='hidden' name='input_%s' id='%s' value='%s'></div>", $field["id"], $type."-".$field["id"], $type );

		return $input;
	}

	/**
	 * assign field settings for the custom field type
	 * 
	 * Gravity Forms Field settings for the custom field type including label, desrciption, etc...
	 */
	public function field_settings(){
	?>
		<script type='text/javascript'>

			jQuery(document).ready(function($) {

				<?php foreach($this->ga_fields as $field_setting ):?>
						fieldSettings['<?php echo $field_setting["id"];?>'] = ".label_setting";
				<?php endforeach;?>

			});

		</script>
	<?php
	}

	/**
	 * assign a CSS class to the parent <li> element
	 * @param string $classes 
	 * @param array $field 
	 * @param object $form 
	 * @return string class or classes for the element
	 */
	public function css_class($classes, $field, $form){
		$type = $field['type'];

		if( $this->in_array_r($type, $this->ga_fields) )
			$classes .= ' gform_hidden';

		return $classes;
	}

	/**
	 * recursive in_array function
	 * 
	 * recursively search multidimensional arrays
	 * @link http://stackoverflow.com/questions/4128323/in-array-and-multidimensional-array
	 * @return boolean if element is found or not found
	 */
	public function in_array_r($needle, $haystack, $strict = false){
    foreach ($haystack as $item) {
      if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->in_array_r($needle, $item, $strict))) {
          return true;
      }
    }

		return false;
	}


	/*================END ADMIN SECTION================*/

	/**
	 * load the WordPress hooks for the front side of WordPress
	 */
	public function front_hooks(){
		add_action("gform_field_css_class", array($this,"css_class"), 10, 3);
		add_action("gform_pre_submission", array($this,"get_values"));
	}

	/**
	 * assign the Google Analytic values to the custom fields
	 * 
	 * grab the Google Analytics data for the form submission. assign the values to the appropriate fields
	 * @param object $form 
	 */
	public function get_values($form){

		foreach( $_POST as $key=>$field ):

			if( $field == 'ga_campaign_source' )
				$_POST[$key] = $this->campaign_source;
			elseif( $field == 'ga_campaign_name' )
				$_POST[$key] = $this->campaign_name;
			elseif( $field == 'ga_campaign_medium' )
				$_POST[$key] = $this->campaign_medium;
			elseif( $field == 'ga_campaign_content' )
				$_POST[$key] = $this->campaign_content;
			elseif( $field == 'ga_campaign_term' )
				$_POST[$key] = $this->campaign_term;
			elseif( $field == 'ga_first_visit' )
				$_POST[$key] = $this->first_visit;
			elseif( $field == 'ga_previous_visit' )
				$_POST[$key] = $this->previous_visit;
			elseif( $field == 'ga_visit_started' )
				$_POST[$key] = $this->current_visit_started;
			elseif( $field == 'ga_times_visited' )
				$_POST[$key] = $this->times_visited;
			elseif( $field == 'ga_pages_viewed' )
				$_POST[$key] = $this->pages_viewed;

		endforeach;
		

	}
}

$gravity_gaparse = new GravityGAParse();