<?php
/**
* Google Apps Login Redirect Filter
*
* @author            JB Christy
* @license           GPL-2.0-or-later
*
* @wordpress-plugin
* Plugin Name:       Google Apps Login Redirect Filter
* Plugin URI:        https://github.com/SU-UComm/galogin-redirect-filter
* Description:       Tell Google Apps Login to redirect to hostname, not webhead
*                    server. So far only required on multi-server cluster on Pantheon.
* Version:           1.0.0
* Requires at least: 5.2
* Requires PHP:      7.2
* Author:            JB Christy
* Author URI:        https://www.stanford.edu/site/
* License:           GPL v2 or later
* License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
* GitHub Plugin URI: https://github.com/SU-UComm/galogin-redirect-filter
*/

namespace Stanford\GAL;

/***
 * When someone attempts to visit a private page that requires a user to login, the
 * Google Apps Login plugin intercepts the request and assigns a page to redirect
 * to after the login handshake. This works perfectly fine in most instances.
 * However, in a Pantheon server cluster the variable the GA Login plugin uses
 * to identify the host instead points to a specific webhead in the server cluster.
 * Attempts to redirect to a specific webhead fail, and the user ends up in the
 * backend, rather than on the page they were attempting to access when the login
 * was requested. This function replaces the webhead with the correct host url.
 *
 * Note: This filter is added with a priority of 99, which means it's called
 * AFTER the GA Login plugin has provided a url. This is so we can simply swap
 * out the webhead name with site's name.
 *
 * @param string $redirect_to - url where GA Login plugin intends to redirect to after SSO handshake
 * @param string $request_from - unused
 * @param object $user - unused
 * @return string - actual url with correct hostname
 */
function ga_login_redirect($redirect_to, $request_from='', $user=null) {
  console_log( [
      '$redirect_to'  => $redirect_to
    , '$request_from' => $request_from
    , '$user' => $user
    , 'typeof $user' => is_array( $user ) ? 'array' : 'object'
  ], 'green' );
  console_log( $_SERVER );

  if ( isset( $_SERVER[ 'HTTP_X_FORWARDED_HOST' ] ) ) {
    $url = parse_url( $redirect_to );
    console_log( [ 'parse redirect_to' => $url ], 'green');
    $new_url = "{$url['scheme']}://{$_SERVER['HTTP_X_FORWARDED_HOST']}{$url['path']}";
    if ( isset( $url['query'] ) && !empty( $url['query'] ) ) {
      $new_url .= "?{$url['query']}";
    }
    console_log( [ 'Redirecting to' => $new_url ], 'red' );
    return $new_url;
  }
  return $redirect_to;
}
// apply this filter AFTER GA Login's own filter, as we're just massaging the hostname it provides
add_filter('login_redirect', 'Stanford\GAL\ga_login_redirect', 99, 3 );

/***
 * Debug function that displays $vars in the browser's console.
 *
 * @param array  $vars  - array of 'Descriptive text' => value
 * @param string $color - what color you want the console message to be
 */
function console_log( $vars, $color = 'blue' ) {
  $log_style = "color:{$color};";
  echo "<script>\n";
  echo "console.log('%c set_gal_login_cookie:', '{$log_style}');\n";
  foreach ( $vars as $name => $value ) {
    echo "console.log('%c - {$name}: " . json_encode( $value ) . "', '{$log_style}');\n";
  }
  echo "</script>\n";
}
?>