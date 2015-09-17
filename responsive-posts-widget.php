<?php
/** 
    * Plugin Name: Responsive Posts Widget
    * Plugin URI: http://plugin.bdwebteam.com/responsive-posts-widget
    * Description: Adds a widget that shows the most recent posts of your site with excerpt, featured image, date by sorting & ordering feature
    * Author: Mahabub Hasan
    * Author URI: http://bdwebteam.com/
    * Version: 1.0.3
    * Text Domain: responsive-posts
    * Domain Path: /languages
    * License: MIT License
    * License URI: http://opensource.org/licenses/MIT
*/

/**
   *
   * @package   Responsive_Posts_Widget
   * @author    Mahabub Masan
   * @license   MIT License
   * @link      http://plugin.bdwebteam.com/responsive-posts-widget
   * @copyright 2015
   * 
 */


if ( ! defined( 'WPINC' ) ) {
	die;
}
if (!defined('PLUGIN_ROOT')) {
	define('PLUGIN_ROOT', dirname(__FILE__) . '/');
	define('PLUGIN_NAME', basename(dirname(__FILE__)) . '/' . basename(__FILE__));
}
if (! defined ( 'WP_CONTENT_URL' ))
	define ( 'WP_CONTENT_URL', get_option ( 'siteurl' ) . '/wp-content' );
