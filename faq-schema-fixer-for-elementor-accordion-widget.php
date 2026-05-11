<?php
/**
 * Plugin Name: FAQ Schema Fixer for Elementor Accordion Widget
 * Description: Rebuilds FAQPage JSON-LD for Elementor Nested Accordion widgets using the real questions and answers rendered on the page.
 * Version: 1.2.0
 * Author: Armando Caballero
 * Author URI: https://armandi.es
 * Text Domain: faq-schema-fixer-for-elementor-accordion-widget
 * Domain Path: /languages
 * License: GPL-2.0-or-later
 * Requires at least: 6.2
 * Requires PHP: 7.4
 *
 * @package FAQSchemaFixerForElementorAccordionWidget
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_init', 'faq_schema_fixer_for_elementor_accordion_widget_register_settings' );
add_action( 'admin_menu', 'faq_schema_fixer_for_elementor_accordion_widget_add_settings_page' );
add_action( 'admin_enqueue_scripts', 'faq_schema_fixer_for_elementor_accordion_widget_enqueue_admin_assets' );
add_action( 'template_redirect', 'faq_schema_fixer_for_elementor_accordion_widget_start_buffer' );
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'faq_schema_fixer_for_elementor_accordion_widget_plugin_action_links' );

/**
 * Return the option key used by the plugin.
 *
 * @return string
 */
function faq_schema_fixer_for_elementor_accordion_widget_option_key() {
	return 'faq_schema_fixer_for_elementor_accordion_widget_settings';
}

/**
 * Return default plugin settings.
 *
 * @return array<string, mixed>
 */
function faq_schema_fixer_for_elementor_accordion_widget_get_defaults() {
	return array(
		'page_ids'         => array(),
		'accordion_class'  => '',
	);
}

/**
 * Return plugin settings merged with defaults.
 *
 * @return array<string, mixed>
 */
function faq_schema_fixer_for_elementor_accordion_widget_get_settings() {
	$settings = get_option( faq_schema_fixer_for_elementor_accordion_widget_option_key(), array() );
	$settings = is_array( $settings ) ? $settings : array();

	return wp_parse_args( $settings, faq_schema_fixer_for_elementor_accordion_widget_get_defaults() );
}

/**
 * Register plugin settings.
 *
 * @return void
 */
function faq_schema_fixer_for_elementor_accordion_widget_register_settings() {
	register_setting(
		'faq_schema_fixer_for_elementor_accordion_widget_settings_group',
		faq_schema_fixer_for_elementor_accordion_widget_option_key(),
		'faq_schema_fixer_for_elementor_accordion_widget_sanitize_settings'
	);
}

/**
 * Sanitize plugin settings.
 *
 * @param array<string, mixed> $input Raw settings.
 * @return array<string, mixed>
 */
function faq_schema_fixer_for_elementor_accordion_widget_sanitize_settings( $input ) {
	$defaults = faq_schema_fixer_for_elementor_accordion_widget_get_defaults();
	$input    = is_array( $input ) ? $input : array();

	$page_ids = array();
	if ( ! empty( $input['page_ids'] ) && is_array( $input['page_ids'] ) ) {
		$page_ids = array_filter( array_map( 'absint', $input['page_ids'] ) );
		$page_ids = array_values( array_unique( $page_ids ) );
	}

	$accordion_class = isset( $input['accordion_class'] ) ? sanitize_html_class( (string) $input['accordion_class'] ) : '';

	if ( empty( $page_ids ) ) {
		add_settings_error(
			faq_schema_fixer_for_elementor_accordion_widget_option_key(),
			'faq_schema_fixer_for_elementor_accordion_widget_missing_pages',
			__( 'Select at least one page where the fix should run.', 'faq-schema-fixer-for-elementor-accordion-widget' ),
			'warning'
		);
	}

	if ( '' === $accordion_class ) {
		add_settings_error(
			faq_schema_fixer_for_elementor_accordion_widget_option_key(),
			'faq_schema_fixer_for_elementor_accordion_widget_missing_class',
			__( 'Enter the custom CSS class assigned to the Elementor Nested Accordion widget.', 'faq-schema-fixer-for-elementor-accordion-widget' ),
			'warning'
		);
	}

	return array(
		'page_ids'        => $page_ids,
		'accordion_class' => '' !== $accordion_class ? $accordion_class : $defaults['accordion_class'],
	);
}

/**
 * Add a settings page under Tools.
 *
 * @return void
 */
