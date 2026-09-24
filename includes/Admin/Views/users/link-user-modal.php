<div id="vczapi-link-user-modal" class="vczapi-modal-overlay" aria-hidden="true">
    <div class="vczapi-modal" role="dialog" aria-labelledby="vczapi-modal-title">
        <div class="vczapi-modal__header">
            <h2 id="vczapi-modal-title" class="vczapi-modal__title">
				<?php esc_html_e( 'Link WordPress User', 'video-conferencing-with-zoom-api' ); ?>
            </h2>
            <button type="button" class="vczapi-modal__close" data-vczapi-dismiss="modal" aria-label="<?php esc_attr_e( 'Close modal', 'video-conferencing-with-zoom-api' ); ?>">
                &times;
            </button>
        </div>

        <form id="vczapi-link-user-form">
            <div class="vczapi-modal__body">
                <input type="hidden" name="zoom_user_id" id="vczapi-modal-zoom-id" value="">
                <input type="hidden" name="wp_user_id" id="vczapi-modal-wp-user-id" value="">

                <div class="vczapi-modal__field">
                    <label for="vczapi-modal-wp-user-search">
						<?php esc_html_e( 'Search WordPress User', 'video-conferencing-with-zoom-api' ); ?>
                    </label>
                    <input type="text"
                           id="vczapi-modal-wp-user-search"
                           class="widefat"
                           autocomplete="off"
                           placeholder="<?php esc_attr_e( 'Type to search by name, username or email...', 'video-conferencing-with-zoom-api' ); ?>">
                    <div class="vczapi-combobox__selection" id="vczapi-modal-wp-user-selection" hidden>
                        <button type="button" class="button-link vczapi-combobox__clear" data-vczapi-clear="wp-user">
							<?php esc_html_e( 'Remove selection', 'video-conferencing-with-zoom-api' ); ?>
                        </button>
                    </div>
                    <ul class="vczapi-combobox__results" id="vczapi-modal-wp-user-results" hidden></ul>
                    <p class="description">
						<?php esc_html_e( 'Select the WordPress user account to associate with this Zoom profile. Leave empty to unlink.', 'video-conferencing-with-zoom-api' ); ?>
                    </p>
                </div>

                <div id="vczapi-modal-feedback" class="vczapi-modal__feedback" style="display: none;"></div>
            </div>
        </form>
    </div>
</div>