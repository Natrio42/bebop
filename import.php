<?php
/**
 * Import for BuddyStream
 */
set_time_limit(900);
ini_set('max_execution_time', 900);

$incPath = str_replace("/wp-content/plugins/bebop", "", getcwd());

ini_set('include_path', $incPath);
//include(ABSPATH . 'wp-load.php');

//if we are ran from the BuddyStream cronservice save the new uniqueKey
if ( isset($_GET['uniqueKey']) ) {
	//remember to put into database to retain cron times etc as this is the old method.
    update_site_option("buddystream_cronservices_uniquekey", $_GET['uniqueKey']);
}

//since BuddyStream 2.5.08
/*if ( ! get_site_option('buddystream_fix_2508')) {

    //get all activity items from BuddyStream and user id in front of secondary_item_id
    global $bp, $wpdb;
    $items = $wpdb->get_results("SELECT * FROM " . $bp->activity->table_name . " WHERE secondary_item_id != '' and (
    type = 'youtube'
    OR type = 'vimeo'
    OR type = 'rss'
    OR type = 'twitter'
    OR type = 'facebook'
    OR type = 'soundcloud'
    OR type = 'googleplus'
    OR type = 'linkedin'
    OR type = 'lastfm'
    OR type = 'flickr'
    OR type = 'googlebuzz'
    ) ;");

    foreach ($items as $item) {
        $wpdb->query("UPDATE " . $bp->activity->table_name . " SET secondary_item_id = '" . $item->user_id . "_" . $item->secondary_item_id . "' WHERE id='" . $item->id . "';");
    }

    update_site_option('buddystream_fix_2508', 1);
}*/

//if network set skip auto loading network import and run the set network
if( isset($_GET['oer']) ) {
    $importer = $_GET['oer'];
}

if( ! isset( $_GET['oer']) ) {

    //directory of extentions
    $handle = opendir(WP_PLUGIN_DIR . "/bebop/extensions");
	
	$extensions = array();
    //loop extentions so we can add active extentions to the import loop
    if ($handle) {
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != ".." && $file != ".DS_Store") {            	
                if (file_exists(WP_PLUGIN_DIR . "/bebop/extensions/" . $file . "/import.php")) {
                    if ( bebop_tables::check_option_exists("bebop_" . $file . "_provider") ) {
                        $extensions[] = $file;
                    }
                }
            }
        }
    }
	
    //save importers to database
	bebop_tables::update_option("buddystream_importers", implode(",", $extensions));

    //check if there is a import queue, if empty reset
     if ( ! bebop_tables::get_option_value("buddystream_importers_queue")) {         	
         bebop_tables::update_option("buddystream_importers_queue", implode(",", $extensions));		 
    }
	
	
    //start the import (one per time)
    $importers = bebop_tables::get_option_value("buddystream_importers_queue");
    $importers = explode(",", $importers);
    $importer = current($importers);

//	Remove this later
var_dump($importer);
//REMOVE THIS LATER^



    //remove importer form queue before starting real import
   unset($importers[0]);
    bebop_tables::update_option("buddystream_importers_queue", implode(",", $importers));
}

//start the importer for real 
if (file_exists(WP_PLUGIN_DIR . "/bebop/extensions/" . $importer . "/import.php")) {
    if ( bebop_tables::get_option_value("bebop_" . $importer . "_provider") ) {

       include_once(WP_PLUGIN_DIR . "/bebop/extensions/" . $importer . "/import.php");

         if (function_exists("Buddystream" . ucfirst($importer) . "ImportStart")) {


            $numberOfItems = call_user_func("Buddystream" . ucfirst($importer) . "ImportStart");

            //create return array
            $infoArray = array(
                'executed' => true,
                'date' => date('d-m-y H:i'),
                'network' => $importer,
                'items' => $numberOfItems
            );

            //echo json_encode($infoArray);
        }
    }
}