<?php
class PMPro_Email_Template_PMProACR_Reminder_2 extends PMPro_Email_Template {

	/**
	 * The user object of the user to send the email to.
     * @var WP_User
     */
    protected $user;

    /**
     * The membership level object of the membership level associated with the abandoned cart.
     * @var stdClass
     */
    protected $membership_level;

	/**
	 * Constructor.
	 *
	 * @since 1.0
	 *
	 * @param WP_User $user The user object of the user to send the email to.
     * @param stdClass $membership_level The membership level object of the membership level associated with the abandoned cart.
	 */
	public function __construct( WP_User $user, stdClass $membership_level ) {
		$this->user = $user;
		$this->membership_level = $membership_level;
	}

	/**
	 * Get the email template slug.
	 *
	 * @since 1.0
	 *
	 * @return string The email template slug.
	 */
	public static function get_template_slug() {
		return 'pmproacr_reminder_2';
	}

	/**
	 * Get the "nice name" of the email template.
	 *
	 * @since 1.0
	 *
	 * @return string The "nice name" of the email template.
	 */
	public static function get_template_name() {
		return esc_html__( 'Abandoned Cart Recovery - Reminder 2', 'pmpro-abandoned-cart-recovery' );
	}

	/**
	 * Get "help text" to display to the admin when editing the email template.
	 *
	 * @since 1.0
	 *
	 * @return string The "help text" to display to the admin when editing the email template.
	 */
	public static function get_template_description() {
		return esc_html__( 'This email is sent as the second reminder to complete a purchase.', 'pmpro-abandoned-cart-recovery' );
	}

	/**
	 * Get the default subject for the email.
	 *
	 * @since 1.0
	 *
	 * @return string The default subject for the email.
	 */
	public static function get_default_subject() {
		return esc_html__( "Reminder: Your !!sitename!! membership is waiting.", 'pmpro-abandoned-cart-recovery' );
	}

	/**
	 * Get the default body content for the email.
	 *
	 * @since 1.0
	 *
	 * @return string The default body content for the email.
	 */
	public static function get_default_body() {
		// Allowed strings for kses checks below.
		$allowed_html = array(
			'a' => array(
				'href' => array(),
				'title' => array(),
			),
			'p' => array(),
		);

		return '<p>' . esc_html__( 'It looks like you may have forgotten to complete checkout for the !!membership_level_name!! membership at !!sitename!!.', 'pmpro-abandoned-cart-recovery' ) . '</p>

<p><a href="!!checkout_url!!">' . esc_html__( 'Complete Your Purchase Now', 'pmpro-abandoned-cart-recovery' ) . '</a></p>

' . wp_kses( __( '<p>If you do not want to receive any more emails about this attempted checkout, <a href="!!opt_out_url!!">click here to opt out of these emails</a>.</p>', 'pmpro-abandoned-cart-recovery' ), $allowed_html );
	}

	/**
	 * Get the email address to send the email to.
	 *
	 * @since 1.0
	 *
	 * @return string The email address to send the email to.
	 */
	public function get_recipient_email() {
		return $this->user->user_email;
	}

	/**
	 * Get the name of the email recipient.
	 *
	 * @since 1.0
	 *
	 * @return string The name of the email recipient.
	 */
	public function get_recipient_name() {
		return $this->user->display_name;
	}


	/**
	 * Get the email template variables for the email paired with a description of the variable.
	 *
	 * @since 1.0
	 *
	 * @return array The email template variables for the email (key => value pairs).
	 */
	public static function get_email_template_variables_with_description() {
		return array(
			'!!display_name!!' => esc_html__( 'The display name of the user.', 'paid-memberships-pro' ),
			'!!user_login!!' => esc_html__( 'The username of the user.', 'paid-memberships-pro' ),
			'!!user_email!!' => esc_html__( 'The email address of the user.', 'paid-memberships-pro' ),
			'!!membership_id!!' => esc_html__( 'The ID of the membership level.', 'paid-memberships-pro' ),
			'!!membership_level_name!!' => esc_html__( 'The name of the membership level.', 'paid-memberships-pro' ),
			'!!checkout_url!!' => esc_html__( 'The URL to the checkout page with the membership level pre-selected.', 'paid-memberships-pro' ),
            '!!opt_out_url!!' => esc_html__( 'The URL the user can click to opt out of future abandoned cart emails.', 'paid-memberships-pro' ),
		);
	}

	/**
	 * Get the email template variables for the email.
	 *
	 * @since 1.0
	 *
	 * @return array The email template variables for the email (key => value pairs).
	 */
	public function get_email_template_variables() {
		return array(
			'!!name!!' => $this->user->display_name,
			'!!display_name!!' => $this->user->display_name,
			'!!user_login!!' => $this->user->user_login,
			'!!user_email!!' => $this->user->user_email,
			'!!membership_id!!' => $this->membership_level->id,
			'!!membership_level_name!!' => $this->membership_level->name,
			'!!checkout_url!!' => pmpro_login_url( pmpro_url( 'checkout', '?pmpro_level=' . $this->membership_level->id ) ),
			'!!opt_out_url!!' => add_query_arg( 'pmproacr_opt_out', urlencode( $this->user->user_email ), home_url() ),
		);
	}

	/**
	 * Returns the arguments to send the test email from the abstract class.
	 *
	 * @since 1.0
	 *
	 * @return array The arguments to send the test email from the abstract class.
	 */
	public static function get_test_email_constructor_args() {
		$test_user = wp_get_current_user();

		// If no user is found, create a placeholder user for the test email.
		if ( empty( $test_user ) || ! ( $test_user instanceof WP_User ) ) {
			$test_user              = new WP_User( 0 );
			$test_user->user_login  = 'test_user';
			$test_user->user_email  = get_option( 'admin_email' );
			$test_user->display_name = __( 'Test User', 'pmpro-abandoned-cart-recovery' );
		}

		$all_levels = pmpro_getAllLevels( true );
		if ( ! empty( $all_levels ) ) {
			$test_membership_level = array_pop( $all_levels );
		} else {
			// Provide a default membership level object.
			$test_membership_level        = new stdClass();
			$test_membership_level->id    = 1;
			$test_membership_level->name  = __( 'Default Level', 'pmpro-abandoned-cart-recovery' );
		}

		return array( $test_user, $test_membership_level );
	}
}

/**
 * Register the email template.
 *
 * @since 1.0
 *
 * @param array $email_templates The email templates (template slug => email template class name)
 * @return array The modified email templates array.
 */
function pmproacr_email_templates_reminder_2( $email_templates ) {
	$email_templates['pmproacr_reminder_2'] = 'PMPro_Email_Template_PMProACR_Reminder_2';
	return $email_templates;
}
add_filter( 'pmpro_email_templates', 'pmproacr_email_templates_reminder_2' );