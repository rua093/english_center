<div id="calendar-detail-modal" role="dialog" aria-modal="true" aria-labelledby="calendar-detail-title" class="fixed inset-0 z-[10000] hidden opacity-0 transition-opacity duration-200">
    <div class="absolute inset-0 bg-slate-950/55 backdrop-blur-sm" onclick="closeCalendarDetail()"></div>
    <div class="relative z-10 flex min-h-full items-end justify-center p-2 sm:items-center sm:p-6">
        <div data-calendar-detail-panel tabindex="-1" class="flex h-[78vh] max-h-[78vh] w-full max-w-5xl translate-y-4 scale-95 flex-col overflow-hidden rounded-t-[2rem] border border-slate-200 bg-white shadow-2xl shadow-slate-900/20 transition-all duration-200 sm:h-[70vh] sm:max-h-[70vh] sm:rounded-[2rem]">
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 bg-gradient-to-r from-blue-50 via-white to-rose-50 px-4 py-4 sm:px-6 sm:py-5">
                <div class="min-w-0">
                    <p class="text-[10px] font-black uppercase tracking-[0.35em] text-blue-500">Calendar detail</p>
                    <h3 id="calendar-detail-title" class="mt-1 text-lg font-black text-slate-900 sm:text-2xl">Chi tiết thời khoá biểu</h3>
                    <p id="calendar-detail-subtitle" class="mt-1 text-sm font-semibold text-slate-500"></p>
                </div>
                <button type="button" onclick="closeCalendarDetail()" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white text-slate-500 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-50 hover:text-slate-900">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="flex-1 space-y-5 overflow-y-auto px-4 py-4 sm:px-6 sm:py-6">
                <div class="flex flex-col gap-3 rounded-[1.5rem] bg-slate-50 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400">Tổng quan</p>
                        <p id="calendar-detail-summary" class="mt-1 text-sm font-bold text-slate-800"></p>
                    </div>
                </div>

                <div class="grid gap-4">
                    <div id="calendar-detail-list" class="space-y-4"></div>
                </div>
            </div>
        </div>
    </div>
</div>