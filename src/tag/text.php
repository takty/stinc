<?php
namespace st;

/**
 *
 * Text Processing Functions
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-11-08
 *
 */


function separate_line( $str, $filter = 'raw' ) {
	$ls = preg_split( "/　　|<\s*br\s*\/?>/ui", $str );
	switch ( $filter ) {
		case 'raw':
			return $ls;
		case 'br':
			return implode( '<br>', array_map( 'esc_html', $ls ) );
		case 'span':
			return '<span>' . implode( '</span><span>', array_map( 'esc_html', $ls ) ) . '</span>';
		case 'div':
			return '<div>' . implode( '</div><div>', array_map( 'esc_html', $ls ) ) . '</div>';
		case 'segment':
			return '<div>' . implode( '</div><div>', array_map( '\st\separate_text_and_make_spans', $ls ) ) . '</div>';
		case 'segment_raw':
			return array_map( '\st\separate_text_and_make_spans', $ls );
		case 'segment_small':
			$sss = array_map( '\st\separate_small', $ls );
			$newLs = [];
			foreach ( $sss as $ss ) {
				$newL = '';
				foreach ( $ss as $s ) {
					$temp = separate_text_and_make_spans( $s[0] );
					if ( ! empty( $s[1] ) ) $temp = "<{$s[1]}>$temp</{$s[1]}>";
					$newL .= $temp;
				}
				$newLs[] = $newL;
			}
			return '<div>' . implode( '</div><div>', $newLs ) . '</div>';
	}
}

function esc_html_br( $str ) {
	$ls = preg_split( "/<\s*br\s*\/?>/iu", $str );
	if ( count( $ls ) === 1 ) {
		return esc_html( $str );
	} else {
		return implode( '<br>', array_map( 'esc_html', $ls ) );
	}
}

function esc_html_br_to_span( $str ) {
	$ls = preg_split( "/<\s*br\s*\/?>/iu", $str );
	if ( count( $ls ) === 1 ) {
		return esc_html( $str );
	} else {
		return '<span>' . implode( '</span><span>', array_map( 'esc_html', $ls ) ) . '</span>';
	}
}

function separate_small( $str ) {
	$ls = preg_split( "/(<small>[\s\S]*?<\/small>)/iu", $str, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
	$ss = [];
	foreach ( $ls as $l ) {
		preg_match( "/<small>([\s\S]*?)<\/small>/iu", $l, $matches );
		if ( empty( $matches ) ) {
			$ss[] = [ $l, '' ];
		} else {
			$ss[] = [ $matches[1], 'small' ];
		}
	}
	return $ss;
}


// -----------------------------------------------------------------------------

function separate_text_and_make_spans( $text ) {
	$pair = ['S*' => 1, '*E' => 1, 'II' => 1, 'KK' => 1, 'HH' => 1, 'HI' => 1];
	$t_prev = '';
	$word = '';
	$parts = [];

	for ( $i = 0, $I = mb_strlen( $text ); $i < $I; $i += 1 ) {
		$c = mb_substr( $text, $i, 1 );
		$t = _get_ctype( $c );
		if ( isset( $pair[ $t_prev . $t ] ) || isset( $pair[ '*' . $t ] ) || isset( $pair[ $t_prev . '*' ] ) ) {
			$word .= $c;
		} else if ( $t === 'O' ) {
			if ( $t_prev === 'O' ) {
				$word .= $c;
			} else {
				if ( ! empty( $word ) ) $parts[] = [$word, 1];
				$word = $c;
			}
		} else {
			if ( ! empty( $word ) ) $parts[] = [$word, ( $t_prev === 'O' ) ? 0 : 1];
			$word = $c;
		}
		$t_prev = $t;
	}
	if ( ! empty( $word )) $parts[] = [$word, ( $t_prev === 'O' ) ? 0 : 1];

	$ret = '';
	foreach ( $parts as $ws ) {
		$ret .= ($ws[1] === 1) ? ('<span>' . esc_html( $ws[0] ) . '</span>') : esc_html( $ws[0] );
	}
	return $ret;
}

function _get_ctype( $c ) {
	$pats = [
		'[「『（［｛〈《【〔〖〘〚]' => 'S',
		'[」』）］｝〉》】〕〗〙〛、，。．？！を]' => 'E',
		'[ぁ-んゝ]' => 'I',
		'[ァ-ヴーｱ-ﾝﾞｰ]' => 'K',
		'[一-龠々〆ヵヶ]' => 'H',
	];
	foreach ( $pats as $p => $t ) {
		if ( preg_match( "/" . $p . "/u", $c ) === 1 ) return $t;
	}
	return 'O';
}
