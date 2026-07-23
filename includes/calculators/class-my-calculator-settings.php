<?php
/**
 * Admin settings page for My Calculator.
 *
 * @package My_Calculator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class My_Calculator_Settings
 */
class My_Calculator_Settings {

	const OPTION_KEY = 'my_calculator_settings';

	/**
	 * Return every supported calculator grouped by category.
	 * Structure: category label => array( slug => calculator label ).
	 *
	 * @return array
	 */
	public static function get_calculator_categories() {
		return array(
			__( 'Bank & Loans', 'my-calculators' ) => array(
				'emi'                   => __( 'EMI Calculator', 'my-calculators' ),
				'home-loan-emi'         => __( 'Home Loan EMI Calculator', 'my-calculators' ),
				'home-loan-eligibility' => __( 'Home Loan Eligibility Calculator', 'my-calculators' ),
				'fd-tdr'                => __( 'Fixed Deposit — TDR (Interest Payout)', 'my-calculators' ),
				'fd-stdr'               => __( 'Fixed Deposit — STDR (Cumulative)', 'my-calculators' ),
				'rd'                    => __( 'Recurring Deposit Calculator', 'my-calculators' ),
			),
			__( 'Post Office & Govt. Savings', 'my-calculators' ) => array(
				'ppf'   => __( 'PPF Calculator', 'my-calculators' ),
				'ssa'   => __( 'Sukanya Samriddhi Account Calculator', 'my-calculators' ),
				'scss'  => __( 'Senior Citizens Savings Scheme Calculator', 'my-calculators' ),
				'kvp'   => __( 'Kisan Vikas Patra Calculator', 'my-calculators' ),
				'mis'   => __( 'Monthly Income Scheme Calculator', 'my-calculators' ),
				'td'    => __( 'Post Office Time Deposit Calculator', 'my-calculators' ),
				'nsc'   => __( 'National Savings Certificate Calculator', 'my-calculators' ),
			),
			__( 'Mutual Funds', 'my-calculators' ) => array(
				'sip'     => __( 'SIP Calculator', 'my-calculators' ),
				'elss'    => __( 'ELSS Calculator', 'my-calculators' ),
				'swp'     => __( 'SWP Calculator', 'my-calculators' ),
				'lumpsum' => __( 'Lumpsum Investment Calculator', 'my-calculators' ),
			),
			__( 'Retirement', 'my-calculators' ) => array(
				'retirement-corpus' => __( 'Retirement Corpus Calculator', 'my-calculators' ),
				'nps'               => __( 'NPS Calculator', 'my-calculators' ),
				'epf'               => __( 'EPF Calculator', 'my-calculators' ),
				'vpf'               => __( 'VPF Calculator', 'my-calculators' ),
				'apy'               => __( 'Atal Pension Yojana Calculator', 'my-calculators' ),
				'pmsym'             => __( 'PM-SYM Calculator', 'my-calculators' ),
				'pmvvy'             => __( 'PM Vaya Vandana Yojana Calculator', 'my-calculators' ),
				'gratuity'          => __( 'Gratuity Calculator', 'my-calculators' ),
			),
			__( 'Tax', 'my-calculators' ) => array(
				'income-tax'     => __( 'Income Tax Calculator', 'my-calculators' ),
				'capital-gains'  => __( 'Capital Gains Tax Calculator', 'my-calculators' ),
			),
			__( 'Insurance', 'my-calculators' ) => array(
				'life-insurance' => __( 'Life Insurance Maturity Calculator', 'my-calculators' ),
				'term-life'      => __( 'Term Life Policy Calculator', 'my-calculators' ),
				'endowment'      => __( 'Endowment Policy Return Calculator', 'my-calculators' ),
				'pli'            => __( 'Postal Life Insurance (PLI) Estimator', 'my-calculators' ),
				'rpli'           => __( 'Rural PLI (RPLI) Estimator', 'my-calculators' ),
				'pmjjby'         => __( 'PM Jeevan Jyoti Bima Yojana Overview', 'my-calculators' ),
				'pmsby'          => __( 'PM Suraksha Bima Yojana Overview', 'my-calculators' ),
			),
			__( 'Bonds', 'my-calculators' ) => array(
				'frsb'      => __( 'Floating Rate Savings Bonds Calculator', 'my-calculators' ),
				'sgb'       => __( 'Sovereign Gold Bond Calculator', 'my-calculators' ),
				'bond-54ec' => __( '54EC Capital Gains Bonds Calculator', 'my-calculators' ),
			),
			__( 'General', 'my-calculators' ) => array(
				'compound-interest' => __( 'Compound Interest Calculator', 'my-calculators' ),
				'simple-interest'   => __( 'Simple Interest Calculator', 'my-calculators' ),
				'inflation'         => __( 'Inflation Calculator', 'my-calculators' ),
			),
		);
	}

