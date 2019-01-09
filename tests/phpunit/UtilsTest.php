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

use MediaWiki\Extension\EditCounts\Utils;
use MediaWiki\MediaWikiServices;
use MediaWikiTestCase;
use Wikimedia\Rdbms\DBConnRef;

/**
 * @covers \MediaWiki\Extension\EditCounts\Utils
 */
class UtilsTest extends MediaWikiTestCase {

	public function testGetDB() {
		$dbw = Utils::getDB( DB_MASTER, MediaWikiServices::getInstance() );
		$dbr = Utils::getDB( DB_REPLICA, MediaWikiServices::getInstance() );
		$this->assertInstanceOf( DBConnRef::class, $dbw );
		$this->assertInstanceOf( DBConnRef::class, $dbr );
	}

	public function testGetEnabledCounters() {
		$counters = Utils::getEnabledCounters( TestConstants::COUNTER_CONFIG );
		$this->assertEquals( 2, sizeof( $counters ) );

		$decrementingCounter = $counters[0];
		$this->assertTrue( $decrementingCounter instanceof DecrementOnRevertTestCounter );
		$this->assertEquals( 'decrement_on_revert', $decrementingCounter->getName() );
		$this->assertEquals( 'decrement_on_revert', $decrementingCounter->getAchievementName() );
		$this->assertEquals( 3, $decrementingCounter->getTargetCount() );

		$resettingCounter = $counters[1];
		$this->assertTrue( $resettingCounter instanceof ResetOnRevertTestCounter );
		$this->assertEquals( 'reset_on_revert', $resettingCounter->getName() );
		$this->assertEquals( 'reset_on_revert', $resettingCounter->getAchievementName() );
		$this->assertEquals( 3, $resettingCounter->getTargetCount() );
	}

}