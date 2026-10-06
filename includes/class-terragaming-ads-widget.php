<?php
/**
 * The classic "TerraGaming Media Ad" widget (sidebars of classic themes).
 *
 * @package TerraGaming_Ads
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * One ad unit placement in a widget area.
 */
final class TerraGaming_Ads_Widget extends WP_Widget {
	/** Registers the widget. */
	public function __construct() {
		parent::__construct(
			'terragaming_ads_widget',
			__( 'TerraGaming Media Ad', 'terragaming-media-ads' ),
			array( 'description' => __( 'A TerraGaming Media ad unit.', 'terragaming-media-ads' ) )
		);
	}

	/**
	 * Front end.
	 *
	 * @param array<string, string> $args     Widget area markup.
	 * @param array<string, string> $instance Saved values.
	 */
	public function widget( $args, $instance ) {
		$html = TerraGaming_Ads_Frontend::placement( $instance['unit'] ?? '' );
		if ( '' === $html ) {
			return;
		}
		echo wp_kses_post( $args['before_widget'] ?? '' );
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from a validated id with esc_attr().
		echo wp_kses_post( $args['after_widget'] ?? '' );
	}

	/**
	 * Admin form.
	 *
	 * @param array<string, string> $instance Saved values.
	 */
	public function form( $instance ) {
		$unit = $instance['unit'] ?? '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'unit' ) ); ?>"><?php esc_html_e( 'Ad unit ID', 'terragaming-media-ads' ); ?></label>
			<input class="widefat code" id="<?php echo esc_attr( $this->get_field_id( 'unit' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'unit' ) ); ?>" type="text" value="<?php echo esc_attr( $unit ); ?>" placeholder="TGM-…" />
		</p>
		<?php
		return '';
	}

	/**
	 * Saves the unit id (validated).
	 *
	 * @param array<string, string> $new_instance Submitted values.
	 * @param array<string, string> $old_instance Previous values.
	 * @return array<string, string>
	 */
	public function update( $new_instance, $old_instance ) {
		unset( $old_instance );
		return array( 'unit' => TerraGaming_Ads_Site_Tag::valid( $new_instance['unit'] ?? '', TerraGaming_Ads_Site_Tag::UNIT ) );
	}
}
