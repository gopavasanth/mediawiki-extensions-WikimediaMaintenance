<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @ingroup Maintenance
 * @ingroup Wikimedia
 */
require_once __DIR__ . '/WikimediaMaintenance.php';

/**
 * Creates the necessary tables to install various extensions on a WMF wiki
 */
class CreateExtensionTables extends Maintenance {
	function __construct() {
		parent::__construct();
		$this->mDescription = 'Creates database tables for specific MediaWiki Extensions';
		$this->addArg( 'extension', 'Which extension to install' );
	}

	function execute() {
		global $IP, $wgFlowDefaultWikiDb;
		$dbw = $this->getDB( DB_MASTER );
		$extension = $this->getArg( 0 );

		$files = [];
		$path = '';

		switch ( strtolower( $extension ) ) {
			case 'babel':
				$files = [ 'babel.sql' ];
				$path = "$IP/extensions/Babel";
				break;

			case 'echo':
				$this->output( "Using special database connection for Echo" );
				$dbw = MWEchoDbFactory::newFromDefault()->getEchoDb( DB_MASTER );
				$dbw->query( "SET storage_engine=InnoDB" );
				$dbw->query( "CREATE DATABASE IF NOT EXISTS " . wfWikiID() );
				$dbw->selectDB( wfWikiID() );
				$files = [ 'echo.sql' ];
				$path = "$IP/extensions/Echo";
				break;

			case 'educationprogram':
				$files = [ 'EducationProgram.sql' ];
				$path = "$IP/extensions/EducationProgram/sql";
				break;

			case 'flaggedrevs':
				$files = [ 'FlaggedRevs.sql' ];
				$path = "$IP/extensions/FlaggedRevs/backend/schema/mysql";
				break;

			case 'flow':
				if ( $wgFlowDefaultWikiDb !== false ) {
					$this->fatalError( "This wiki uses $wgFlowDefaultWikiDb for Flow tables. They don't need to be created on the project database, which is the scope of this script." );
				}
				$files = [ 'flow.sql' ];
				$path = "$IP/extensions/Flow";
				break;

			case 'linter':
				$files = [ 'linter.sql' ];
				$path = "$IP/extensions/Linter";
				break;

			case 'newsletter':
				$files = [
					'nl_newsletters.sql',
					'nl_issues.sql',
					'nl_subscriptions.sql',
					'nl_publishers.sql',
				];
				$path = "$IP/extensions/Newsletter/sql";
				break;

			case 'oathauth':
				$files = [ 'tables.sql' ];
				$path = "$IP/extensions/OATHAuth/sql/mysql";
				break;

			case 'oauth':
				$files = [ 'OAuth.sql' ];
				$path = "$IP/extensions/OAuth/backend/schema/mysql";
				break;

			case 'ores':
				$files = [
					'ores_model.sql',
					'ores_classification.sql',
				];
				$path = "$IP/extensions/ORES/sql";
				break;

			case 'pageassessments':
				$files = [
					'addProjectsTable.sql',
					'addReviewsTable.sql',
				];
				$path = "$IP/extensions/PageAssessments/db";
				break;

			case 'shorturl':
				$files = [ 'shorturls.sql' ];
				$path = "$IP/extensions/ShortUrl/schemas";
				break;

			case 'translate':
				$files = [
					'revtag.sql',
					'translate_groupstats.sql',
					'translate_metadata.sql',
					'translate_sections.sql',
					'translate_groupreviews.sql',
					'translate_messageindex.sql',
					'translate_reviews.sql',
				];
				$path = "$IP/extensions/Translate/sql";
				break;

			case 'wikilove':
				$files = [ 'WikiLoveLog.sql' ];
				$path = "$IP/extensions/WikiLove/patches";
				break;

			default:
				$this->fatalError( "This script is not configured to create tables for $extension\n" );
		}

		$this->output( "Creating $extension tables..." );
		foreach ( $files as $file ) {
			$dbw->sourceFile( "$path/$file" );
		}
		$this->output( "done!\n" );
	}
}

$maintClass = 'CreateExtensionTables';
require_once DO_MAINTENANCE;
