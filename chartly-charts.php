<?php

/*
Plugin Name: Chart.ly Charts
Version: 1.0.3
Plugin URI: http://www.stocktwits.com
Description: This simple widget displays your recently added Chart.ly chart's.
Author: StockTwits, christopherross
Author URI: http://www.stocktwits.com
*/

define('MAGPIE_CACHE_ON', 0); //2.7 Cache Bug

$chartly_options['widget_fields']['title'] = array('label'=>'Widget Title:', 'type'=>'text', 'default'=>'Chart.ly Charts');
$chartly_options['widget_fields']['username'] = array('label'=>'Username:', 'type'=>'text', 'default'=>'');
$chartly_options['widget_fields']['num'] = array('label'=>'Number of charts:', 'type'=>'text', 'default'=>'4');
$chartly_options['widget_fields']['linked'] = array('label'=>'Link to charts:', 'type'=>'checkbox', 'default'=>false);

$chartly_options['prefix'] = 'chartly';


// Display Chart.ly chart's
function chartly_charts($username = '', $num = 4, $linked = true, $title = "") {
  	
	
	global $chartly_options;
	
	
	if (strlen($title) > 0) {} else {$title = $item['title'];} 
	
	//$file = @file_get_contents("http://chart.ly/user/".$username.".rss");
  	$file = file_get_contents('http://www.chart.ly/api/streams/user/'.$username.'.json');
  echo '<div id="Chartly_wrapper"><div id="moduleContent" class="moduleContent">';
  echo '<div id="moduleHeader" class="moduleHeader">';
  echo '<div id="moduleIcon" class="ico">';
  echo '<img height="16" width="16" src="/' . PLUGINDIR . '/chartly-charts/chartly-favicon.gif" />';
  echo '</div>';
  echo '<div class="title" id="moduleTitle">' . $title . '</div></div>';
  echo '<div id="moduleContent" class="moduleContent">';

   // Format:
   // <description>&lt;a href="http://chart.ly/f78zq8" &gt; &lt;img src="http://chart.ly/assets/f78zq8_s.PNG" border="0" title="View this Chart" &gt; &lt;/a&gt;</description>

   $options = get_option('widget_chartly');

   $item = $options[$number];
   foreach($chartly_options['widget_fields'] as $key => $field) {
      if (! isset($item[$key])) {
         $item[$key] = $field['default'];
      }
   }


  	$obj = json_decode($file,20);
	
	foreach ($obj as $line) {
		foreach ($line as $lt) {
		if (is_array($lt)) {
		foreach ($lt as $lth) {
		
			if ($chartlycount < $num) {
				if (strlen($lth['thumbnail'])>5) {
				
				$thumbnail = $lth['thumbnail'];
				$thumbnail = str_replace('http://chart.ly','http://www.chart.ly',$thumbnail);
				$full = str_replace('thumbnail_','',$thumbnail);
				
				if($linked == "true") {
					$thumb[] = '<a href="'.$full.'" target="_new"><img src="'.$thumbnail.'" width="100" height="50" class="chartly-pic" /></a>';
				 } else {
					$thumb[] = '<img src="'.$thumbnail.'" width="100" height="50" class="chartly-pic" />';
				}
				$chartlycount++;
			} 
			 
			}
		}
		}
		}
	}
	
	


   if (count($thumb)>0) {
   	foreach ($thumb as $chart) {
		echo $chart;
	}
   } else {
      echo '<div>No charts found</div>';
	}

   echo '</div>';
   echo '<div align="center" id="moduleFooter" class="moduleFooter">Powered by <a href="http://chart.ly">Chart.ly</a></div></div></div>';
}


