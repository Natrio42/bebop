<?php
/**
 * Add javascript and stylesheet file for Youtube
 */

wp_enqueue_style('buddystreamyoutube', plugins_url() . '/buddystream/extentions/youtube/style.css');



/**
 * Replace all embed urls into new embed urls (old content)
 */

add_filter( 'bp_get_activity_content','BuddystreamYoutubeEmbed', 8);
add_filter( 'bp_get_activity_content_body','BuddystreamYoutubeEmbed', 8);
function BebopYoutubeEmbed($text) {
    
    $return = "";
    $return = $text;
    $return = str_replace('watch/?v=', 'embed/', $return);
    
    return $return; 
}


/**
 * 
 * Page loader functions 
 *
 */

function bebop_youtube()
{
  bebop_extensions::page_loader('youtube');
}