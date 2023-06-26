<?php

class Zoom_Connect_User_Account {
	public static ?Zoom_Connect_User_Account $instance = null;

	public static function get_instance(): ?Zoom_Connect_User_Account {
		return is_null( self::$instance ) ? self::$instance = new self() : self::$instance;
	}

	public function __construct() {
	}

	public function render() {
		$account_id    = get_user_meta( get_current_user_id(), 'zoom_user_account_id', true );
		$client_id     = get_user_meta( get_current_user_id(), 'zoom_user_client_id', true );
		$client_secret = get_user_meta( get_current_user_id(), 'zoom_user_client_secret', true );

        // Output the form
		?>
        <div style="padding: 20px; background: #cccccc">
            <h2>Users Server to Server Oauth Credentials</h2>
            <form method="post" action="" style="display: flex; flex-direction:column; align-items: flex-start; gap: 20px;">
                <div style=" display: flex;align-items: center;">
                    <label for="client_id" style="width: 150px">Oauth Account ID:</label>
                    <input type="text" name="account_id" value="<?php echo esc_attr( $account_id ); ?>" required/>
                </div>
                <div style=" display: flex;align-items: center;">
                    <label for="client_id" style="width: 150px">Oauth Client ID:</label>
                    <input type="text" name="client_id" value="<?php echo esc_attr( $client_id ); ?>" required/>
                </div>
                <div style=" display: flex;align-items: center;">
                    <label for="client_secret" style="width: 150px">Oauth Client Secret:</label>
                    <input type="text" name="client_secret" value="<?php echo esc_attr( $client_secret ); ?>" required/>
                </div>
                <input type="submit" name="save_user_credentials" value="Save Credentials"/>
            </form>
        </div>
		<?php
		$access_token = \vczapi\S2SOAuth::get_instance()->generateAndSaveAccessToken( $account_id, $client_id, $client_secret, true );
		if ( is_wp_error( $access_token ) ) {
			// Handle the error appropriately
			$error_message = $access_token->get_error_message();
			// Display the error message to the user
			echo '<p>Error: ' . esc_html( $error_message ) . '</p>';
		} else {
			// Access token generated successfully
			echo '<p>Access token generated and saved successfully!</p>';
		}
	}
	public function save_api_credentials() {
		if ( isset( $_POST['save_user_credentials'] ) ) {
			$account_id    = sanitize_text_field( $_POST['account_id'] );
			$client_id     = sanitize_text_field( $_POST['client_id'] );
			$client_secret = sanitize_text_field( $_POST['client_secret'] );

			// Save the API credentials for the current user
			update_user_meta( get_current_user_id(), 'zoom_user_account_id', $account_id );
			update_user_meta( get_current_user_id(), 'zoom_user_client_id', $client_id );
			update_user_meta( get_current_user_id(), 'zoom_user_client_secret', $client_secret );
		}
	}
}




