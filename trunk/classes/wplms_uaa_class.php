<?php

if(!class_exists('WPLMS_Unit_Addon_Class'))
{   
    class WPLMS_Unit_Addon_Class 
    {
            
        public function __construct(){   
          add_filter('wplms_course_metabox',array($this,'add_number_unit_access_backend'));
          add_filter('wplms_course_creation_tabs',array($this,'add_number_unit_access_frontend'));
          add_filter('wplms_unit_metabox',array($this,'add_number_of_access'));
          add_action('the_content',array($this,'check_update_user_access_meta'));
          add_action('plugins_loaded',array($this,'wplms_uaa_translations'));
        } // END public function __construct
        public function activate(){
        }
        public function deactivate(){
        }
        
		function add_number_unit_access_backend($settings){
		  $settings['vibe_unit_access_number']=array( // Text Input
		      'label' => __('Number of times user can access course units','wplms-uaa'), // <label>
		      'desc'  => '', // description
		      'id'  => 'vibe_unit_access_number', // field id and name
		      'type'  => 'number', // type of field
		      'std' => 0
		    );
		  return $settings;
		}

		function add_number_unit_access_frontend($settings){
		  $fields = $settings['course_settings']['fields'];
		  $arr=array(array( // Text Input
		      'label' => __('Number of times user can access course units','wplms-uaa'), // <label>
		      'desc'=> __('Number of times user can access course units','wplms-uaa' ),
		      'text'=> __('Number of times user can access course units','wplms-uaa' ),
		      'id'  => 'vibe_unit_access_number', // field id and name
		      'type'  => 'number', // type of field
		      'default' => 0
		      ));
		           array_splice($fields, (count($fields)-15), 0,$arr );
		           $settings['course_settings']['fields'] = $fields;
		           return $settings;
		}



		function add_number_of_access($settings){
		  $settings['number_access']=array( // Text Input
		      'label' => __('Number of times user can access this unit','wplms-uaa'), // <label>
		      'desc'  => '', // description
		      'id'  => 'number_access', // field id and name
		      'type'  => 'number', // type of field
		      'std' => 0,
		    );
		  return $settings;
		}
		function check_update_user_access_meta($content){
		    global $post;
		    $id=$post->ID;
		    if((!empty($id) && get_post_type($id)!='unit') || !is_user_logged_in())
		      return $content;

		  	if((is_user_logged_in() && current_user_can('manage_options')) || (is_user_logged_in() &&  get_current_user_id()==$post->post_author))
		  		return $content;
		    $course_id=bp_course_get_unit_course_id($id);

		    $user_id=get_current_user_id();
		    $course_count= get_post_meta($course_id,'vibe_unit_access_number',true);
		    $unit_count=get_post_meta($id,'number_access',true);
		    $count=0;
		    $user_course_unit_count=0;
		    if(!empty($unit_count)){
		      $count=get_user_meta($user_id,'number_access'.$id,true);
		    }elseif(!empty($course_count)){
		       $user_course_unit_count=get_user_meta($user_id,'vibe_unit_access_number'.$id,true);
		    }
		    $count++;
		    $user_course_unit_count++;
		   
		    if(!empty($unit_count) && $count <= $unit_count){
		      update_user_meta($user_id,'number_access'.$id, $count);
		    }elseif(!empty($course_count) && $user_course_unit_count <= $course_count ){
		       update_user_meta($user_id,'vibe_unit_access_number'.$id, $user_course_unit_count);
		    }
		    if(!empty($unit_count)  && isset($count) && $count > $unit_count){
		      $content= '<div class="message" style="margin-bottom:50px">Allowed unit access limit('.$unit_count.') is over .</div>';
		    }elseif(!empty($unit_count) && isset($count) && $count <= $unit_count ){
		      echo '<div class="message" style="margin-bottom:50px">You can access this unit '.($unit_count-$count).' more time(s)</div>';
		    }elseif(!empty($course_count)  && isset($user_course_unit_count) && $user_course_unit_count > $course_count){
		      $content= '<div class="message" style="margin-bottom:50px">Allowed unit access limit('.$course_count.') is over .</div>';
		    }elseif(!empty($course_count) && isset($user_course_unit_count) && $user_course_unit_count <= $course_count ){
		      echo '<div class="message" style="margin-bottom:50px">You can access this unit '.($course_count-$user_course_unit_count).' more time(s)</div>';
		    }
		    return $content;
		}
		function wplms_uaa_translations(){
	          $locale = apply_filters("plugin_locale", get_locale(), 'wplms-uaa');
	          $lang_dir = dirname( __FILE__ ) . '/languages/';
	          $mofile        = sprintf( '%1$s-%2$s.mo', 'wplms-uaa', $locale );
	          $mofile_local  = $lang_dir . $mofile;
	          $mofile_global = WP_LANG_DIR . '/plugins/' . $mofile;

	          if ( file_exists( $mofile_global ) ) {
	              load_textdomain( 'wplms-uaa', $mofile_global );
	          } else {
	              load_textdomain( 'wplms-uaa', $mofile_local );
	          }  
	    }
       
    } // END class WPLMS_Unit_Addon_Class
} // END if(!class_exists('WPLMS_Unit_Addon_Class'))
?>