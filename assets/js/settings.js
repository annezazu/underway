/* Underway settings page — sync the master AI switch with per-widget switches.
 *
 * Rules:
 *  - When the master is ON, per-widget toggles are forced ON and greyed (they
 *    follow the master). They remain technically toggleable: doing so turns
 *    the master OFF (since master ON means "use across all").
 *  - When the master is OFF, per-widget toggles are independent.
 *  - Toggling the master ON checks every per-widget toggle.
 *  - Toggling the master OFF leaves per-widget toggles in their current state.
 */

(function () {
	'use strict';

	function init() {
		var panel = document.querySelector('.underway-panel');
		if (!panel) {
			return;
		}
		var master = panel.querySelector('input[name="ai[master]"]');
		var perWidget = panel.querySelectorAll('input[name^="ai[modules]"]');
		if (!master || !perWidget.length) {
			return;
		}

		function syncPanel() {
			panel.classList.toggle('is-master-on', master.checked);
		}

		function allChecked() {
			for (var i = 0; i < perWidget.length; i++) {
				if (!perWidget[i].checked) {
					return false;
				}
			}
			return true;
		}

		master.addEventListener('change', function () {
			if (master.checked) {
				// Master ON → check every per-widget toggle.
				for (var i = 0; i < perWidget.length; i++) {
					perWidget[i].checked = true;
				}
			}
			syncPanel();
		});

		perWidget.forEach(function (cb) {
			cb.addEventListener('change', function () {
				// If any per-widget is OFF, the master can't be ON.
				if (!cb.checked && master.checked) {
					master.checked = false;
				}
				// If everything is back to ON, sync the master ON too.
				if (allChecked()) {
					master.checked = true;
				}
				syncPanel();
			});
		});

		// Initial sync (in case form state is inconsistent on load).
		if (master.checked && !allChecked()) {
			master.checked = false;
		} else if (!master.checked && allChecked()) {
			// All widgets on but master off — leave as-is; matches a transitional
			// state where the user opted in widget-by-widget without flipping master.
		}
		syncPanel();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
