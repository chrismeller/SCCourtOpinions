<?php

	header( 'Content-Type: text/xml' );

	error_reporting(-1);
	ini_set('display_errors', true);
	date_default_timezone_set('UTC');

	$expiration = 21600;		// 6 hours

	if ( file_exists( 'cache/atom_supreme.cache' ) ) {
		$mtime = filemtime( 'cache/atom_supreme.cache' );
		if ( time() < $mtime + $expiration ) {
			echo file_get_contents( 'cache/atom_supreme.cache' );
			die();
		}
	}

	require('sccourtopinions/opinion.php');
	require('sccourtopinions/sccourtopinions.php');
	require('sccourtopinions/supremecourt.php');

	$opinions = SCCourtOpinions\SupremeCourt::factory()->opinions();

	$dom = new DOMDocument('1.0', 'utf-8');
	$dom->formatOutput = true;

	// create the root feed node with its namespace
	$feed = $dom->createElementNS( 'http://www.w3.org/2005/Atom', 'feed' );

	// create the title node
	$title_node = $dom->createTextNode( 'South Carolina Supreme Court Published Opinions' );
	$title = $dom->createElement( 'title' );
	$title->appendChild( $title_node );

	// add the title to the feed node
	$feed->appendChild( $title );

	// and the link node
	$link = $dom->createElement( 'link' );
	$link->setAttribute( 'href', 'http://www.judicial.state.sc.us/opinions/indexSCPub.cfm' );

	// add it to the feed node
	$feed->appendChild( $link );

	// add the "required" "self" link node

	// first we have to figure out what the URL actually is
	$self_proto = ( isset( $_SERVER['HTTPS'] ) && !empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] != 'off' ) ? 'https://' : 'http://';
	$self_host = ( isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : ( isset( $_SERVER['SERVER_NAME'] ) ? $_SERVER['SERVER_NAME'] : '' ) );	// HTTP_HOST is not set for HTTP/1.0 requests
	$self_url = $self_proto . $self_host . ( isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '' );
	$self_link = $dom->createElement( 'link' );
	$self_link->setAttribute( 'href', $self_url );
	$self_link->setAttribute( 'rel', 'self' );

	// and add it to the feed
	$feed->appendChild( $self_link );

	// figure out the last updated date - should be the date of the first item in the list
	if ( count( $opinions ) > 0 ) {
		$last_updated = $opinions[0]->date;
	}
	else {
		// otherwise, it's now - we just checked
		$last_updated = new DateTime();
	}

	$updated = $dom->createElement( 'updated', $last_updated->format( DateTime::ATOM ) );

	$feed->appendChild( $updated );

	$author = $dom->createElement( 'author' );
	$author_name = $dom->createElement( 'name', 'Supreme Court of South Carolina' );

	$author->appendChild( $author_name );

	$feed->appendChild( $author );

	// the search criteria never changes, simply key it on the content
	$search_key = 'south carolina supreme court opinions';
	$uuid = hash( 'md5', $search_key );		// md5 so we get 32 chars back

	$uuid_hex = uuid_hex( $uuid );

	$id = $dom->createElement( 'id', 'urn:uuid:' . $uuid_hex );

	$feed->appendChild( $id );

	$i = 0;
	foreach ( $opinions as $item ) {

		$entry = $dom->createElement( 'entry' );

		$title_node = $dom->createTextNode( $item->title );
		$title = $dom->createElement('title');
		$title->appendChild( $title_node );

		$link = $dom->createElement( 'link' );
		$link->setAttribute( 'href', $item->url );

		// for orders, the id is not unique, but the URL is
		if ( $item->type == 'order' ) {
			$uuid = hash( 'md5', $item->url );
		}
		else {
			$uuid = hash( 'md5', $item->id );
		}
		$uuid_hex = uuid_hex( $uuid );
		$id = $dom->createElement( 'id', 'urn:uuid:' . $uuid_hex );

		$updated = $dom->createElement( 'updated', $item->date->format( DateTime::ATOM ) );

		$summary_node = $dom->createTextNode( $item->description  );
		$summary = $dom->createElement( 'summary' );
		$summary->appendChild( $summary_node );
		$summary->setAttribute( 'type', 'html' );

		$entry->appendChild( $title );
		$entry->appendChild( $link );
		$entry->appendChild( $id );
		$entry->appendChild( $updated );
		$entry->appendChild( $summary );

		$feed->appendChild( $entry );

		$i++;

	}

	// add the root feed node to the document
	$dom->appendChild( $feed );

	$xml = $dom->saveXML();

	file_put_contents( 'cache/atom_supreme.cache', $xml );

	echo $xml;

	function uuid_hex ( $uuid ) {
		$uuid = str_split( $uuid );
		$uuid_hex = '';
		for ( $i = 0; $i < 32; $i++ ) {
			if ( $i == 8 || $i == 12 || $i == 16 || $i == 20 ) {
				$uuid_hex .= '-';
			}
			$uuid_hex .= $uuid[ $i ];
		}

		return $uuid_hex;
	}

?>