<?php
/**
 * Browser and OS Detection Utility
 *
 * Provides helper methods to parse User Agent strings for submission meta recording.
 *
 * @package GenForm
 */

namespace GenForm\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class DetectionHelper {

	/**
	 * Retrieve a simplified snapshot of the current visitor's device.
	 *
	 * @return array{browser: string, os: string}
	 */
	public static function getInfo(): array {
		$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
		return array(
			'browser' => self::getBrowser( $ua ),
			'os'      => self::getOS( $ua ),
		);
	}

	/**
	 * Map User Agent patterns to friendly Browser names.
	 */
	private static function getBrowser( string $ua ): string {
		$b  = 'Unknown';
		$bs = array(
			'/msie/i'    => 'IE',
			'/firefox/i' => 'Firefox',
			'/safari/i'  => 'Safari',
			'/chrome/i'  => 'Chrome',
			'/edge/i'    => 'Edge',
			'/opera/i'   => 'Opera',
			'/mobile/i'  => 'Mobile',
		);
		foreach ( $bs as $r => $v ) {
			if ( preg_match( $r, $ua ) ) {
				$b = $v;
			}
		}
		// Refine Safari detection given that Chrome identifies as Safari.
		if ( $b === 'Safari' && preg_match( '/chrome/i', $ua ) ) {
			$b = 'Chrome';
		}
		return $b;
	}

	/**
	 * Map User Agent patterns to friendly OS names.
	 */
	private static function getOS( string $ua ): string {
		$o  = 'Unknown';
		$os = array(
			'/windows nt 10/i'      => 'Win 10',
			'/windows nt 11/i'      => 'Win 11',
			'/macintosh|mac os x/i' => 'macOS',
			'/linux/i'              => 'Linux',
			'/iphone/i'             => 'iOS',
			'/android/i'            => 'Android',
		);
		foreach ( $os as $r => $v ) {
			if ( preg_match( $r, $ua ) ) {
				$o = $v;
			}
		}
		return $o;
	}
}
