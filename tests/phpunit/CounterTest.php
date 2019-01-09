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

namespace MediaWiki\Extension\EditCounts\Test;

use MediaWiki\Extension\EditCounts\Counter;
use MediaWiki\Extension\EditCounts\Utils;
use MediaWikiTestCase;

/**
 * @group Database
 * @covers \MediaWiki\Extension\EditCounts\Counter
 */
class CounterTest extends MediaWikiTestCase {

	/** @var Array<Counter> */
	private $counters;

	public function setUp() {
		parent::setUp();
		$this->tablesUsed = array_merge( $this->tablesUsed, [
			'edit_counts',
			'edit_counts_achievements'
		] );
		$this->counters = Utils::getEnabledCounters( TestConstants::COUNTER_CONFIG );
	}

	public function testInitialState() {
		foreach ( $this->counters as $counter ) {
			$this->assertEquals( 0, $counter->getCount( 0 ) );
			$this->assertFalse( $counter->isAchievementUnlocked( 0 ) );
		}
	}

	public function testIncrementDecrement() {
		foreach ( $this->counters as $counter ) {
			$counter->increment( 0 );
			$this->assertEquals( 1, $counter->getCount( 0 ) );
			$counter->decrement( 0 );
			$this->assertEquals( 0, $counter->getCount( 0 ) );
		}
	}

	public function testReset() {
		foreach ( $this->counters as $counter ) {
			$counter->increment( 0 );
			$this->assertEquals( 1, $counter->getCount( 0 ) );
			$counter->reset( 0 );
			$this->assertEquals( 0, $counter->getCount( 0 ) );
		}
	}

	public function testOnEditSuccess() {
		foreach ( $this->counters as $counter ) {
			$counter->onEditSuccess( 0, null );
			$this->assertEquals( 1, $counter->getCount( 0 ) );
		}
	}

	public function testOnRevert() {
		foreach ( $this->counters as $counter ) {
			$counter->onEditSuccess( 0, null );
			$counter->onEditSuccess( 0, null );
			$this->assertEquals( 2, $counter->getCount( 0 ) );
			$counter->onRevert( 0, null );
		}
		$decrementOnRevertCounter = $this->counters[0];
		$this->assertEquals( 1, $decrementOnRevertCounter->getCount( 0 ) );
		$resetOnRevertCounter = $this->counters[1];
		$this->assertEquals( 0, $resetOnRevertCounter->getCount( 0 ) );
	}

	public function testUnlockAchievement() {
		foreach ( $this->counters as $counter ) {
			$counter->unlockAchievement( 0 );
			$this->assertTrue( $counter->isAchievementUnlocked( 0 ) );
		}
	}

	public function testTargetCountAchievementUnlocked() {
		foreach ( $this->counters as $counter ) {
			foreach ( range( 1, $counter->getTargetCount() ) as $i ) {
				$counter->onEditSuccess( 0, null );
			}
			$this->assertTrue( $counter->isAchievementUnlocked( 0 ) );
		}
	}

}