function faq_schema_fixer_for_elementor_accordion_widget_add_settings_page() {
	add_management_page(
		__( 'FAQ Schema Fixer', 'faq-schema-fixer-for-elementor-accordion-widget' ),
		__( 'FAQ Schema Fixer', 'faq-schema-fixer-for-elementor-accordion-widget' ),
		'manage_options',
		'faq-schema-fixer-for-elementor-accordion-widget',
		'faq_schema_fixer_for_elementor_accordion_widget_render_settings_page'
	);
}

/**
 * Enqueue admin assets for the settings page.
 *
 * @param string $hook_suffix Current admin hook.
 * @return void
 */
function faq_schema_fixer_for_elementor_accordion_widget_enqueue_admin_assets( $hook_suffix ) {
	if ( 'tools_page_faq-schema-fixer-for-elementor-accordion-widget' !== $hook_suffix ) {
		return;
	}

	wp_enqueue_style(
		'faq-schema-fixer-for-elementor-accordion-widget-admin',
		plugin_dir_url( __FILE__ ) . 'assets/css/admin.css',
		array(),
		'1.2.0'
	);

	wp_enqueue_script(
		'faq-schema-fixer-for-elementor-accordion-widget-admin',
		plugin_dir_url( __FILE__ ) . 'assets/js/admin.js',
		array(),
		'1.2.0',
		true
	);
}

/**
 * Add a direct settings link on the plugins screen.
 *
 * @param array<int, string> $links Existing plugin links.
 * @return array<int, string>
 */
function faq_schema_fixer_for_elementor_accordion_widget_plugin_action_links( $links ) {
	$settings_link = sprintf(
		'<a href="%s">%s</a>',
		esc_url( admin_url( 'tools.php?page=faq-schema-fixer-for-elementor-accordion-widget' ) ),
		esc_html__( 'Settings', 'faq-schema-fixer-for-elementor-accordion-widget' )
	);

	array_unshift( $links, $settings_link );

	return $links;
}

/**
 * Render the plugin settings page.
 *
 * @return void
 */
