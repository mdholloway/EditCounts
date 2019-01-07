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

	/**
	 * Get all edit counts for the user.
	 * @param int $centralId user ID
	 * @return ResultWrapper
	 */
	public function getCountsForUser( $centralId ) {
		return $this->dbr->select(
			'edit_counts',
			[ 'ec_property', 'ec_value' ],
			[ 'ec_user' => $centralId ],
			__METHOD__
		);
	}

	/**
	 * Get all unlocked achievements for the user.
	 * @param int $userId user ID
	 * @return Array<string> list of unlocked achievement keys
	 */
	public function getAchievementsForUser( $centralId ) {
		return $this->dbr->selectFieldValues(
			'edit_counts_achievements',
			'eca_property',
			[ 'eca_user' => $centralId ],
			__METHOD__
		);
	}

	/**
	 * Get a specific edit count for the user.
	 * @param int $userId user ID
	 * @param string $property edit count key
	 * @return int edit count for the specified key
	 */
	public function getCount( $centralId, $property ) {
		return $this->dbr->selectField(
			'edit_counts',
			'ec_value',
			[
				'ec_user' => $centralId,
				'ec_property' => $property
			],
			__METHOD__
		);
	}

	/**
	 * Set the count for a given edit count key.
	 * @param int $userId user ID
	 * @param string $property edit count key
	 * @param int $value new value
	 * @return bool true if operation completed successfully
	 */
	public function setCount( $centralId, $property, $value ) {
		return $this->dbw->upsert(
			'edit_counts',
			[
				'ec_user' => $centralId,
				'ec_property' => $property,
				'ec_value' => $value
			],
			[ 'ec_user', 'ec_property' ],
			[
				'ec_user' => $centralId,
				'ec_property' => $property,
				'ec_value' => $value
			],
			__METHOD__
		);
	}

	/**
	 * Get whether the specified achievement has been unlocked.
	 * @param int $userId user ID
	 * @param string $property achievement key
	 * @return bool whether the achievement is unlocked
	 */
	public function getAchievementUnlocked( $centralId, $property ) {
		return $this->dbr->selectRowCount(
			'edit_counts_achievements',
			'*',
			[
				'eca_user' => $centralId,
				'eca_property' => $property
			],
			__METHOD__
		) ? true : false;
	}

	/**
	 * Add a row to edit_counts_achievements to mark the achievement unlocked
	 * @param int $userId user ID
	 * @param string $property achievement key
	 * @return bool true if the operation completed successfully
	 */
	public function setAchievementUnlocked( $centralId, $property ) {
		return $this->dbw->insert(
			'edit_counts_achievements',
			[
				'eca_user' => $centralId,
				'eca_property' => $property
			],
			__METHOD__
		);
	}

	/**
	 * Delete an unlocked achievement.
	 * N.B. I don't think we should actually use this.
	 * @param int $userId user ID
	 * @param string $property achievement key
	 * @return bool true if the operation completed successfully
	 */
	public function deleteAchievementUnlocked( $centralId, $property ) {
		return $this->dbw->delete(
			'edit_counts_achievements',
			[
				'eca_user' => $centralId,
				'eca_property' => $property
			],
			__METHOD__
		);
	}

	/**
	 * Get the tag summary for the specified revision.
	 * This returns a list of tag names as stored in the DB, therefore
	 * we can simply search for a substring such as 'app edit' without
	 * needing to worry about i18n.
	 * @param int $revId revision ID
	 * @return string tag summary, provided as a comma-separated list
	 * 	e.g., "mobile edit,mobile app edit"
	 */
	public function getTagSummary( $revId ) {
		return $this->dbr->selectField(
			'tag_summary',
			'ts_tags',
			[ 'ts_rev_id' => $revId ],
			__METHOD__
		);
	}

}
