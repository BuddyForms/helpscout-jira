<?php
/*
 * @package WordPress
 * @subpackage tkFreemiusEvents
 * @author ThemKraft Dev Team
 * @copyright 2019 ThemeKraft
 * @link https://www.themekraft.com/
 * @license GPLv2 or later
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use HelpScoutApp\DynamicApp;
use JiraRestApi\Configuration\ArrayConfiguration;
use JiraRestApi\Issue\IssueService;
use JiraRestApi\JiraException;

class tkHelpScoutJiraAdmin {

	function __construct() {
		add_action( 'admin_menu', array( $this, 'create_setting_page' ) );
		add_action( 'admin_init', array( $this, 'settings_init' ) );

		add_action( 'rest_api_init', array( $this, 'register_route' ) );
	}

	public function register_route() {
		register_rest_route( 'helpscout_jira/v1', '/ticket', array(
			'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'helpscout_jira_freemius_webhook_endpoint' )
		) );
	}

	/**
	 * Process the webhook from freemius
	 *
	 * @param WP_REST_Request $request
	 *
	 */
	public function helpscout_jira_freemius_webhook_endpoint( $request ) {
		try {
			$helpscout_key = get_option( 'helpscout_jira_settings_key' );
			if ( empty( $helpscout_key ) ) {
				trigger_error( 'helpscout_jira_freemius_webhook::Invalid HelpScout Configuration', E_USER_NOTICE );
			}
			$app = new DynamicApp( $helpscout_key );
			if ( $app->isSignatureValid() ) {
				$convo         = $app->getConversation();
				$user          = $app->getUser();
				$ticket_number = $convo->getNumber();
				if ( ! empty( $ticket_number ) ) {
					try {
						$jira_host = get_option( 'helpscout_jira_host' );
						$jira_user = get_option( 'helpscout_jira_user' );
						$jira_api  = get_option( 'helpscout_jira_api' );

						if ( empty( $jira_host ) || empty( $jira_user ) || empty( $jira_api ) ) {
							trigger_error( 'helpscout_jira_freemius_webhook::Invalid Jira Configuration', E_USER_NOTICE );
						}

						$jira_config   = new ArrayConfiguration(
							array(
								'jiraHost'     => $jira_host,
								'jiraUser'     => $jira_user,
								'jiraPassword' => $jira_api,
							)
						);
						$issue_service = new IssueService( $jira_config );

						$html = array();
						$ret  = $issue_service->search( 'HelpScout ~ "' . $ticket_number . '"' );

						if ( ! empty( $ret ) && ! empty( $ret->issues ) ) {
							$html[] = '<h4>Related Jira Tickets</h4>';
							foreach ( $ret->getIssues() as $issue ) {
								$ticket_name          = $issue->key;
								$ticket_created_at    = $issue->fields->created->format( 'd/m/Y h:i a' );
								$ticket_reporter_name = $issue->fields->creator->displayName;
								$ticket_priority_name = $issue->fields->priority->name;
								$ticket_priority_icon = $issue->fields->priority->iconUrl;
								$ticket_status        = $issue->fields->status->name;
								$status_color_class   = 'badge red';
								switch ( $ticket_status ) {
									case 'To Do':
										$status_color_class = 'badge orange';
										break;
									case 'Done':
										$status_color_class = 'badge green';
										break;
									case 'In Progress':
									case 'In Review':
										$status_color_class = 'badge blue';
										break;
								}
								$html[] = '<ul class="c-sb-list c-sb-list--two-line">';
								$html[] = sprintf( '<li class="c-sb-list-item"><span class="c-sb-list-item__label">Ticket:<span class="c-sb-list-item__text"><a target="_blank" href="https://themkraft.atlassian.net/browse/%s">%s</a></span></span></li>', $ticket_name, $ticket_name );
								$html[] = sprintf( '<li class="c-sb-list-item"><span class="c-sb-list-item__label">Created at:<span class="c-sb-list-item__text">%s</span></span></li>', $ticket_created_at );
								$html[] = sprintf( '<li class="c-sb-list-item"><span class="c-sb-list-item__label">Reporter by:<span class="c-sb-list-item__text">%s</span></span></li>', $ticket_reporter_name );
								$html[] = sprintf( '<li class="c-sb-list-item"><span class="c-sb-list-item__label">Priority:<span class="c-sb-list-item__text">%s <img src="%s" style="width: 18px; height: 18px;"/></span></span></li>', $ticket_priority_name, $ticket_priority_icon );
								$html[] = sprintf( '<li class="c-sb-list-item"><span class="c-sb-list-item__label">Status:<span class="c-sb-list-item__text %s" style="color: #fff;">%s</span></span></li>', $status_color_class, $ticket_status );
								$html[] = '</ul>';
							}

						} else {
							$html[] = '<h4>No tickets detected, create one</h4>';
						}
						$html[] = '<hr/>';
						$html[] = '<p><a target="_blank" href="https://themkraft.atlassian.net/secure/BrowseProjects.jspa">Jira Projects</a></p>';

						require 'tkJiraUser.php';

						$user_service = new tkJiraUser( $jira_config );
						$paramArray   = [
							'query' => $user->getFirstName() . ' ' . $user->getLastName(),
						];

						// get the user info.
						$users = $user_service->pickUsers( $paramArray );
						if ( ! empty( $users ) && ! empty( $users[0] ) ) {
							$reporter = $users[0]->accountId;
							$summary  = $convo->getSubject() . ' :: ' . $ticket_number;
							$html[]   = sprintf( '<p><a target="_blank" href="https://themkraft.atlassian.net/secure/CreateIssueDetails!init.jspa?issuetype=10007&pid=10001&summary=%s&reporter=%s&customfield_10037=%s">Create Ticket</a></p>', $summary, $reporter, $ticket_number );
						}

						echo $app->getResponse( $html );
					} catch ( JiraException $ex ) {
						trigger_error( 'helpscout_jira_freemius_webhook::' . $ex->getMessage(), E_USER_NOTICE );
					}
				}
			} else {
				echo 'Invalid Request';
			}

		} catch ( Exception $ex ) {
			trigger_error( 'helpscout_jira_freemius_webhook::' . $ex->getMessage(), E_USER_NOTICE );
		}
	}

	public function create_setting_page() {
		add_options_page( __( 'HelpScout+Jira', 'tk_helpscout_jira_locale' ), __( 'HelpScout+Jira', 'tk_helpscout_jira_locale' ), 'manage_options', tkHelpScoutJira::get_slug(), array( $this, 'helpscout_jira_page' ) );
	}

	public function helpscout_jira_page() {
		include TK_HELPSCOUT_JIRA_VIEW_PATH . 'html_admin_screen.php';
	}

	public function settings_init() {
		add_settings_section( 'helpscout_jira_option_section', '', '', 'helpscout_jira_option' );

		add_settings_field( 'helpscout_jira_settings_key', __( 'HelpScout Key', 'tk_helpscout_jira_locale' ), array( $this, 'helpscout_jira_settings_key_cb' ), 'helpscout_jira_option', 'helpscout_jira_option_section' );
		add_settings_field( 'helpscout_jira_wp_endpoint', __( 'WebHook URL', 'tk_helpscout_jira_locale' ), array( $this, 'helpscout_jira_wp_endpoint_cb' ), 'helpscout_jira_option', 'helpscout_jira_option_section' );

		add_settings_field( 'helpscout_jira_host', __( 'Jira Host', 'tk_helpscout_jira_locale' ), array( $this, 'helpscout_jira_host_cb' ), 'helpscout_jira_option', 'helpscout_jira_option_section' );
		add_settings_field( 'helpscout_jira_user', __( 'Jira User', 'tk_helpscout_jira_locale' ), array( $this, 'helpscout_jira_user_cb' ), 'helpscout_jira_option', 'helpscout_jira_option_section' );
		add_settings_field( 'helpscout_jira_api', __( 'Jira API', 'tk_helpscout_jira_locale' ), array( $this, 'helpscout_jira_api_cb' ), 'helpscout_jira_option', 'helpscout_jira_option_section' );

		register_setting( 'helpscout_jira_option', 'helpscout_jira_settings_key' );
		register_setting( 'helpscout_jira_option', 'helpscout_jira_host' );
		register_setting( 'helpscout_jira_option', 'helpscout_jira_user' );
		register_setting( 'helpscout_jira_option', 'helpscout_jira_api' );
		register_setting( 'helpscout_jira_option', 'helpscout_jira_wp_endpoint' );
	}

	public function helpscout_jira_settings_key_cb( $args ) {
		$value = get_option( 'helpscout_jira_settings_key' );
		?>
        <p>
            <input type="password" name="helpscout_jira_settings_key" value="<?php echo isset( $value ) ? esc_attr( $value ) : ''; ?>" style="width: 350px;">
        <p>
            <label for="helpscout_jira_settings_key">This is the key we will verify from Freemius Webhook.</label>
            <a target="_blank" href="https://randomkeygen.com/">RandomKeygen </a>
        </p>
        </p>
		<?php
	}

	public function helpscout_jira_host_cb() {
		$value = get_option( 'helpscout_jira_host' );
		?>
        <p>
            <input type="text" name="helpscout_jira_host" value="<?php echo isset( $value ) ? esc_attr( $value ) : ''; ?>" style="width: 350px;">
        </p>
		<?php
	}

	public function helpscout_jira_user_cb() {
		$value = get_option( 'helpscout_jira_user' );
		?>
        <p>
            <input type="text" name="helpscout_jira_user" value="<?php echo isset( $value ) ? esc_attr( $value ) : ''; ?>" style="width: 350px;">
        </p>
		<?php
	}

	public function helpscout_jira_api_cb() {
		$value = get_option( 'helpscout_jira_api' );
		?>
        <p>
            <input type="password" name="helpscout_jira_api" value="<?php echo isset( $value ) ? esc_attr( $value ) : ''; ?>" style="width: 350px;">
        </p>
		<?php
	}

	public function helpscout_jira_wp_endpoint_cb() {
		?>
        <p>
            <input type="text" readonly name="helpscout_jira_conv_secret" value="<?php echo esc_url( home_url( '/wp-json/helpscout_jira/v1/ticket' ) ) ?>" style="width: 350px;">
        </p>
		<?php
	}
}
