const menuToggle = document.getElementById("mobile-menu-toggle");
const mainNav = document.getElementById("main-nav");

if (menuToggle && mainNav) {
	const closeMobileMenu = () => {
		mainNav.classList.add("hidden");
		mainNav.classList.remove("flex");
		menuToggle.setAttribute("aria-expanded", "false");
	};

	const openMobileMenu = () => {
		mainNav.classList.remove("hidden");
		mainNav.classList.add("flex");
		menuToggle.setAttribute("aria-expanded", "true");
	};

	closeMobileMenu();

	menuToggle.addEventListener("click", () => {
		if (mainNav.classList.contains("hidden")) {
			openMobileMenu();
			return;
		}

		closeMobileMenu();
	});

	document.addEventListener("click", (event) => {
		const target = event.target;
		if (!(target instanceof Node)) {
			return;
		}

		if (!mainNav.contains(target) && !menuToggle.contains(target)) {
			closeMobileMenu();
		}
	});
}

const roleTabList = document.querySelector("[data-role-switcher]");
const rolePanelsWrap = document.querySelector("[data-role-panels]");

if (roleTabList && rolePanelsWrap) {
	const roleTabs = roleTabList.querySelectorAll("button[data-role]");
	const rolePanels = rolePanelsWrap.querySelectorAll("[data-role]");
	const activeTabClasses = ["border-blue-200", "bg-blue-50", "text-blue-700"];
	const inactiveTabClasses = ["border-slate-200", "bg-white", "text-slate-700"];
	const activePanelClasses = ["border-blue-200", "bg-blue-50"];

	const setActiveRole = (role) => {
		roleTabs.forEach((tab) => {
			const isActive = tab.dataset.role === role;
			tab.classList.remove(...activeTabClasses, ...inactiveTabClasses);
			tab.classList.add(...(isActive ? activeTabClasses : inactiveTabClasses));
			tab.setAttribute("aria-selected", isActive ? "true" : "false");
		});

		rolePanels.forEach((panel) => {
			const isActive = panel.dataset.role === role;
			panel.classList.toggle("hidden", !isActive);
			panel.classList.remove(...activePanelClasses);
			if (isActive) {
				panel.classList.add(...activePanelClasses);
			}
		});
	};

	roleTabs.forEach((tab) => {
		tab.addEventListener("click", () => {
			setActiveRole(tab.dataset.role || "");
		});
	});

	const defaultActiveTab =
		Array.from(roleTabs).find((tab) => tab.classList.contains("border-blue-200")) || roleTabs[0];

	if (defaultActiveTab) {
		setActiveRole(defaultActiveTab.dataset.role || "");
	}
}

document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
	anchor.addEventListener("click", (event) => {
		const href = anchor.getAttribute("href");
		if (!href || href === "#") {
			return;
		}

		const target = document.querySelector(href);
		if (!target) {
			return;
		}

		event.preventDefault();
		target.scrollIntoView({ behavior: "smooth", block: "start" });
		if (mainNav) {
			mainNav.classList.add("hidden");
			mainNav.classList.remove("flex");
			menuToggle?.setAttribute("aria-expanded", "false");
		}
	});
});

mainNav?.querySelectorAll("a").forEach((link) => {
	link.addEventListener("click", () => {
		mainNav.classList.add("hidden");
		mainNav.classList.remove("flex");
		menuToggle?.setAttribute("aria-expanded", "false");
	});
});

document.querySelectorAll('input[type="tel"], input[name*="phone"]').forEach((input) => {
	if (!(input instanceof HTMLInputElement)) {
		return;
	}
	if (input.dataset.phoneSanitized === "1") {
		return;
	}

	const sanitizePhoneValue = () => {
		input.value = input.value.replace(/\D+/g, "");
	};

	input.dataset.phoneSanitized = "1";
	input.setAttribute("inputmode", "numeric");
	input.setAttribute("pattern", "[0-9]*");
	input.addEventListener("input", sanitizePhoneValue);
	input.addEventListener("paste", () => {
		requestAnimationFrame(sanitizePhoneValue);
	});
});
