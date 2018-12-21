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

	/** @var string */
	private $name;

	/** @var string */
	private $achievementName;

	/** @var int */
	private $targetCount;

	/** @var Dao> */
	private $dao;

	public function __construct( $name, $achievementName, $targetCount ) {
		$this->name = $name;
		$this->achievementName = $achievementName;
		$this->targetCount = $targetCount;
		$this->dao = new Dao();
	}

	/**
	 * @param User $user user who edited
	 */
	abstract public function onEditSuccess( $user );

	/**
	 * @param User $user user who was reverted
	 */
	abstract public function onRevert( $user );

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
	 * @param Function<User> $condition optional filtering condition
	 * @return int incremented count
	 */
	public function increment( $user, $condition = null ) {
		if ( $condition && !$condition( $user ) ) {
			return -1;
		}
		$count = (int)$this->getCount( $user );
		$this->validate( $count, $this->name );
		$this->dao->setCount( $user->getId(), $this->name, ++$count );

		if ( $count >= $this->targetCount ) {
			$this->unlockAchievement( $user );
		}

		return $count;
	}

	/**
	 * Decrement count for User $user
	 * @param User $user
	 * @param Function<User> $condition optional filtering condition
	 * @return int decremented count
	 */
	public function decrement( $user, $condition = null ) {
		if ( $condition && !$condition( $user ) ) {
			return -1;
		}
		$count = (int)$this->getCount( $user );
		$this->validate( $count, $this->name );
		$this->dao->setCount( $user->getId(), $this->name, --$count );
		return $count;
	}

	/**
	 * Reset count for User $user
	 * @param User $user 
	 * @param Function<User> $condition optional filtering condition
	 * @return int new count (0)
	 */
	public function reset( $user, $condition = null ) {
		if ( $condition && !$condition( $user ) ) {
			return -1;
		}
		$this->dao->setCount( $user->getId(), $this->name, 0 );
		return 0;
	}

	public function isAchievementUnlocked( $user ) {
		return $this->dao->getAchievementUnlocked( $user->getId(), $this->achievementName );
	}

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
