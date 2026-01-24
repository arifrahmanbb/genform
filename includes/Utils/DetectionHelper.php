<?php
/**
 * Detection Helper for GenForm
 *
 * @package GenForm
 */

namespace GenForm\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class DetectionHelper
 * Detects Browser and OS from User Agent string.
 */
final class DetectionHelper {

	/**
	 * Get Browser and OS info.
	 *
	 * @return array{browser: string, os: string}
	 */
	public static function getInfo(): array {
		$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

		return array(
			'browser' => self::getBrowser( $ua ),
			'os'      => self::getOS( $ua ),
		);
	}

	/**
	 * Detect Browser.
	 */
	private static function getBrowser( string $ua ): string {
		$browser = 'Unknown Browser';

		$browsers = array(
			'/msie/i'      => 'Internet Explorer',
			'/firefox/i'   => 'Firefox',
			'/safari/i'    => 'Safari',
			'/chrome/i'    => 'Chrome',
			'/edge/i'      => 'Edge',
			'/opera/i'     => 'Opera',
			'/netscape/i'  => 'Netscape',
			'/maxthon/i'   => 'Maxthon',
			'/konqueror/i' => 'Konqueror',
			'/mobile/i'    => 'Handheld Browser',
		);

		foreach ( $browsers as $regex => $value ) {
			if ( preg_match( $regex, $ua ) ) {
				$browser = $value;
			}
		}

		// Chrome matches Safari too.
		if ( 'Safari' === $browser && preg_match( '/chrome/i', $ua ) ) {
			$browser = 'Chrome';
		}

		return $browser;
	}

	/**
	 * Detect OS.
	 */
	private static function getOS( string $ua ): string {
		$os = 'Unknown OS';

		$os_array = array(
			'/windows nt 10/i'      => 'Windows 10',
			'/windows nt 11/i'      => 'Windows 11',
			'/windows nt 6.3/i'     => 'Windows 8.1',
			'/windows nt 6.2/i'     => 'Windows 8',
			'/windows nt 6.1/i'     => 'Windows 7',
			'/windows nt 6.0/i'     => 'Windows Vista',
			'/windows nt 5.2/i'     => 'Windows Server 2003/XP x64',
			'/windows nt 5.1/i'     => 'Windows XP',
			'/windows xp/i'         => 'Windows XP',
			'/windows nt 5.0/i'     => 'Windows 2000',
			'/windows me/i'         => 'Windows ME',
			'/win98/i'              => 'Windows 98',
			'/win95/i'              => 'Windows 95',
			'/win16/i'              => 'Windows 3.11',
			'/macintosh|mac os x/i' => 'macOS',
			'/mac_powerpc/i'        => 'Mac OS 9',
			'/linux/i'              => 'Linux',
			'/ubuntu/i'             => 'Ubuntu',
			'/iphone/i'             => 'iOS (iPhone)',
			'/ipod/i'               => 'iOS (iPod)',
			'/ipad/i'               => 'iOS (iPad)',
			'/android/i'            => 'Android',
			'/blackberry/i'         => 'BlackBerry',
			'/webos/i'              => 'Mobile',
		);

		foreach ( $os_array as $regex => $value ) {
			if ( preg_match( $regex, $ua ) ) {
				$os = $value;
			}
		}

		return $os;
	}
}
