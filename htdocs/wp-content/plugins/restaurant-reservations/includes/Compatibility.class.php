<?php
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'rtbCompatibility' ) ) {
/**
 * Class to handle backwards compatibilty issues for Restaurant Reservations
 *
 * @since 1.3
 */
class rtbCompatibility {

	/**
	 * Set up hooks
	 */
	public function __construct() {

		// Preserve this defined constant in case anyone relied on it
		// to check if the plugin was active
		define( 'RTB_TEXTDOMAIN', 'rtbdomain' );

		// Load a .mo file for an old textdomain if one exists
		add_filter( 'load_textdomain_mofile', array( $this, 'load_old_textdomain' ), 10, 2 );

		// Run a filter deprecrated in 1.4.3
		add_filter( 'rtb_bookings_table_views_date_range', array( $this, 'rtn_bookings_table_views_schedule' ) );

	}

	/**
	 * Load a .mo file for an old textdomain if one exists
	 *
	 * In versions prior to 1.3, the textdomain did not match the plugin
	 * slug. This had to be changed to comply with upcoming changes to
	 * how translations are managed in the .org repo. This function
	 * checks to see if an old translation file exists and loads it if
	 * it does, so that people don't lose their translations.
	 *
	 * Old textdomain: rtbdomain
	 */
	public function load_old_textdomain( $mofile, $textdomain ) {

		if ( $textdomain === 'restaurant-reservations' && 0 === strpos( $mofile, WP_LANG_DIR . '/plugins/'  ) && !file_exists( $mofile ) ) {
			$mofile = dirname( $mofile ) . DIRECTORY_SEPARATOR . str_replace( $textdomain, 'rtbdomain', basename( $mofile ) );
		}

		return $mofile;
	}

	/**
	 * Run a filter on the admin bookings page display views that was
	 * deprecrated in v1.4.3
	 *
	 * @since 1.4.3
	 */
	public function rtn_bookings_table_views_schedule( $views ) {
		return apply_filters( 'rtn_bookings_table_views_schedule', $views );
	}

}
} // endif

/**
 * This adds a function missing in PHP versions less than 5.3 which is used to
 * properly format non-standard Latin characters in the name portion of an
 * email's Reply-To headers. The name variable is passed through this function
 * before being added to the headers.
 *
 * If it detects that the function already exists, it will do nothing.
 *
 * From: http://php.net/manual/en/function.quoted-printable-encode.php#115840#
 */
if ( !function_exists( 'quoted_printable_encode' ) ) {
function quoted_printable_encode($str) {
    $php_qprint_maxl = 75;
    $lp = 0;
    $ret = '';
    $hex = "0123456789ABCDEF";
    $length = strlen($str);
    $str_index = 0;

    while ($length--) {
        if ((($c = $str[$str_index++]) == "\015") && ($str[$str_index] == "\012") && $length > 0) {
            $ret .= "\015";
            $ret .= $str[$str_index++];
            $length--;
            $lp = 0;
        } else {
            if (ctype_cntrl($c)
                || (ord($c) == 0x7f)
                || (ord($c) & 0x80)
                || ($c == '=')
                || (($c == ' ') && ($str[$str_index] == "\015")))
            {
                if (($lp += 3) > $php_qprint_maxl)
                {
                    $ret .= '=';
                    $ret .= "\015";
                    $ret .= "\012";
                    $lp = 3;
                }
                $ret .= '=';
                $ret .= $hex[ord($c) >> 4];
                $ret .= $hex[ord($c) & 0xf];
            }
            else
            {
                if ((++$lp) > $php_qprint_maxl)
                {
                    $ret .= '=';
                    $ret .= "\015";
                    $ret .= "\012";
                    $lp = 1;
                }
                $ret .= $c;
                if($lp == 1 && $c == '.') {
                    $ret .= '.';
                    $lp++;
                }
            }
        }
    }

    return $ret;
}
} // endif