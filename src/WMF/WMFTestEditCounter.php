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
 * A basic edit counter for development and testing.
 */
class WMFTestEditCounter extends Counter {

	const COUNT_PROP = 'test_edits';
	const FEATURE_UNLOCKED_PROP = 'test_edits_feature_unlocked';
	const FEATURE_UNLOCKED_COUNT = 5;

	public function __construct() {
		parent::__construct(
			self::COUNT_PROP,
			self::FEATURE_UNLOCKED_PROP,
			self::FEATURE_UNLOCKED_COUNT
		);
	}

	public function onEditSuccess( $user ) {
		return $this->increment( $user );
	}

	public function onRevert( $user ) {
		return $this->reset( $user );
	}

}
