<link rel="stylesheet" href="<?php echo plugins_url() . '/bebop/core/resources/css/user.css';?>" type="text/css">
<?php

global $bp;

//resets the user's data data
if( isset( $_GET['reset'] ) ) {
	if ( $_GET['reset']  == 'true' ) {	
		bebop_tables::remove_user_from_provider($bp->loggedin_user->id, 'twitter');
	}
}

if ( isset( $_GET['oauth_token'] ) ) {
	
    //Handle the oAuth requests
    $OAuth = new bebop_oauth();
    $OAuth->setRequestTokenUrl('http://api.twitter.com/oauth/request_token');
    $OAuth->setAccessTokenUrl('http://api.twitter.com/oauth/access_token');
    $OAuth->setAuthorizeUrl('https://api.twitter.com/oauth/authorize');
    
    $OAuth->setParameters(array('oauth_verifier' => $_GET['oauth_verifier']));
    $OAuth->setCallbackUrl($bp->loggedin_user->domain . 'bebop-oers/?oer=twitter');
    $OAuth->setConsumerKey(bebop_tables::get_option_value("bebop_twitter_consumer_key"));
    $OAuth->setConsumerSecret(bebop_tables::get_option_value("bebop_twitter_consumer_secret"));
    $OAuth->setRequestToken(bebop_tables::get_user_meta_value($bp->loggedin_user->id,'bebop_twitter_oauth_token_temp'));
    $OAuth->setRequestTokenSecret(bebop_tables::get_user_meta_value($bp->loggedin_user->id,'bebop_twitter_oauth_token_secret_temp'));
    
    $accessToken = $OAuth->accessToken();
   
    bebop_tables::update_user_meta($bp->loggedin_user->id, 'twitter', 'bebop_twitter_oauth_token', $accessToken['oauth_token'] );
    bebop_tables::update_user_meta($bp->loggedin_user->id, 'twitter', 'bebop_twitter_oauth_token_secret', $accessToken['oauth_token_secret'] );
    bebop_tables::update_user_meta($bp->loggedin_user->id, 'twitter', 'bebop_twitter_sync_to_activity_stream', 1);

    //for other plugins
    do_action('bebop_twitter_activated');
  }

if ( $_POST ) {
    bebop_tables::update_user_meta($bp->loggedin_user->id, 'twitter', 'bebop_twitter_sync_to_activity_stream', $_POST['bebop_twitter_sync_to_activity_stream']);
    bebop_tables::update_user_meta($bp->loggedin_user->id, 'twitter', 'bebop_twitter_filtergood', $_POST['bebop_twitter_filtergood']);
    bebop_tables::update_user_meta($bp->loggedin_user->id, 'twitter', 'bebop_twitter_filterbad', $_POST['bebop_twitter_filterbad']);

    echo '<div class="bebop_message">Settings Saved</div>';
}

//put some options into variables
$bebop_twitter_sync_to_activity_stream = bebop_tables::get_user_meta_value($bp->loggedin_user->id, 'bebop_twitter_sync_to_activity_stream');

if ( ( bebop_tables::get_option_value('bebop_twitter_provider') == 'on') && ( bebop_tables::check_option_exists('bebop_twitter_consumer_key') ) ) {
	if ( bebop_tables::get_user_meta_value($bp->loggedin_user->id, 'bebop_twitter_oauth_token') ) {
	    echo '<form id="settings_form" action="' . $bp->loggedin_user->domain . 'bebop-oers/?oer=twitter" method="post">
	    <h3> Settings</h3>';
	    
			echo '<br/><h5>Sync tweets to activity stream</h5>
			<input type="radio" name="bebop_twitter_sync_to_activity_stream" id="bebop_twitter_sync_to_activity_stream" value="1"';  if ($bebop_twitter_sync_to_activity_stream == 1) { echo 'checked'; } echo '>
			<label for="yes">Yes</label>
			
			<input type="radio" name="bebop_twitter_sync_to_activity_stream" id="bebop_twitter_sync_to_activity_stream" value="0"'; if ($bebop_twitter_sync_to_activity_stream == 0) { echo 'checked'; } echo '>
			<label for="no">No</label>

			<input type="submit" value="Save Settings">';
	    
	    if( bebop_tables::get_user_meta_value($bp->loggedin_user->id, 'bebop_twitter_oauth_token') ) {
	        echo '<br><a class="button_auth" href="?oer=twitter&reset=true">Remove Authorisation</a>';
		}
		echo '</form>';
	}
	else {
		echo '<h3> setup</h3>
		You may setup twitter intergration over here.
		Before you can begin using twitter with this site you must authorise on twitter by clicking the link below.';
		
		//oauth
		$OAuth = new bebop_oauth();
		$OAuth->setRequestTokenUrl('http://api.twitter.com/oauth/request_token');
		$OAuth->setAccessTokenUrl('http://api.twitter.com/oauth/access_token');
		$OAuth->setAuthorizeUrl('https://api.twitter.com/oauth/authorize');
		$OAuth->setCallbackUrl($bp->loggedin_user->domain . 'bebop-oers/?oer=twitter');
		$OAuth->setConsumerKey(bebop_tables::get_option_value("bebop_twitter_consumer_key"));
		$OAuth->setConsumerSecret(bebop_tables::get_option_value("bebop_twitter_consumer_secret"));
		 
		//get the oauth token
		$requestToken = $OAuth->requestToken();
	
		$OAuth->setRequestToken($requestToken['oauth_token']);
		$OAuth->setRequestTokenSecret($requestToken['oauth_token_secret']);
		 
		bebop_tables::update_user_meta($bp->loggedin_user->id, 'twitter', 'bebop_twitter_oauth_token_temp','' . $requestToken['oauth_token'].'');
		bebop_tables::update_user_meta($bp->loggedin_user->id, 'twitter', 'bebop_twitter_oauth_token_secret_temp','' . $requestToken['oauth_token_secret'].'');
	
		
		//get the redirect url for the user
		$redirectUrl = $OAuth->getRedirectUrl();
		if( $redirectUrl ) {
			echo '<br><a class="button_auth" href="' . $redirectUrl . '">Start Authorisation</a>';
		}
		else{
			echo 'authentication is all broken :(';
		}
	}
}//End if ( ( bebop_tables::get_option_value('bebop_twitter_on') == true) && ( bebop_tables::check_option_exists('bebop_twitter_consumer_key') ) ) {
else {
	echo 'Twitter has not yet been configured. Please contact the blog admin to make sure twitter is enables as an OER provider.';
}