	/**
	 * Flat slug => label map, derived from the categorized list.
	 *
	 * @return array
	 */
	public static function get_calculator_types() {
		$flat = array();
		foreach ( self::get_calculator_categories() as $category => $calcs ) {
			foreach ( $calcs as $slug => $label ) {
				$flat[ $slug ] = $label;
			}
		}
		return $flat;
	}

	/**
	 * Default settings used on activation and as a fallback.
	 *
	 * @return array
	 */
	public static function get_default_settings() {
		$defaults = array(
			'enabled'         => array_keys( self::get_calculator_types() ), // all enabled by default.
			'primary_color'   => '#0F6E4F',
			'currency_symbol' => '₹',

			// Bank & Loans.
			'default_rate_loan'   => 9.5,
			'default_rate_fd'     => 7.0,
			'default_rate_rd'     => 6.8,

			// Post Office & Govt Savings.
			'default_rate_ppf'  => 7.1,
			'default_rate_ssa'  => 8.2,
			'default_rate_scss' => 8.2,
			'default_rate_kvp'  => 7.5,
			'default_rate_mis'  => 7.4,
			'default_rate_td'   => 7.0,
			'default_rate_nsc'  => 7.7,

			// Mutual Funds.
			'default_rate_sip'   => 12,
			'default_rate_elss'  => 12,
			'default_rate_swp'   => 8,
			'default_rate_lumpsum' => 10,

			// Retirement.
			'default_inflation'    => 6,
			'default_rate_nps'     => 10,
			'default_rate_epf'     => 8.25,
			'default_rate_vpf'     => 8.25,
			'default_rate_apy'     => 8,

			// Tax.
			'default_ltcg_rate' => 12.5,
			'default_stcg_rate' => 20,
			'default_cess_rate' => 4,

			// Insurance.
			'default_rate_annuity'  => 6,
			'default_pli_rate'      => 45,
			'default_rpli_rate'     => 40,

			// Bonds.
			'default_frsb_rate' => 8.05,
			'default_sgb_rate'  => 2.5,
			'default_sgb_gold_growth' => 8,
			'default_54ec_rate' => 5.25,

			// General.
			'default_compound_rate' => 8,
			'default_simple_rate'   => 8,
		);
		return $defaults;
	}

	/**
	 * Get current settings merged with defaults (so new options never break old installs).
	 *
	 * @return array
	 */
	public static function get_settings() {
		$saved = get_option( self::OPTION_KEY, array() );
		return wp_parse_args( $saved, self::get_default_settings() );
	}

