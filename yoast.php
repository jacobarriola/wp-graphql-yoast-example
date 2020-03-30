<?php

namespace App;

const SEO_TYPE = 'SEO';

add_action( 'graphql_register_types', function () {

	register_graphql_object_type( SEO_TYPE, [
		'description' => 'SEO data for a given post',
		'fields'      => [
			'title'       => [
				'type'        => 'String',
				'description' => 'The SEO title for the post',
				'resolve'     => function ( $source, $args, $info, $context ) {

					/* @var $yoast \WPSEO_Frontend */
					/* @var $post \WPGraphQL\Model\Post */


					$yoast = $source['yoast'];
					$post  = $source['post'];

					$title = $yoast->get_seo_title( $post );

					// Provide a fallback if yoast title wasn't set
					if ( empty( $title ) ) {
						// Go get the Yoast formatted title
						$site_title_format = $yoast->get_title_from_options( 'title-' . $post->post_type, $post );

						$title = sprintf( '%s %s', $post->titleRaw, $site_title_format );
					}

					$yoast->reset();

					return $title;
				},
			],
			'description' => [
				'type'        => 'String',
				'description' => 'The SEO meta description for the post',
				'resolve'     => function ( $source, $args, $info, $context ) {
					$meta = get_post_meta( $source['post']->ID, '_yoast_wpseo_metadesc', true );

					return ! empty( $meta ) ? $meta : null;
				},
			],
		],
	] );


	$allowed_post_types = get_post_types( [ 'show_in_graphql' => true ], 'objects' );

	if ( ! empty( $allowed_post_types ) ) {

		foreach ( $allowed_post_types as $allowed_post_type ) {

			register_graphql_field( $allowed_post_type->graphql_single_name, 'seo', [
				'type'    => SEO_TYPE,
				'resolve' => function ( $post, $args, $info, $context ) {
					$yoast = \WPSEO_Frontend::get_instance();

					return [ 'post' => $post, 'yoast' => $yoast ];
				},
			] );
		}
	}


} );










