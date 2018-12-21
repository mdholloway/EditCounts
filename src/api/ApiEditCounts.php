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

namespace MediaWiki\Extension\EditCounts\Api;

use ApiBase;
use MediaWiki\Extension\EditCounts\Dao;
use Wikimedia\Rdbms\ResultWrapper;

class ApiEditCounts extends ApiBase {

	/**
	 * Entry point for executing the module
	 * @inheritDoc
	 * @return void
	 */
	public function execute() {
		if ( $this->getUser()->isAnon() ) {
			$this->dieWithError( [ 'apierror-mustbeloggedin',
				$this->msg( 'action-viewmyprivateinfo' ) ], 'notloggedin' );
		}
		$this->checkUserRightsAny( 'viewmyprivateinfo' );

		$dao = new Dao();
		$userId = $this->getUser()->getId();
		$counts = $this->resultFromWrapper( $dao->getCountsForUser( $userId ), 'ec_property', 'ec_value' );
		$achievements = $dao->getAchievementsForUser( $userId );
	
		$this->getResult()->addValue( null, 'editcounts', [
			'counts' => $counts,
			'achievements' => $achievements
		]);
	}

	/**
	 * // TODO: Add params if we need/want finer-grained queries
	 * @inheritDoc
	 * @return array
	 */
	protected function getAllowedParams() {
		return [
			// 'prop' => [
			// 	self::PARAM_TYPE => 'string'
			// ],
		];
	}

	/**
	 * @inheritDoc
	 * @return bool
	 */
	public function isInternal() {
		return true;
	}

	private function resultFromWrapper( ResultWrapper $wrapper, string $keyName, string $valName ) {
		$result = [];
		foreach ( $wrapper as $row ) {
			$result[$row->$keyName] = $row->$valName;
		}
		return $result;
	}

}
