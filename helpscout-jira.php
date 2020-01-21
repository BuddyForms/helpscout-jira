<?php
/*
 * Plugin Name: TK HelpScout integrate Jira
 * Plugin URI: https://www.themekraft.com/
 * Description: Connect HelpScout with Jira.
 * Version: 1.0.0
 * Author: ThemeKraft Dev Team
 * Author URI: https://www.themekraft.com/#team
 * License: GPLv2 or later
 * Text Domain: tk_helpscout_jira_locale
 *
 *****************************************************************************
 *
 * This script is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 ****************************************************************************
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'tkHelpScoutJira' ) ) {

//	require_once dirname( __FILE__ ) . '/classes/tk-freev-fs.php';
//	new tkFreemiusEventsFs();

	class tkHelpScoutJira {

		/**
		 * Instance of this class
		 *
		 * @var $instance tkFreemiusEvents
		 */
		protected static $instance = null;
		private static $plugin_slug = 'tk_helpscout_jira';
		private static $version = '1.0.0';

		private function __construct() {
			$this->constants();
			$this->load_plugin_textdomain();

//			$bf_freemius = tkFreemiusEventsFs::getFreemius();
//			if ( ! empty( $bf_freemius ) && $bf_freemius->is_paying_or_trial__premium_only() ) {
			require_once 'vendor/autoload.php';
			require_once 'includes/tk-helpscout-jira-admin.php';
			new tkHelpScoutJiraAdmin();
//			}
		}

		public static function get_slug() {
			return self::$plugin_slug;
		}

		static function get_version() {
			return self::$version;
		}

		public static function assets_path( $name, $extension = 'js' ) {
			$base_path = ( $extension == 'js' ) ? TK_HELPSCOUT_JIRA_JS_PATH : TK_HELPSCOUT_JIRA_CSS_PATH;
			$suffix    = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			return $base_path . $name . $suffix . '.' . $extension;
		}

		private function constants() {
			define( 'TK_HELPSCOUT_JIRA_CSS_PATH', plugin_dir_url( __FILE__ ) . 'assets/css/' );
			define( 'TK_HELPSCOUT_JIRA_JS_PATH', plugin_dir_url( __FILE__ ) . 'assets/js/' );
			define( 'TK_HELPSCOUT_JIRA_VIEW_PATH', dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR );
		}

		/**
		 * Return an instance of this class.
		 *
		 * @return tkFreemiusEvents A single instance of this class.
		 */
		public static function get_instance() {
			// If the single instance hasn't been set, set it now.
			if ( null === self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		public function load_plugin_textdomain() {
			load_plugin_textdomain( 'tk_helpscout_jira_locale', false, basename( dirname( __FILE__ ) ) . '/languages' );
		}

	}

	add_action( 'plugins_loaded', array( 'tkHelpScoutJira', 'get_instance' ), 1 );
}
