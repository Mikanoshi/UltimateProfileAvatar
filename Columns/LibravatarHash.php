<?php

namespace Piwik\Plugins\UltimateProfileAvatar\Columns;

use Piwik\Common;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

class LibravatarHash extends VisitDimension {
	protected $columnName = 'libravatar_hash';
	protected $columnType = 'CHAR(64) NULL';
	protected $nameSingular = 'UltimateProfileAvatar_LibravatarHash';
	protected $segmentName = 'libravatarHash';
	protected $acceptValues = 'User\'s email hashed with SHA-256';

	private function readHash(Request $request): ?string {
		$hash = Common::getRequestVar('libravatar_hash', '', 'string', $request->getParams());
		$hash = strtolower(trim($hash));
		return preg_match('/^[a-f0-9]{64}$/', $hash) ? $hash : null;
    }

	public function onNewVisit(Request $request, Visitor $visitor, $action) {
		return $this->readHash($request);
	}

	public function onExistingVisit(Request $request, Visitor $visitor, $action) {
		return $this->readHash($request) ?? false;
	}
}

?>