	/**
	 * Register the admin menu page.
	 */
	public function add_settings_page() {
		add_menu_page(
			__( 'My Calculators', 'my-calculators' ),
			__( 'My Calculators', 'my-calculators' ),
			'manage_options',
			'my-calculators',
			array( $this, 'render_settings_page' ),
			'dashicons-calculator',
			58
		);

		add_submenu_page(
			'my-calculators',
			__( 'Calculators', 'my-calculators' ),
			__( 'Calculators', 'my-calculators' ),
			'manage_options',
			'my-calculators',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Add a "Settings" link on the Plugins list page.
	 *
	 * @param array $links Existing plugin action links.
	 * @return array
	 */
	public function add_settings_link( $links ) {
		$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=my-calculators' ) ) . '">' . esc_html__( 'Settings', 'my-calculators' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Register settings, sections, and fields via the Settings API.
	 */
	public function register_settings() {
		register_setting(
			'my_calculator_settings_group',
			self::OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
			)
		);
	}

	/**
	 * All rate field keys, with their human label, grouped identically to
	 * the calculator categories above for the settings-page UI.
	 *
	 * @return array
	 */
	public static function get_rate_field_groups() {
		return array(
			__( 'Bank & Loans', 'my-calculators' ) => array(
				'default_rate_loan' => __( 'Loan / EMI interest rate (%)', 'my-calculators' ),
				'default_rate_fd'   => __( 'Fixed Deposit rate (%)', 'my-calculators' ),
				'default_rate_rd'   => __( 'Recurring Deposit rate (%)', 'my-calculators' ),
			),
			__( 'Post Office & Govt. Savings', 'my-calculators' ) => array(
				'default_rate_ppf'  => __( 'PPF interest rate (%)', 'my-calculators' ),
				'default_rate_ssa'  => __( 'Sukanya Samriddhi rate (%)', 'my-calculators' ),
				'default_rate_scss' => __( 'SCSS rate (%)', 'my-calculators' ),
				'default_rate_kvp'  => __( 'Kisan Vikas Patra rate (%)', 'my-calculators' ),
				'default_rate_mis'  => __( 'Monthly Income Scheme rate (%)', 'my-calculators' ),
				'default_rate_td'   => __( 'Post Office Time Deposit rate (%)', 'my-calculators' ),
				'default_rate_nsc'  => __( 'NSC rate (%)', 'my-calculators' ),
			),
			__( 'Mutual Funds', 'my-calculators' ) => array(
				'default_rate_sip'     => __( 'SIP expected annual return (%)', 'my-calculators' ),
				'default_rate_elss'    => __( 'ELSS expected annual return (%)', 'my-calculators' ),
				'default_rate_swp'     => __( 'SWP expected annual return (%)', 'my-calculators' ),
				'default_rate_lumpsum' => __( 'Lumpsum expected annual return (%)', 'my-calculators' ),
			),
			__( 'Retirement', 'my-calculators' ) => array(
				'default_inflation' => __( 'Assumed inflation rate (%)', 'my-calculators' ),
				'default_rate_nps'  => __( 'NPS expected annual return (%)', 'my-calculators' ),
				'default_rate_epf'  => __( 'EPF interest rate (%)', 'my-calculators' ),
				'default_rate_vpf'  => __( 'VPF interest rate (%)', 'my-calculators' ),
				'default_rate_apy'  => __( 'Atal Pension Yojana assumed return (%)', 'my-calculators' ),
			),
			__( 'Tax', 'my-calculators' ) => array(
				'default_ltcg_rate' => __( 'Long-term capital gains rate — equity (%)', 'my-calculators' ),
				'default_stcg_rate' => __( 'Short-term capital gains rate — equity (%)', 'my-calculators' ),
				'default_cess_rate' => __( 'Health & education cess (%)', 'my-calculators' ),
			),
			__( 'Insurance', 'my-calculators' ) => array(
				'default_rate_annuity' => __( 'Annuity / bonus annual return (%)', 'my-calculators' ),
				'default_pli_rate'     => __( 'PLI premium per ₹1000 sum assured / year', 'my-calculators' ),
				'default_rpli_rate'    => __( 'RPLI premium per ₹1000 sum assured / year', 'my-calculators' ),
			),
			__( 'Bonds', 'my-calculators' ) => array(
				'default_frsb_rate'       => __( 'Floating Rate Savings Bond rate (%)', 'my-calculators' ),
				'default_sgb_rate'        => __( 'Sovereign Gold Bond interest rate (%)', 'my-calculators' ),
				'default_sgb_gold_growth' => __( 'Assumed gold price growth (%/yr)', 'my-calculators' ),
				'default_54ec_rate'       => __( '54EC capital gains bond rate (%)', 'my-calculators' ),
			),
			__( 'General', 'my-calculators' ) => array(
				'default_compound_rate' => __( 'Default compound interest rate (%)', 'my-calculators' ),
				'default_simple_rate'   => __( 'Default simple interest rate (%)', 'my-calculators' ),
			),
		);
	}

	/**
	 * Sanitize all settings input.
	 *
	 * @param array $input Raw input from the settings form.
	 * @return array Sanitized settings.
	 */
	public function sanitize_settings( $input ) {
		$clean   = array();
		$valid   = array_keys( self::get_calculator_types() );
		$enabled = isset( $input['enabled'] ) && is_array( $input['enabled'] ) ? $input['enabled'] : array();

		$clean['enabled'] = array_values( array_intersect( $valid, array_map( 'sanitize_text_field', wp_unslash( $enabled ) ) ) );

		$clean['primary_color']   = isset( $input['primary_color'] ) ? sanitize_hex_color( wp_unslash( $input['primary_color'] ) ) : '#0F6E4F';
		$clean['currency_symbol'] = isset( $input['currency_symbol'] ) ? sanitize_text_field( wp_unslash( $input['currency_symbol'] ) ) : '₹';

		$rate_groups = self::get_rate_field_groups();
		foreach ( $rate_groups as $group => $fields ) {
			foreach ( $fields as $key => $label ) {
				$value         = isset( $input[ $key ] ) ? round( (float) $input[ $key ], 2 ) : 0;
				$clean[ $key ] = max( 0, min( 100, $value ) );
			}
		}

		return $clean;
	}

	/**
	 * Render the full settings page markup.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings   = self::get_settings();
		$categories = self::get_calculator_categories();
		$rate_groups = self::get_rate_field_groups();
		?>
		<div class="wrap my-calculator-admin-wrap">
			<div class="mc-admin-header">
				<h1><?php esc_html_e( 'My Calculators', 'my-calculators' ); ?></h1>
				<p><?php esc_html_e( 'Manage which financial calculators are available on your site, set default rates, and grab the shortcode for each calculator.', 'my-calculators' ); ?></p>
			</div>

			<form method="post" action="options.php">
				<?php settings_fields( 'my_calculator_settings_group' ); ?>

				<div class="mc-admin-card">
					<h2><?php esc_html_e( 'General', 'my-calculators' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="mc_primary_color"><?php esc_html_e( 'Primary Color', 'my-calculators' ); ?></label></th>
							<td><input type="text" id="mc_primary_color" class="my-calculator-color-field" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[primary_color]" value="<?php echo esc_attr( $settings['primary_color'] ); ?>" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="mc_currency_symbol"><?php esc_html_e( 'Currency Symbol', 'my-calculators' ); ?></label></th>
							<td><input type="text" id="mc_currency_symbol" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[currency_symbol]" value="<?php echo esc_attr( $settings['currency_symbol'] ); ?>" class="small-text" /></td>
						</tr>
					</table>
				</div>

				<div class="mc-admin-card">
					<h2><?php esc_html_e( 'Enabled Calculators &amp; Shortcodes', 'my-calculators' ); ?></h2>
					<p class="description">
						<?php esc_html_e( 'Show every enabled calculator at once, organized into tabs by category:', 'my-calculators' ); ?>
						<code>[my_calculator]</code>
						—
						<?php esc_html_e( 'or embed just one using its shortcode below.', 'my-calculators' ); ?>
					</p>

					<?php foreach ( $categories as $category => $calcs ) : ?>
						<h3 class="mc-admin-category-heading"><?php echo esc_html( $category ); ?></h3>
						<table class="widefat striped my-calculator-types-table">
							<thead>
								<tr>
									<th style="width:70px;"><?php esc_html_e( 'Enabled', 'my-calculators' ); ?></th>
									<th><?php esc_html_e( 'Calculator', 'my-calculators' ); ?></th>
									<th><?php esc_html_e( 'Shortcode', 'my-calculators' ); ?></th>
								</tr>
							</thead>
							<tbody>
							<?php foreach ( $calcs as $slug => $label ) : ?>
								<tr>
									<td>
										<input type="checkbox"
											name="<?php echo esc_attr( self::OPTION_KEY ); ?>[enabled][]"
											value="<?php echo esc_attr( $slug ); ?>"
											<?php checked( in_array( $slug, $settings['enabled'], true ) ); ?> />
									</td>
									<td><?php echo esc_html( $label ); ?></td>
									<td><code>[my_calculator type="<?php echo esc_attr( $slug ); ?>"]</code></td>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
					<?php endforeach; ?>
				</div>

				<div class="mc-admin-card">
					<h2><?php esc_html_e( 'Default Rates', 'my-calculators' ); ?></h2>
					<p class="description">
						<?php esc_html_e( 'These pre-fill each calculator. Visitors can still override the value for a single calculation. Government scheme rates change periodically — keep these current.', 'my-calculators' ); ?>
					</p>
					<?php foreach ( $rate_groups as $group => $fields ) : ?>
						<h3 class="mc-admin-category-heading"><?php echo esc_html( $group ); ?></h3>
						<table class="form-table" role="presentation">
							<?php foreach ( $fields as $key => $label ) : ?>
								<tr>
									<th scope="row"><label for="mc_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
									<td><input type="number" step="0.01" id="mc_<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $settings[ $key ] ); ?>" class="small-text" /></td>
								</tr>
							<?php endforeach; ?>
						</table>
					<?php endforeach; ?>
				</div>

				<?php submit_button(); ?>
			</form>

			<hr/>
			<p>
				<?php
				printf(
					/* translators: %s: link to the Credit Score settings page */
					esc_html__( 'Also want to let visitors check their credit score and eligibility? %s', 'my-calculators' ),
					'<a href="' . esc_url( admin_url( 'admin.php?page=my-calculators-credit' ) ) . '">' . esc_html__( 'Go to Credit Score settings', 'my-calculators' ) . '</a>'
				);
				?>
			</p>
			<p>
				<strong><?php esc_html_e( 'Plugin by', 'my-calculators' ); ?></strong>
				Biswajit Barman — <a href="https://myadvisers.in" target="_blank" rel="noopener noreferrer">myadvisers.in</a>
				&middot; <?php echo esc_html( sprintf( /* translators: %s: plugin version */ __( 'Version %s', 'my-calculators' ), MY_CALCULATORS_VERSION ) ); ?>
			</p>
		</div>
		<?php
	}
}
