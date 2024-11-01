<?php
/**
 * Plugin Name: WP Blog Posts
 * Plugin URI: https://wordpress.org/plugins/wp-blog-posts
 * Description: WP Blog Posts plugin ability to display blog posts with title, short description, date, etc... to our website.
 * Author: Laxman Prajapati
 * Author URI: http://laxmanprajapati.in/
 * Version: 1.0.2
 * Text Domain: wp-blog-posts
 *
 * WP Blog Posts is distributed under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * WP Blog Posts is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with WP Blog Posts. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package WPBlogPosts
 * @author Laxman Prajapati
 * @version 1.0.2
 */

class WP_Blog_Posts {
	public function __construct() {
		register_activation_hook( __FILE__, array( __CLASS__, 'wpbp_active_function' ) );
		add_action( 'activated_plugin', array( __CLASS__, 'wpbp_activation_redirect' ) );
		
		// Admin JS
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'wpbp_assets' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'wpbp_frontend_assets' ) );

		// Setting Page
		add_action( 'admin_menu', array( __CLASS__, 'wpbp_add_menu' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( __CLASS__, 'wpbp_add_link' ) );
		add_action( 'admin_init', array( __CLASS__, 'wpbp_setting_display' ) );

		// WP Blog Posts Shortcode
		add_shortcode( 'wp_blog_posts',  array($this, 'wpbp_posts_section_sc') );
	}

	/**
	 * Activation default option.
	 *
	 * @return void
	 */
	public function wpbp_active_function() {
		add_option( 'wpbp-switch', 1 );
	}

	/**
	 * After Activate redirection.
	 *
	 * @return void
	 */
	public function wpbp_activation_redirect( $plugin ) {
		if ( plugin_basename( __FILE__ ) === $plugin ) {
			exit( esc_url( wp_safe_redirect( admin_url( 'options-general.php?page=wp-blog-posts' ) ) ) );
		}
	}

	/**
	 * Admin Custom Script and CSS assets.
	 *
	 * @return void
	 */
	public function wpbp_assets() {
		wp_enqueue_style( 'wpbp-backend-css', plugin_dir_url( __FILE__ ) . 'assets/css/wpbp-backend.css', array(), '1.0.1', false );
	}

	/**
	 * Frontend Custom Script and CSS assets.
	 *
	 * @return void
	 */
	public function wpbp_frontend_assets() {
		wp_enqueue_style( 'wpbp-frontend-css', plugin_dir_url( __FILE__ ) . 'assets/css/wpbp-front.css', array(), '1.0.1', false );
	}

	/**
	 * Add settings page under the Settings.
	 *
	 * @return void
	 */
	public function wpbp_add_menu() {
		add_options_page(
			__( 'WP Blog Posts', 'wp-blog-posts' ),
			__( 'WP Blog Posts', 'wp-blog-posts' ),
			'manage_options',
			'wp-blog-posts',
			array( __CLASS__, 'wpbp_options_page' ),
			60
		);
	}

	/**
	 * Add setting page link
	 *
	 * @return array
	 */
	public function wpbp_add_link( $links ) {
		return array_merge(
			array(
				'settings' => sprintf(
					'<a href="%1$s">%2$s</a>',
					esc_url( admin_url( 'options-general.php?page=wp-blog-posts' ) ),
					__( 'Settings', 'wp-blog-posts' )
				),
			),
			$links
		);
	}

	/**
	 * Plugin Pages
	 */
	public function wpbp_options_page() {
		printf( '<div class="wrap">' );
		printf( '<div class="wpbpsection">' );
		printf( '<form method="post" class="wpbp-option-page" action="options.php">' );
		settings_fields( 'wpbp_setting_section' );
		printf( '<div class="wpbp-head-section"><div class="wpbp-logo-area"><img src="'.plugin_dir_url( __FILE__ ).'assets/images/wpbp_logo.png" class="wpbp_logo" height="50" width="50" title="WP Blog Posts" /></div>' );
		printf( '<div class="wpbp-title-area"><h3>WP Blog Posts</h3></div></div>' );
		do_settings_sections( 'wp-blog-posts' );
		submit_button( __( 'Save', 'wp-blog-posts' ) );
		printf( '</form></div></div>' );
	}

	/**
	 * Display settins with page
	 *
	 * @return void
	 */
	public function wpbp_setting_display() {
		/**-- Setting Page Section Title --**/
		add_settings_section( 'wpbp_setting_section', esc_html__( '', 'wp-blog-posts' ), array( __CLASS__, 'wpbp_content_callback' ), 'wp-blog-posts' );

		add_settings_field( 'wpbp-switch', esc_html__( 'WP Blog Posts', 'wp-blog-posts' ), array( __CLASS__, 'wpbp_setting_element' ), 'wp-blog-posts', 'wpbp_setting_section' );
		$wpbp_switch_args = array(
			'type'              => 'string',
			'sanitize_callback' => array( __CLASS__, 'wpbp_sanitize_checkbox' ),
			'default'           => 0,
		);
		register_setting( 'wpbp_setting_section', 'wpbp-switch', $wpbp_switch_args );

		/** WP Blog Posts Shortcode **/
		add_settings_field( 'wpbp-shortcode', esc_html__( 'Shortcode', 'wp-blog-posts' ), array( __CLASS__, 'wpbp_shortcode' ), 'wp-blog-posts', 'wpbp_setting_section' );
		register_setting( 'wpbp_setting_section', 'wpbp-shortcode', $wpbp_shortcode );
		
	}

	/**
	 * Setting page description.
	 *
	 * @return void
	 */
	public function wpbp_content_callback() {
		esc_html_e( '', 'wp-blog-posts' );
	}

	/**
	 * Add Color field.
	 *
	 * @return void
	 */
	
	/** WP Blog Posts Field **/
	public function wpbp_shortcode() {
		printf( '<input type="text" name="wpbp-shortcode" id="wpbp-shortcode" class="wpbp-shortcode" value="[wp_blog_posts limit=&#34;5&#34; column=&#34;3&#34; cat=&#34;5&#34;]" readonly /><span class="description" id="wpbp-description">
		Example of the shortcode.</span><br><span class="small_desc_shortcode"><i>limit="5" = 5 is Limit of post</i> <i>column="3" = 3 is Column of the listing section</i> <i>cat="5" = 5 is Category ID</i></span>', esc_html( get_option( 'wpbp-shortcode' ) ) );
	}

	/**
	 * Callback for enable.
	 *
	 * @return void
	 */
	public function wpbp_setting_element() {
		$wpbp_enable = get_option( 'wpbp-switch' );
		printf( '<label class="switch"><input type="checkbox" name="wpbp-switch" id="wpbp-switch" value="1" %1$s /><span class="slider round"></span></label><p class="description" id="tagline-description">Enable Or Disable?</p>', ( ( '0' !== $wpbp_enable ) ? ( esc_attr( 'checked' ) ) : '' ) );
	}

	/**
	 * Checkbox value.
	 *
	 * @return integer
	 */
	public function wpbp_sanitize_checkbox( $input ) {
		return ( '1' !== $input ) ? 0 : 1;
	}

	/**
	 * Shortcode of WP Blog Posts plugin.
	 *
	 */
	public function wpbp_posts_section_sc($atts){
		$atts = shortcode_atts(array('limit' => '3', 'column' => '3', 'cat' => '0'), $atts, 'wpbp_posts_section_sc' );
		$BlogColumn = $atts['column'];
		if($BlogColumn == 1){
			$BlogColumnClass = 'span_1_of_1';
		} elseif($BlogColumn == 2){
			$BlogColumnClass = 'span_1_of_2';
		} else{
			$BlogColumnClass = 'span_1_of_3';
		}
		$wpbp_m_switch = get_option( 'wpbp-switch' );
		if ( '0' !== $wpbp_m_switch ) {
		    ob_start();
			global $post;
		    $args = array(
				'posts_per_page' => $atts['limit'],
			    'offset' => 0,
			    'cat' => $atts['cat'],
			    'orderby' => 'post_date',
			    'order' => 'DESC',
			    'post_type' => 'post',
				'post_status' => 'publish'
			);
			$wp_query = new WP_Query($args); ?>

			<div class="wpbp_blog_listing group">
				<?php while ($wp_query-> have_posts() ) : $wp_query->the_post(); ?>
						<?php 
							$wpbp_content = strip_tags(get_post_field('post_content', $post->ID));
							if (strlen($wpbp_content) > 100) 
							{
								$wpbp_content_limit = substr(strip_shortcodes($wpbp_content), 0, 100);
								$wpbp_content = substr($wpbp_content_limit, 0, strrpos($wpbp_content_limit, ' ')).'...'; 
							}
							$wpbp_image = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'single-post-thumbnail' );
						?>
						<article id="post-<?php echo $post->ID; ?>" class="wpbp_column type-post post-<?php echo $post->ID; ?> <?php echo $BlogColumnClass; ?>">
							<div class="wpbp-article-content">	
								<div class="wpbp_image_container">
									<a href="<?php echo get_permalink($post->ID);?>" class="wpbp_featured_image_url wpbp_view wpbp_overlay wpbp_zoom">
										<img class="post-image entry-image wpbp_featured_image" src="<?php echo $wpbp_image[0]; ?>" itemprop="image"  width="<?php echo $wpbp_image[1]; ?>" height="<?php echo $wpbp_image[2]; ?>" title="<?php the_title(); ?>" alt="post-thumbnail">
										<div class="wpbp_mask wpbp_flex_center">
											<p class="wpbp_post_action_icon">
												<img src="<?php echo plugin_dir_url( __FILE__ ).'assets/images/post-action.png'; ?>">
											</p>
										</div>
									</a>
								</div> 

								<h2 class="entry-title">
									<a href="<?php echo get_permalink($post->ID);?>"><?php the_title(); ?></a>
								</h2>

								<p class="wpbp_post_meta">by <span class="author"><a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" title="<?php echo esc_attr( get_the_author() ); ?>"><?php the_author(); ?></a></span> | <span class="published"><?php echo get_the_date(); ?></span> | <?php echo get_the_category_list(', '); ?></p>
							</div>
							<div class="post-content">
								<p><?php echo $wpbp_content; ?></p>
							</div>
							<div class="post-rm-link">
								<a href="<?php echo get_permalink($post->ID);?>" class="wpbp-rm-link">Read More</a>
							</div>
						</article>
					
				<?php endwhile;	?>
			</div>

		<?php }
		$wpbp_output = ob_get_clean();
		return $wpbp_output;
	}
}

new WP_Blog_Posts();