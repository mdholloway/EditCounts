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

use MediaWiki\MediaWikiServices;

class Dao {

	/** @var IDatabase */
	private $dbw;

	/** @var IDatabase */
	private $dbr;

	public function __construct() {
		$services = MediaWikiServices::getInstance();
		$this->dbw = Utils::getDB( DB_MASTER, $services );
		$this->dbr = Utils::getDB( DB_REPLICA, $services );
	}

	public function getCount( $userId, $property ) {
		return $this->dbr->selectField(
			'edit_counts',
			'ec_value',
			[
				'ec_user' => $userId,
				'ec_property' => $property
			],
			__METHOD__
		);
	}

	public function setCount( $userId, $property, $value ) {
		return $this->dbw->upsert(
			'edit_counts',
			[
				'ec_user' => $userId,
				'ec_property' => $property,
				'ec_value' => $value
			],
			[ 'ec_user', 'ec_property' ],
			[
				'ec_user' => $userId,
				'ec_property' => $property,
				'ec_value' => $value
			],
			__METHOD__
		);
	}

}
