<?php
/**
 * Plugin Name: Acasolino – Progetti
 * Description: Tipo di contenuto Progetti (archivio /progetti/, singolo /progetto/<slug>/) con i campi comuni.
 */

defined('ABSPATH') || exit;

const ACASOLINO_PROGETTO_CAMPI = [
	'cliente'     => 'Cliente',
	'anno'        => 'Anno',
	'ruolo'       => 'Ruolo',
	'link'        => 'Link al progetto',
];

add_action('init', function () {
	register_post_type('progetto', [
		'labels' => [
			'name'          => 'Progetti',
			'singular_name' => 'Progetto',
			'add_new_item'  => 'Nuovo progetto',
			'edit_item'     => 'Modifica progetto',
			'all_items'     => 'Tutti i progetti',
		],
		'public'       => true,
		'has_archive'  => 'progetti',
		'rewrite'      => ['slug' => 'progetto', 'with_front' => false],
		'menu_icon'    => 'dashicons-portfolio',
		'menu_position'=> 5,
		'show_in_rest' => true,
		'supports'     => ['title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'revisions', 'custom-fields'],
	]);

	register_taxonomy('tipologia', 'progetto', [
		'labels'            => ['name' => 'Tipologie', 'singular_name' => 'Tipologia'],
		'public'            => true,
		'hierarchical'      => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'rewrite'           => ['slug' => 'progetti/tipologia', 'with_front' => false],
	]);

	foreach (array_keys(ACASOLINO_PROGETTO_CAMPI) as $chiave) {
		register_post_meta('progetto', $chiave, [
			'type' => 'string', 'single' => true, 'show_in_rest' => true,
			'sanitize_callback' => $chiave === 'link' ? 'esc_url_raw' : 'sanitize_text_field',
		]);
	}
	register_post_meta('progetto', 'in_evidenza', [
		'type' => 'boolean', 'single' => true, 'show_in_rest' => true, 'default' => false,
	]);
});

add_action('add_meta_boxes_progetto', function () {
	add_meta_box('acasolino-progetto', 'Scheda progetto', function ($post) {
		wp_nonce_field('acasolino_progetto', 'acasolino_progetto_nonce');
		echo '<table class="form-table" role="presentation">';
		foreach (ACASOLINO_PROGETTO_CAMPI as $chiave => $etichetta) {
			printf(
				'<tr><th><label for="ac-%1$s">%2$s</label></th><td><input type="%3$s" id="ac-%1$s" name="ac_%1$s" value="%4$s" class="regular-text"></td></tr>',
				esc_attr($chiave), esc_html($etichetta), $chiave === 'link' ? 'url' : 'text',
				esc_attr(get_post_meta($post->ID, $chiave, true))
			);
		}
		printf(
			'<tr><th>In evidenza</th><td><label><input type="checkbox" name="ac_in_evidenza" value="1" %s> Mostra in home</label></td></tr>',
			checked(get_post_meta($post->ID, 'in_evidenza', true), true, false)
		);
		echo '</table>';
	}, 'progetto', 'normal', 'high');
});

add_action('save_post_progetto', function ($post_id) {
	if (!isset($_POST['acasolino_progetto_nonce'])
		|| !wp_verify_nonce($_POST['acasolino_progetto_nonce'], 'acasolino_progetto')
		|| (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
		|| !current_user_can('edit_post', $post_id)) {
		return;
	}
	foreach (array_keys(ACASOLINO_PROGETTO_CAMPI) as $chiave) {
		update_post_meta($post_id, $chiave, wp_unslash($_POST['ac_' . $chiave] ?? ''));
	}
	update_post_meta($post_id, 'in_evidenza', !empty($_POST['ac_in_evidenza']));
});

// Archivio progetti: ordine manuale (attributi pagina), poi data.
add_action('pre_get_posts', function ($query) {
	if (!is_admin() && $query->is_main_query() && $query->is_post_type_archive('progetto')) {
		$query->set('orderby', ['menu_order' => 'ASC', 'date' => 'DESC']);
	}
});
