<?php
/**
 * @package Media-Instagram
 * @version 1.1
 */
/*
Plugin Name: Wordpress Media from Instagram
Plugin URI: http://www.eggemplo.com
Description: Add media images from Instagram
Author: eggemplo
Version: 1.1
Author URI: http://www.eggemplo.com
*/

define( 'MEDIAINSTAGRAM_DOMAIN', 'mediainstagram' );

define( 'MEDIAINSTAGRAM_FILE', __FILE__ );

if ( !defined( 'MEDIAINSTAGRAM_CORE_DIR' ) ) {
	define( 'MEDIAINSTAGRAM_CORE_DIR', WP_PLUGIN_DIR . '/mediainstagram' );
}

class MediaInstagram_Plugin {
	
	public static function init() {
		add_action( 'init', array( __CLASS__, 'wp_init' ) );
	}
	
	public static function wp_init () {
		// Add a new submenu
		add_action('admin_menu', array(__CLASS__, 'mediainstagram_menu'));
		
		wp_enqueue_script( 'media-instagram', plugins_url( '/js/media-instagram.js', __FILE__ ) );
		wp_enqueue_style('media-instagram', plugins_url( '/css/instagram.css', __FILE__ ) );
		
	}


	public static function mediainstagram_menu() {
		$my_page = add_media_page(
				'Instagram settings',
				'Instagram Settings',
				'administrator',
				'mediainstagramsettings',
				array(__CLASS__,'mediainstagram_page_setting' ));
	
		$my_page = add_media_page(
				'Media Instagram',
				'Media Instagram',
				'administrator',
				'mediainstagram',
				array(__CLASS__,'mediainstagram_page' ));
	
	}
	
	/*
	public static function admin_enqueue_scripts() {
		wp_enqueue_media();
		wp_enqueue_style('thickbox');
		wp_enqueue_script('thickbox');
		wp_enqueue_script( 'custom-header' );
	
	
	}
	*/
	
	public static function mediainstagram_page() {
		
		//2 - Include the php class
		if ( !class_exists("instagramPhp") )
			include_once('API/instagram.php');
		
		//3 - Instanciate
		$username = get_option("medinstagram-user");
		$access_token = get_option("medinstagram-token");
		echo "<h2>" . __("Media Instagram", MEDIAINSTAGRAM_DOMAIN) . "</h2>";
		
		if( !empty($username) && !empty($access_token) ){
			$isg = new instagramPhp($username,$access_token); //instanciates the class with the parameters
			$shots = $isg->getUserMedia(); //Get the shots from instagram
		} else {
			echo 'Please update your settings to provide a valid username and access token';
		}
		
		//4 - Display
		
		if(!empty($shots)){ 
			echo $isg->simpleDisplayForm($shots);
				
			echo '<form action="#" method="post" id="instagram_upload_form" >';
			
			submit_button("Upload to Media", "primary", "upload"); 
			
			echo '</form>';	

			$i = 0;
			foreach ( $shots->data as $shot) {
				?>
				<script type="text/javascript">
					instagram_list_images[<?php echo $i;?>] = new Array();
					instagram_list_images[<?php echo $i;?>]['src'] = "<?php echo $shot->{'images'}->{'standard_resolution'}->{'url'}; ?>";
					instagram_list_images[<?php echo $i;?>]['name'] = "<?php echo $shot->{'id'}; ?>.jpg";
					instagram_list_images[<?php echo $i;?>]['caption'] = "<?php echo $shot->{'caption'}->{'text'} ?>";
			    </script>
				<?php
				$i ++;
			} 	
				
		}
		 
		if ( isset( $_POST['upload'] ) ) {
			$imagenes = $_POST['instagramimage'];
			$names = $_POST['instagramimagename'];
			$captions = $_POST['instagramimagecaption'];
			
			$i = 0;
			foreach ($imagenes as $imagen) {
				self::uploadImage($imagen, $names[$i], $captions[$i]);
				$i ++;
			}
		}	
				
	}
	
	public static function mediainstagram_page_setting() {
	?>
		<div class="wrap">
		<h2><?php echo __( 'Media Instagram settings', MEDIAINSTAGRAM_DOMAIN ); ?></h2>
		<?php 
		if ( isset( $_POST['submit'] ) ) {
		
			add_option( 'medinstagram-user', $_POST['medinstagram-user'] ); 
			update_option( 'medinstagram-user', $_POST['medinstagram-user'] );

			add_option( 'medinstagram-token', $_POST['medinstagram-token'] ); 
			update_option( 'medinstagram-token', $_POST['medinstagram-token'] );
			
		} 

		?>
		<form method="post" action="">
		    <table class="form-table">
		        <tr valign="top">
		        <th scope="row"><?php echo __( 'Username:', MEDIAINSTAGRAM_DOMAIN ); ?></th>
		        <td>
		        	<input type="text" name="medinstagram-user" value="<?php echo get_option('medinstagram-user'); ?>" />
		        	<p class="description"><?php echo __( '', MEDIAINSTAGRAM_DOMAIN ); ?></p>
		        </td>
		        </tr>
		         
		        <tr valign="top">
		        <th scope="row"><?php echo __( 'Access token:', MEDIAINSTAGRAM_DOMAIN ); ?></th>
		        <td><input type="text" name="medinstagram-token" value="<?php echo get_option('medinstagram-token'); ?>" /></td>
		        </tr>
		    </table>
		    
		    <?php submit_button("Save"); ?>
		    
		    <?php settings_fields( 'medinstagram-settings' ); ?>
		    
		</form>
		
		</div>
		<?php 
	}
	
	function uploadImage($url, $name, $caption) {
	$image_path = $url;
		//$image_path ="http://eofdreams.com/data_images/dreams/dog/dog-01.jpg";
		//$filename = "dog-01.jpg";
		$filename = $name;
		
		$uploaddir = wp_upload_dir();
		$uploadfile = $uploaddir['path'] . '/' . $filename;
		
		$contents= file_get_contents($image_path);
		$savefile = fopen($uploadfile, 'w');
		fwrite($savefile, $contents);
		fclose($savefile);
		
		
		$wp_filetype = wp_check_filetype(basename($filename), null );
		
		
		$attachment = array(
				'post_mime_type' => $wp_filetype['type'],
				'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
				'post_content'   => $caption,
				'post_status'    => 'inherit'
		);
		
		$attach_id = wp_insert_attachment( $attachment, $uploadfile );
		
		$imagenew = get_post( $attach_id );
		$fullsizepath = get_attached_file( $imagenew->ID );
		$attach_data = wp_generate_attachment_metadata( $attach_id, $fullsizepath );
		wp_update_attachment_metadata( $attach_id, $attach_data );

}
	
}

MediaInstagram_Plugin::init();