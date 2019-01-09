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

use MediaWiki\Extension\EditCounts\Dao;
use MediaWikiTestCase;

/**
 * @group Database
 * @covers \MediaWiki\Extension\EditCounts\Dao
 */
class DaoTest extends MediaWikiTestCase {

	/** @var Dao $dao */
	private $dao;

	public function setUp() {
		parent::setUp();
		$this->tablesUsed = array_merge( $this->tablesUsed, [
			'edit_counts',
			'edit_counts_achievements'
		] );
		$this->dao = new Dao();
	}

	public function testEmpty() {
		$this->assertEquals( false, $this->dao->getCountsForUser( 0 )->fetchRow() );
		$this->assertEquals( [], $this->dao->getAchievementsForUser( 0 ) );
	}

	public function testCounts() {
		$this->dao->setCount( 0, 'foo', 1 );
		$this->assertEquals( 1, $this->dao->getCount( 0, 'foo' ) );
		$this->dao->setCount( 0, 'foo', 0 );
		$this->assertEquals( 0, $this->dao->getCount( 0, 'foo' ) );
	}

	public function testAchievements() {
		$this->assertFalse( $this->dao->getAchievementUnlocked( 0, 'foo' ) );
		$this->dao->setAchievementUnlocked( 0, 'foo' );
		$this->assertTrue( $this->dao->getAchievementUnlocked( 0, 'foo' ) );
		$this->dao->deleteAchievementUnlocked( 0, 'foo' );
		$this->assertFalse( $this->dao->getAchievementUnlocked( 0, 'foo' ) );
	}

}
