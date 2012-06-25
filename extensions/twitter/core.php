<?php 
function bebopTwitterSharing(){
 global $bp;
    if (bebop_tables::get_option("tweetstream_consumer_key")) {
        if (get_user_meta($bp->loggedin_user->id, 'tweetstream_token',1)) {
            echo'<span class="twitter_share_button" onclick="twitter_addTag()" id="'.__('Also post this to my Twitter account.', 'buddystream_twitter').'"></span>';

            $max_message = __("You\'ve reached the max. amount of characters for a Tweet.  The Message will appear truncated on Twitter.", "bebop_twitter");
            echo '<div class="twitter_share_counterbox">
                    <div class="twitter_share_counter">140</div>
                  </div>
            <div class="twitter_hoverbox"></div>';

        }
    }
}


/**
 * Replace all twitpic and yfroc images for real thumbnails
 */

add_filter( 'bp_get_activity_content','BuddystreamTwitterImages',5 );
add_filter( 'bp_get_activity_content_body','BuddystreamTwitterImages',5 );

function bebopTwitterImages($text) {
 
    if(bp_get_activity_type() == 'twitter'){
        $text = preg_replace('#http://twitpic.com/([a-z0-9_]+)#i', '<a href="http://twitpic.com/\\1" target="_blank" rel="external"><img width="60" src="http://twitpic.com/show/mini/\\1" /></a>', $text);
        $text = preg_replace('#http://yfrog.com/([a-z0-9_]+)#i', '<a href="http://yfrog.com/\\1" target="_blank" rel="external"><img width="60" src="http://yfrog.com/\\1.th.jpg" /></a>', $text);
        $text = preg_replace('#http://yfrog.us/([a-z0-9_]+)#i', '<a href="http://yfrog.us/\\1" target="_blank" rel="external"><img width="60" src="http://yfrog.us/\\1:frame" /></a>', $text);
    }
    
  return $text;
}

/**
 * Post update to Twitter
 */

function bebopTwitterPostUpdate($content = "", $shortLink = "", $user_id = 0) {
    
    global $bp;
    
    //handle oauth calls
    $buddystreamOAuth = new BuddyStreamOAuth();
    $buddystreamOAuth->setRequestTokenUrl('http://api.twitter.com/oauth/request_token');
    $buddystreamOAuth->setAccessTokenUrl('http://api.twitter.com/oauth/access_token');
    $buddystreamOAuth->setAuthorizeUrl('https://api.twitter.com/oauth/authorize');
    $buddystreamOAuth->setCallbackUrl($bp->root_domain);
    $buddystreamOAuth->setConsumerKey(bebop_tables::get_option("tweetstream_consumer_key"));
    $buddystreamOAuth->setConsumerSecret(bebop_tables::get_option("tweetstream_consumer_secret"));
    $buddystreamOAuth->setAccessToken(get_user_meta($bp->loggedin_user->id,'tweetstream_token', 1));
    $buddystreamOAuth->setAccessTokenSecret(get_user_meta($bp->loggedin_user->id,'tweetstream_tokensecret', 1));
    $buddystreamOAuth->setRequestType('POST');
    $buddystreamOAuth->setParameters(array('status' => BuddyStreamFilters::filterPostContent($content, $shortLink, 140)));
    $buddystreamOAuth->oAuthRequest('https://api.twitter.com/1/statuses/update.json');
    
}

/**
 * 
 * Page loader functions 
 *
 */

//called from  menu item creation on bebop_page_loader.php
function bebop_twitter() {
    bebop_extensions::page_loader('twitter');
}