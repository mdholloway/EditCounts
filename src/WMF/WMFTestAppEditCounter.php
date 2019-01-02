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

namespace MediaWiki\Extension\EditCounts\WMF;

use MediaWiki\Extension\EditCounts\Counter;

/**
 * A counter of app edits for development and testing.
 */
class WMFTestAppEditCounter extends Counter {

	const COUNT_PROP = 'test_app_edits';
	const FEATURE_UNLOCKED_PROP = 'test_app_edits_feature_unlocked';
	const FEATURE_UNLOCKED_COUNT = 5;

	public function __construct() {
		parent::__construct(
			self::COUNT_PROP,
			self::FEATURE_UNLOCKED_PROP,
			self::FEATURE_UNLOCKED_COUNT
		);
	}

	public function onEditSuccess( $user, $request ) {
		if ( $this->isRequestFromApp( $request ) ) {
			return $this->increment( $user );
		}
	}

	public function onRevert( $user, $revId ) {
		if ( $this->isRevisionAppEdit( $revId ) ) {
			return $this->reset( $user );
		}
	}

	private function isRequestFromApp( $request ) {
		$ua = $request->getHeader( 'User-agent' );
		if ( $ua ) {
			return strpos( $ua, 'WikipediaApp/' ) === 0;
		}
		return false;
	}

	private function isRevisionAppEdit( $revId ) {
		$ts = $this->dao->getTagSummary( $revId );
		if ( $ts ) {
			return strpos( $ts, 'app edit' ) !== false;
		}
		return false;
	}

}
