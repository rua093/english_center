<?php
declare(strict_types=1);

require_once __DIR__ . '/../partials/header.php';
if (isset($__pageContent)) {
    echo $__pageContent;
} else {
    require __DIR__ . '/index.php';
}

$eventsJson = json_encode($dbEvents ?? []);
$calendarScript = <<<'HTML'
<script>
document.addEventListener('DOMContentLoaded', function () {
    const app = document.querySelector('section.min-h-screen');
    if (!app) {
        return;
    }

    const eventsData = __EVENTS_JSON__;
    let currentDate = new Date();
    let currentView = 'month';

    const colorMap = {
        blue: 'bg-blue-50 text-blue-700 border-blue-200 border-l-[3px] border-l-blue-600',
        emerald: 'bg-emerald-50 text-emerald-700 border-emerald-200 border-l-[3px] border-l-emerald-600',
        rose: 'bg-rose-50 text-rose-700 border-rose-200 border-l-[3px] border-l-rose-600',
        amber: 'bg-amber-50 text-amber-700 border-amber-200 border-l-[3px] border-l-amber-600',
    };

    const tooltipColorMap = {
        blue: 'bg-blue-600',
        emerald: 'bg-emerald-600',
        rose: 'bg-rose-600',
        amber: 'bg-amber-600',
    };

    function renderCalendar() {
        const grid = document.getElementById('calendar-grid');
        const title = document.getElementById('calendar-title');
        if (!grid || !title) {
            return;
        }

        grid.innerHTML = '';

        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();
        const todayStr = new Date().toISOString().split('T')[0];

        if (currentView === 'month') {
            title.innerText = `Tháng ${month + 1}, ${year}`;
            const firstDay = new Date(year, month, 1);
            const lastDayPrevMonth = new Date(year, month, 0).getDate();
            const startDayIdx = firstDay.getDay() === 0 ? 6 : firstDay.getDay() - 1;
            const daysInMonth = new Date(year, month + 1, 0).getDate();

            for (let i = startDayIdx; i > 0; i--) {
                const d = lastDayPrevMonth - i + 1;
                renderDayCell(grid, d, 'prev', false);
            }

            for (let d = 1; d <= daysInMonth; d++) {
                const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                renderDayCell(grid, d, 'current', dateStr === todayStr, dateStr);
            }

            const totalCellsSoFar = startDayIdx + daysInMonth;
            const remainingCells = 42 - totalCellsSoFar;
            for (let d = 1; d <= remainingCells; d++) {
                renderDayCell(grid, d, 'next', false);
            }
        } else {
            const curr = new Date(currentDate);
            const first = curr.getDate() - (curr.getDay() === 0 ? 6 : curr.getDay() - 1);
            const startWeek = new Date(curr.setDate(first));
            title.innerText = `Tuần ${startWeek.getDate()}/${startWeek.getMonth() + 1}`;

            for (let i = 0; i < 7; i++) {
                const d = new Date(startWeek);
                d.setDate(startWeek.getDate() + i);
                const dStr = d.toISOString().split('T')[0];
                renderWeekColumn(grid, d, dStr === todayStr, dStr);
            }
        }

        updateUpcomingPanel();
        setupTooltipListeners();
        updateButtons();
    }

    function renderDayCell(container, day, type, isToday, dateStr) {
        const isDimmed = type !== 'current';
        const bgClass = isToday ? 'bg-gradient-to-br from-blue-100 via-white to-cyan-100' : (isDimmed ? 'bg-slate-50/70' : 'bg-white');
        const textClass = isDimmed ? 'text-slate-300' : (isToday ? 'text-blue-700 font-black' : 'text-slate-700');

        let html = `<div class="${bgClass} min-h-[110px] p-2 border-t border-white/20 border-r border-r-slate-200/40 transition-all hover:z-20 group relative">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full text-sm md:text-base font-black ${isToday ? 'bg-blue-600 text-white shadow-md shadow-blue-200' : ''} ${textClass}">${day}</span>
            <div class="mt-1.5 space-y-1">`;

        if (!isDimmed) {
            eventsData.filter((event) => event.date === dateStr).forEach((event) => {
                html += `<div class="event-chip ${colorMap[event.type]} text-[9px] font-black px-1.5 py-1 rounded-md cursor-help truncate shadow-sm hover:brightness-95"
                    data-title="${event.title}" data-time="${event.time}" data-teacher="${event.teacher}" data-room="${event.room}" data-color="${event.type}">${event.title}</div>`;
            });
        }

        html += `</div></div>`;
        container.innerHTML += html;
    }

    function renderWeekColumn(container, dateObj, isToday, dateStr) {
        let html = `<div class="${isToday ? 'bg-gradient-to-b from-blue-50/70 to-cyan-50/40' : 'bg-white'} min-h-[400px] p-3 border-t border-white/35 border-r border-r-slate-200/40">
            <p class="text-center mb-4">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full text-xl font-black ${isToday ? 'bg-blue-600 text-white shadow-md shadow-blue-200' : 'bg-slate-100 text-slate-700'}">${dateObj.getDate()}</span>
                <span class="mt-2 block text-[10px] font-black uppercase tracking-[0.28em] text-blue-400">Tháng ${dateObj.getMonth() + 1}</span>
            </p>
            <div class="space-y-2">`;

        eventsData.filter((event) => event.date === dateStr).forEach((event) => {
            html += `<div class="event-chip ${colorMap[event.type]} p-2.5 rounded-xl text-[11px] font-black cursor-help shadow-sm"
                data-title="${event.title}" data-time="${event.time}" data-teacher="${event.teacher}" data-room="${event.room}" data-color="${event.type}">
                <div class="opacity-70 text-[9px] mb-1 uppercase tracking-tighter">${event.time}</div>
                <div class="leading-tight">${event.title}</div>
            </div>`;
        });

        html += `</div></div>`;
        container.innerHTML += html;
    }

    function updateUpcomingPanel() {
        const list = document.getElementById('upcoming-list');
        if (!list) {
            return;
        }

        const today = new Date();
        const tomorrow = new Date();
        tomorrow.setDate(today.getDate() + 1);

        const datesToShow = [today.toISOString().split('T')[0], tomorrow.toISOString().split('T')[0]];
        const upcoming = eventsData.filter((event) => datesToShow.includes(event.date));

        if (upcoming.length === 0) {
            list.innerHTML = `<div class="text-center py-8"><p class="text-xs font-bold text-slate-400 uppercase tracking-widest leading-relaxed">Không có lịch học<br>trong 48h tới</p></div>`;
            return;
        }

        list.innerHTML = upcoming.map((event) => `
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 hover:border-blue-200 transition group cursor-pointer">
                <div class="flex justify-between items-start mb-2">
                    <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full ${event.type === 'blue' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700'}">${event.date === datesToShow[0] ? 'Hôm nay' : 'Ngày mai'}</span>
                    <span class="text-[10px] font-bold text-slate-400">${event.time}</span>
                </div>
                <h4 class="text-sm font-black text-slate-800 group-hover:text-blue-600 transition">${event.title}</h4>
                <p class="text-[10px] text-slate-500 font-medium mt-1 uppercase tracking-wider">${event.room} • ${event.teacher}</p>
            </div>
        `).join('');
    }

    function setupTooltipListeners() {
        const tooltip = document.getElementById('event-tooltip');
        if (!tooltip) {
            return;
        }

        document.querySelectorAll('.event-chip').forEach((chip) => {
            chip.onmouseenter = () => {
                const rect = chip.getBoundingClientRect();
                const tooltipTitle = document.getElementById('tooltip-title');
                const tooltipTime = document.getElementById('tooltip-time');
                const tooltipTeacher = document.getElementById('tooltip-teacher');
                const tooltipRoom = document.getElementById('tooltip-room');
                const tooltipColor = document.getElementById('tooltip-color');

                if (tooltipTitle) tooltipTitle.innerText = chip.dataset.title || '';
                if (tooltipTime) tooltipTime.innerText = chip.dataset.time || '';
                if (tooltipTeacher) tooltipTeacher.innerText = chip.dataset.teacher || '';
                if (tooltipRoom) tooltipRoom.innerText = chip.dataset.room || '';
                if (tooltipColor) tooltipColor.className = `w-1.5 h-10 rounded-full ${tooltipColorMap[chip.dataset.color || 'blue']}`;

                tooltip.style.left = `${rect.left + rect.width / 2}px`;
                tooltip.style.top = `${rect.top - 10}px`;
                tooltip.style.transform = 'translate(-50%, -100%) scale(1)';
                tooltip.classList.remove('hidden');
                setTimeout(() => tooltip.classList.add('opacity-100'), 10);
            };

            chip.onmouseleave = () => {
                tooltip.classList.remove('opacity-100');
                setTimeout(() => tooltip.classList.add('hidden'), 200);
            };
        });
    }

    function setView(view) {
        currentView = view;
        renderCalendar();
    }

    function changeDate(offset) {
        if (currentView === 'month') {
            currentDate.setMonth(currentDate.getMonth() + offset);
        } else {
            currentDate.setDate(currentDate.getDate() + offset * 7);
        }
        renderCalendar();
    }

    function resetToToday() {
        currentDate = new Date();
        renderCalendar();
    }

    function updateButtons() {
        const monthButton = document.getElementById('btn-view-month');
        const weekButton = document.getElementById('btn-view-week');
        if (!monthButton || !weekButton) {
            return;
        }

        monthButton.className = currentView === 'month'
            ? 'px-5 py-2 text-xs font-bold uppercase rounded-lg bg-white shadow-md text-blue-700'
            : 'px-5 py-2 text-xs font-bold uppercase rounded-lg text-slate-400 hover:text-slate-600';
        weekButton.className = currentView === 'week'
            ? 'px-5 py-2 text-xs font-bold uppercase rounded-lg bg-white shadow-md text-blue-700'
            : 'px-5 py-2 text-xs font-bold uppercase rounded-lg text-slate-400 hover:text-slate-600';
    }

    window.setView = setView;
    window.changeDate = changeDate;
    window.resetToToday = resetToToday;

    renderCalendar();
});
</script>
HTML;

$calendarScript = str_replace('__EVENTS_JSON__', $eventsJson ?: '[]', $calendarScript);
echo $calendarScript;

require_once __DIR__ . '/../partials/footer.php';

