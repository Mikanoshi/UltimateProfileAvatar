<?php

namespace Piwik\Plugins\UltimateProfileAvatar;

use Piwik\Http;
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

	function resolveUrl(string $base, string $location): ?string	{
		$location = trim($location);
		if ($location === '') return null;
		if (preg_match('#^https?://[^\s/]+#i', $location)) return $location;

		$parts = parse_url($base);
		if ($parts === false || empty($parts['scheme']) || empty($parts['host'])) return null;
		if (strpos($location, '//') === 0) return $parts['scheme'].':'.$location;
		
		if ($location[0] === '/')
		return $parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '').$location;

		return null;
	}

	function sendHeadRequest(string $url, int $maxRedirects = 5): int {
		for ($i = 0; $i <= $maxRedirects; $i++) {
			$result = null;
			try {
				$result = Http::sendHttpRequest($url, 3, null, null, null, false, false, true, 'HEAD');
			} catch (\Exception $e) {
				return 0;
			}
			if (!is_array($result) || empty($result['status'])) return 0;
			$status = (int)$result['status'];
			if ($status >= 300 && $status < 400) {
				$headers = array_change_key_case($result['headers'], CASE_LOWER);
				if (empty($headers['location'])) break;
				$location = is_array($headers['location']) ? end($headers['location']) : $headers['location'];
				$url = $this->resolveUrl($url, $location);
				if (empty($url)) break;
				continue;
			}
			return $status;
		}
		return 0;
	}

	function applyAvatarURL($hash, $visitorId, &$avatarURL) {
		$settings = StaticContainer::get(UserSettings::class);
		$doLibravatar = $settings->useLibravatar->getValue();
		$doDicebear = $settings->dicebearFallback->getValue();

		if ($doLibravatar && $hash !== null) {
			$libravatar = 'https://seccdn.libravatar.org/avatar/'.rawurlencode($hash).'?s=240&d=404';
			if ($this->sendHeadRequest($libravatar) === 200) {
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
			'<img src="'.htmlspecialchars($avatarURL, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'" loading="lazy"/>'.
			'<ul class="details"><li>'.htmlspecialchars($avatarDesc, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'</li></ul></span>';
	}
}

?>