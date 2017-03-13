<?php
/*
Plugin Name: Media File Renamer
Plugin URI: http://www.meow.fr
Description: Auto-rename the files when titles are modified and update and the references (links). Manual Rename is a Pro option. Please read the description.
Version: 2.7.2
Author: Jordy Meow
Author URI: http://www.meow.fr
Text Domain: media-file-renamer
Domain Path: /languages

Dual licensed under the MIT and GPL licenses:
http://www.opensource.org/licenses/mit-license.php
http://www.gnu.org/licenses/gpl.html

Originally developed for two of my websites:
- Totoro Times (http://www.totorotimes.com)
- Haikyo (http://www.haikyo.org)
*/

class Meow_MediaFileRenamer {

	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
		add_action( 'plugins_loaded', array( $this, 'init_actions' ) );
	}

	function init() {
		require( 'meow_footer.php' );
		load_plugin_textdomain( 'media-file-renamer', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		add_action( 'admin_head', array( $this, 'admin_head' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'wp_ajax_mfrh_rename_media', array( $this, 'wp_ajax_mfrh_rename_media' ) );
		add_filter( 'media_send_to_editor', array( $this, 'media_send_to_editor' ), 20, 3 );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'edit_attachment', array( $this, 'edit_attachment' ) );
		add_action( 'add_attachment', array( $this, 'edit_attachment' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_rename_metabox' ) );
		add_filter( 'attachment_fields_to_save', array( $this, 'attachment_save' ), 20, 2 );
		add_action( 'save_post', array( $this, 'save_post' ) );

		// Column for Media Library
		$auto_rename = $this->getoption( 'auto_rename', 'mfrh_basics', 'media_title' );
		if ( $auto_rename != 'none' ) {
			add_filter( 'manage_media_columns', array( $this, 'add_media_columns' ) );
			add_action( 'manage_media_custom_column', array( $this, 'manage_media_custom_column' ), 10, 2 );
		}

		// Media Library
		add_filter( 'views_upload', array( $this, 'views_upload' ) );
		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
	}

	/**
	 *
	 * MEDIA LIBRARY FILTER
	 *
	 */

	function pre_get_posts ( $query ) {
		if ( !empty( $_GET['to_rename'] ) && $_GET['to_rename'] == 1 ) {
			$query->query_vars['meta_key'] = '_require_file_renaming';
			$query->query_vars['meta_value'] = true;
		}
		return $query;
	}

	function views_upload( $views ) {
		$this->file_counter( $flagged, $total );
		if ( !empty( $_GET['to_rename'] ) && $_GET['to_rename'] == 1 ) {
			if ( isset( $views['all'] ) )
				$views['all'] = str_replace( "current", "", $views['all'] );
			$views['to_rename'] = sprintf("<a class='current' href='upload.php?to_rename=1'>%s</a> (%d)", __("Rename", 'media-file-renamer'), $flagged);
		}
		else {
			$views['to_rename'] = sprintf("<a href='upload.php?to_rename=1'>%s</a> (%d)", __("Rename", 'media-file-renamer'), $flagged);
		}
			return $views;
	}

	/**
	 *
	 * ERROR/INFO MESSAGE HANDLING
	 *
	 */

	function admin_notices() {
		$screen = get_current_screen();
		if ( ( $screen->base == 'post' && $screen->post_type == 'attachment' ) ||
			( $screen->base == 'media' && isset( $_GET['attachment_id'] ) ) ) {
			$attachmentId = isset( $_GET['post'] ) ? $_GET['post'] : $_GET['attachment_id'];
			if ( $this->check_attachment( $attachmentId, $output ) ) {
				if ( $output['desired_filename_exists'] ) {
					echo '<div class="error"><p>
						The file ' . $output['desired_filename'] . ' already exists. Please give a new title for this media.
					</p></div>';
				}
			}
			if ( $this->wpml_media_is_installed() && !$this->is_real_media( $attachmentId ) ) {
				echo '<div class="error"><p>
					This attachment seems to be a virtual copy (or translation). Media File Renamer will not make any modification from here.
				</p></div>';
			}
		}
	}

	/**
	 *
	 * 'RENAME' LINK
	 *
	 */

	function add_media_columns($columns) {
			$columns['mfrh_column'] = __( 'Rename', 'media-file-renamer' );
			return $columns;
	}

	function manage_media_custom_column( $column_name, $id ) {
		$paged = isset( $_GET['paged'] ) ? ( '&paged=' . $_GET['paged'] ) : "";
		if ( $column_name == 'mfrh_column' ) {
			$check = $this->check_attachment( $id, $output );
			if ( $check )
				$this->generate_explanation( $output );
			else if ( $output['manual'] ) {
				echo "<span title='" . __( 'Manually renamed.', 'media-file-renamer' ) . "' style='font-size: 24px; color: #36B15C;' class='dashicons dashicons-yes'></span>";
				$page = isset( $_GET['page'] ) ? ( '&page=' . $_GET['page'] ) : "";
				echo "<a title='" . __( 'Locked to manual only. Click to unlock it.', 'media-file-renamer' ) . "' href='?" . $page . "&mfrh_unlock=" . $id . $paged . "'><span style='color: #36B15C; font-size: 20px; margin-top: 0px;' class='dashicons dashicons-lock'></span></a>";
			}
			else {
				echo "<span title='" . __( 'Automatically renamed.', 'media-file-renamer' ) . "'style='font-size: 24px; color: #36B15C;' class='dashicons dashicons-yes'></span>";
				$page = isset( $_GET['page'] ) ? ( '&page=' . $_GET['page'] ) : "";
				echo "<a title='" . __( 'Click to lock it to manual only.', 'media-file-renamer' ) . "' href='?" . $page . "&mfrh_lock=" . $id . $paged . "'><span style='font-size: 16px; margin-top: 1px;' class='dashicons dashicons-unlock'></span></a>";
			}
		}
	}

	function admin_head() {
		if ( !empty( $_GET['mfrh_rename'] ) ) {
			$mfrh_rename = $_GET['mfrh_rename'];
			$this->rename_media( get_post( $mfrh_rename, ARRAY_A ), null );
			$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'mfrh_rename' ), $_SERVER['REQUEST_URI'] );
		}
		if ( !empty( $_GET['mfrh_unlock'] ) ) {
			$mfrh_unlock = $_GET['mfrh_unlock'];
			delete_post_meta( $mfrh_unlock, '_manual_file_renaming' );
			$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'mfrh_rename' ), $_SERVER['REQUEST_URI'] );
		}
		if ( !empty( $_GET['mfrh_lock'] ) ) {
			$mfrh_lock = $_GET['mfrh_lock'];
			add_post_meta( $mfrh_lock, '_manual_file_renaming', true, true );
			$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'mfrh_rename' ), $_SERVER['REQUEST_URI'] );
		}

		?>
		<script type="text/javascript" >

			var current;
			var ids = [];

			function mfrh_process_next() {
				var data = { action: 'mfrh_rename_media', subaction: 'renameMediaId', id: ids[current - 1] };
				jQuery('#mfrh_progression').text(current + "/" + ids.length);
				jQuery.post(ajaxurl, data, function (response) {
					if (++current <= ids.length) {
						mfrh_process_next();
					}
					else {
						jQuery('#mfrh_progression').html("<?php echo __( "Done. Please <a href='javascript:history.go(0)'>refresh</a> this page.", 'media-file-renamer' ); ?>");
					}
				});
			}

			function mfrh_rename_media(all) {
				current = 1;
				ids = [];
				var data = { action: 'mfrh_rename_media', subaction: 'getMediaIds', all: all ? '1' : '0' };
				jQuery('#mfrh_progression').text("<?php echo __( "Please wait...", 'media-file-renamer' ); ?>");
				jQuery.post(ajaxurl, data, function (response) {
					reply = jQuery.parseJSON(response);
					ids = reply.ids;
					jQuery('#mfrh_progression').html(current + "/" + ids.length);
					mfrh_process_next();
				});
			}
		</script>
		<?php
	}

	/**
	 *
	 * BULK MEDIA RENAME PAGE
	 *
	 */

	 function wp_ajax_mfrh_rename_media() {
		$subaction = $_POST['subaction'];
		if ( $subaction == 'getMediaIds' ) {
			$all = intval( $_POST['all'] );
			$ids = array();
			$total = 0;
			global $wpdb;
			$postids = $wpdb->get_col( "SELECT p.ID FROM $wpdb->posts p WHERE post_status = 'inherit' AND post_type = 'attachment'" );
			foreach ( $postids as $id ) {
				if ($all)
					array_push( $ids, $id );
				else if ( get_post_meta( $id, '_require_file_renaming', true ) )
					array_push( $ids, $id );
				$total++;
			}
			$reply = array();
			$reply['ids'] = $ids;
			$reply['total'] = $total;
			echo json_encode( $reply );
			die;
		}
		else if ( $subaction == 'renameMediaId' ) {
			$id = intval( $_POST['id'] );
			$this->rename_media( get_post( $id, ARRAY_A ), null );
			echo 1;
			die();
		}
		echo 0;
		die();
	}

	function admin_menu() {
		$auto_rename = $this->getoption( 'auto_rename', 'mfrh_basics', 'media_title' );
		if ( $auto_rename != 'none' ) {
			add_media_page( 'Media File Renamer', __( 'File Renamer', 'media-file-renamer' ), 'manage_options', 'rename_media_files', array( $this, 'rename_media_files' ) );
		}
		add_options_page( 'Media File Renamer', 'File Renamer', 'manage_options', 'mfrh_settings', array( $this, 'settings_page' ) );
	}

	function wpml_media_is_installed() {
		return defined( 'WPML_MEDIA_VERSION' );
	}

	// To avoid issue with WPML Media for instance
	function is_real_media( $id ) {
		if ( $this->wpml_media_is_installed() ) {
			global $sitepress;
			$language = $sitepress->get_default_language( $id );
			return icl_object_id( $id, 'attachment', true, $language ) == $id;
		}
		return true;
	}

	function file_counter( &$flagged, &$total, $force = false ) {
		global $wpdb;
		$postids = $wpdb->get_col( "SELECT p.ID FROM $wpdb->posts p WHERE post_status = 'inherit'
				AND post_type = 'attachment'" );
		static $calculated = false;
		static $sflagged = 0;
		static $stotal = 0;
		if ( !$calculated || $force ) {
			$stotal = 0;
			$sflagged = 0;
			foreach ( $postids as $id ) {
				$require_file_renaming = get_post_meta( $id, '_require_file_renaming', true );
				$stotal++;
				if ( $require_file_renaming )
					$sflagged++;
			}
		}
		$calculated = true;
		$flagged = $sflagged;
		$total = $stotal;
	}


	function is_header_image( $id ) {
		static $headers = false;
		if ( $headers == false ) {
			global $wpdb;
			$headers = $wpdb->get_col( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attachment_is_custom_header'" );
		}
		return in_array( $id, $headers );
	}

	function generate_unique_filename( $actual, $dirname, $filename, $counter = null ) {
		$new_filename = $filename;
		if ( !is_null( $counter ) ) {
			$whereisdot = strrpos( $new_filename, '.' );
			$new_filename = substr( $new_filename, 0, $whereisdot ) . '-' . $counter
				. '.' . substr( $new_filename, $whereisdot + 1 );
		}
		if ( $actual == $new_filename )
			return false;
		if ( file_exists( $dirname . "/" . $new_filename ) )
			return $this->generate_unique_filename( $actual, $dirname, $filename,
				is_null( $counter ) ? 2 : $counter + 1 );
		return $new_filename;
	}

	function get_post_from_media( $id ) {
		global $wpdb;
		$postid = $wpdb->get_var( $wpdb->prepare( "
			SELECT post_parent p
			FROM $wpdb->posts p
			WHERE ID = %d", $id ),
			0, 0 );
		if ( empty( $postid ) )
			return null;
		return get_post( $postid, OBJECT );
	}

	// Return false if everything is fine, otherwise return true with an output.
	function check_attachment( $id, &$output = array() ) {
		$auto_rename = $this->getoption( 'auto_rename', 'mfrh_basics', 'media_title' );
		if ( $auto_rename === 'none') {
			delete_post_meta( $id, '_require_file_renaming' );
			return false;
		}
		if ( get_post_meta( $id, '_manual_file_renaming', true ) ) {
			$output['manual'] = true;
			return false;
		}

		// Skip header images
		if ( $this->is_header_image( $id ) ) {
			delete_post_meta( $id, '_require_file_renaming' );
			return false;
		}

		// Get information
		$post = get_post( $id, ARRAY_A );
		$base_title = $post['post_title'];
		if ( $auto_rename == 'post_title' ) {
			$attachedpost = $this->get_post_from_media( $post['ID'] );
			if ( is_null( $attachedpost ) )
				return false;
			$base_title = $attachedpost->post_title;
		}
		$desired_filename = $this->new_filename( $post, $base_title );
		$old_filepath = get_attached_file( $post['ID'] );
		$path_parts = pathinfo( $old_filepath );

		// Dead file, let's forget it!
		if ( !file_exists( $old_filepath ) ) {
			delete_post_meta( $id, '_require_file_renaming' );
			return false;
		}

		// Filename is equal to sanitized title
		if ( $desired_filename == $path_parts['basename'] ) {
			delete_post_meta( $id, '_require_file_renaming' );
			return false;
		}

		// Send info to the requester function
		$output['post_id'] = $post['ID'];
		$output['post_title'] = $post['post_title'];
		$output['current_filename'] = $path_parts['basename'];
		$output['desired_filename'] = $desired_filename;
		$output['desired_filename_exists'] = false;
		if ( file_exists( $path_parts['dirname'] . "/" . $desired_filename ) ) {
			$is_numbered = $this->getoption( 'numbered_files', 'mfrh_basics', false );
			if ( $this->is_pro() && $is_numbered ) {
				$output['desired_filename'] = $this->generate_unique_filename( $path_parts['basename'],
					$path_parts['dirname'], $desired_filename );
				if ( $output['desired_filename'] == false ) {
					delete_post_meta( $id, '_require_file_renaming' );
					return false;
				}
				add_post_meta( $post['ID'], '_numbered_filename', $output['desired_filename'], true );
			}
			else {
				$output['desired_filename_exists'] = true;
				if ( strtolower( $output['current_filename'] ) == strtolower( $output['desired_filename'] ) ) {
					// If Windows, let's be careful about the fact that case doesn't affect files
					delete_post_meta( $post['ID'], '_require_file_renaming' );
					return false;
				}
			}
		}

		// It seems it could be renamed :)
		if ( !get_post_meta( $post['ID'], '_require_file_renaming' ) ) {
			add_post_meta( $post['ID'], '_require_file_renaming', true, true );
		}
		return true;
	}

	function check_text() {
		$issues = array();
		global $wpdb;
		$ids = $wpdb->get_col( "
			SELECT p.ID
			FROM $wpdb->posts p
			WHERE post_status = 'inherit'
			AND post_type = 'attachment'
		" );
		foreach ( $ids as $id )
			if ( $this->check_attachment( $id, $output ) )
				array_push( $issues, $output );
		return $issues;
	}

	function generate_explanation( $file ) {
		if ( $file['post_title'] == "" ) {
			echo " <a class='button-primary' href='post.php?post=" . $file['post_id'] . "&action=edit'>" . __( 'Edit Media', 'media-file-renamer' ) . "</a><br /><small>" . __( 'This title cannot be used for a filename.', 'media-file-renamer' ) . "</small>";
		}
		else if ( $file['desired_filename_exists'] ) {
			echo "<a class='button-primary' href='post.php?post=" . $file['post_id'] . "&action=edit'>" . __( 'Edit Media', 'media-file-renamer' ) . "</a><br /><small>" . __( 'The ideal filename already exists. If you would like to use a count and rename it, enable the <b>Numbered Files</b> option in the plugin settings.', 'media-file-renamer' ) . "</small>";
		}
		else {
			$page = isset( $_GET['page'] ) ? ( '&page=' . $_GET['page'] ) : "";
			$mfrh_scancheck = ( isset( $_GET ) && isset( $_GET['mfrh_scancheck'] ) ) ? '&mfrh_scancheck' : '';
			$mfrh_to_rename = ( !empty( $_GET['to_rename'] ) && $_GET['to_rename'] == 1 ) ? '&to_rename=1' : '';
			$modify_url = "post.php?post=" . $file['post_id'] . "&action=edit";
			$page = isset( $_GET['page'] ) ? ( '&page=' . $_GET['page'] ) : "";

			echo "<a class='button-primary' href='?" . $page . $mfrh_scancheck . $mfrh_to_rename . "&mfrh_rename=" . $file['post_id'] . "'>" . __( 'Auto-Rename', 'media-file-renamer' ) . "</a>";
			echo "<a title='" . __( 'Click to lock it to manual only.', 'media-file-renamer' ) . "' href='?" . $page . "&mfrh_lock=" . $file['post_id'] . "'><span style='font-size: 16px; margin-top: 5px;' class='dashicons dashicons-unlock'></span></a>";

			echo"<br /><small>" .
				sprintf( __( 'Rename to %s. You can also <a href="%s">edit this media</a>.', 'media-file-renamer' ), $file['desired_filename'], $modify_url ) . "</small>";
		}
	}

	function rename_media_files() {
		?>
		<div class='wrap'>
		<?php jordy_meow_donation(); ?>
		<div id="icon-upload" class="icon32"><br></div>
		<h1>Media File Renamer <?php by_jordy_meow(); ?></h1>

		<?php
		$checkFiles = null;
		if ( isset( $_GET ) && isset( $_GET['mfrh_scancheck'] ) )
			$checkFiles = $this->check_text();
		$this->file_counter( $flagged, $total, true );
		?>

		<div style='margin-top: 12px; background: #FFF; padding: 5px; border-radius: 4px; height: 28px; box-shadow: 0px 0px 6px #C2C2C2;'>
			<?php if ($flagged > 0) { ?>
				<a onclick='mfrh_rename_media(false)' id='mfrh_rename_dued_images' class='button-primary'>
					<?php echo sprintf( __( "Rename <span class='mfrh-flagged'>%d</span> flagged media", 'media-file-renamer' ), $flagged ); ?>
				</a>
			<?php } else { ?>
				<a id='mfrh_rename_dued_images' class='button-primary'>
					<?php echo sprintf( __( "Rename <span class='mfrh-flagged'>%d</span> flagged media", 'media-file-renamer' ), $flagged ); ?>
				</a>
			<?php } ?>

			<a onclick='mfrh_rename_media(true)' id='mfrh_rename_all_images' class='button'
				style='margin-left: 10px; margin-right: 10px'>
				<?php echo sprintf( __( "Unlock & Rename all %d media", 'media-file-renamer' ), $total ); ?>
			</a>
			<span id='mfrh_progression'></span>
		</div>

		<p>
			<b>There are <span class='mfrh-flagged' style='color: red;'><?php _e( $flagged ); ?></span> media files flagged for auto-renaming out of <?php _e( $total ); ?> in total.</b> Those are the files that couldn't be renamed on the fly when their names were updated. You can now rename those flagged media, or rename all of them (which will unlock them all and force their renaming). <span style='color: red;'>Please backup your WordPress upload folder and database before using these functions.</span>
		</p>

		<table class='wp-list-table widefat fixed media' style='margin-top: 15px;'>
			<thead>
				<tr><th><?php _e( 'Title', 'media-file-renamer' ); ?></th><th><?php _e( 'Current Filename', 'media-file-renamer' ); ?></th><th><?php _e( 'Desired Filename', 'media-file-renamer' ); ?></th><th><?php _e( 'Action', 'media-file-renamer' ); ?></th></tr>
			</thead>
			<tfoot>
				<tr><th><?php _e( 'Title', 'media-file-renamer' ); ?></th><th><?php _e( 'Current Filename', 'media-file-renamer' ); ?></th><th><?php _e( 'Desired Filename', 'media-file-renamer' ); ?></th><th><?php _e( 'Action', 'media-file-renamer' ); ?></th></tr>
			</tfoot>
			<tbody>
				<?php
					if ( $checkFiles != null ) {
						foreach ( $checkFiles as $file ) {
							echo "<tr><td><a href='post.php?post=" . $file['post_id'] . "&action=edit'>" . ( $file['post_title'] == "" ? "(no title)" : $file['post_title'] ) . "</a></td>"
								. "<td>" . $file['current_filename'] . "</td>"
								. "<td>" . $file['desired_filename'] . "</td>";
							echo "<td>";
							$this->generate_explanation( $file );
							echo "</td></tr>";
						}
					}
					else if ( isset( $_GET['mfrh_scancheck'] ) && ( $checkFiles == null || count( $checkFiles ) < 1 ) ) {
						?><tr><td colspan='4'><div style='width: 100%; margin-top: 15px; margin-bottom: 15px; text-align: center;'>
							<div style='margin-top: 15px;'><?php _e( 'There are no issues. Cool!<br />Let\'s go visit <a target="_blank" href=\'http://jordymeow.com\'>The Offbeat Guide of Japan</a> :)', 'media-file-renamer' ); ?></div>
						</div></td><?php
					}
					else if ( $checkFiles == null ) {
						?><tr><td colspan='4'><div style='width: 100%; text-align: center;'>
							<a class='button-primary' href="?page=rename_media_files&mfrh_scancheck" style='margin-top: 15px; margin-bottom: 15px; height: 35px; padding: 5px; width: 200px;'>
								<?php echo sprintf( __( "Scan All & Show Issues", 'media-file-renamer' ) ); ?>
							</a>
						</div></td><?php
					}
				?>
			</tbody>
		</table>
		</div>
		<?php
		jordy_meow_footer();
	}

	/**
	 *
	 * RENAME ON SAVE / PUBLISH
	 * Originally proposed by Ben Heller
	 * Added and modified by Jordy Meow
	 */

	function rename_media_on_publish ( $post_id ) {
		$onsave = $this->getoption( "rename_on_save", "mfrh_basics", false );
		$args = array( 'post_type' => 'attachment',
			'numberposts' => -1, 'post_status' =>'any', 'post_parent' => $post_id );
		$attachments = get_posts( $args );
		if ( $attachments ) {
			foreach ( $attachments as $attachment ) {
				$attachment = get_post( $attachment, ARRAY_A );
				$this->check_attachment( $attachment['ID'] );
				if ( $onsave ) {
					$this->rename_media( $attachment, $attachment, true );
				}
			}
		}
	}

	function save_post( $post_id ) {
		$status = get_post_status( $post_id );
		if ( !in_array( $status, array( 'publish', 'future' ) ) )
			return;
		$this->rename_media_on_publish( $post_id );
	}

	/**
	 *
	 * EDITOR
	 *
	 */

	function edit_attachment( $post_ID ) {
		$this->check_attachment( $post_ID, $output );
	}

	function media_send_to_editor( $html, $attachment_id, $attachment ) {
		$this->check_attachment( $attachment_id, $output );
		return $html;
	}

	function add_rename_metabox() {
		add_meta_box( 'mfrh_media', 'Filename', array( $this, 'attachment_fields' ), 'attachment', 'side', 'high' );
	}

	function attachment_fields( $post ) {
		$info = pathinfo( get_attached_file( $post->ID ) );
		$basename = $info['basename'];
		$is_manual = $this->getoption( 'manual_rename', 'mfrh_basics', false );
		echo '<input type="text" ' . ( $is_manual && $this->is_pro() ? '' : 'readonly' ) . ' class="widefat" name="mfrh_new_filename" value="' . $basename. '" />';
		if ( !$is_manual ) {
			echo '<p class="description">You need to enable <b>Manual Rename</b> in the plugin settings.</p>';
		}
		else if ( !$this->is_pro() ) {
			echo '<p class="description">This feature is for <a target="_blank" href="http://apps.meow.fr/media-file-renamer/">Pro users</a> only.</p>';
		}
		else {
			echo '<p class="description">You can rename the file manually.</p>';
		}
		return $post;
	}

	function attachment_save( $post, $attachment ) {
		$auto_rename = $this->getoption( 'auto_rename', 'mfrh_basics', 'media_title' );
		$info = pathinfo( get_attached_file( $post['ID'] ) );
		$basename = $info['basename'];
		$new = $post['mfrh_new_filename'];

		// The filename is being changed manually, let's force it through $new.
		if ( !empty( $new ) && $basename !== $new )
			return $this->rename_media( $post, $attachment, false, $new );

		$auto_rename = $this->getoption( 'auto_rename', 'mfrh_basics', 'media_title' );
		if ( $auto_rename == 'media_title' ) {
			// If the title was not changed, don't do anything.
			if ( get_the_title( $post['ID'] ) == $post['post_title'] )
				return $post;
			return $this->rename_media( $post, $attachment, false, null );
		}
		return $post;
	}

	function log_sql( $data, $antidata ) {
		if ( !$this->getoption( 'logsql', 'mfrh_basics', false ) || !$this->is_pro() )
			return;
		$fh = fopen( trailingslashit( WP_PLUGIN_DIR ) . 'media-file-renamer/mfrh_sql.log', 'a' );
		$fh_anti = fopen( trailingslashit( WP_PLUGIN_DIR ) . 'media-file-renamer/mfrh_sql_revert.log', 'a' );
		$date = date( "Y-m-d H:i:s" );
		fwrite( $fh, "{$data}\n" );
		fwrite( $fh_anti, "{$antidata}\n" );
		fclose( $fh );
		fclose( $fh_anti );
	}

	function log( $data, $inErrorLog = false ) {
		if ( $inErrorLog )
			error_log( $data );
		if ( !$this->getoption( 'log', 'mfrh_basics', false ) )
			return;
		$fh = fopen( trailingslashit( WP_PLUGIN_DIR ) . 'media-file-renamer/media-file-renamer.log', 'a' );
		$date = date( "Y-m-d H:i:s" );
		fwrite( $fh, "$date: {$data}\n" );
		fclose( $fh );
	}

	function is_pro() {
		$validated = get_transient( 'mfrh_validated' );
		if ( $validated ) {
			$serial = get_option( 'mfrh_pro_serial');
			return !empty( $serial );
		}
		$subscr_id = get_option( 'mfrh_pro_serial', "" );
		if ( !empty( $subscr_id ) )
			return $this->validate_pro( $this->getoption( "subscr_id", "mfrh_pro", array() ) );
		return false;
	}

	function validate_pro( $subscr_id ) {
		delete_option( 'mfrh_pro_serial', "" );
		delete_option( 'mfrh_pro_status', "" );
		set_transient( 'mfrh_validated', false, 0 );
		if ( empty( $subscr_id ) )
			return false;
		$response = wp_remote_post( 'http://apps.meow.fr/wp-json/meow/v1/auth', array(
			'body' => array( 'subscr_id' => $subscr_id, 'item' => 'media-file-renamer', 'url' => get_site_url() )
		) );
		$body = is_array( $response ) ? $response['body'] : null;
		$post = @json_decode( $body );
		if ( !$post || $post->code ) {
			$status = __( "There was an error while validating the serial.<br />Please contact <a target='_blank' href='http://apps.meow.fr/contact/'>Meow Apps</a> and mention the following log.<br /><br /><small>" . print_r( $response, true ) . "</small>" );
			update_option( 'mfrh_pro_status', $status );
			return false;
		}
		if ( !$post->success ) {
			if ( $post->message_code == "NO_SUBSCRIPTION" )
				$status = __( "Your serial does not seem right." );
			else if ( $post->message_code == "NOT_ACTIVE" )
				$status = __( "Your subscription is not active." );
			else if ( $post->message_code == "TOO_MANY_URLS" )
				$status = __( "Too many URLs are linked to your subscription." );
			else
				$status = "There is a problem with your subscription.";
			update_option( 'mfrh_pro_status', $status );
			return false;
		}
		set_transient( 'mfrh_validated', $subscr_id, 3600 * 24 * 100 );
		update_option( 'mfrh_pro_serial', $subscr_id );
		update_option( 'mfrh_pro_status', __( "Your subscription is enabled." ) );
		return true;
	}


	/**
	 *
	 * SETTINGS PAGE
	 *
	 */

	function settings_page() {
		global $mfrh_settings_api;
		echo '<div class="wrap">';
			jordy_meow_donation(true);
		echo "<div id='icon-options-general' class='icon32'><br></div><h1>Media File Renamer";
			by_jordy_meow();
			echo "</h1>";
			$mfrh_settings_api->show_navigation();
			$mfrh_settings_api->show_forms();
			echo '</div>';
		jordy_meow_footer();
	}

	function getoption( $option, $section, $default = '' ) {
			$options = get_option( $section );
			if ( isset( $options[$option] ) ) {
					if ( $options[$option] == "off" ) {
							return false;
					}
					if ( $options[$option] == "on" ) {
							return true;
					}
					return $options[$option];
			}
			return $default;
	}

	function setoption( $option, $section, $value ) {
			$options = get_option( $section );
			if ( empty( $options ) ) {
					$options = array();
			}
			$options[$option] = $value;
			update_option( $section, $options );
	}

	function admin_init() {
		require( 'mfrh_class.settings-api.php' );
		if ( isset( $_GET['reset'] ) ) {
			if ( file_exists( plugin_dir_path( __FILE__ ) . '/media-file-renamer.log' ) )
				unlink( plugin_dir_path( __FILE__ ) . '/media-file-renamer.log' );
			if ( file_exists( plugin_dir_path( __FILE__ ) . '/mfrh_sql.log' ) )
				unlink( plugin_dir_path( __FILE__ ) . '/mfrh_sql.log' );
			if ( file_exists( plugin_dir_path( __FILE__ ) . '/mfrh_sql_revert.log' ) )
				unlink( plugin_dir_path( __FILE__ ) . '/mfrh_sql_revert.log' );
		}

		// Default Auto-Generate
		$auto_rename = $this->getoption( 'auto_rename', 'mfrh_basics', null );
		if ( $auto_rename === null || $auto_rename === true )
			$this->setoption( 'auto_rename', 'mfrh_basics', 'media_title' );

		// Default Rename Slug
		$rename_slug = $this->getoption( 'rename_slug', 'mfrh_basics', null );
		if ( $rename_slug === null )
				$this->setoption( 'rename_slug', 'mfrh_basics', 'on' );

		// Default Rename GUID
		$rename_guid = $this->getoption( 'rename_guid', 'mfrh_basics', null );
		if ( $rename_guid === null )
				$this->setoption( 'rename_guid', 'mfrh_basics', 'on' );

		// Default Update Posts
		$update_posts = $this->getoption( 'update_posts', 'mfrh_basics', null );
		if ( $update_posts === null )
				$this->setoption( 'update_posts', 'mfrh_basics', 'on' );

		// Default Post Meta
		$update_postmeta = $this->getoption( 'update_postmeta', 'mfrh_basics', null );
		if ( $update_postmeta === null )
				$this->setoption( 'update_postmeta', 'mfrh_basics', 'on' );

		// Default UTF-8
		$update_postmeta = $this->getoption( 'utf8_filename', 'mfrh_basics', null );
		if ( $update_postmeta === null )
				$this->setoption( 'utf8_filename', 'mfrh_basics', 'off' );

		// Force Rename
		$force_rename = $this->getoption( 'force_rename', 'mfrh_basics', null );
		if ( $force_rename === null )
				$this->setoption( 'force_rename', 'mfrh_basics', 'off' );

		if ( isset( $_POST ) && isset( $_POST['mfrh_pro'] ) )
				$this->validate_pro( $_POST['mfrh_pro']['subscr_id'] );
		$pro_status = get_option( 'mfrh_pro_status', "Not Pro." );

		$sections = array(
			array(
				'id' => 'mfrh_basics',
				'title' => __( 'Basics', 'media-file-renamer' )
			),
			array(
				'id' => 'mfrh_pro',
				'title' => __( 'Pro', 'media-file-renamer' )
			)
		);
		$fields = array(
			'mfrh_basics' => array(
				array(
					'name' => 'auto_rename',
					'label' => __( 'Auto Rename', 'media-file-renamer' ),
					'desc' => __( 'If the plugin considers that it is too dangerous to rename the file directly at some point, it will be flagged internally as <b>to be renamed</b>. The list of those flagged files can be found in Media > File Renamer and they can be renamed from there.', 'media-file-renamer' ),
					'type' => 'radio',
					'default' => 'media_title',
					'options' => array(
						'none' => __( "None<br /><small>Disabled. Only manual renaming can be used.</small><br />", 'media-file-renamer' ),
						'media_title' => __( "Media Title<br /><small>The filename will be renamed automatically depending on the title of the media. <b>This is the recommended method.</b></small><br />", 'media-file-renamer' ),
						'post_title' => __( "Post Title (Pro)<br /><small>The filename will be renamed automatically depending on the title of the post the media <b>is attached to</b>.</small><br />", 'media-file-renamer' ),
					)
				), array(
						'name' => 'manual_rename',
						'label' => __( 'Manual Rename (Pro)', 'media-file-renamer' ),
						'desc' => __( 'Enable manual renaming in the Media edit screen.<br /><small>This feature is only for Pro users (check the Pro tab).</small>', 'media-file-renamer' ),
						'type' => 'checkbox',
						'default' => false
				), array(
						'name' => 'numbered_files',
						'label' => __( 'Numbered Files (Pro)', 'media-file-renamer' ),
						'desc' => __( 'Identical filenames will be allowed and a number will be added.<br /><small>This is useful if your titles the same ones for many of your images (myfile.jpg, myfile-2.jpg, myfile-3.jpg, etc).</small>', 'media-file-renamer' ),
						'type' => 'checkbox',
						'default' => false
				), array(
						'name' => 'side_updates',
						'label' => '',
						'desc' => __( '<h1>Side-Updates</h1><small>When the files are renamed, many links to them on your WordPress might be broken. By default, the plugin updates all the references in the posts. As the plugin evolves (thanks to the Pro version), more and more plugins/themes will be covered by those updates as we discover them together.</small>', 'media-file-renamer' ),
						'type' => 'html'
				), array(
						'name' => 'update_posts',
						'label' => __( 'Update Posts', 'media-file-renamer' ),
						'desc' => __( 'Update the references to the renamed files in the posts (pages and custom types included).', 'media-file-renamer' ),
						'type' => 'checkbox',
						'default' => false
				), array(
						'name' => 'update_postmeta',
						'label' => __( 'Update Post Meta', 'media-file-renamer' ),
						'desc' => __( 'Update the references in the posts metadata (including pages and custom types metadata).', 'media-file-renamer' ),
						'type' => 'checkbox',
						'default' => false
				), array(
						'name' => 'update_something',
						'label' => __( '', 'media-file-renamer' ),
						'desc' => __( '<i>Something is not updated when you rename a file? Please <a href="http://apps.meow.fr/contact/">contact me</a> and I will add support for it.</i>', 'media-file-renamer' ),
						'type' => 'html',
						'default' => false
				), array(
						'name' => 'rename_slug',
						'label' => __( 'Rename Slug', 'media-file-renamer' ),
						'desc' => __( 'The image slug will be renamed like the new filename.<br /><small>Better to keep this un-checked as the link might have been referenced somewhere else.</small>', 'media-file-renamer' ),
						'type' => 'checkbox',
						'default' => true
				), array(
						'name' => 'rename_guid',
						'label' => __( 'Rename GUID<br /><small>(aka "File name")</small>', 'media-file-renamer' ),
						'desc' => __( 'The GUID will be renamed like the new filename.<br /><small>Better to keep this un-checked. Have a look a the FAQ.</small>', 'media-file-renamer' ),
						'type' => 'checkbox',
						'default' => false
				), array(
						'name' => 'sync_alt',
						'label' => __( 'Alt. Text = Title (Pro)<br /><small>Sync Alternative Text</small>', 'media-file-renamer' ),
						'desc' => __( 'The Alternative Text will always be synchronized with the Title.<br /><small>Keep in mind that the HTML in your posts and pages will be however not modified, that is too dangerous!</small>', 'media-file-renamer' ),
						'type' => 'checkbox',
						'default' => false
				), array(
						'name' => 'advanced',
						'label' => '',
						'desc' => __( '<h1>Advanced</h1><small>If you are geeky this section might be more interesting for you. <b>Want to clear/reset the logs? Click <a href="?page=mfrh_settings&reset=true">here</a>.</b></small>', 'media-file-renamer' ),
						'type' => 'html'
				), array(
						'name' => 'rename_on_save',
						'label' => __( 'Rename On Save', 'media-file-renamer' ),
						'desc' => __( 'Attachments will be renamed automatically when published posts/pages are saved.<br /><small>You can change the names of your media while editing a post but that wouldn\'t let the plugin updates the HTML, of course. With this option, the plugin will check for any changes in the media names and will update your post right after you saved it.</small>', 'media-file-renamer' ),
						'type' => 'checkbox',
						'default' => false
				), array(
						'name' => 'utf8_filename',
						'label' => __( 'UTF-8 Filename (Pro)', 'media-file-renamer' ),
						'desc' => __( 'The plugin will be allowed to use non-ASCII characters in the filenames.<br /><small>This usually doesn\'t work well on Windows installs.</small>', 'media-file-renamer' ),
						'type' => 'checkbox',
						'default' => false
				), array(
						'name' => 'force_rename',
						'label' => __( 'Force Rename (Pro)', 'media-file-renamer' ),
						'desc' => __( 'Update the references to the file even if the file renaming itself was not successful.<br /><small>You might want to use that option if your install is broken and you are trying to link your Media to files for which the filenames has been altered (after a migration for exemple)</small>', 'media-file-renamer' ),
						'type' => 'checkbox',
						'default' => false
				), array(
						'name' => 'log',
						'label' => __( 'Logs', 'media-file-renamer' ),
						'desc' => __( 'Simple logging that explains which actions has been run. The file is <a target="_blank" href="' . plugins_url("media-file-renamer") . '/media-file-renamer.log">media-file-renamer.log</a>.', 'media-file-renamer' ),
						'type' => 'checkbox',
						'default' => false
				), array(
						'name' => 'logsql',
						'label' => __( 'SQL Logs (Pro)<br />+ Revert SQL', 'media-file-renamer' ),
						'desc' => __( 'The files <a target="_blank" href="' . plugins_url("media-file-renamer") . '/mfrh_sql.log">mfrh_sql.log</a> and <a target="_blank" href="' . plugins_url("media-file-renamer") . '/mfrh_sql_revert.log">mfrh_sql_revert.log</a> will be created and they will include the raw SQL queries which were run by the plugin. If there is an issue, the revert file can help you reverting the changes more easily. <br /><small>This feature is only for Pro users (check the Pro tab).</small>', 'media-file-renamer' ),
						'type' => 'checkbox',
						'default' => false
				)
			),
			'mfrh_pro' => array(
				array(
						'name' => 'pro',
						'label' => '',
						'desc' => __( sprintf( 'Status: %s', $pro_status ), 'media-file-renamer' ),
						'type' => 'html'
				),
				array(
						'name' => 'subscr_id',
						'label' => __( 'Serial', 'media-file-renamer' ),
						'desc' => __( '<br />Enter your serial or subscription ID here. If you don\'t have one yet, get one <a target="_blank" href="http://apps.meow.fr/media-file-renamer/">right here</a>.', 'media-file-renamer' ),
						'type' => 'text',
						'default' => ""
				),
			)
		);
		global $mfrh_settings_api;
		$mfrh_settings_api = new WeDevs_Settings_API;
		$mfrh_settings_api->set_sections( $sections );
		$mfrh_settings_api->set_fields( $fields );
		$mfrh_settings_api->admin_init();
	}

	/**
	 *
	 * THE FUNCTION THAT MAKES COFFEE, BROWNIES AND GIVE MASSAGES ALL AT THE SAME TIME WITH NO COMPLAIN
	 * Rename Files + Update Posts
	 *
	 */

	// NEW MEDIA FILE INFO (depending on the title of the media)
	function new_filename( $media, $title, $forceFilename = null ) {
		if ( $forceFilename )
			$forceFilename = preg_replace( '/\\.[^.\\s]{3,4}$/', '', trim( $forceFilename ) );
		$force = !empty( $forceFilename );
		$old_filepath = get_attached_file( $media['ID'] );
		$path_parts = pathinfo( $old_filepath );
		$old_filename = $path_parts['basename'];
		// This line is problematic during the further rename that exclude the extensions. Better to implement
		// this properly with thorough testing later.
		//$ext = str_replace( 'jpeg', 'jpg', $path_parts['extension'] ); // In case of a jpeg extension, rename it to jpg
		$ext = $path_parts['extension'];

		if ( $force )
			$sanitized_media_title = $forceFilename;
		else {
			$utf8_filename = $this->getoption( 'utf8_filename', 'mfrh_basics', null ) && $this->is_pro();
			$sanitized_media_title = $utf8_filename ? sanitize_file_name( $title ) :
				str_replace( "%", "-", sanitize_title( $title ) );
		}
		if ( empty( $sanitized_media_title ) )
			$sanitized_media_title = "empty";
		$sanitized_media_title = $sanitized_media_title . '.' . $ext;
		if ( !$forceFilename )
			$sanitized_media_title = apply_filters( 'mfrh_new_filename', $sanitized_media_title, $old_filename, $media );
		return $sanitized_media_title;
	}

	// Only replace the first occurence
	function str_replace( $needle, $replace, $haystack ) {
		$pos = strpos( $haystack, $needle );
		if ( $pos !== false ) {
		    $haystack = substr_replace( $haystack, $replace, $pos, strlen( $needle ) );
		}
		return $haystack;
	}

	function rename_media( $post, $attachment, $disableMediaLibraryMode = false, $forceFilename = null ) {
		$force = !empty( $forceFilename );
		$manual = get_post_meta( $post['ID'], '_manual_file_renaming', true );
		$require = get_post_meta( $post['ID'], '_require_file_renaming', false );
		$auto_rename = $this->getoption( 'auto_rename', 'mfrh_basics', 'media_title' );
		$numbered_filename = get_post_meta( $post['ID'], '_numbered_filename', true );
		if ( !empty( $numbered_filename ) ) {
			$this->log( "Numbered filename ($numbered_filename) is being injected." );
			$forceFilename = $numbered_filename;
			delete_post_meta( $post['ID'], '_numbered_filename' );
		}

			// MEDIA TITLE & FILE PARTS
		$meta = wp_get_attachment_metadata( $post['ID'] );
		$old_filepath = get_attached_file( $post['ID'] ); // '2011/01/whatever.jpeg'
		$path_parts = pathinfo( $old_filepath );
		$directory = $path_parts['dirname']; // '2011/01'
		$old_filename = $path_parts['basename']; // 'whatever.jpeg'
		$old_ext = $path_parts['extension'];

		// This line is problematic during the further rename that exclude the extensions. Better to implement
		// this properly with thorough testing later.
		//$ext = str_replace( 'jpeg', 'jpg', $path_parts['extension'] ); // In case of a jpeg extension, rename it to jpg
		$ext = $path_parts['extension'];

		$this->log( "** Rename Media: " . $old_filename );

		// Was renamed manually? Avoid renaming when title has been changed.
		if ( !$this->is_real_media( $post['ID'] ) ) {
			$this->log( "Attachment {$post['ID']} looks like a translation, better not to continue." );
			delete_post_meta( $post['ID'], '_require_file_renaming' );
			return $post;
		}

		// If this is being renamed based on the post the media is attached to.
		$base_new_title = $post['post_title'];
		if ( !$force && $auto_rename == 'post_title' ) {
			$linkedpost = $this->get_post_from_media( $post['ID'] );
			if ( empty( $linkedpost ) ) {
				$this->log( "Attachment {$post['ID']} is not linked to a post yet it seems." );
				delete_post_meta( $post['ID'], '_require_file_renaming' );
				return $post;
			}
			$base_new_title = $linkedpost->post_title;
		}

		// Empty post title when renaming using title? Let's not go further.
		if ( !$force && empty( $base_new_title ) ) {
			$this->log( "Title is empty, doesn't rename." );
			delete_post_meta( $post['ID'], '_require_file_renaming' );
			return $post;
		}

		// Is it a header image? Skip.
		if ( $this->is_header_image( $post['ID'] ) ) {
			$this->log( "Doesn't rename header image." );
			delete_post_meta( $post['ID'], '_require_file_renaming' );
			return $post;
		}
		if ( $manual && !$this->is_pro() ) {
			return $post;
		}

		delete_post_meta( $post['ID'], '_manual_file_renaming' );
		$sanitized_media_title = $this->new_filename( $post, $base_new_title, $forceFilename );
		$this->log( "New file should be: " . $sanitized_media_title );

		// Don't do anything if the media title didn't change or if it would turn to an empty string
		if ( $path_parts['basename'] == $sanitized_media_title ) {
			$this->log( "File seems renamed already." );
			delete_post_meta( $post['ID'], '_require_file_renaming' );
			return $post;
		}

		// MEDIA LIBRARY USAGE DETECTION
		// Detects if the user is using the Media Library or 'Add an Image' (while a post edit)
		// If it is not the Media Library, we don't rename, to avoid issues
		$media_library_mode = !isset( $attachment['image-size'] ) || $disableMediaLibraryMode;
		if ( !$media_library_mode ) {
			// This media requires renaming
			if ( !get_post_meta( $post['ID'], '_require_file_renaming' ) )
				add_post_meta( $post['ID'], '_require_file_renaming', true, true );
			$this->log( "Seems like the user is editing a post. Marked the file as to be renamed." );
			return $post;
		}

		$force_rename = $this->getoption( 'force_rename', 'mfrh_basics', false ) && $this->is_pro();

		// NEW DESTINATION FILES ALREADY EXISTS - WE DON'T DO NOTHING
		$new_filepath = trailingslashit( $directory ) . $sanitized_media_title;
		if ( !$force_rename && file_exists( $directory . "/" . $sanitized_media_title ) ) {

			$desired = false;
			$is_numbered = $this->getoption( 'numbered_files', 'mfrh_basics', false );
			if ( $this->is_pro() && $is_numbered ) {
				$desired = $this->generate_unique_filename( $old_filename,
					$path_parts['dirname'], $sanitized_media_title );
			}
			if ( $desired != false ) {
				$this->log( "Seems like $sanitized_media_title could be numbered as $desired." );
				$new_filepath = trailingslashit( $directory ) . $desired;
				$sanitized_media_title = $desired;
			}
			else {
				if ( !get_post_meta( $post['ID'], '_require_file_renaming' ) )
					add_post_meta( $post['ID'], '_require_file_renaming', true, true );
				$this->log( "The new file already exists ($new_filepath), it is safer to avoid doing anything." );
				return $post;
			}
		}

		// Exact same code as rename-media, it's a good idea to keep track of the original filename.
		$original_filename = get_post_meta( $post['ID'], '_original_filename', true );
		if ( empty( $original_filename ) )
			add_post_meta( $post['ID'], '_original_filename', $old_filename, true );

		// Rename the main media file.
		try {
			if ( ( !file_exists( $old_filepath ) || !rename( $old_filepath, $new_filepath ) ) && !$force_rename ) {
				$this->log( "The file couldn't be renamed from $old_filepath to $new_filepath." );
				return $post;
			}
			$this->log( "File $old_filepath renamed to $new_filepath." );
			do_action( 'mfrh_path_renamed', $post, $old_filepath, $new_filepath );
		}
		catch (Exception $e) {
			return $post;
		}

		// Filenames without extensions
		$noext_old_filename = $this->str_replace( '.' . $old_ext, '', $old_filename );
		$noext_new_filename = $this->str_replace( '.' . $ext, '', $sanitized_media_title );

		// Update the attachment meta
		if ( $meta ) {
			$meta['file'] = $this->str_replace( $noext_old_filename, $noext_new_filename, $meta['file'] );
			if ( isset( $meta["url"] ) && $meta["url"] != "" && count( $meta["url"] ) > 4 )
				$meta["url"] = $this->str_replace( $noext_old_filename, $noext_new_filename, $meta["url"] );
			else
				$meta["url"] = $noext_new_filename . "." . $ext;
		}

		// Images
		if ( wp_attachment_is_image( $post['ID'] ) ) {
			// Loop through the different sizes in the case of an image, and rename them.
			$orig_image_urls = array();
			$orig_image_data = wp_get_attachment_image_src( $post['ID'], 'full' );
			$orig_image_urls['full'] = $orig_image_data[0];
			if ( empty( $meta['sizes'] ) ) {
				$this->log( "The WP metadata for attachment " . $post['ID'] . " does not exist.", true );
			}
			else {
				foreach ( $meta['sizes'] as $size => $meta_size ) {
					$meta_old_filename = $meta['sizes'][$size]['file'];
					$meta_old_filepath = trailingslashit( $directory ) . $meta_old_filename;
					$meta_new_filename = $this->str_replace( $noext_old_filename, $noext_new_filename, $meta_old_filename );
					$meta_new_filepath = trailingslashit( $directory ) . $meta_new_filename;
					$orig_image_data = wp_get_attachment_image_src( $post['ID'], $size );
					$orig_image_urls[$size] = $orig_image_data[0];
					// ak: Double check files exist before trying to rename.
					if ( $force_rename || ( file_exists( $meta_old_filepath ) && ( ( !file_exists( $meta_new_filepath ) )
						|| is_writable( $meta_new_filepath ) ) ) ) {
						// WP Retina 2x is detected, let's rename those files as well
						if ( function_exists( 'wr2x_generate_images' ) ) {
							$wr2x_old_filepath = $this->str_replace( '.' . $ext, '@2x.' . $ext, $meta_old_filepath );
							$wr2x_new_filepath = $this->str_replace( '.' . $ext, '@2x.' . $ext, $meta_new_filepath );
							if ( file_exists( $wr2x_old_filepath ) && ( (!file_exists( $wr2x_new_filepath ) ) || is_writable( $wr2x_new_filepath ) ) ) {
								@rename( $wr2x_old_filepath, $wr2x_new_filepath );
								$this->log( "Retina file $wr2x_old_filepath renamed to $wr2x_new_filepath." );
								do_action( 'mfrh_path_renamed', $post, $wr2x_old_filepath, $wr2x_new_filepath );
							}
						}
						@rename( $meta_old_filepath, $meta_new_filepath );
						$meta['sizes'][$size]['file'] = $meta_new_filename;
						$this->log( "File $meta_old_filepath renamed to $meta_new_filepath." );
						do_action( 'mfrh_path_renamed', $post, $meta_old_filepath, $meta_new_filepath );
					}
				}
			}
		}
		else {
			$orig_attachment_url = wp_get_attachment_url( $post['ID'] );
		}

		// This media doesn't require renaming anymore
		delete_post_meta( $post['ID'], '_require_file_renaming' );
		if ( $force ) {
			add_post_meta( $post['ID'], '_manual_file_renaming', true, true );
		}

		// Update metadata
		if ( $meta )
			wp_update_attachment_metadata( $post['ID'], $meta );
		update_attached_file( $post['ID'], $new_filepath );
		clean_post_cache( $post['ID'] );

		// Call the actions so that the plugin's plugins can update everything else (than the files)
		if ( wp_attachment_is_image( $post['ID'] ) ) {
			$orig_image_url = $orig_image_urls['full'];
			$new_image_data = wp_get_attachment_image_src( $post['ID'], 'full' );
			$new_image_url = $new_image_data[0];
			do_action( 'mfrh_url_renamed', $post, $orig_image_url, $new_image_url );
			if ( !empty( $meta['sizes'] ) ) {
				foreach ( $meta['sizes'] as $size => $meta_size ) {
					$orig_image_url = $orig_image_urls[$size];
					$new_image_data = wp_get_attachment_image_src( $post['ID'], $size );
					$new_image_url = $new_image_data[0];
					do_action( 'mfrh_url_renamed', $post, $orig_image_url, $new_image_url );
				}
			}
		}
		else {
			$new_attachment_url = wp_get_attachment_url( $post['ID'] );
			do_action( 'mfrh_url_renamed', $post, $orig_attachment_url, $new_attachment_url );
		}

		// HTTP REFERER set to the new media link
		if ( isset( $_REQUEST['_wp_original_http_referer'] ) && strpos( $_REQUEST['_wp_original_http_referer'], '/wp-admin/' ) === false ) {
			$_REQUEST['_wp_original_http_referer'] = get_permalink( $post['ID'] );
		}

		do_action( 'mfrh_media_renamed', $post, $old_filepath, $new_filepath );
		return $post;
	}

	/**
	 *
	 * INTERNAL ACTIONS (HOOKS)
	 * Mostly from the Side-Updates
	 *
	 * Available actions are:
	 * mfrh_path_renamed
	 * mfrh_url_renamed
	 * mfrh_media_renamed
	 *
	 */

	// Register internal actions
	function init_actions() {
		if ( $this->getoption( "update_posts", "mfrh_basics", true ) )
			add_action( 'mfrh_url_renamed', array( $this, 'action_update_posts' ), 10, 3 );
		if ( $this->getoption( "update_postmeta", "mfrh_basics", true ) )
			add_action( 'mfrh_url_renamed', array( $this, 'action_update_postmeta' ), 10, 3 );
		if ( $this->getoption( "rename_slug", "mfrh_basics", true ) )
			add_action( 'mfrh_media_renamed', array( $this, 'action_update_slug' ), 10, 3 );
		if ( $this->getoption( "sync_alt", "mfrh_basics", false ) && $this->is_pro() )
			add_action( 'mfrh_media_renamed', array( $this, 'action_sync_alt' ), 10, 3 );
		if ( $this->getoption( "rename_guid", "mfrh_basics", false ) )
			add_action( 'mfrh_media_renamed', array( $this, 'action_rename_guid' ), 10, 3 );
	}

	// Slug update
	function action_update_slug( $post, $old_filepath, $new_filepath ) {
		$oldslug = $post['post_name'];
		$info = pathinfo( $new_filepath );
		$newslug = preg_replace( '/\\.[^.\\s]{3,4}$/', '', $info['basename'] );
		global $wpdb;
		$query = $wpdb->prepare( "UPDATE $wpdb->posts SET post_name = '%s' WHERE ID = '%d'", $newslug,  $post['ID'] );
		$query_revert = $wpdb->prepare( "UPDATE $wpdb->posts SET post_name = '%s' WHERE ID = '%d'", $oldslug,  $post['ID'] );
		$this->log_sql( $query, $query_revert );
		$wpdb->query( $query );
		clean_post_cache( $post['ID'] );
		$this->log( "Slug $oldslug renamed into $newslug." );
	}

	function action_sync_alt( $post, $old_filepath, $new_filepath ) {
		update_post_meta( $post['ID'], '_wp_attachment_image_alt', $post['post_title'] );
		$this->log( "Alt. Text set to {$post['post_title']}." );
	}

	// The GUID should never be updated but... this will if the option is checked.
	// [TigrouMeow] It the recent version of WordPress, the GUID is not part of the $post (even though it is in database)
	// Explanation: http://pods.io/2013/07/17/dont-use-the-guid-field-ever-ever-ever/
	function action_rename_guid( $post, $old_filepath, $new_filepath ) {
		$meta = wp_get_attachment_metadata( $post['ID'] );
		$old_guid = get_the_guid( $post['ID'] );
		if ( $meta ) {
			$upload_dir = wp_upload_dir();
			$new_filepath = wp_get_attachment_url( $post['ID'] );
		}
		global $wpdb;
		$query = $wpdb->prepare( "UPDATE $wpdb->posts SET guid = '%s' WHERE ID = '%d'", $new_filepath,  $post['ID'] );
		$query_revert = $wpdb->prepare( "UPDATE $wpdb->posts SET guid = '%s' WHERE ID = '%d'", $old_guid,  $post['ID'] );
		$this->log_sql( $query, $query_revert );
		$wpdb->query( $query );
		clean_post_cache( $post['ID'] );
		$this->log( "Guid $old_guid changed to $new_filepath." );
	}

	// Mass update of all the meta with the new filenames
	function action_update_postmeta( $post, $orig_image_url, $new_image_url ) {
		global $wpdb;
		$query = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = '%s' WHERE meta_key <> '_original_filename' AND (TRIM(meta_value) = '%s' OR TRIM(meta_value) = '%s');", $new_image_url, $orig_image_url, str_replace( ' ', '%20', $orig_image_url ) );
		$query_revert = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = '%s' WHERE meta_key <> '_original_filename' AND meta_value = '%s';", $orig_image_url, $new_image_url );
		$wpdb->query( $query );
		$this->log_sql( $query, $query_revert );
		$this->log( "Metadata exactly like $orig_image_url were replaced by $new_image_url." );
	}

	// Mass update of all the articles with the new filenames
	function action_update_posts( $post, $orig_image_url, $new_image_url ) {
		global $wpdb;
		$query = $wpdb->prepare( "UPDATE $wpdb->posts SET post_content = REPLACE(post_content, '%s', '%s');", $orig_image_url, $new_image_url );
		$query_revert = $wpdb->prepare( "UPDATE $wpdb->posts SET post_content = REPLACE(post_content, '%s', '%s');", $new_image_url, $orig_image_url );
		$wpdb->query( $query );
		$this->log_sql( $query, $query_revert );
		$this->log( "Post content like $orig_image_url were replaced by $new_image_url." );
	}
}

if ( is_admin() ) {
	$mfrh = new Meow_MediaFileRenamer();
	include( 'mfrh_custom.php' );
}
