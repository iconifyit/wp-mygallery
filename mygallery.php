<?php
/**
 * Create a very simple gallery from a list of media IDs.
 *
 * @link              http://iconify.it
 * @since             1.0.0
* @package           My-Gallery
*
 * @wordpress-plugin
* Plugin Name:       My Gallery
* Plugin URI:        http://technify.me
 * Description:       Display a very simple gallery from a list of media IDs.
 * Version:           1.0.0
* Author:            Scott Lewis
* Author URI:        http://technify.me
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       technifyme
*/

class MyGallery {

    /**
     * Do some WP cleanup.
     */
    function __construct() {
        remove_action( 'wp_head',             'print_emoji_detection_script', 7 );
        remove_action( 'wp_print_styles',     'print_emoji_styles' );
        remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
        remove_action( 'admin_print_styles',  'print_emoji_styles' );
    }

    /**
     * Safe way to get a value from an object or array.
     */
    public static function get( $subject, $key, $default=null ) {

        $value = $default;

        if ( is_object($subject) && isset($subject->$key) ) {
            $value = $subject->$key;
        }
        else if ( is_array($subject) && isset($subject[$key]) ) {
            $value = $subject[$key];
        }

        return $value;
    }

    /**
     * Debug utility function.
     */
    public static function dump($what, $die=false ) {
        printf('<pre>%s</pre>', print_r( $what, true ));
        if ( $die ) die('End Dump');
    }

    /**
     * Remove the JetPack OpenGraph tags
     */
    public static function clean( $header ) {

        remove_action( 'wp_head', 'jetpack_og_tags' );
    }

    /**
     * Handle the gallery shortcode
     */
    public static function shortcode( $attrs ) {

        ob_start();
        $attrs = shortcode_atts(
            array('ids' => array(), 'size' => 'medium'),
            $attrs
        );

        $class = self::get( $attrs, 'size', 'medium' );

        $the_query = new WP_Query(array(
            'post_type'      => array( 'attachment' ),
            'post__in'       => @explode(',', $attrs['ids']),
            'posts_per_page' => -1,
            'post_status'    => 'inherit',
            'post_mime_type' => 'image',
            'order'          => 'asc'
        ));
        ?>
        <?php if ($the_query->have_posts()) : ?>
            <ul class="inline-gallery <?php echo $class; ?>">
                <?php while($the_query->have_posts()) : $the_query->the_post() ; ?>
                    <?php
                    $alt_sentence = "";
                    $alt_text = get_post_meta( get_the_ID(), '_wp_attachment_image_alt', true );
                    if (empty($alt_text)) {
                        $alt_text = get_the_title( get_the_ID() );
                    }
                    if (empty($alt_text)) {
                        $alt_text = pathinfo( basename(get_the_guid()), PATHINFO_FILENAME );
                    }
                    if (! empty($alt_text)) {
                        $alt_sentence = self::to_sentence( 'icon', $alt_text );
                        $alt_text = self::oxfordize( $alt_text );
                    }
                    ?>
                    <li><img src="<?php the_guid(); ?>" title="<?php echo $alt_sentence; ?>" alt="<?php echo $alt_sentence; ?>" /></li>
                <?php endwhile; ?>
            </ul>
        <?php endif; ?>
        <?php
        $gallery = ob_get_contents();
        ob_end_clean();
        wp_reset_postdata();
        return $gallery;
    }

    /**
     * Converts an array of image tags into a proper sentence.
     *
     * @param sring 		$subject	The subject of the sentence
     * @param array|string 	$words		The list of words (tags)
     * @param int 			$lenth		The maximum word length
     * @param string 		$and		The 'and' text (and, &, et, i, etc).
     *
     * @return string
     */
    public static function to_sentence($subject, $words, $max=12, $and='and') {

        return trim( "{$subject} related to " . self::oxfordize($words, $max, $and) );
    }


    /**
     * Converts an array of image tags into a proper sentence.
     *
     * @param array|string 	$words		The list of words (tags)
     * @param int 			$lenth		The maximum word length
     * @param string 		$and		The 'and' text (and, &, et, i, etc).
     *
     * @return string
     */
    public static function oxfordize($words, $max=12, $and='and' ) {

        /**
         * If $words is not an array, no need to go further.
         */
        if (! is_array($words)) {
            foreach (array(',', '-', ' ') as $char) {
                if (substr_count($words, $char) > 0) {
                    $delim = $char;
                    break;
                }
            }
            $words = explode($delim, $words);
        }

        /**
         * Let's make sure we don't exceed the max length.
         */
        if (count($words) > $max) {
            $words = array_slice($words, 0, $max);
        }

        /**
         * If any of the 'words' have an ampersand or 'and', let's add more words.
         */
        $more_words = array();
        foreach ($words as $word) {
            $more_words = array_merge($more_words, explode('&', $word) );
        }

        /**
         * Push the updated list of words back onto the variable.
         */
        $words = $more_words;

        /**
         * Look for ' and ' and convert to comma-separated words.
         */
        $more_words = array();
        foreach ($words as $word) {
            $more_words = array_merge( $more_words, explode(' and ', $word) );
        }

        /**
         * Trim any trailing/leading white space to be neat.
         */
        $words = array_map( 'trim', $more_words );

        if ( count($words) == 1 ) {

            $first = reset($words);
            $last  = "";
            $and   = "";
        }
        else if ( count($words) == 2 ) {

            $first = reset($words);
            $last  = end($words);
        }
        else {

            /**
             * Grab all but the last word ...
             */

            $first = implode(', ', array_slice( $words, 0, count($words) -1 ));

            /**
             * Let's add the Oxford comma.
             * (Yes, we are that anal ... and pedantic) :-)
             */
            $first .= ",";

            /**
             * Get the very last word.
             */
            $last = end($words);
        }

        /**
         * And put them together in a sentence
         * of which the Queen herself would be proud.
         */
        return trim( "{$first} {$and} {$last}" );
    }
}

add_action( 'template_redirect', array('MyGallery', 'clean') );
add_shortcode( 'my_gallery', array( 'MyGallery', 'shortcode' ) );
