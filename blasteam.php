<?php
/**
 * @package BLASTREAM
 * @version 1.0.0
 */
/*
Plugin Name: Blastream
Description: A plugin to integrate your blastream room within your wordpress website
Version: 1.0.0
Author URI: https://www.blastream.com/
License: GPLv2 or later
Text Domain: blastream
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2019-2022 Blastream.
*/

define('BLASTREAM_APP_URL', 'app.v2.blastream.com');
define('BLASTREAM_API_URL', 'api.v2.blastream.com');



function blastream_shortcode_room_generator($attributes){
    $options = get_option( 'blastream_options_group', '' );

    extract(shortcode_atts(
        array(
    	      'slug' => $options['slug'],
              'width' => $options['width'],
              'height' => $options['height'],
    ), $attributes));

   
    if (!$slug) {
        return ("<h4>Error: slug is empty !</h4>");
    }
    $slug_test = wp_remote_get("https://".BLASTREAM_API_URL."/channel/static/".$slug."/custom");
    $body = wp_remote_retrieve_body( $slug_test );
    $res = json_decode($body);
    if ($res->error) {
        return ("Error: ".$res->error);
    }

    $src = "https://".BLASTREAM_APP_URL."/".$slug;
    $current_user = wp_get_current_user();
    if ( $current_user->exists() ) {
       $src = $src."?username=".$current_user->user_login;
    } 
    $iframe = "<iframe  allow='allowfullscreen; microphone; camera; display-capture' 
        src='".$src."'
        style='height:".$height.";width:".$width.";border:none; margin:0; padding:0;overflow:hidden;margin:-5px;'>
    </iframe>";
    

    return $iframe;
}
add_shortcode('blastream_room', 'blastream_shortcode_room_generator');

function blastream_register_settings () {
    register_setting( 'blastream_options_group', 'blastream_options_group');
    add_settings_section( 'room_settings', 'Room Settings', 'blastream_plugin_section_text', 'blastream_plugin' );

    add_settings_field( 'blastream_setting_slug', 'Slug', 'blastream_setting_slug', 'blastream_plugin', 'room_settings' );
    add_settings_field( 'blastream_setting_width', 'Width', 'blastream_setting_width', 'blastream_plugin', 'room_settings' );
    add_settings_field( 'blastream_setting_height', 'Height', 'blastream_setting_height', 'blastream_plugin', 'room_settings' );

} 

add_action ('admin_init', 'blastream_register_settings');


function blastream_plugin_section_text() {
    echo '<p>Here you can set all the default options for using the shortcode to integrate your room</p>';
}

function blastream_setting_slug() {
    $options = get_option( 'blastream_options_group' );
    echo  "<input id='blastream_setting_slug' name='blastream_options_group[slug]' type='text' value='" . esc_attr( $options['slug'] ) . "' />";
}

function blastream_setting_width() {
    $options = get_option( 'blastream_options_group' );
    echo "<input id='blastream_setting_width' name='blastream_options_group[width]' type='text' value='" . esc_attr( $options['width'] ) . "' />";
}

function blastream_setting_height() {
    $options = get_option( 'blastream_options_group' );
    echo "<input id='blastream_setting_height' name='blastream_options_group[height]' type='text' value='" . esc_attr( $options['height'] ) . "' />";
}

function blastream_plugin_html_form() {
    ?>
    <h2>Blastream Room Settings</h2>
    <form action="options.php" method="POST">
        <?php 
        settings_fields( 'blastream_options_group' );
        do_settings_sections( 'blastream_plugin' ); 
        ?>
        <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
    </form>
    <?php
}


function blastream_setting_page () {
    add_options_page ('Blastream Room Default', 'Blastream Room Default Setting', 'manage_options', 'blastream-room-default-url','blastream_plugin_html_form'); 
} 
add_action('admin_menu', 'blastream_setting_page');