if (! defined ( 'WP_CONTENT_DIR' ))
	define ( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if (! defined ( 'WP_PLUGIN_URL' ))
	define ( 'WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins' );
if (! defined ( 'WP_PLUGIN_DIR' ))
	define ( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
    require_once(dirname(__FILE__).'/post_resizer.php'); 
    add_action('widgets_init', create_function('', 'return register_widget("responsive_posts");'));
class responsive_posts extends WP_Widget {
             
    //	@var string (The plugin version)		
	var $version = '1.0.3';
	//	@var string $localizationDomain (Domain used for localization)
	var $localizationDomain = 'responsive-posts';
	//	@var string $pluginurl (The url to this plugin)
	var $pluginurl = '';
	//	@var string $pluginpath (The path to this plugin)		
	var $pluginpath = '';	

	function responsive_posts() {
		$this->__construct();
	}
	
	function __construct() {
		$name = dirname ( plugin_basename ( __FILE__ ) );
		$this->pluginurl = WP_PLUGIN_URL . "/$name/";
		$this->pluginpath = WP_PLUGIN_DIR . "/$name/";
		add_action ( 'wp_print_styles', array (&$this, 'responsive_posts_css' ) );
		
		$widget_ops = array ('classname' => 'responsive-posts', 'description' => __ ( 'Show recent posts from selected category. Includes advanced options.', $this->localizationDomain ) );
		$this->WP_Widget ( 'responsive-posts', __ ( 'Responsive Posts Widget ', $this->localizationDomain ), $widget_ops );
	}	
	function responsive_posts_css() {
		$name = "responsive-posts-widget.css";
		if (false !== @file_exists ( TEMPLATEPATH . "/$name" )) {
			$css = get_template_directory_uri () . "/$name";
		} else {
			$css = $this->pluginurl . $name;
		}
		wp_enqueue_style ( 'responsive-posts-widget', $css, false, $this->version, 'screen' );
	}   
	function widget($args, $instance) {
		extract( $args );
		$instance['title']?NULL:$instance['title']='';
		$title = apply_filters('widget_title',$instance['title']);
		$output = $before_widget."\n";
		if($title) {
			$output .= $before_title.'<a class="post-read-more" href="'.$instance['title_url'].'">'.$title.'</a>'.$after_title;
		} else {
			$output .= '<div class="widget clearfix">';
		}
		ob_start();	

		$posts = new WP_Query( array(
			'post_type'		=> array( 'post' ),
			'showposts'		=> $instance['posts_num'],
			'cat'			=> $instance['posts_cat_id'],
			'ignore_sticky_posts'	=> true,
			'orderby'		=> $instance['posts_orderby'],
			'order'			=> 'DESC',
			'date_query' => array(
				array(
					'after' => $instance['posts_time'],
				),
			),
		) );	       
	$output .= '<div class="more_posts">';
		while ($posts->have_posts()): $posts->the_post(); 
            $show_title_limit=$instance['word_posts_title'];
                $post_title = get_the_title(get_the_ID($post->ID));
                $trimmed_title = wp_trim_words( $post_title,$show_title_limit);
                $post_permalink=get_the_permalink(get_the_ID($post->ID));
                $author_posts_url=get_author_posts_url(get_the_author_meta('ID'));
                $the_author=get_the_author(get_the_ID($post->ID));               
                $show_content_limit=$instance['word_posts_content'];
                $content = get_the_content(get_the_ID($post->ID));
                $trimmed_content = wp_trim_words( $content,$show_content_limit);
			    $output .= '<div class="poat_item">';
                    $trainer_thumb   = get_post_thumbnail_id($post->ID);
                    $trainer_img_url = wp_get_attachment_url( $trainer_thumb,'medium' );
                    $img_width=$instance['posts_thumb_width'];
                    $img_height=$instance['posts_thumb_width'];
                    $post_thum_img   = post_thum_resize( $trainer_img_url,$img_width,$img_height, true );                    
                    if($instance['posts_thumb']) {
                        if ( has_post_thumbnail() ):							
                            $output .= '<a class="post-pull-left" href="' .$post_permalink . '" title=" ' .$title. '">';
                            $output .= '<img class="img-responsive" src="'. $post_thum_img. '" alt="' .$title .'" />';							
                            $output .= '</a>';
                        endif;
                    }
					$output .= '<div class="responsive_posts_details">';
                    if($instance['posts_title']==1) {
                        $output .='<h3 style="font-size:' . $instance['title_font_size'] .';" class="responsive-posts-title"><a href="'.$post_permalink.'" rel="bookmark" title="'.$trimmed_title.'">'.$trimmed_title.'</a></h3>';
                   } 
                    if ($instance['show_date']==1 || $instance['show_author']==1 || $instance['show_comments']==1) :
                        $output .= '<div class="entry-meta">';
                            if ($instance['show_date']==1) :
                                $output .= '<span>'.get_the_date().'</span>';
                        endif; 
                            if ($instance['show_date']==1 && $instance['show_author']==1) :
                                 $output .= '<span class="sep">|</span>';
                        endif; 
                            if ($instance['show_author']==1) : 
                                $output .= '<span class="author vcard"> By:';
                                $output .= '<a href="'. $author_posts_url.'" rel="author" class="fn">'. $the_author.'</a></span>'; 
                        endif;
                           if ($instance['show_author']==1 && $instance['show_comments']==1) :
                                $output .= '<span class="sep">|</span>';
                        endif;
                                if ($instance['show_comments']==1) : 
                                $output .= '<a class="comments" href="'. get_comments_link( $post->ID ).'">' . get_comments_number($post->ID) .'</a>';
                        endif; 
                        $output .= '</div>';
                    endif;                             
            if($instance['posts_content']==1) {  
                $output .= '<p>'.$trimmed_content.'</p>';                            
            } 
             if($instance['show_read_more']==1) {
                $output .= '<a class="post-read-more" href="'.$post_permalink.'">Read More</a>';
            } 
			$output .= '</div>';            
          $output .= '</div>';
		endwhile; 
	$output .= '</div>';   
		$output .= ob_get_clean();
		$output .= $after_widget."\n";
		echo $output;
	}

/*  Widget update
/* ------------------------------------ */
	public function update($new,$old) {
        $instance = $old;
        $instance['title'] = strip_tags($new['title']);
        $instance['title_font_size'] = strip_tags($new['title_font_size']);
        $instance['title_url'] = strip_tags($new['title_url']);
        // Posts
        $instance['posts_thumb'] = $new['posts_thumb']?1:0;
        $instance['posts_thumb_width'] = strip_tags($new['posts_thumb_width']);
        $instance['posts_thumb_hight'] = strip_tags($new['posts_thumb_hight']); 
        $instance['posts_title'] = $new['posts_title']?1:0;
        $instance['word_posts_title'] = strip_tags($new['word_posts_title']);
        $instance['posts_content'] = $new['posts_content']?1:0;
        $instance['show_date'] = $new['show_date']?1:0;
        $instance['show_author'] = $new['show_author']?1:0;
        $instance['show_comments'] = $new['show_comments']?1:0;
        $instance['show_read_more'] = $new['show_read_more']?1:0;
        $instance['word_posts_content'] = strip_tags($new['word_posts_content']);
        $instance['posts_num'] = strip_tags($new['posts_num']);
        $instance['posts_cat_id'] = strip_tags($new['posts_cat_id']);
        $instance['posts_orderby'] = strip_tags($new['posts_orderby']);
        $instance['posts_time'] = strip_tags($new['posts_time']);
        return $instance;
	}

/*  Widget form
/* ------------------------------------ */
	public function form($instance) {
		// Default widget settings
		$defaults = array(
			'title' 			 => '',
            'title_font_size'    =>'14px',   
		  // Posts
			'posts_thumb' 		 => 1,            
                'posts_thumb_width' 		 => '100',
                'posts_thumb_hight' 		 => '100', 
             'posts_title' 	 => 1,
                'word_posts_title' => '5',
            'posts_content' 	 => 1,
                'word_posts_content' => '10',
			'posts_num' 		 => '4',            
			'posts_cat_id' 		 => '0',
			'posts_orderby' 	 => 'date',
            'show_date'		     => 1,
            'show_author'        => 1,
            'show_comments'      => 1,
            'show_read_more'     => 1,
			'posts_time' 		 => '0',
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
?>
	
	<div class="responsive_posts-options-posts">
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title :', 'responsive_posts'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($instance["title"]); ?>" />
		</p>  
        <p>
			<label for="<?php echo $this->get_field_id('title_font_size'); ?>"><?php _e('Title Font Size :', 'responsive_posts'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title_font_size'); ?>" name="<?php echo $this->get_field_name('title_font_size'); ?>" type="text" value="<?php echo esc_attr($instance["title_font_size"]); ?>" />
		</p>    
        <p>
			<label for="<?php echo $this->get_field_id('title_url'); ?>"><?php _e('Title URL :', 'responsive_posts'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title_url'); ?>" name="<?php echo $this->get_field_name('title_url'); ?>" type="text" value="<?php echo esc_attr($instance["title_url"]); ?>"  placeholder="<?php echo esc_attr( 'http://' ); ?>" />
		</p>  
        <p>
			<input type="checkbox" class="checkbox checkboxcontent" id="<?php echo $this->get_field_id('posts_title'); ?>" name="<?php echo $this->get_field_name('posts_title'); ?>" <?php checked( (bool) $instance["posts_title"], true ); ?>>
			<label for="<?php echo $this->get_field_id('posts_title'); ?>"><?php _e('Show Posts Title:', 'responsive_posts'); ?></label>
		</p>
        <p style="padding-left: 20px;" class="content_show_box">
			<label style="width: 55%; display: inline-block;" for="<?php echo $this->get_field_id("word_posts_title"); ?>"><?php _e('Words to show:', 'responsive_posts'); ?></label>
			<input style="width:20%;" id="<?php echo $this->get_field_id("word_posts_title"); ?>" name="<?php echo $this->get_field_name("word_posts_title"); ?>" type="text" value="<?php echo absint($instance["word_posts_title"]); ?>" size='3' />
		</p>
		<p>
			<input type="checkbox" class="checkbox checkboxcontent" id="<?php echo $this->get_field_id('posts_content'); ?>" name="<?php echo $this->get_field_name('posts_content'); ?>" <?php checked( (bool) $instance["posts_content"], true ); ?>>
			<label for="<?php echo $this->get_field_id('posts_content'); ?>"><?php _e('Posts Content:', 'responsive_posts'); ?></label>
		</p>
        <p style="padding-left: 20px;" class="content_show_box">
			<label style="width: 55%; display: inline-block;" for="<?php echo $this->get_field_id("word_posts_content"); ?>"><?php _e('Words to show:', 'responsive_posts'); ?></label>
			<input style="width:20%;" id="<?php echo $this->get_field_id("word_posts_content"); ?>" name="<?php echo $this->get_field_name("word_posts_content"); ?>" type="text" value="<?php echo absint($instance["word_posts_content"]); ?>" size='3' />
		</p>		
		<p>
			<input type="checkbox" class="checkbox checkimg" id="<?php echo $this->get_field_id('posts_thumb'); ?>" name="<?php echo $this->get_field_name('posts_thumb'); ?>" <?php checked( (bool) $instance["posts_thumb"], true ); ?>>
			<label for="<?php echo $this->get_field_id('posts_thumb'); ?>"><?php _e('Show thumbnails:', 'responsive_posts'); ?></label>
		</p>        
        <p style="padding-left: 20px;" class="posts_thumb_width">
			<label style="width: 55%; display: inline-block;" for="<?php echo $this->get_field_id("posts_thumb_width"); ?>"><?php _e('Posts thumb width:', 'responsive_posts'); ?></label>
			<input style="width:20%;" id="<?php echo $this->get_field_id("posts_thumb_width"); ?>" name="<?php echo $this->get_field_name("posts_thumb_width"); ?>" type="text" value="<?php echo absint($instance["posts_thumb_width"]); ?>" size='3' />
		</p>
         <p style="padding-left: 20px;" class="posts_thumb_hight">
			<label style="width: 55%; display: inline-block;" for="<?php echo $this->get_field_id("posts_thumb_hight"); ?>"><?php _e('Posts thumb hight:', 'responsive_posts'); ?></label>
			<input style="width:20%;" id="<?php echo $this->get_field_id("posts_thumb_hight"); ?>" name="<?php echo $this->get_field_name("posts_thumb_hight"); ?>" type="text" value="<?php echo absint($instance["posts_thumb_hight"]); ?>" size='3' />
		</p>
        <p class="img_show_box">
			<label style="width: 55%; display: inline-block;" for="<?php echo $this->get_field_id("posts_num"); ?>"><?php _e('Items to show:', 'responsive_posts'); ?></label>
			<input style="width:20%;" id="<?php echo $this->get_field_id("posts_num"); ?>" name="<?php echo $this->get_field_name("posts_num"); ?>" type="text" value="<?php echo absint($instance["posts_num"]); ?>" size='3' />
		</p>         
       		
		<p>
			<label style="width: 100%; display: inline-block;" for="<?php echo $this->get_field_id("posts_cat_id"); ?>"><?php _e('Category:', 'responsive_posts'); ?></label>
			<?php wp_dropdown_categories( array( 'name' => $this->get_field_name("posts_cat_id"), 'selected' => $instance["posts_cat_id"], 'show_option_all' => 'All', 'show_count' => true ) ); ?>		
		</p>
		<p style="padding-top: 0.3em;">
			<label style="width: 100%; display: inline-block;" for="<?php echo $this->get_field_id("posts_orderby"); ?>"><?php _e('Order by:', 'responsive_posts'); ?></label>
			<select style="width: 100%;" id="<?php echo $this->get_field_id("posts_orderby"); ?>" name="<?php echo $this->get_field_name("posts_orderby"); ?>">
			  <option value="date"<?php selected( $instance["posts_orderby"], "date" ); ?>><?php _e('Most recent/Date', 'responsive_posts'); ?></option>
			  <option value="comment_count"<?php selected( $instance["posts_orderby"], "comment_count" ); ?>><?php _e('Most commented', 'responsive_posts'); ?></option>
			  <option value="rand"<?php selected( $instance["posts_orderby"], "rand" ); ?>><?php _e('Random', 'responsive_posts'); ?></option>
			</select>	
		</p>
		<p style="padding-top: 0.3em;">
			<label style="width: 100%; display: inline-block;" for="<?php echo $this->get_field_id("posts_time"); ?>"><?php _e('Posts from:', 'responsive_posts'); ?></label>
			<select style="width: 100%;" id="<?php echo $this->get_field_id("posts_time"); ?>" name="<?php echo $this->get_field_name("posts_time"); ?>">
			  <option value="0"<?php selected( $instance["posts_time"], "0" ); ?>><?php _e('All time', 'responsive_posts'); ?></option>
			  <option value="1 year ago"<?php selected( $instance["posts_time"], "1 year ago" ); ?>><?php _e('This year', 'responsive_posts'); ?></option>
			  <option value="1 month ago"<?php selected( $instance["posts_time"], "1 month ago" ); ?>><?php _e('This month', 'responsive_posts'); ?></option>
			  <option value="1 week ago"<?php selected( $instance["posts_time"], "1 week ago" ); ?>><?php _e('This week', 'responsive_posts'); ?></option>
			  <option value="1 day ago"<?php selected( $instance["posts_time"], "1 day ago" ); ?>><?php _e('Past 24 hours', 'responsive_posts'); ?></option>
			</select>	
		</p>
		<p>
			<input type="checkbox" class="checkbox show_read_more" id="<?php echo $this->get_field_id('show_read_more'); ?>" name="<?php echo $this->get_field_name('show_read_more'); ?>" <?php checked( (bool) $instance["show_read_more"], true ); ?>>
			<label for="<?php echo $this->get_field_id('show_read_more'); ?>"><?php _e('Show read more:', 'responsive_posts'); ?></label>
		</p>   
        <h4>Post Meta:</h4>
		<p>
			<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_date'); ?>" name="<?php echo $this->get_field_name('show_date'); ?>" <?php checked( (bool) $instance["show_date"], true ); ?>>
			<label for="<?php echo $this->get_field_id('show_date'); ?>"><?php _e('Show Date:', 'responsive_posts'); ?></label>
		</p>
        <p>
			<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_author'); ?>" name="<?php echo $this->get_field_name('show_author'); ?>" <?php checked( (bool) $instance["show_author"], true ); ?>>
			<label for="<?php echo $this->get_field_id('show_author'); ?>"><?php _e('Show author:', 'responsive_posts'); ?></label>
		</p>
        <p>
			<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_comments'); ?>" name="<?php echo $this->get_field_name('show_comments'); ?>" <?php checked( (bool) $instance["show_comments"], true ); ?>>
			<label for="<?php echo $this->get_field_id('show_comments'); ?>"><?php _e('Show comments:', 'responsive_posts'); ?></label>
		</p>
	</div>
<?php
    }
}