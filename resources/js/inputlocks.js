function applyInputLocks() {
	document.querySelectorAll("[data-input-locked]").forEach((container) => {
		container
			.querySelectorAll("input, select, textarea, button")
			.forEach((control) => {
				const hasUnlockOverride =
					control.hasAttribute("data-input-unlock") ||
					Boolean(control.closest("[data-input-unlock]"));

				if (hasUnlockOverride) {
					if (control.dataset.lockDisabled === "true") {
						control.disabled = false;
						delete control.dataset.lockDisabled;
					}
					return;
				}

				if (!control.disabled) {
					control.dataset.lockDisabled = "true";
				}

				control.disabled = true;
			});
	});
}

// Ensure locking behavior during dynamic rendering
const observer = new MutationObserver(() => {
	applyInputLocks();
});

observer.observe(document.body, {
	childList: true,
	subtree: true,
	attributes: true,
	attributeFilter: ["data-input-locked", "data-input-unlock"],
});

applyInputLocks();
