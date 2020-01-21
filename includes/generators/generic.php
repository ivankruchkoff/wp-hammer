<?php

namespace WP_CLI\Hammer\Generators\Generic;
use WP_CLI\Iterators\Query;
use joshtronic\LoremIpsum;
use PWGen\PWGen;
use MarkovPHP\WordChain;

/**
 * Lorem Ipsum content generator
 * @param $length number of words
 *
 * @return string of ipsum text of specified length
 */
function ipsum( $length ) {
	$lipsum = new LoremIpsum();
	// Since all Lorem Ipsum strings start with lorem ipsum dolor sit amet consectetur adipiscing elit, we remove
	// words 3-8, 6 words, so that shorter strings (e.g. titles) have more variety in their words.
	return str_replace( ' dolor sit amet consectetur adipiscing elit', '', $lipsum->words( $length + 6 ) );
}

/**
 * Random string content generator
 * @param $length number of words
 *
 * @return string random string of content
 */
function random( $length ) {
	$random = new PWGen();
	$random->setNumerals( false );
	$random->setCapitalize( false );
	$content = '';
	if ( $length > 0 ) {
		while ( $length -- > 0 ) {
			$random->setLength( rand( 4, 8 ) ); // Generate words of 4-8 chars
			$content .= $random->generate() . ' ';
		}
	}
	return rtrim( $content );
}

/**
 * Markov chain content generator
 * @param $length number of words
 *
 * @return string generated string
 */
function markov( $length, $table, $column ) {
	$sample = get_content_for_table_column( $table, $column );
	$content = '';
	if ( $length > 0 ) {
		$chain = new WordChain( $sample, $length );
		$content = $chain->generate( $length );
	}
	return $content;
}

/**
 * Fetch all content for $wpdb->prefix_$table.$column for using as a sample pool for markov chains
 * @param $table table name
 * @param $column column name
 *
 * @return string content of the column in the table
 */
function get_content_for_table_column( $table, $column ) {
	$transient_key = "wp_hammer_table_content_{$table}_{$column}";
	$content = get_transient( $transient_key );
	if ( false === $content ) {
		global $wpdb;
		$query   = "SELECT $column FROM $wpdb->prefix{$table}";
		$results = new Query( $query );
		$content = '';
		while ( $results->valid() ) {
			$content .= $results->current()->{$column};
			$results->next();

		}
		if ( strlen( $content ) ) {
			set_transient( $transient_key, $content, 30 );
		}
	}
	return $content;
}
