<?php
/*
Plugin Name: Team Page
Plugin URI: http://www.jonbishop.com/downloads/wordpress-plugins/team-page/
Description: Uses WordPress profiles to help create team pages for your site
Version: 1
Author: Jon Bishop
Author URI: http://www.jonbishop.com
License: GPL2
*/

//http://wp.smashingmagazine.com/2012/06/05/front-end-author-listing-user-search-wordpress/


if (!defined( 'TEAMPAGE_URL' )){
	define('TEAMPAGE_URL', plugin_dir_url(__FILE__));
}
if (!defined( 'TEAMPAGE_PATH')){
	define('TEAMPAGE_PATH', plugin_dir_path(__FILE__));
}
if (!defined( 'TEAMPAGE_BASENAME') ){
	define('TEAMPAGE_BASENAME', plugin_basename( __FILE__ ));
}
if (!defined( 'TEAMPAGE_ADMIN') ){
	define('TEAMPAGE_ADMIN', get_bloginfo('url')."/wp-admin");
}


class WPTeam {
	
	function WPTeam() {
		// Team page
		if(is_admin()){
			add_filter('user_contactmethods', array(&$this, 'filter_contact'));
		} else {
			add_shortcode('team_page',  array(&$this, 'create_team_shortcode'));
		}
	}
	//=============================================
	// Add shortcode
	//=============================================
	function create_team_shortcode($atts) {
                extract( shortcode_atts( array(
                        'id' => array(),
                ), $atts ) );
                if(!empty($id)){
                    $str = trim(preg_replace('|\\s*(?:' . preg_quote(',') . ')\\s*|', ',', $id));
                    $id = explode(",", $str);
                }
		$content = $this->get_team_info($id);
                
                // Check for nested shortcodes
                $content = do_shortcode($content);
                
		return $content;
	}

