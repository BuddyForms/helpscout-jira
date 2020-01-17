<div class="wrap">

	<?php include_once TK_HELPSCOUT_JIRA_VIEW_PATH .'html_admin_header.php'; ?>

	<form method="post" action="options.php">
		<?php wp_nonce_field( 'update-options' ); ?>
		<?php settings_fields( 'helpscout_jira_option' ); ?>
		<?php do_settings_sections( 'helpscout_jira_option' ); ?>
        <?php submit_button(); ?>
	</form>

</div>
