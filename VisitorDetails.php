<?php

namespace Piwik\Plugins\UltimateProfileAvatar;

use Piwik\Request;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\Live\VisitorDetailsAbstract;

class VisitorDetails extends VisitorDetailsAbstract {
	function appendUrlParam(string $url, string $name, string $value): string {
		$parts = parse_url($url);
		if ($parts === false) return $url;

		$query = [];
		if (isset($parts['query'])) parse_str($parts['query'], $query);
		$query[$name] = $value;

		$result = '';
		if (isset($parts['scheme'])) $result .= $parts['scheme'].':';
		if (isset($parts['host'])) {
			$result .= '//';
			if (isset($parts['user'])) $result .= $parts['user'].(isset($parts['pass']) ? ':'.$parts['pass'] : '').'@';
			$result .= $parts['host'];
			if (isset($parts['port'])) $result .= ':'.$parts['port'];
		}
		$result .= $parts['path'] ?? '';
		$result .= '?'.http_build_query($query, '', '&', PHP_QUERY_RFC3986);
		if (isset($parts['fragment'])) $result .= '#'.$parts['fragment'];

		return $result;
	}

	function applyAvatarURL($hash, $visitorId, &$avatarURL) {
		$settings = StaticContainer::get(UserSettings::class);
		$doLibravatar = $settings->useLibravatar->getValue();
		$doDicebear = $settings->dicebearFallback->getValue();

		if ($doLibravatar && $hash !== null) {
			$libravatar = 'https://seccdn.libravatar.org/avatar/'.rawurlencode($hash).'?s=240&d=404';

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $libravatar);
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_NOBODY, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_MAXREDIRS, 20);
			curl_setopt($ch, CURLOPT_TIMEOUT, 2);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_exec($ch);
			$code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
			curl_close ($ch);

			if ($code == 200) {
				$avatarURL = $libravatar;
				$doDicebear = false;
			}
		}

		if ($doDicebear) {
			$dicebearURL = trim($settings->dicebearURL->getValue());			
			if (empty($hash)) $hash = hash('sha256', $visitorId);
			if (empty($dicebearURL)) $dicebearURL = 'https://api.dicebear.com/10.x/voxel-art/svg?tags=animation&backgroundColor=&flip=horizontal';
			$avatarURL = $this->appendUrlParam($dicebearURL, 'seed', $hash);
		}
	}

	public function extendVisitorDetails(&$visitor) {
		$hash = $this->details['libravatar_hash'] ?? ($this->details['gravatar_hash'] ?? null);
		if (empty($hash) && !empty($this->details['user_id']) && filter_var($this->details['user_id'], FILTER_VALIDATE_EMAIL))
		$hash = hash('sha256', strtolower(trim((string)$this->details['user_id'])));
		$visitor['libravatar_hash'] = $hash;
	}

	public function initProfile($visits, &$profile) {
		$hash = null;
		foreach ($visits->getRows() as $visit) {
			$hash = $visit->getColumn('libravatar_hash') ?? null;
			if (empty($hash) || $hash === 'false') {
				$hash = null;
				continue;
			} else break;
		}
		$this->applyAvatarURL($hash, $profile['visitorId'], $profile['visitorAvatar']);
		$profile['visitorDescription'] = empty($this->details['user_id']) ? $profile['visitorId'] : $this->details['user_id'];
	}

	public function renderIcons($visitorDetails) {
		if (!StaticContainer::get(UserSettings::class)->showInVisitsLog->getValue()) return '';

		$request = Request::fromRequest();
		$action = $request->getStringParameter('action', '');
		if ($action !== 'getLastVisitsDetails') return '';

		$hash = $visitorDetails->getColumn('libravatar_hash') ?? null;
		if (empty($hash) || $hash === 'false') $hash = null;

		$userId = $visitorDetails->getColumn('userId');
		$visitorId = $visitorDetails->getColumn('visitorId');
		$avatarURL = null;
		$avatarDesc = empty($userId) ? $visitorId : $userId;
		$this->applyAvatarURL($hash, $visitorId, $avatarURL);

		return '<span class="visitorLogIconWithDetails ultimateProfileAvatar">'.
			'<img src="'.$avatarURL.'" loading="lazy"/>'.
			'<ul class="details"><li>'.$avatarDesc.'</li></ul></span>';
	}
}

?>