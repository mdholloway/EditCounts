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

use \Error;

abstract class Counter {

	/** @var Dao> */
	protected $dao;

	/** @var string */
	private $name;

	/** @var string */
	private $achievementName;

	/** @var int */
	private $targetCount;

	/**
	 * @param string $name edit count tag
	 * @param string $achievementName achievement tag
	 * @param int $targetCount target count to unlock the associated achievement
	 */
	public function __construct( $name, $achievementName, $targetCount ) {
		$this->name = $name;
		$this->achievementName = $achievementName;
		$this->targetCount = $targetCount;
		$this->dao = new Dao();
	}

	/**
	 * Specifies the action to take when a successful edit is made.
	 * E.g., increment a counter if the edit is an in-app Wikidata description edit.
	 * @param int $centralId central ID user who edited
	 * @param Request $request the current request object
	 */
	abstract public function onEditSuccess( $centralId, $request );

	/**
	 * Specifies the action to take when a revert is performed.
	 * E.g., decrement or reset an editor's counter if the reverted edit is an in-app Wikidata
	 *  description edit.
	 * @param int $centralId central ID of the user who was reverted
	 * @param bool $revId reverted revision ID
	 */
	abstract public function onRevert( $centralId, $revId );

	/**
	 * @return string $name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return int $targetCount
	 */
	public function getTargetCount() {
		return $this->targetCount;
	}

	/**
	 * @return string $achievementName
	 */
	public function getAchievementName() {
		return $this->achievementName;
	}

	/**
	 * Get count for user
	 * @param int $centralId central ID of the user
	 * @return int value of counter
	 */
	public function getCount( $centralId ) {
		$count = $this->dao->getCount( $centralId, $this->name );
		if ( $count ) {
			return $count;
		}
		return 0;
	}

	/**
	 * Increment count for user
	 * @param int $centralId central ID of the user
	 * @return int incremented count
	 */
	public function increment( $centralId ) {
		$count = (int)$this->getCount( $centralId );
		$this->validate( $count, $this->name );
		$this->dao->setCount( $centralId, $this->name, ++$count );

		if ( $count >= $this->targetCount && !$this->isAchievementUnlocked( $centralId ) ) {
			$this->unlockAchievement( $centralId );
		}

		return $count;
	}

	/**
	 * Decrement count for user
	 * @param int $centralId central ID of the user
	 * @return int decremented count
	 */
	public function decrement( $centralId ) {
		$count = (int)$this->getCount( $centralId );
		$this->validate( $count, $this->name );
		$this->dao->setCount( $centralId, $this->name, --$count );
		return $count;
	}

	/**
	 * Reset count for user
	 * @param int $centralId central ID of the user
	 * @return int new count (0)
	 */
	public function reset( $centralId ) {
		$this->dao->setCount( $centralId, $this->name, 0 );
		return 0;
	}

	/**
	 * Get whether the achievement is unlocked for a user
	 * @param int $centralId central ID of the user
	 * @return bool whether the achievement is unlocked
	 */
	public function isAchievementUnlocked( $centralId ) {
		return $this->dao->getAchievementUnlocked( $centralId, $this->achievementName );
	}

	/**
	 * Set the achievement unlocked for a user
	 * @param int $centralId central ID of the user
	 * @return bool true if the operation completed successfully
	 */
	public function unlockAchievement( $centralId ) {
		return $this->dao->setAchievementUnlocked( $centralId, $this->achievementName );
	}

	/**
	 * Validate count retrieved from user options.
	 * @throws Error if $result is null or not an integer
	 */
	private function validate( $count ) {
		if ( $count === null ) {
			throw new Error( 'Count for ' . $this->name . ' is null' );
		}
		if ( !is_int( $count ) ) {
			throw new Error( 'Count for ' . $this->name . ' is not an integer' );
		}
	}

}
