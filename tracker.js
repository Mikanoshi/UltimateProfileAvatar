(function () {

function init() {
	var libravatarHash = false;

	Matomo.on('TrackerSetup', function (tracker) {
		tracker.UltimateProfileAvatar = {
			setLibravatarHash: function (hash) {
				libravatarHash = hash;
			}
		};
	});

	Matomo.addPlugin('UltimateProfileAvatar', {
		log: function () {
			return libravatarHash ? '&libravatar_hash=' + encodeURIComponent(libravatarHash) : '';
		}
	});
}

if (typeof window.Matomo === 'object') {
	init();
} else {
	if (typeof window.matomoPluginAsyncInit !== 'object') window.matomoPluginAsyncInit = [];
	window.matomoPluginAsyncInit.push(init);
}

})();
