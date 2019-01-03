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
	 * @param User $user user who edited
	 * @param Request $request the current request object
	 */
	abstract public function onEditSuccess( $user, $request );

	/**
	 * Specifies the action to take when a revert is performed.
	 * E.g., decrement or reset an editor's counter if the reverted edit is an in-app Wikidata
	 *  description edit.
	 * @param User $user user who was reverted
	 * @param bool $revId reverted revision ID
	 */
	abstract public function onRevert( $user, $revId );

	/**
	 * Get count for User $user
	 * @param User $user
	 * @return int value of counter
	 */
	public function getCount( $user ) {
		$count = $this->dao->getCount( $user->getId(), $this->name );
		if ( $count ) {
			return $count;
		}
		return 0;
	}

	/**
	 * Increment count for User $user
	 * @param User $user
	 * @return int incremented count
	 */
	public function increment( $user ) {
		$count = (int)$this->getCount( $user );
		$this->validate( $count, $this->name );
		$this->dao->setCount( $user->getId(), $this->name, ++$count );

		if ( $count >= $this->targetCount && !$this->isAchievementUnlocked( $user ) ) {
			$this->unlockAchievement( $user );
		}

		return $count;
	}

	/**
	 * Decrement count for User $user
	 * @param User $user
	 * @return int decremented count
	 */
	public function decrement( $user ) {
		$count = (int)$this->getCount( $user );
		$this->validate( $count, $this->name );
		$this->dao->setCount( $user->getId(), $this->name, --$count );
		return $count;
	}

	/**
	 * Reset count for User $user
	 * @param User $user
	 * @return int new count (0)
	 */
	public function reset( $user ) {
		$this->dao->setCount( $user->getId(), $this->name, 0 );
		return 0;
	}

	/**
	 * Get whether the achievement is unlocked for $user
	 * @param User $user
	 * @return bool whether the achievement is unlocked
	 */
	public function isAchievementUnlocked( $user ) {
		return $this->dao->getAchievementUnlocked( $user->getId(), $this->achievementName );
	}

	/**
	 * Set the achievement unlocked for $user
	 * @param User $user
	 * @return bool true if the operation completed successfully
	 */
	public function unlockAchievement( $user ) {
		return $this->dao->setAchievementUnlocked( $user->getId(), $this->achievementName );
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
