<style>
	@keyframes bell-shake {
		0%, 100% { transform: rotate(0deg) translateY(0); }
		10% { transform: rotate(-8deg) translateY(-1px); }
		20% { transform: rotate(8deg) translateY(-1px); }
		30% { transform: rotate(-6deg) translateY(0); }
		40% { transform: rotate(6deg) translateY(0); }
		50% { transform: rotate(-4deg) translateY(-1px); }
		60% { transform: rotate(4deg) translateY(0); }
		70% { transform: rotate(-2deg) translateY(0); }
		80% { transform: rotate(2deg) translateY(0); }
		90% { transform: rotate(0deg) translateY(-1px); }
	}

	.contact-bell {
		animation: bell-shake 1.8s ease-in-out infinite;
		transform-origin: center bottom;
	}

	.contact-bell:nth-child(2) {
		animation-delay: 0.15s;
	}

	.contact-bell:nth-child(3) {
		animation-delay: 0.3s;
	}

	.contact-bell:nth-child(4) {
		animation-delay: 0.45s;
	}

	.contact-bell:nth-child(5) {
		animation-delay: 0.6s;
	}

	.contact-bell:hover {
		animation-play-state: paused;
	}
</style>

<div class="fixed bottom-5 right-4 z-50 flex flex-col items-end gap-3 sm:bottom-6 sm:right-6">
    <a href="#hero-video" class="group flex h-11 w-11 sm:h-12 sm:w-12 items-center justify-center rounded-full bg-slate-800 text-white shadow-[0_10px_25px_rgba(15,23,42,0.28)] ring-2 ring-white transition-all hover:-translate-y-1 hover:bg-slate-700" aria-label="Đi tới hero video">
			<i class="fa-solid fa-arrow-up text-[15px] sm:text-base transition-transform duration-300 group-hover:-translate-y-0.5"></i>
		</a>
    <a href="https://www.facebook.com/" target="_blank" rel="noopener noreferrer" class="group contact-bell flex h-11 w-11 sm:h-12 sm:w-12 items-center justify-center rounded-full bg-[#1877F2] text-white shadow-[0_10px_25px_rgba(24,119,242,0.28)] ring-2 ring-white transition-all hover:-translate-y-1 hover:scale-105" aria-label="Facebook" style="animation-delay: 0s;">
			<i class="fa-brands fa-facebook-f text-[15px] sm:text-base"></i>
		</a>
    <a href="https://zalo.me/" target="_blank" rel="noopener noreferrer" class="group contact-bell flex h-11 w-11 sm:h-12 sm:w-12 items-center justify-center rounded-full bg-[#0068FF] text-white shadow-[0_10px_25px_rgba(0,104,255,0.28)] ring-2 ring-white transition-all hover:-translate-y-1 hover:scale-105" aria-label="Zalo" style="animation-delay: 0.15s;">
			<span class="text-[13px] sm:text-sm font-black leading-none tracking-tight">Z</span>
		</a>
    <a href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer" class="group contact-bell flex h-11 w-11 sm:h-12 sm:w-12 items-center justify-center rounded-full bg-gradient-to-br from-[#f09433] via-[#e6683c] via-[#dc2743] to-[#bc1888] text-white shadow-[0_10px_25px_rgba(220,39,67,0.28)] ring-2 ring-white transition-all hover:-translate-y-1 hover:scale-105" aria-label="Instagram" style="animation-delay: 0.3s;">
			<i class="fa-brands fa-instagram text-[15px] sm:text-base"></i>
		</a>
    <a href="https://www.messenger.com/" target="_blank" rel="noopener noreferrer" class="group contact-bell flex h-11 w-11 sm:h-12 sm:w-12 items-center justify-center rounded-full bg-[#0084FF] text-white shadow-[0_10px_25px_rgba(0,132,255,0.28)] ring-2 ring-white transition-all hover:-translate-y-1 hover:scale-105" aria-label="Messenger" style="animation-delay: 0.45s;">
			<i class="fa-brands fa-facebook-messenger text-[15px] sm:text-base"></i>
		</a>
	</div>
