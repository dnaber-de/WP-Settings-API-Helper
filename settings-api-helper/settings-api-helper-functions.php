<?php

/**
 * validate and sanitizing functions
 * for the Settings API Helper
 *
 * @package Wordpress
 * @subpackage Settings API Helper
 * @author David Naber <kontakt@dnaber.de>
 */


if ( ! function_exists( 'sh_sanitize_hex_color' ) ) {

	/**
	 * sanitizes a hex color string
	 *
	 * @param string $color
	 * @return string $default (Optional)
	 */
	function sh_sanitize_hex_color( $color, $default = '#000000' ) {

		# begin with a hash
		if ( 1 !== strpos( '#', $color ) ) {
			$color = str_replace( '#', '', $color );
			$color = '#' . $color;
		}
		$color = preg_replace( "~[^a-f0-9#]~i", '', $color );
		$color = strtolower( $color );
		if ( '#' === $color ) # input was empty or completely nonsense
			return $default;

		# sanitize three-diget values like #fff
		if ( 4 == strlen( $color ) ) {
			$offset = 1;
			while ( 7 > strlen( $color ) ) {
				$color =
					  substr( $color, 0, $offset + 1 )
					. substr( $color, $offset, 1 )
					. substr( $color, $offset + 1 )
				;
				$offset += 2;
			}
		} else {
			# repeat the last digit
			$length = strlen( $color );
			$last   = substr( $color, $length - 1);
			$color .= str_repeat( $last, 7 - $length );
		}

		return $color;
	}
}

if ( ! function_exists( 'sh_validate_datetime' ) ) {

	/**
	 * validates a datetime string by comparing the input with the sanitized value
	 *
	 * @param string $datetime
	 * @retun bool
	 */
	function sh_validate_datetime( $datetime ) {

		$datetime  = trim( $datetime );
		$sanitized = sh_sanitize_datetime( $datetime );

		return ! ( bool ) strcmp( $datetime, $sanitized );
	}
}

if ( ! function_exists( 'sh_sanitize_datetime' ) ) {

	/**
	 * sanitizes a datetime string with the format yyyy-mm-ddTHH:mm:ss±hh:mm
	 *
	 * @param string $datetime
	 * @return string
	 */
	function sh_sanitize_datetime( $datetime ) {

		$date = $time = $timezone = NULL;

		# remove all rubish
		$datetime = trim( $datetime );
		$datetime = preg_replace( "~[^0-9\s-:_.,TZ+]~", '', $datetime );

		#if there is no separator 'T', take the first Space
		if ( FALSE === strpos( 'T', $datetime ) ) {
			if ( 0 < strpos( $datetime, ' ') && strpos( $datetime, ' ' ) < strlen( $datetime ) ) {
				$pos      = strpos( $datetime, ' ' );
				$datetime = substr_replace( $datetime, 'T', $pos, 1 );
			} else {
				$datetime .= 'T'; #no time given
			}
		}
		$datetime = trim( $datetime, '+-:' );

		# chunk into date, time and timezone
		list( $date, $time ) = explode( 'T', $datetime );

		# date
		$date = sh_sanitize_date( $date );

		if ( $time ) {
			if ( FALSE !== strpos( $time, 'Z', strlen( $time ) - 1 ) ) {
				$timezone = 'Z';
				$time = rtrim( $time, 'Z' );
			} else {
				$time_match = array();
				if ( preg_match( "~^(.+)(\+|-)([\d:]+)$~", $time, $time_match ) ) {
					$time = $time_match[ 1 ];
					# timezone is optional (datetime-local)
					if ( ! empty( $time_match[ 2 ] ) && ! empty( $time_match[ 3 ] ) ) {
						# sanitize timezone
						$abs_offset   = $time_match[ 3 ];

						$timezone_pre = '' == trim( $time_match[ 2 ] )
							? '+'
							: $time_match[ 2 ];

						$abs_offset = explode( ':', $abs_offset );
						$abs_offset[ 0 ] = ( int ) $abs_offset[ 0 ];
						$abs_offset[ 1 ] = empty( $abs_offset[ 1 ] ) ? 0 : ( int ) $abs_offset[ 1 ];

						if ( 13 < ( int ) $abs_offset[ 0 ] )
							$abs_offset[ 0 ] = 12;
						if ( 59 < ( int ) $abs_offset[ 1 ] || 12 === $abs_offset[ 0 ] )
							$abs_offset[ 1 ] = 0;

						$abs_offset[ 0 ] = 9 < $abs_offset[ 0 ]
							? ( string ) $abs_offset[ 0 ]
							: '0' . ( string ) $abs_offset[ 0 ]
						;
						$abs_offset[ 1 ] = 9 < $abs_offset[ 1 ]
							? ( string ) $abs_offset[ 1 ]
							: '0' . ( string ) $abs_offset[ 1 ]
						;

						$timezone = $timezone_pre . $abs_offset[ 0 ] . ':' . $abs_offset[ 1 ];

					} else  {
						$timezone = '';
					}
				} else {
					$timezone = '';
				}
			}
		} else {
			$time = '';
		}
		$time = sh_sanitize_time( $time );

		return $date . 'T' . $time . $timezone;

	}
}

if ( ! function_exists( 'sh_validate_date' ) ) {

	/**
	 * validates a date string
	 *
	 * @param string $date
	 * @retun bool
	 */
	function sh_validate_date( $date ) {

		$datetime  = trim( $date );
		$sanitized = sh_sanitize_date( $date );

		return ! ( bool ) strcmp( $date, $sanitized );
	}
}

