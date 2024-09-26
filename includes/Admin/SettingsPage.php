<?php

namespace Iyzico\IyzipayWoocommerce\Admin;

use Iyzico\IyzipayWoocommerce\Checkout\CheckoutSettings;
use Iyzico\IyzipayWoocommerce\Pwi\PwiSettings;

class SettingsPage {

	private $checkoutSettings;
	private $pwiSettings;

	public function __construct() {
		$this->checkoutSettings = new CheckoutSettings();
		$this->pwiSettings      = new PwiSettings();

		add_action( 'admin_menu', [ $this, 'addAdminMenu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueueAdminScripts' ] );
		add_action( 'admin_post_iyzico_save_settings', [ $this, 'saveSettings' ] );
	}

	public function addAdminMenu(): void {
		add_menu_page(
			__( 'iyzico Ayarları', 'woocommerce-iyzico' ),
			__( 'iyzico', 'woocommerce-iyzico' ),
			'manage_options',
			'iyzico',
			[ $this, 'displaySettingsPage' ],
			'dashicons-admin-generic',
			56
		);
	}

	public function displaySettingsPage(): void {
		$active_tab = $_GET['tab'] ?? 'checkout';
		?>
        <div class="bg-gray-100 min-h-screen p-6">
            <h1 class="text-3xl font-bold mb-6"><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <div class="bg-white rounded-lg shadow-md p-6">
                <nav class="flex mb-6">
                    <a href="<?php echo admin_url( 'admin.php?page=iyzico&tab=checkout' ); ?>"
                       class="mr-4 px-4 py-2 rounded-md <?php echo $active_tab == 'checkout' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700'; ?>">
						<?php _e( 'Checkout Form', 'woocommerce-iyzico' ); ?>
                    </a>
                    <a href="<?php echo admin_url( 'admin.php?page=iyzico&tab=pwi' ); ?>"
                       class="px-4 py-2 rounded-md <?php echo $active_tab == 'pwi' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700'; ?>">
						<?php _e( 'Pay with iyzico', 'woocommerce-iyzico' ); ?>
                    </a>
                </nav>
                <form method="post" action="admin-post.php" class="space-y-6">
					<?php
					wp_nonce_field( 'iyzico_save_settings', 'iyzico_nonce' );
					?>
                    <input type="hidden" name="action" value="iyzico_save_settings" />
                    <input type="hidden" name="tab" value="<?php echo esc_attr( $active_tab ); ?>" />

					<?php
					if ( $active_tab == 'pwi' ) {
						$this->renderSettingsFields( $this->pwiSettings );
					} else {
						$this->renderSettingsFields( $this->checkoutSettings );
					}
					?>

                    <div class="mt-6">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
							<?php _e( 'Save Settings', 'woocommerce-iyzico' ); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
		<?php
	}

	private function renderSettingsFields( $settingsClass ): void {
		$fields  = $settingsClass->getFormFields();
		$options = $settingsClass->getSettings();

		foreach ( $fields as $key => $field ) {
			$value = $options[ $key ] ?? ( $field['default'] ?? '' );
			$this->renderField( [
				'key'   => $key,
				'field' => $field,
				'value' => $value,
			] );
		}
	}

	public function renderField( $args ): void {
		$key   = $args['key'];
		$field = $args['field'];
		$value = $args['value'];
		$name  = 'iyzico_options[' . esc_attr( $key ) . ']';

		echo '<div class="mb-4">';
		if ( $field['type'] != 'checkbox' ) {
			echo '<label for="' . esc_attr( $key ) . '" class="block text-sm font-medium text-gray-700 mb-1">' . esc_html( $field['title'] ) . '</label>';
		}

		switch ( $field['type'] ) {
			case 'text':
				?>
                <input type="text" id="<?php echo esc_attr( $key ); ?>" name="<?php echo $name; ?>"
                       value="<?php echo esc_attr( $value ); ?>"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"/>
				<?php
				break;
			case 'checkbox':
				?>
                <div class="flex items-center">
                    <input type="checkbox" id="<?php echo esc_attr( $key ); ?>" name="<?php echo $name; ?>"
                           value="yes" <?php checked( $value, 'yes' ); ?>
                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"/>
                    <label for="<?php echo esc_attr( $key ); ?>" class="ml-2 block text-sm text-gray-900">
						<?php echo esc_html( $field['label'] ?? '' ); ?>
                    </label>
                </div>
				<?php
				break;
			case 'select':
				?>
                <select id="<?php echo esc_attr( $key ); ?>" name="<?php echo $name; ?>"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
					<?php
					foreach ( $field['options'] as $option_value => $option_label ) {
						?>
                        <option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $value, $option_value ); ?>><?php echo esc_html( $option_label ); ?></option>
						<?php
					}
					?>
                </select>
				<?php
				break;
		}

		if ( isset( $field['description'] ) ) {
			echo '<p class="mt-1 text-sm text-gray-500">' . esc_html( $field['description'] ) . '</p>';
		}

		echo '</div>';
	}

	public function saveSettings(): void {
		if ( ! isset( $_POST['iyzico_nonce'] ) || ! wp_verify_nonce( $_POST['iyzico_nonce'], 'iyzico_save_settings' ) ) {
			wp_die( __( 'Güvenlik kontrolü başarısız oldu.', 'woocommerce-iyzico' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'İzin reddedildi.', 'woocommerce-iyzico' ) );
		}

		$active_tab         = $_POST['tab'] ?? 'checkout';
		$submitted_settings = $_POST['iyzico_options'] ?? array();

		if ( $active_tab == 'pwi' ) {
			$settingsClass = $this->pwiSettings;
		} else {
			$settingsClass = $this->checkoutSettings;
		}

		$settings = array();
		foreach ( $settingsClass->form_fields as $key => $field ) {
			if ( isset( $submitted_settings[ $key ] ) ) {
				$value = $submitted_settings[ $key ];
			} else {
				$value = null;
			}

			switch ( $field['type'] ) {
				case 'text':
					$settings[ $key ] = sanitize_text_field( $value );
					break;
				case 'checkbox':
					$settings[ $key ] = ( $value === 'yes' ) ? 'yes' : 'no';
					break;
				case 'select':
					if ( $value !== null && array_key_exists( $value, $field['options'] ) ) {
						$settings[ $key ] = $value;
					} else {
						$settings[ $key ] = $field['default'];
					}
					break;
				// Diğer alan türleri için eklemeler yapabilirsiniz
			}
		}

		$settingsClass->setSettings( $settings );
		wp_redirect( admin_url( 'admin.php?page=iyzico&tab=' . $active_tab . '&settings-updated=true' ) );
		exit;
	}

	public function enqueueAdminScripts( $hook_suffix ): void {
		if ( 'toplevel_page_iyzico' != $hook_suffix ) {
			return;
		}

		wp_enqueue_style(
			'tailwindcss',
			'https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css',
			array(),
			'2.2.19'
		);

		wp_enqueue_style(
			'iyzico-admin-css',
			PLUGIN_URL . '/assets/css/admin.css',
			array( 'tailwindcss' ),
			'1.0.0'
		);
	}
}