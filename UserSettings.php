<?

namespace Piwik\Plugins\UltimateProfileAvatar;

use Piwik\Piwik;
use Piwik\Settings\Setting;
use Piwik\Settings\FieldConfig;

class UserSettings extends \Piwik\Settings\Plugin\UserSettings {
	public $useLibravatar;
	public $dicebearFallback;
	public $dicebearURL;
	public $showInVisitsLog;
	protected function init() {
		$this->title = Piwik::translate('UltimateProfileAvatar_PluginName');
		$this->useLibravatar = $this->makeSetting('useLibravatar', true, FieldConfig::TYPE_BOOL,
			function (FieldConfig $field) {
				$field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;
				$field->title = Piwik::translate('UltimateProfileAvatar_UseLibravatar');
				$help_part1 = Piwik::translate('UltimateProfileAvatar_UseLibravatar_Desc1');
				$help_part2 = Piwik::translate('UltimateProfileAvatar_UseLibravatar_Desc2');
				$help_part3 = Piwik::translate('UltimateProfileAvatar_UseLibravatar_Desc3');
				$help_part4 = Piwik::translate('UltimateProfileAvatar_UseLibravatar_Desc4');
				$field->inlineHelp = $help_part1.":<br>
				<ul class='browser-default'>
				<li>".$help_part2."<br>
				<pre><code>_paq.push(
	['UltimateProfileAvatar.setLibravatarHash', 'lowercase-hexadecimal-64-character-sha256-email-hash'],
	['trackPageView']
)</code></pre></li>
				<li>".$help_part3."
				<pre><code>_paq.push(
	['setUserId', 'some@email.addr'],
	['trackPageView']
)</code></pre></li>
				</ul><br>
				".$help_part4;
			}
		);
		$this->dicebearFallback = $this->makeSetting('dicebearFallback', true, FieldConfig::TYPE_BOOL,
			function (FieldConfig $field) {
				$field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;
				$field->title = Piwik::translate('UltimateProfileAvatar_DiceBearFallback');
				$field->inlineHelp = Piwik::translate('UltimateProfileAvatar_DiceBearFallback_Desc');
			}
		);
		$this->dicebearURL = $this->makeSetting('dicebearUrl', '', FieldConfig::TYPE_STRING, function (FieldConfig $field) {
			$field->uiControl = FieldConfig::UI_CONTROL_TEXT;
			$field->title = Piwik::translate('UltimateProfileAvatar_DiceBearCustomURL');
			$field->inlineHelp = Piwik::translate('UltimateProfileAvatar_DiceBearCustomURL_Desc');
			$field->condition = 'dicebearFallback';
			$field->uiControlAttributes = ['placeholder' => 'https://api.dicebear.com/10.x/voxel-art/svg?tags=animation&borderRadius=5'];
			$field->validate = function ($value) {
				$value = trim((string)$value);
				if ($value === '') return;
				if (!preg_match('#^https://[^\s/]+(/\S*)?$#i', $value))
				throw new \Exception(Piwik::translate('UltimateProfileAvatar_InvalidURL'));
			};
			$field->transform = function ($value) {
				return trim((string)$value);
			};
		});
		$this->showInVisitsLog = $this->makeSetting('showInVisitsLog', true, FieldConfig::TYPE_BOOL,
			function (FieldConfig $field) {
				$field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;
				$field->title = Piwik::translate('UltimateProfileAvatar_ShowInVisitsLog');
				$field->inlineHelp = Piwik::translate('UltimateProfileAvatar_ShowInVisitsLog_Desc');
			}
		);
	}
}

?>