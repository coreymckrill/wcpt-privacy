<?php

namespace WordCamp\WCPT\Privacy;
defined( 'WPINC' ) || die();

use WP_Query;

add_filter( 'wp_privacy_personal_data_exporters', __NAMESPACE__ . '\register_personal_data_exporters' );
//add_filter( 'wp_privacy_personal_data_erasers', __NAMESPACE__ . '\register_personal_data_erasers' );

/**
 * Registers the personal data exporter for each WordCamp post type.
 *
 * @param array $exporters
 *
 * @return array
 */
function register_personal_data_exporters( $exporters ) {
	$exporters['wcb_speaker'] = array(
		'exporter_friendly_name' => __( 'WordCamp Application Data', 'wordcamporg' ),
		'callback'               => __NAMESPACE__ . '\personal_data_exporter',
	);

	return $exporters;
}

/**
 * Finds and exports personal data associated with an email address in a WCPT post.
 *
 * @param string $email_address
 * @param int    $page
 *
 * @return array
 */
function personal_data_exporter( $email_address, $page ) {
	$post_query     = _get_wordcamp_posts( $email_address, $page );
	$email_keys     = _get_email_postmeta_keys();
	$data_to_export = array();

	foreach ( (array) $post_query->posts as $post ) {
		$wcpt_data_to_export = [];

		foreach ( $email_keys as $email_key => $props ) {
			$email_value = get_post_meta( $post->ID, $email_key, true );

			if ( $email_value === $email_address ) {
				$wcpt_data_to_export[] = [
					'name'  => $email_key,
					'value' => $email_value,
				];

				foreach ( $props['assoc_fields'] as $assoc_key ) {
					$assoc_value = get_post_meta( $post->ID, $assoc_key, true );

					if ( ! empty( $assoc_value ) ) {
						$wcpt_data_to_export[] = [
							'name'  => $assoc_key,
							'value' => $assoc_value,
						];
					}
				}
			}
		}


	}

	$done = $post_query->max_num_pages <= $page;

	return [
		'data' => $data_to_export,
		'done' => $done,
	];
}

/**
 * Registers the personal data eraser for each WordCamp post type.
 *
 * @param array $erasers
 *
 * @return array
 */
function register_personal_data_erasers( $erasers ) {
	$erasers['wcb_speaker'] = array(
		'exporter_friendly_name' => __( 'WordCamp Application Data', 'wordcamporg' ),
		'callback'               => __NAMESPACE__ . '\personal_data_eraser',
	);

	return $erasers;
}

/**
 * Finds and erases personal data associated with an email address in a WCPT post.
 *
 * @param string $email_address
 * @param int    $page
 *
 * @return array
 */
function personal_data_eraser( $email_address, $page ) {
	$post_query     = _get_wordcamp_posts( $email_address, $page );
	$items_removed  = false;
	$items_retained = false;
	$messages       = [];

	foreach ( (array) $post_query->posts as $post ) {
		// @todo
	}

	$done = $post_query->max_num_pages <= $page;

	return array(
		'items_removed'  => $items_removed,
		'items_retained' => $items_retained,
		'messages'       => $messages,
		'done'           => $done,
	);
}

/**
 * Get the list of WCPT posts associated with a particular email address.
 *
 * @param string $email_address
 * @param int    $page
 *
 * @return WP_Query
 */
function _get_wordcamp_posts( $email_address, $page ) {
	$page   = (int) $page;
	$number = 20;

	// @todo Given the number of meta query clauses here, would we be better off with a custom SELECT query that looks
	// for the email address in _any_ postmeta row?
	$args = [
		'post_type'      => WCPT_POST_TYPE_ID,
		'post_status'    => 'any',
		'orderby'        => 'ID',
		'numberposts'    => - 1,
		'perm'           => 'readable',
		'posts_per_page' => $number,
		'paged'          => $page,
		'meta_query'     => [
			'relation' => 'OR',
			[
				'key'     => '_application_data',
				'value'   => $email_address,
				'compare' => 'LIKE', // There are multiple places in the serialized array where an email address could be.
			],
		],
	];

	$email_keys = _get_email_postmeta_keys();

	foreach ( $email_keys as $key => $props ) {
		$args['meta_query'][] = [
			'key'   => $key,
			'value' => $email_address,
		];
	}

	return new WP_Query( $args );
}

/**
 * Define the list of postmeta fields that may contain an email address, and other fields associated with each of them.
 *
 * @return array
 */
function _get_email_postmeta_keys() {
	// @todo Since the postmeta keys are also the field labels, we might need to expand this array to include translatable
	// strings for the labels.

	return [
		'Email Address' => [
			'assoc_fields' => [
				'Organizer Name',
				'WordPress.org Username',
				'Telephone',
				'Mailing Address',
			],
		],
		'Sponsor Wrangler E-mail Address' => [
			'assoc_fields' => [
				'Sponsor Wrangler Name',
			],
		],
		'Budget Wrangler E-mail Address' => [
			'assoc_fields' => [
				'Budget Wrangler Name',
			],
		],
		'Venue Wrangler E-mail Address' => [
			'assoc_fields' => [
				'Venue Wrangler Name',
			],
		],
		'Speaker Wrangler E-mail Address' => [
			'assoc_fields' => [
				'Speaker Wrangler Name',
			],
		],
		'Food/Beverage Wrangler E-mail Address' => [
			'assoc_fields' => [
				'Food/Beverage Wrangler Name',
			],
		],
		'Swag Wrangler E-mail Address' => [
			'assoc_fields' => [
				'Swag Wrangler Name',
			],
		],
		'Volunteer Wrangler E-mail Address' => [
			'assoc_fields' => [
				'Volunteer Wrangler Name',
			],
		],
		'Printing Wrangler E-mail Address' => [
			'assoc_fields' => [
				'Printing Wrangler Name',
			],
		],
		'Design Wrangler E-mail Address' => [
			'assoc_fields' => [
				'Design Wrangler Name',
			],
		],
		'Website Wrangler E-mail Address' => [
			'assoc_fields' => [
				'Website Wrangler Name',
			],
		],
		'Social Media/Publicity Wrangler E-mail Address' => [
			'assoc_fields' => [
				'Social Media/Publicity Wrangler Name',
			],
		],
		'A/V Wrangler E-mail Address' => [
			'assoc_fields' => [
				'A/V Wrangler Name',
			],
		],
		'Party Wrangler E-mail Address' => [
			'assoc_fields' => [
				'Party Wrangler Name',
			],
		],
		'Travel Wrangler E-mail Address' => [
			'assoc_fields' => [
				'Travel Wrangler Name',
			],
		],
		'Safety Wrangler E-mail Address' => [
			'assoc_fields' => [
				'Safety Wrangler Name',
			],
		],
		'Mentor E-mail Address' => [
			'assoc_fields' => [
				'Mentor WordPress.org User Name',
				'Mentor Name',
			],
		],
	];
}
