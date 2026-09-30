<?php

namespace Piwik\Plugins\UltimateProfileAvatar;

class UltimateProfileAvatar extends \Piwik\Plugin {
	public function registerEvents() {
		return [ 'AssetManager.getStylesheetFiles' => 'getCSS' ];
	}

	public function getCSS(&$files) {
		$files[] = 'plugins/UltimateProfileAvatar/stylesheets/UltimateProfileAvatar.css';
	}
}

?>