	//=============================================
	// Change profile page contact options
	//=============================================
	function filter_contact($contactmethods) {
		unset($contactmethods['aim']);
		unset($contactmethods['jabber']);
		unset($contactmethods['yim']);
		$contactmethods['user_title'] = 'Title';
		$contactmethods['twitter'] = 'Twitter';
		$contactmethods['facebook'] = 'Facebook';
		$contactmethods['linkedin'] = 'LinkedIn';
		$contactmethods['digg'] = 'Digg';
		$contactmethods['flickr'] = 'Flickr';
		$contactmethods['stumbleupon'] = 'StumbleUpon';
		$contactmethods['youtube'] = 'YouTube';
		$contactmethods['yelp'] = 'Yelp';
		$contactmethods['reddit'] = 'Reddit';
		$contactmethods['delicious'] = 'Delicious';
		$contactmethods['aim'] = 'AIM';
		$contactmethods['jabber'] = 'Jabber / Google Talk';
		$contactmethods['yim'] = 'Yahoo IM';
		return $contactmethods;
	}
	//=============================================
	// Change profile page contact options
	//=============================================
	function team_get_social($author_ID, $social){
		$content="";
		if($author_social = get_the_author_meta($social,$author_ID)){
			switch ($social){
				case 'twitter':
					$content = "<a href='http://www.twitter.com/" . $author_social . "'><img src='" . TEAMPAGE_URL ."/images/twitter.png' alt='Twitter'/></a>";
					break;
				case 'facebook':
					$content = "<a href='http://www.facebook.com/" . $author_social . "'><img src='" . TEAMPAGE_URL ."/images/facebook.png' alt='Facebook'/></a>";
					break;
				case 'linkedin':
					$content = "<a href='http://www.linkedin.com/in/" . $author_social . "'><img src='" . TEAMPAGE_URL ."/images/linkedin.png' alt='LinkedIn'/></a>";
					break;
				case 'digg':
					$content = "<a href='http://digg.com/" . $author_social . "'><img src='" . TEAMPAGE_URL ."/images/digg.png' alt='Digg'/></a>";
					break;
				case 'flickr':
					$content = "<a href='" . $author_social . "'><img src='" . TEAMPAGE_URL ."/images/flickr.png' alt='Flickr'/></a>";
					break;
				case 'stumbleupon':
					$content = "<a href='" . $author_social . "'><img src='" . TEAMPAGE_URL ."/images/stumbleupon.png' alt='Stumbleupon'/></a>";
					break;
				case 'youtube':
					$content = "<a href='http://www.youtube.com/user/" . $author_social . "'><img src='" . TEAMPAGE_URL ."/images/youtube.png' alt='YouTube'/></a>";
					break;
				case 'yelp':
					$content = "<a href='" . $author_social . "'><img src='" . TEAMPAGE_URL ."/images/yelp.png' alt='Yelp'/></a>";
					break;
				case 'reddit':
					$content = "<a href='http://www.reddit.com/user/" . $author_social . "'><img src='" . TEAMPAGE_URL ."/images/reddit.png' alt='Reddit'/></a>";
					break;
				case 'delicious':
					$content = "<a href='http://www.delicious.com/" . $author_social . "'><img src='" . TEAMPAGE_URL ."/images/delicious.png' alt='Delicious'/></a>";
					break;
			}
		}
		return $content;
	}
	//=============================================
	// Display team members
	//=============================================
	function get_team_info($team_list = array()){
		global $wpdb;
		$settings = array();
		$settings = get_option('settings');
		$content = "";
		$the_author_title = "";
                $sorted_team_results = array();

                $query_del = ' WHERE'; // begin query with WHERE and deliminate with OR
                $query_params = '';
                // create query paramaters to retrieive specifc team members
                foreach( $team_list as $team_member ){
                    // check to see if current member is admin and if admin is hidden in settings
                    if($team_member == 1 && isset($settings['team_admin']) && $settings['team_admin']){
                        // Do nothing
                    } else {
                        $query_params .= $query_del . ' ID = ' . $team_member;
                        $query_del = ' OR';
                    }
                }
                // if admin is hidden in settings make sure to exclude from results
		if(isset($settings['team_admin']) && $settings['team_admin']){
                    $query_params .= $query_del . ' ID != 1';
                }
                // get authors
		$team_results = $wpdb->get_results('SELECT DISTINCT ID FROM '.$wpdb->users . $query_params);

                // loop through and display authors
		if($team_results){
                    // Sort if $team_list exists or just fill up sort array with team member values
                    if(empty($team_list)){
                        foreach($team_results as $team_member){
				$userdata = get_userdata($team_member->ID);
                                array_push($sorted_team_results, $userdata);
                        }
                    } else {
                        foreach($team_list as $team_member){
				$userdata = get_userdata($team_member);
                                array_push($sorted_team_results, $userdata);
                        }
                    }
                    // loop through and display authors
                    foreach($sorted_team_results as $team_member){
                            $the_author_title = "";
                            $member_ID = $team_member->ID;
                            $userdata = get_userdata($member_ID);
                            $the_author_description = apply_filters('the_content',$team_member->description);

                            $content .= "<div class='team' id='team-" . get_the_author_meta('user_login', $member_ID) . "'>";
                            if(get_the_author_meta('user_title', $member_ID)){
                                    $the_author_title = " - " . get_the_author_meta('user_title', $member_ID);
                            }
                            $content .= "<h3>" . get_the_author_meta('display_name', $member_ID) . $the_author_title . "</h3>";
                            $content .= "<div class='description'>";
                            if(isset($settings['team_avatars']) && $settings['team_avatars']){
                              $content .= "<div class='team-avatar'>" .  get_avatar(get_the_author_meta('user_email', $member_ID), 80) ."</div>";
                            }
                            $content .= "<p>". $the_author_description ."</p>";
                            if(get_the_author_meta('twitter', $member_ID) || get_the_author_meta('facebook', $member_ID) || get_the_author_meta('linkedin', $member_ID) || get_the_author_meta('digg', $member_ID) || get_the_author_meta('flickr', $member_ID) || get_the_author_meta('stumbleupon', $member_ID) || get_the_author_meta('youtube', $member_ID) || get_the_author_meta('yelp', $member_ID)|| get_the_author_meta('reddit', $member_ID) || get_the_author_meta('delicious', $member_ID)){
                                    $content .= "<p>";
                                    $content .= $this->team_get_social($member_ID, 'twitter');
                                    $content .= $this->team_get_social($member_ID, 'facebook');
                                    $content .= $this->team_get_social($member_ID, 'linkedin');
                                    $content .= $this->team_get_social($member_ID, 'digg');
                                    $content .= $this->team_get_social($member_ID, 'flickr');
                                    $content .= $this->team_get_social($member_ID, 'stumbleupon');
                                    $content .= $this->team_get_social($member_ID, 'youtube');
                                    $content .= $this->team_get_social($member_ID, 'yelp');
                                    $content .= $this->team_get_social($member_ID, 'reddit');
                                    $content .= $this->team_get_social($member_ID, 'delicious');
                                    $content .= "</p>";
                            }
                            $content .= "</div></div>";
			}
		}
		return $content;
	}	
}

global $WPTeamPage;
$WPTeamPage = new WPTeam();
// RegisterDefault settings
//register_activation_hook(__FILE__, array( $WPTeamPage, 'add_defaults'));
?>