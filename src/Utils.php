<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * @file
 */

namespace MediaWiki\Extension\EditCounts;

use CentralIdLookup;
use MediaWiki\MediaWikiServices;
use User;

class Utils {

	/**
	 * Get a database connection.
	 * @param int $db Index of the connection to get, e.g. DB_MASTER or DB_REPLICA.
	 * @param MediaWikiServices $services
	 * @return DBConnRef
	 */
	public static function getDB( $db, $services ) {
		$loadBalancerFactory = $services->getDBLoadBalancerFactory();
		$loadBalancer = $loadBalancerFactory->getMainLB();
		return $loadBalancer->getLazyConnectionRef( $db, [] );
	}

	/**
	 * Get the central user ID for $user
	 * @param User user local User
	 * @return int central user ID
	 */
	public static function getCentralId( User $user ) {
		return CentralIdLookup::factory()->centralIdFromLocalUser( $user,
			CentralIdLookup::AUDIENCE_RAW );
	}

	/**
	 * Get the set of enabled counters defined in the extension configuation.
	 * @param Array $counterConfig (for testing) counter definitions to use in place of those
	 * 	in the extension configuration
	 * @return Array<Counter> array containing the enabled counters
	 */
	public static function getEnabledCounters( $counterConfig = null ) {
		if ( !$counterConfig ) {
			$cf = MediaWikiServices::getInstance()->getConfigFactory();
			$counterConfig = $cf->makeConfig( 'EditCounts' )->get( 'EditCountsEnabledCounters' );
		}
		return array_map( function ( $conf ) {
			return new $conf['class'](
				$conf['count_prop'],
				$conf['feature_unlocked_prop'],
				$conf['feature_unlocked_count'] );
		}, $counterConfig );
	}

}