function faq_schema_fixer_for_elementor_accordion_widget_render_settings_page() {
	$settings     = faq_schema_fixer_for_elementor_accordion_widget_get_settings();
	$pages        = get_pages(
		array(
			'sort_column' => 'post_title',
			'post_status' => array( 'publish', 'private', 'draft', 'future' ),
		)
	);
	$selected_ids = ! empty( $settings['page_ids'] ) && is_array( $settings['page_ids'] ) ? array_map( 'absint', $settings['page_ids'] ) : array();
	$selected_map = array_fill_keys( $selected_ids, true );
	?>
	<div class="wrap fsfew-wrap">
		<h1><?php esc_html_e( 'FAQ Schema Fixer for Elementor Accordion Widget', 'faq-schema-fixer-for-elementor-accordion-widget' ); ?></h1>
		<?php settings_errors( faq_schema_fixer_for_elementor_accordion_widget_option_key() ); ?>

		<div class="fsfew-grid fsfew-grid-3">
			<div class="fsfew-card fsfew-summary-card">
				<h2><?php esc_html_e( 'Selected pages', 'faq-schema-fixer-for-elementor-accordion-widget' ); ?></h2>
				<p><?php echo esc_html( count( $selected_ids ) ); ?></p>
			</div>
			<div class="fsfew-card fsfew-summary-card">
				<h2><?php esc_html_e( 'Accordion CSS class', 'faq-schema-fixer-for-elementor-accordion-widget' ); ?></h2>
				<p><?php echo esc_html( ! empty( $settings['accordion_class'] ) ? $settings['accordion_class'] : __( 'Not configured', 'faq-schema-fixer-for-elementor-accordion-widget' ) ); ?></p>
			</div>
			<div class="fsfew-card fsfew-summary-card">
				<h2><?php esc_html_e( 'Processing scope', 'faq-schema-fixer-for-elementor-accordion-widget' ); ?></h2>
				<p><?php esc_html_e( 'Only the selected pages and the chosen Elementor accordion class.', 'faq-schema-fixer-for-elementor-accordion-widget' ); ?></p>
			</div>
		</div>

		<div class="fsfew-grid fsfew-grid-2">
			<form method="post" action="options.php" class="fsfew-card fsfew-settings-card">
				<?php settings_fields( 'faq_schema_fixer_for_elementor_accordion_widget_settings_group' ); ?>
				<h2><?php esc_html_e( 'Settings', 'faq-schema-fixer-for-elementor-accordion-widget' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Limit the fix to the exact pages and accordion widget you want to target.', 'faq-schema-fixer-for-elementor-accordion-widget' ); ?></p>

				<table class="form-table" role="presentation">
					<tbody>
						<tr>
							<th scope="row">
								<label for="fsfew-accordion-class"><?php esc_html_e( 'Elementor accordion CSS class', 'faq-schema-fixer-for-elementor-accordion-widget' ); ?></label>
							</th>
							<td>
								<input
									type="text"
									name="<?php echo esc_attr( faq_schema_fixer_for_elementor_accordion_widget_option_key() ); ?>[accordion_class]"
									id="fsfew-accordion-class"
									class="regular-text"
									value="<?php echo esc_attr( $settings['accordion_class'] ); ?>"
									placeholder="faq-schema-fix"
								>
								<p class="description"><?php esc_html_e( 'Enter one CSS class without a dot. Elementor adds this class to the outer widget wrapper generated on the frontend. Assign it in Advanced > CSS Classes on the Nested Accordion widget.', 'faq-schema-fixer-for-elementor-accordion-widget' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="fsfew-page-search"><?php esc_html_e( 'Pages where the fix should run', 'faq-schema-fixer-for-elementor-accordion-widget' ); ?></label>
							</th>
							<td>
								<div class="fsfew-page-picker" data-empty-label="<?php echo esc_attr__( 'No pages selected yet.', 'faq-schema-fixer-for-elementor-accordion-widget' ); ?>">
									<input
										type="search"
										id="fsfew-page-search"
										class="regular-text fsfew-page-search"
										placeholder="<?php esc_attr_e( 'Search pages by title...', 'faq-schema-fixer-for-elementor-accordion-widget' ); ?>"
									>
									<div class="fsfew-selected-pages" id="fsfew-selected-pages" aria-live="polite">
										<?php if ( empty( $selected_ids ) ) : ?>
											<span class="fsfew-selected-empty"><?php esc_html_e( 'No pages selected yet.', 'faq-schema-fixer-for-elementor-accordion-widget' ); ?></span>
										<?php endif; ?>
									</div>
									<div class="fsfew-page-list" id="fsfew-page-list">
										<?php foreach ( $pages as $page ) : ?>
											<?php
											$page_id    = (int) $page->ID;
											/* translators: %d: WordPress page ID. */
											$page_title = $page->post_title ? $page->post_title : sprintf( __( 'Page #%d', 'faq-schema-fixer-for-elementor-accordion-widget' ), $page_id );
											$is_checked = isset( $selected_map[ $page_id ] );
											?>
											<label class="fsfew-page-option" data-page-title="<?php echo esc_attr( $page_title ); ?>">
												<input
													type="checkbox"
													name="<?php echo esc_attr( faq_schema_fixer_for_elementor_accordion_widget_option_key() ); ?>[page_ids][]"
													value="<?php echo esc_attr( $page_id ); ?>"
													data-page-title="<?php echo esc_attr( $page_title ); ?>"
													<?php checked( $is_checked ); ?>
												>
												<span class="fsfew-page-option-title"><?php echo esc_html( $page_title ); ?></span>
												<span class="fsfew-page-option-meta">#<?php echo esc_html( $page_id ); ?></span>
											</label>
										<?php endforeach; ?>
									</div>
								</div>
								<p class="description"><?php esc_html_e( 'Search pages, tick the ones you want, and they will appear above as selected pills.', 'faq-schema-fixer-for-elementor-accordion-widget' ); ?></p>
							</td>
						</tr>
					</tbody>
				</table>

				<?php submit_button( __( 'Save settings', 'faq-schema-fixer-for-elementor-accordion-widget' ) ); ?>
			</form>

			<div class="fsfew-card fsfew-help-card">
				<h2><?php esc_html_e( 'How to use this plugin', 'faq-schema-fixer-for-elementor-accordion-widget' ); ?></h2>
				<ol class="fsfew-help-steps">
					<li><?php esc_html_e( 'Open the page in Elementor where you are using the Nested Accordion widget as an FAQ block.', 'faq-schema-fixer-for-elementor-accordion-widget' ); ?></li>
					<li><?php esc_html_e( 'Edit the specific accordion widget and open Advanced > CSS Classes.', 'faq-schema-fixer-for-elementor-accordion-widget' ); ?></li>
					<li><?php esc_html_e( 'Add a unique class, for example faq-schema-fix.', 'faq-schema-fixer-for-elementor-accordion-widget' ); ?></li>
					<li><?php esc_html_e( 'Come back to this tools page, enter the same class, and select the pages where the fix should run.', 'faq-schema-fixer-for-elementor-accordion-widget' ); ?></li>
					<li><?php esc_html_e( 'Save the settings and reload the selected page on the frontend.', 'faq-schema-fixer-for-elementor-accordion-widget' ); ?></li>
					<li><?php esc_html_e( 'Inspect the final HTML or use a structured data testing tool to confirm the FAQPage JSON-LD has been rebuilt correctly.', 'faq-schema-fixer-for-elementor-accordion-widget' ); ?></li>
				</ol>

				<h3><?php esc_html_e( 'What the plugin changes', 'faq-schema-fixer-for-elementor-accordion-widget' ); ?></h3>
				<p><?php esc_html_e( 'The plugin reads the visible question and answer text rendered by the targeted Elementor Nested Accordion widget, rebuilds a FAQPage JSON-LD block, and either replaces the existing FAQ schema in that widget or injects a new one if none is present.', 'faq-schema-fixer-for-elementor-accordion-widget' ); ?></p>

				<h3><?php esc_html_e( 'Validate the result', 'faq-schema-fixer-for-elementor-accordion-widget' ); ?></h3>
				<p><?php esc_html_e( 'After saving the settings and reloading the page, validate the final frontend output with these tools:', 'faq-schema-fixer-for-elementor-accordion-widget' ); ?></p>
				<ul class="fsfew-validation-links">
					<li><a href="https://search.google.com/test/rich-results" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Google Rich Results Test', 'faq-schema-fixer-for-elementor-accordion-widget' ); ?></a></li>
					<li><a href="https://validator.schema.org/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Schema.org Validator', 'faq-schema-fixer-for-elementor-accordion-widget' ); ?></a></li>
				</ul>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Start a frontend buffer only for configured pages.
 *
 * @return void
 */
function faq_schema_fixer_for_elementor_accordion_widget_start_buffer() {
	if ( is_admin() || wp_is_json_request() || is_feed() || ! is_singular() ) {
		return;
	}

	$settings = faq_schema_fixer_for_elementor_accordion_widget_get_settings();
	$page_ids = ! empty( $settings['page_ids'] ) && is_array( $settings['page_ids'] ) ? array_map( 'absint', $settings['page_ids'] ) : array();
	$class    = ! empty( $settings['accordion_class'] ) ? sanitize_html_class( (string) $settings['accordion_class'] ) : '';
	$object_id = get_queried_object_id();

	if ( empty( $page_ids ) || '' === $class || ! $object_id || ! in_array( (int) $object_id, $page_ids, true ) ) {
		return;
	}

	ob_start( 'faq_schema_fixer_for_elementor_accordion_widget_process_buffer' );
}

/**
 * Process the full HTML response and rebuild FAQ schema for matching Nested Accordion widgets.
 *
 * @param string $html Full HTML buffer.
 * @return string
 */
function faq_schema_fixer_for_elementor_accordion_widget_process_buffer( $html ) {
	if ( ! is_string( $html ) || '' === $html ) {
		return $html;
	}

	$settings        = faq_schema_fixer_for_elementor_accordion_widget_get_settings();
	$accordion_class = ! empty( $settings['accordion_class'] ) ? sanitize_html_class( (string) $settings['accordion_class'] ) : '';

	if ( '' === $accordion_class || false === strpos( $html, 'elementor-widget-n-accordion' ) || false === strpos( $html, $accordion_class ) ) {
		return $html;
	}

	if ( ! class_exists( 'DOMDocument' ) || ! class_exists( 'DOMXPath' ) || ! function_exists( 'mb_detect_encoding' ) || ! function_exists( 'mb_convert_encoding' ) ) {
		return $html;
	}

	$previous_errors = libxml_use_internal_errors( true );

	$dom      = new DOMDocument();
	$encoding = mb_detect_encoding( $html, 'UTF-8, ISO-8859-1', true );
	$encoding = $encoding ? $encoding : 'UTF-8';
	$loaded   = $dom->loadHTML( '<?xml encoding="UTF-8">' . mb_convert_encoding( $html, 'HTML-ENTITIES', $encoding ) );

	if ( ! $loaded ) {
		libxml_clear_errors();
		libxml_use_internal_errors( $previous_errors );
		return $html;
	}

	$xpath        = new DOMXPath( $dom );
	$class_token  = trim( $accordion_class );
	$widgets      = $xpath->query(
		sprintf(
			"//*[contains(concat(' ', normalize-space(@class), ' '), ' %s ') and contains(concat(' ', normalize-space(@class), ' '), ' elementor-widget-n-accordion ')]",
			$class_token
		)
	);

	if ( ! $widgets || 0 === $widgets->length ) {
		libxml_clear_errors();
		libxml_use_internal_errors( $previous_errors );
		return $html;
	}

	foreach ( $widgets as $widget ) {
		if ( ! $widget instanceof DOMElement ) {
			continue;
		}

		$entities = faq_schema_fixer_for_elementor_accordion_widget_extract_entities( $xpath, $widget );

		if ( empty( $entities ) ) {
			continue;
		}

		$schema = array(
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => $entities,
		);

		$json   = wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		$script = faq_schema_fixer_for_elementor_accordion_widget_find_faq_script( $xpath, $widget );

		if ( $script instanceof DOMNode ) {
			while ( $script->firstChild ) {
				$script->removeChild( $script->firstChild );
			}

			$script->appendChild( $dom->createTextNode( $json ) );
			continue;
		}

		$container = $xpath->query( ".//*[contains(concat(' ', normalize-space(@class), ' '), ' elementor-widget-container ')]", $widget )->item(0);
		$container = $container instanceof DOMNode ? $container : $widget;
		$new_node  = $dom->createElement( 'script', $json );
		$new_node->setAttribute( 'type', 'application/ld+json' );
		$container->appendChild( $new_node );
	}

	$output = $dom->saveHTML();
	$output = is_string( $output ) ? $output : '';
	$output = preg_replace( '/^<!DOCTYPE.+?>/i', '', $output );
	$output = preg_replace( '/^<\?xml.+?\?>/i', '', $output );

	libxml_clear_errors();
	libxml_use_internal_errors( $previous_errors );

	return $output ? $output : $html;
}

/**
 * Extract FAQ entities from one Nested Accordion widget.
 *
 * @param DOMXPath   $xpath  DOM XPath helper.
 * @param DOMElement $widget Widget node.
 * @return array<int, array<string, mixed>>
 */
function faq_schema_fixer_for_elementor_accordion_widget_extract_entities( DOMXPath $xpath, DOMElement $widget ) {
	$items    = $xpath->query( ".//*[contains(concat(' ', normalize-space(@class), ' '), ' e-n-accordion-item ')]", $widget );
	$entities = array();

	if ( ! $items || 0 === $items->length ) {
		return $entities;
	}

	foreach ( $items as $item ) {
		if ( ! $item instanceof DOMElement ) {
			continue;
		}

		$title_node = $xpath->query( ".//*[contains(concat(' ', normalize-space(@class), ' '), ' e-n-accordion-item-title-text ')]", $item )->item(0);
		$question   = faq_schema_fixer_for_elementor_accordion_widget_normalize_text( $title_node );

		if ( '' === $question ) {
			continue;
		}

		$region_node = $xpath->query( ".//*[@role='region']", $item )->item(0);
		$answer      = faq_schema_fixer_for_elementor_accordion_widget_normalize_text( $region_node );

		$entities[] = array(
			'@type'          => 'Question',
			'name'           => $question,
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => $answer,
			),
		);
	}

	return $entities;
}

/**
 * Find the existing FAQ JSON-LD script inside the widget, if present.
 *
 * @param DOMXPath   $xpath  DOM XPath helper.
 * @param DOMElement $widget Widget node.
 * @return DOMNode|null
 */
function faq_schema_fixer_for_elementor_accordion_widget_find_faq_script( DOMXPath $xpath, DOMElement $widget ) {
	$scripts = $xpath->query( ".//script[@type='application/ld+json']", $widget );

	if ( ! $scripts || 0 === $scripts->length ) {
		return null;
	}

	foreach ( $scripts as $script ) {
		$content = $script instanceof DOMNode ? trim( (string) $script->textContent ) : '';

		if ( '' === $content ) {
			continue;
		}

		$decoded = json_decode( $content, true );
		if ( ! is_array( $decoded ) ) {
			continue;
		}

		if ( isset( $decoded['@type'] ) && 'FAQPage' === $decoded['@type'] ) {
			return $script;
		}
	}

	return null;
}

/**
 * Extract normalized plain text from a DOM node.
 *
 * @param DOMNode|null $node Source node.
 * @return string
 */
function faq_schema_fixer_for_elementor_accordion_widget_normalize_text( $node ) {
	if ( ! $node instanceof DOMNode ) {
		return '';
	}

	$text = preg_replace( '/\s+/u', ' ', (string) $node->textContent );
	return trim( (string) $text );
}
