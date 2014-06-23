<?php

	namespace SCCourtOpinions;

	class SupremeCourt {

		const BASE_URL = 'http://www.judicial.state.sc.us/opinions/indexSCPub.cfm';
		const OPINIONS_BASE_URL = 'http://www.judicial.state.sc.us';

		protected function parse_dom ( $contents ) {

			$dom = new \DOMDocument( '1.0', 'utf-8' );

			// @ because most of these pages are invalid HTML... shocking
			@$dom->loadHTML( $contents );

			return $dom;

		}

		public static function factory ( ) {

			$class = __CLASS__;

			return new $class();

		}

		public function opinions ( $year = null, $month = null ) {

			$now = new \DateTime();
			if ( $year == null ) {
				$year = $now->format('Y');
			}

			if ( $month == null ) {
				$month = $now->format('n');
			}

			$url = self::BASE_URL . '?year=' . $year . '&month=' . $month;

			$contents = file_get_contents( $url );

			$dom = $this->parse_dom( $contents );

			$xpath = new \DOMXPath( $dom );

			// looks like we get headers in the formats "M-D-YYYY - Opinions" and "M-D-YYYY - Orders"... match both
			$headers = $xpath->query( '//div//b[ contains( text(), "- O" )]' );

			$opinions = array();
			for ( $i = 0; $i < $headers->length; $i++ ) {

				$header = $headers->item( $i );

				list( $opinions_date, $opinions_description ) = explode( ' - ', $header->nodeValue );

				$opinions_date = \DateTime::createFromFormat( 'm-d-Y', $opinions_date );

				// is there another header?
				if ( $i < $headers->length - 1 ) {
					$next_header = $headers->item( $i + 1 );
				}
				else {
					$next_header = null;
				}

				if ( $next_header ) {
					$query = '//a[ @class="blueLink2" and preceding::b[ contains( text(), "' . $header->nodeValue . '" ) ] and following::b[ contains( text(), "' . $next_header->nodeValue . '" ) ] ]';
				}
				else {
					$query = '//a[ @class="blueLink2" and preceding::b[ contains( text(), "' . $header->nodeValue . '" ) ] ]';
				}

				$links = $xpath->query( $query );

				foreach ( $links as $link ) {

					list( $id, $title ) = explode( ' - ', $link->nodeValue );

					$opinion = new Opinion();
					$opinion->id = $id;
					$opinion->title = $title;
					$opinion->date = $opinions_date;
					$opinion->type = trim( strtolower( $opinions_description ), 's' );
					$opinion->url = self::OPINIONS_BASE_URL . $link->getAttribute('href');

					// get the next blockquote in the document, it should be the description for this link
					$description = $xpath->query( './following::blockquote', $link );

					$opinion->description = trim( $description->item( 0 )->nodeValue );

					$opinions[] = $opinion;

				}

			}

			return $opinions;

		}

	}

	class Opinion {
		public $id;
		public $title;
		public $url;
		public $description;
		public $date;
		public $type;
	}

?>