// Chart.ly widget stuff
function widget_chartly_init() {
   if (!function_exists('register_sidebar_widget'))
      return;

      $check_options = get_option('widget_chartly');
      if ($check_options['number']=='') {
            $check_options['number'] = 1;
            update_option('widget_chartly', $check_options);
      }

   function widget_chartly($args, $number = 1) {

     global $chartly_options;

      extract($args);

      include_once(ABSPATH . WPINC . '/rss.php');
      $options = get_option('widget_chartly');

      $item = $options[$number];
      foreach($chartly_options['widget_fields'] as $key => $field) {
         if (! isset($item[$key])) {
            $item[$key] = $field['default'];
         }
      }

      // These lines generate the output for traditional widget
      //echo $before_widget . $before_title . $item['title'] . $after_title;
      //echo '<ul id="chartly">';
      chartly_charts($item['username'], $item['num'], $item['linked']);
      //echo '</ul>';
      //echo '<div align="center" id="moduleFooter" class="moduleFooter">Powered by <a href="http://chart.ly">Chart.ly</a></div>';
      //echo $after_widget;
   }

   function widget_chartly_control($number) {

      global $chartly_options;

      $options = get_option('widget_chartly');

      if ( isset($_POST['chartly-submit']) ) {

         foreach($chartly_options['widget_fields'] as $key => $field) {
            $options[$number][$key] = $field['default'];
            $field_name = sprintf('%s_%s_%s', $chartly_options['prefix'], $key, $number);

            if ($field['type'] == 'text') {
               $options[$number][$key] = strip_tags(stripslashes($_POST[$field_name]));
            } elseif ($field['type'] == 'checkbox') {
               $options[$number][$key] = isset($_POST[$field_name]);
            }
         }

         update_option('widget_chartly', $options);
      }

      foreach($chartly_options['widget_fields'] as $key => $field) {

         $field_name = sprintf('%s_%s_%s', $chartly_options['prefix'], $key, $number);
         $field_checked = '';
         if ($field['type'] == 'text') {
            $field_value = htmlspecialchars($options[$number][$key], ENT_QUOTES);
         } elseif ($field['type'] == 'checkbox') {
            $field_value = 1;
            if (! empty($options[$number][$key])) {
               $field_checked = 'checked="checked"';
            }
         }

         printf('<p style="text-align:right;" class="chartly_field"><label for="%s">%s <input id="%s" name="%s" type="%s" value="%s" class="%s" %s /></label></p>',
            $field_name, __($field['label']), $field_name, $field_name, $field['type'], $field_value, $field['type'], $field_checked);
      }
      echo '<input type="hidden" id="chartly-submit" name="chartly-submit" value="1" />';
   }


   function widget_chartly_setup() {
      $options = $newoptions = get_option('widget_chartly');

      if ( isset($_POST['chartly-number-submit']) ) {
         $number = (int) $_POST['chartly-number'];
         $newoptions['number'] = $number;
      }

      if ( $options != $newoptions ) {
         update_option('widget_chartly', $newoptions);
         widget_chartly_register();
      }
   }

   function widget_chartly_register() {

      $options = get_option('widget_chartly');
      $dims = array('width' => 300, 'height' => 300);
      $class = array('classname' => 'widget_chartly');

      for ($i = 1; $i <= 9; $i++) {
         $name = sprintf(__('My Stock Charts\'s'), $i);
         $id = "chartly-$i"; // Never never never translate an id
         wp_register_sidebar_widget($id, $name, $i <= $options['number'] ? 'widget_chartly' : /* unregister */ '', $class, $i);
         wp_register_widget_control($id, $name, $i <= $options['number'] ? 'widget_chartly_control' : /* unregister */ '', $dims, $i);
      }

      add_action('sidebar_admin_setup', 'widget_chartly_setup');
   }

   widget_chartly_register();
}

// Run our code later in case this loads prior to any required plugins.
add_action('widgets_init', 'widget_chartly_init');
add_action ('wp_head',  'header_action');    // Load our custom stylesheet after anything else.

//===========================================================================
// log_event2 (__FILE__, __LINE__, "Message", "extra data");

function log_event2 ($filename, $linenum, $message, $extra_text="")
{
   $log_filename   = dirname(__FILE__) . '/__log.php';
   $logfile_header = '<?php header("Location: /"); exit();' . "\r\n" . '/* =============== 25Pix.com LOG file =============== */' . "\r\n";
   $logfile_tail   = "\r\n?>";

   // Delete too long logfiles.
   if (@file_exists ($log_filename) && @filesize($log_filename)>1000000)
      unlink ($log_filename);

   $filename = basename ($filename);

   if (file_exists ($log_filename))
      {
      // 'r+' non destructive R/W mode.
      $fhandle = fopen ($log_filename, 'r+');
      if ($fhandle)
         fseek ($fhandle, -strlen($logfile_tail), SEEK_END);
      }
   else
      {
      $fhandle = fopen ($log_filename, 'w');
      if ($fhandle)
         fwrite ($fhandle, $logfile_header);
      }

   if ($fhandle)
      {
      fwrite ($fhandle, "\r\n// " . $_SERVER['REMOTE_ADDR'] . ' -> ' . date("Y-m-d, G:i:s.u") . "|$filename($linenum)|: " . $message . ($extra_text?"\r\n//    Extra Data: $extra_text":"") . $logfile_tail);
      fclose ($fhandle);
      }
}
//===========================================================================
//===========================================================================
function header_action ()
{
   echo '<link type="text/css" rel="stylesheet" href="/' . PLUGINDIR . '/chartly-charts/chartly.css" />' . "\n";
}
//===========================================================================


?>