if ( ! function_exists( 'sh_sanitize_date' ) ) {

	/**
	 * sanitizes a datetime string with the format yyyy-mm-dd
	 *
	 * @param string $date
	 * @return string
	 */
	function sh_sanitize_date( $date ) {

		$date       = str_replace( array( '.', ',', ':', '_' ), '-', $date );
		$date       = trim( $date, '-' );
		$date       = explode( '-', $date );
		$thirty_one = array( '01', '03', '05', '07', '08', '10', '12' );
		$leap_year  = FALSE;

		#year
		$date[ 0 ] = empty( $date[ 0 ] ) ? 0 : ( int ) $date[ 0 ];
		#month
		$date[ 1 ] = empty( $date[ 1 ] ) ? 0 : ( int ) $date[ 1 ];
		#day
		$date[ 2 ] = empty( $date[ 2 ] ) ? 0 : ( int ) $date[ 2 ];

		#sanitize year
		#two-digets value will be interpreted as the 20. centry
		if ( 0 <= $date[ 0 ] && $date[ 0 ] <= 99 )
			$date[ 0 ] += 1900;
		elseif ( 1000 > $date[ 0 ] )
			$date[ 0 ] = 1000;
		elseif ( 9999 < $date[ 0 ] )
			$date[ 0 ] = 9999;
		$date[ 0 ] = ( string ) $date[ 0 ];

		#sanitize month
		if ( 0 === $date[ 0 ] % 4 )
			$leap_year = TRUE;

		if ( 0 === $date[ 0 ] % 100 )
			$leap_year = FALSE;

		if ( 0 === $date[ 0 ] % 400 )
			$leap_year = TRUE;

		if ( 1 > $date[ 1 ] )
			$date[ 1 ] = 1;
		elseif ( 12 < $date[ 1 ] )
			$date[ 1 ] = 12;

		$date[ 1 ] = ( string ) $date[ 1 ];
		if ( 1 === strlen( $date[ 1 ] ) )
			$date[ 1 ] = '0' . $date[ 1 ];


		#sanitizes day
		if ( $date[ 2 ] < 1 )
			$date[ 2 ] = 1;
		elseif( $date[ 2 ] > 31 )
			$date[ 2 ] = 31;

		if ( ! in_array( $date[ 1 ], $thirty_one ) ) {
			if ( 30 < $date[ 2 ] ) {
				$date[ 2 ] = 30;
			}
		} elseif ( '02' === $date[ 2 ] ) {
			if ( $leap_year && 29 > $date[ 3 ] )
				$date[ 3 ] = 29;
			elseif ( 28 > $date[ 3 ] )
				$date[ 3 ] = 28;
		}
		$date[ 2 ] = ( string ) $date[ 2 ];
		if ( 1 === strlen( $date[ 2 ] ) )
			$date[ 2 ] = '0' . $date[ 2 ];

		return implode(
			array(
				$date[ 0 ],
				$date[ 1 ],
				$date[ 2 ]
			),
			'-'
		);

	}
}

if ( ! function_exists( 'sh_validate_time' ) ) {

	/**
	 * validates a time string
	 *
	 * @param string $datetime
	 * @retun bool
	 */
	function sh_validate_time( $time ) {

		$datetime  = trim( $time );
		$sanitized = sh_sanitize_time( $time );

		return ! ( bool ) strcmp( $time, $sanitized );
	}
}

if ( ! function_exists( 'sh_sanitize_time' ) ) {

	/**
	 * sanitizes a time string with the format HH:ii:ss
	 *
	 * @param string $time
	 * @return string
	 */
	function sh_sanitize_time( $time ) {

		$time = str_replace( array( '.', ',', '-', '_' ), ':', $time );
		$time = trim( $time, ':' );
		$time = explode( ':', $time );
		# hours
		$time[ 0 ] = empty( $time[ 0 ] ) ? 0 : ( int ) $time[ 0 ];
		# minutes
		$time[ 1 ] = empty( $time[ 1 ] ) ? 0 : ( int ) $time[ 1 ];
		# seconds
		$time[ 2 ] = empty( $time[ 2 ] ) ? 0 : ( int ) $time[ 2 ];

		if ( 0 <= $time[ 0 ] && $time[ 0 ] <=23 ) {
			if ( 0 === $time[ 0 ] )
				$time[ 0 ] = '00';
			else
				$time[ 0 ] = ( string ) $time[ 0 ];

			if ( 1 === strlen( $time[ 0 ] ) )
				$time[ 0 ] = '0' . $time[ 0 ];
		} else {
			$time[ 0 ] = '00';
		}

		for ( $i = 1; $i < 3; $i++ ) {
			if ( 0 <= $time[ $i ] && $time[ $i ] <=59 ) {
				if ( 0 === $time[ $i] )
					$time[ $i ] = '00';
				else
					$time[ $i ] = ( string ) $time[ $i ];

				if ( 1 === strlen( $time[ $i ] ) )
					$time[ $i ] = '0' . $time[ $i ];
			} else {
				$time[ $i ] = '00';
			}
		}

		return implode(
			array(
				$time[ 0 ],
				$time[ 1 ],
				$time[ 2 ]
			),
			':'
		);

	}
}
