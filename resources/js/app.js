import "./bootstrap";
import Alpine from "alpinejs";
import "trix";
import { initTrixAttachmentUpload } from "./trix-upload";
import Chart from "chart.js/auto";

initTrixAttachmentUpload();

Alpine.data("toast", (initialMessage = "", initialType = "success") => ({
    visible: false,
    message: initialMessage,
    type: initialType,
    progress: 100,
    duration: 4000,
    interval: null,

    init() {
        if (this.message) this.show();
        window.addEventListener("notify", (event) => {
            this.message = event.detail.message;
            this.type = event.detail.type || "success";
            this.show();
        });
    },
    show() {
        this.visible = true;
        this.progress = 100;
        if (this.interval) clearInterval(this.interval);
        const step = 100 / (this.duration / 50);
        this.interval = setInterval(() => {
            this.progress -= step;
            if (this.progress <= 0) this.close();
        }, 50);
    },
    close() {
        this.visible = false;
        clearInterval(this.interval);
    },
    get theme() {
        switch (this.type) {
            case "warning":
                return {
                    bg_icon: "bg-amber-100 dark:bg-amber-900/30",
                    text_icon: "text-amber-600 dark:text-amber-400",
                    progress_bg: "bg-amber-200 dark:bg-amber-900",
                    progress_fill: "bg-amber-500",
                    icon: "warning",
                    title: "Peringatan",
                };
            case "error":
                return {
                    bg_icon: "bg-red-100 dark:bg-red-900/30",
                    text_icon: "text-red-600 dark:text-red-400",
                    progress_bg: "bg-red-200 dark:bg-red-900",
                    progress_fill: "bg-red-500",
                    icon: "error",
                    title: "Gagal",
                };
            case "success":
            default:
                return {
                    bg_icon: "bg-emerald-100 dark:bg-emerald-900/30",
                    text_icon: "text-emerald-600 dark:text-emerald-400",
                    progress_bg: "bg-emerald-200 dark:bg-emerald-900",
                    progress_fill: "bg-emerald-500",
                    icon: "check_circle",
                    title: "Berhasil",
                };
        }
    },
}));

Alpine.data(
    "chartHandler",
    (trendData, statusData, chartData, durationData, priorityData) => ({
        trendChart: null,
        statusChart: null,
        serviceChart: null,
        entityChart: null,
        monthlyTrendChart: null,
        durationChart: null,
        priorityChart: null,

        init() {
            if (trendData) this.renderTrend(trendData);
            if (statusData) this.renderStatus(statusData);
            if (chartData) {
                this.renderService(chartData);
                this.renderEntity(chartData);
                this.renderMonthly(chartData);
            }
            if (durationData) this.renderDuration(durationData);
            if (priorityData) this.renderPriority(priorityData);

            // Tema listener
            const observer = new MutationObserver(() => {
                this.updateChartsTheme();
            });
            observer.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ["class"],
            });
        },

        get isDark() {
            return document.documentElement.classList.contains("dark");
        },

        getGridColor() {
            return this.isDark ? "#334155" : "#e2e8f0";
        },

        getTextColor() {
            return this.isDark ? "#cbd5e1" : "#475569";
        },

        renderTrend(data) {
            const ctx = document.getElementById("trendChart");
            if (!ctx) return;
            const labels = Object.keys(data);
            const values = Object.values(data);

            this.trendChart = new Chart(ctx, {
                type: "line",
                data: {
                    labels,
                    datasets: [
                        {
                            label: "Tiket Masuk",
                            data: values,
                            borderColor: "#3b82f6",
                            backgroundColor: (context) => {
                                const bg =
                                    context.chart.ctx.createLinearGradient(
                                        0,
                                        0,
                                        0,
                                        300,
                                    );
                                bg.addColorStop(0, "rgba(59, 130, 246, 0.4)");
                                bg.addColorStop(1, "rgba(59, 130, 246, 0.0)");
                                return bg;
                            },
                            borderWidth: 2.5,
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: "#ffffff",
                            pointBorderColor: "#3b82f6",
                            pointRadius: 3,
                            pointHoverRadius: 5,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            mode: "index",
                            intersect: false,
                            backgroundColor: this.isDark
                                ? "#1e293b"
                                : "#ffffff",
                            titleColor: this.isDark ? "#fff" : "#0f172a",
                            bodyColor: this.isDark ? "#cbd5e1" : "#334155",
                            borderColor: this.isDark ? "#334155" : "#e2e8f0",
                            borderWidth: 1,
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: this.getGridColor(),
                                borderDash: [5, 5],
                            },
                            ticks: {
                                color: this.getTextColor(),
                                stepSize: 1,
                                precision: 0,
                                callback: (v) =>
                                    Number.isInteger(v) ? v : null,
                            },
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                color: this.getTextColor(),
                                maxTicksLimit: 15,
                            },
                        },
                    },
                    interaction: {
                        mode: "nearest",
                        axis: "x",
                        intersect: false,
                    },
                },
            });
        },

        renderStatus(data) {
            const ctx = document.getElementById("statusChart");
            if (!ctx) return;

            this.statusChart = new Chart(ctx, {
                type: "doughnut",
                data: {
                    labels: ["Menunggu", "Proses", "Selesai", "Ditolak"],
                    datasets: [
                        {
                            data: [
                                data.waiting,
                                data.progress,
                                data.done,
                                data.reject,
                            ],
                            backgroundColor: [
                                "#ca8a04",
                                "#2563eb",
                                "#059669",
                                "#dc2626",
                            ],
                            hoverOffset: 6,
                            borderWidth: 0,
                        },
                    ],
                },
                options: {
                    plugins: {
                        legend: {
                            position: "bottom",
                            labels: {
                                color: this.getTextColor(),
                                usePointStyle: true,
                                padding: 16,
                                font: {
                                    family: "'Inter Variable', sans-serif",
                                    size: 11,
                                },
                            },
                        },
                    },
                    cutout: "72%",
                },
            });
        },

        renderService(data) {
            const ctx = document.getElementById("serviceBarChart");
            if (!ctx) return;

            // Responsive height & thickness based on screen width
            const isMobile = window.innerWidth < 640;
            const serviceCount = data.services_labels?.length ?? 0;
            const rowHeight = isMobile ? 64 : 42; // more space per row on mobile
            const barThick = isMobile ? 14 : 10;
            const minHeight = isMobile ? 320 : 260;
            const computedHeight = Math.max(
                minHeight,
                serviceCount * rowHeight + 60,
            );
            ctx.parentElement.style.height = computedHeight + "px";

            this.serviceChart = new Chart(ctx, {
                type: "bar",
                data: {
                    labels: data.services_labels,
                    datasets: [
                        {
                            label: "Total",
                            data: data.services_totals,
                            backgroundColor: "#8b5cf6",
                            borderRadius: 4,
                            barThickness: barThick,
                        },
                        {
                            label: "Selesai",
                            data: data.services_done ?? [],
                            backgroundColor: "#10b981",
                            borderRadius: 4,
                            barThickness: barThick,
                        },
                        {
                            label: "Ditolak",
                            data: data.services_reject ?? [],
                            backgroundColor: "#ef4444",
                            borderRadius: 4,
                            barThickness: barThick,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: "y",
                    plugins: {
                        legend: {
                            position: "top",
                            labels: {
                                color: this.getTextColor(),
                                usePointStyle: true,
                                padding: 16,
                                font: { size: 11 },
                            },
                        },
                        tooltip: {
                            backgroundColor: this.isDark
                                ? "#1e293b"
                                : "#ffffff",
                            titleColor: this.isDark ? "#fff" : "#0f172a",
                            bodyColor: this.isDark ? "#cbd5e1" : "#334155",
                            borderColor: this.isDark ? "#334155" : "#e2e8f0",
                            borderWidth: 1,
                        },
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: {
                                color: this.getGridColor(),
                                borderDash: [5, 5],
                            },
                            ticks: {
                                color: this.getTextColor(),
                                stepSize: 1,
                                precision: 0,
                                maxTicksLimit: isMobile ? 5 : 10,
                            },
                        },
                        y: {
                            grid: { display: false },
                            ticks: {
                                color: this.getTextColor(),
                                font: { size: isMobile ? 10 : 11 },
                                padding: isMobile ? 6 : 4,
                            },
                        },
                    },
                    layout: {
                        padding: { top: 4, bottom: 4 },
                    },
                },
            });
        },

        renderEntity(data) {
            const ctx = document.getElementById("entityPieChart");
            if (!ctx) return;

            const colors = data.entity_colors ?? [
                "#3b82f6",
                "#8b5cf6",
                "#10b981",
                "#14b8a6",
                "#fb923c",
                "#facc15",
                "#9ca3af",
            ];

            this.entityChart = new Chart(ctx, {
                type: "doughnut",
                data: {
                    labels: data.entity_labels,
                    datasets: [
                        {
                            data: data.entity_totals,
                            backgroundColor: colors,
                            borderWidth: 0,
                            hoverOffset: 4,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: this.isDark
                                ? "#1e293b"
                                : "#ffffff",
                            titleColor: this.isDark ? "#fff" : "#0f172a",
                            bodyColor: this.isDark ? "#cbd5e1" : "#334155",
                            borderColor: this.isDark ? "#334155" : "#e2e8f0",
                            borderWidth: 1,
                        },
                    },
                    cutout: "60%",
                },
            });
        },

        // ---- NEW: Monthly Trend (Bar) ----
        renderMonthly(data) {
            const ctx = document.getElementById("monthlyTrendChart");
            if (!ctx) return;

            // Build full 12-month skeleton from the data range
            // Determine year range from labels (format "Jan 2025")
            const allMonths = [];
            const monthNames = [
                "Jan",
                "Feb",
                "Mar",
                "Apr",
                "Mei",
                "Jun",
                "Jul",
                "Agu",
                "Sep",
                "Okt",
                "Nov",
                "Des",
            ];

            // Find earliest and latest year in data
            let minYear = new Date().getFullYear();
            let maxYear = minYear;
            (data.monthly_labels ?? []).forEach((lbl) => {
                const parts = lbl.split(" ");
                const yr = parseInt(parts[parts.length - 1]);
                if (!isNaN(yr)) {
                    minYear = Math.min(minYear, yr);
                    maxYear = Math.max(maxYear, yr);
                }
            });

            // If span ≤ 12 months show only those months in range, otherwise show 12
            const labelToKey = {};
            (data.monthly_labels ?? []).forEach((lbl, i) => {
                labelToKey[lbl] = i;
            });

            // Always show 12 month slots: use current year if only 1 year
            const showYear = minYear === maxYear ? minYear : null;
            const fullLabels = monthNames.map((m) =>
                showYear ? `${m} ${showYear}` : m,
            );

            // Map existing data onto the 12 slots
            const totalArr = new Array(12).fill(0);
            const doneArr = new Array(12).fill(0);
            const rejectArr = new Array(12).fill(0);

            (data.monthly_labels ?? []).forEach((lbl, i) => {
                // Parse month index from label
                const parts = lbl.split(" ");
                const engMonths = [
                    "January",
                    "February",
                    "March",
                    "April",
                    "May",
                    "June",
                    "July",
                    "August",
                    "September",
                    "October",
                    "November",
                    "December",
                ];
                const idMonths = [
                    "Januari",
                    "Februari",
                    "Maret",
                    "April",
                    "Mei",
                    "Juni",
                    "Juli",
                    "Agustus",
                    "September",
                    "Oktober",
                    "November",
                    "Desember",
                ];
                let mIdx = engMonths.findIndex((m) => lbl.startsWith(m));
                if (mIdx === -1)
                    mIdx = idMonths.findIndex((m) => lbl.startsWith(m));
                if (mIdx === -1) mIdx = i % 12;
                totalArr[mIdx] = data.monthly_totals?.[i] ?? 0;
                doneArr[mIdx] = data.monthly_done?.[i] ?? 0;
                rejectArr[mIdx] = data.monthly_reject?.[i] ?? 0;
            });

            this.monthlyTrendChart = new Chart(ctx, {
                type: "bar",
                data: {
                    labels: fullLabels,
                    datasets: [
                        {
                            label: "Total Tiket",
                            data: totalArr,
                            backgroundColor: (ctx) => {
                                const g = ctx.chart.ctx.createLinearGradient(
                                    0,
                                    0,
                                    0,
                                    260,
                                );
                                g.addColorStop(0, "rgba(99, 102, 241, 0.85)");
                                g.addColorStop(1, "rgba(99, 102, 241, 0.25)");
                                return g;
                            },
                            borderRadius: 5,
                            borderSkipped: false,
                            barThickness: 14,
                        },
                        {
                            label: "Selesai",
                            data: doneArr,
                            backgroundColor: "rgba(16, 185, 129, 0.75)",
                            borderRadius: 5,
                            borderSkipped: false,
                            barThickness: 14,
                        },
                        {
                            label: "Ditolak",
                            data: rejectArr,
                            backgroundColor: "rgba(239, 68, 68, 0.70)",
                            borderRadius: 5,
                            borderSkipped: false,
                            barThickness: 14,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "top",
                            labels: {
                                color: this.getTextColor(),
                                usePointStyle: true,
                                padding: 14,
                                font: { size: 11 },
                            },
                        },
                        tooltip: {
                            backgroundColor: this.isDark ? "#1e293b" : "#fff",
                            titleColor: this.isDark ? "#fff" : "#0f172a",
                            bodyColor: this.isDark ? "#cbd5e1" : "#334155",
                            borderColor: this.isDark ? "#334155" : "#e2e8f0",
                            borderWidth: 1,
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: this.getGridColor(),
                                borderDash: [5, 5],
                            },
                            ticks: {
                                color: this.getTextColor(),
                                stepSize: 1,
                                precision: 0,
                            },
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                color: this.getTextColor(),
                                font: { size: 10 },
                            },
                        },
                    },
                },
            });
        },

        // ---- NEW: Duration histogram ----
        renderDuration(data) {
            const ctx = document.getElementById("durationChart");
            if (!ctx) return;

            this.durationChart = new Chart(ctx, {
                type: "bar",
                data: {
                    labels: Object.keys(data),
                    datasets: [
                        {
                            label: "Jumlah Tiket",
                            data: Object.values(data),
                            backgroundColor: [
                                "#10b981",
                                "#3b82f6",
                                "#f59e0b",
                                "#f97316",
                                "#ef4444",
                            ],
                            borderRadius: 6,
                            borderSkipped: false,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: this.isDark ? "#1e293b" : "#fff",
                            titleColor: this.isDark ? "#fff" : "#0f172a",
                            bodyColor: this.isDark ? "#cbd5e1" : "#334155",
                            borderColor: this.isDark ? "#334155" : "#e2e8f0",
                            borderWidth: 1,
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: this.getGridColor(),
                                borderDash: [5, 5],
                            },
                            ticks: { color: this.getTextColor(), precision: 0 },
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: this.getTextColor() },
                        },
                    },
                },
            });
        },

        // ---- NEW: Priority doughnut ----
        renderPriority(data) {
            const ctx = document.getElementById("priorityChart");
            if (!ctx) return;

            const labels = Object.keys(data);
            const values = Object.values(data);

            // Map both lowercase (from PHP enum) and uppercase variants
            const colorMap = {
                high: "#ef4444",
                HIGH: "#ef4444",
                medium: "#f59e0b",
                MEDIUM: "#f59e0b",
                low: "#10b981",
                LOW: "#10b981",
            };
            const labelMap = {
                high: "High",
                HIGH: "High",
                medium: "Medium",
                MEDIUM: "Medium",
                low: "Low",
                LOW: "Low",
            };

            const bgColors = labels.map((l) => colorMap[l] ?? "#9ca3af");
            const displayLabels = labels.map((l) => labelMap[l] ?? l);

            this.priorityChart = new Chart(ctx, {
                type: "doughnut",
                data: {
                    labels: displayLabels,
                    datasets: [
                        {
                            data: values,
                            backgroundColor: bgColors,
                            borderWidth: 0,
                            hoverOffset: 8,
                        },
                    ],
                },
                options: {
                    plugins: {
                        legend: {
                            position: "bottom",
                            labels: {
                                color: this.getTextColor(),
                                usePointStyle: true,
                                pointStyle: "circle",
                                padding: 16,
                                font: { size: 11 },
                            },
                        },
                        tooltip: {
                            backgroundColor: this.isDark
                                ? "#1e293b"
                                : "#ffffff",
                            titleColor: this.isDark ? "#fff" : "#0f172a",
                            bodyColor: this.isDark ? "#cbd5e1" : "#334155",
                            borderColor: this.isDark ? "#334155" : "#e2e8f0",
                            borderWidth: 1,
                            callbacks: {
                                label(ctx) {
                                    const total =
                                        ctx.dataset.data.reduce(
                                            (a, b) => a + b,
                                            0,
                                        ) || 1;
                                    const pct = Math.round(
                                        (ctx.parsed / total) * 100,
                                    );
                                    return ` ${ctx.parsed} tiket (${pct}%)`;
                                },
                            },
                        },
                    },
                    cutout: "65%",
                },
            });
        },

        updateChartsTheme() {
            const charts = [
                this.trendChart,
                this.serviceChart,
                this.monthlyTrendChart,
                this.durationChart,
            ];
            charts.forEach((chart) => {
                if (!chart) return;
                if (chart.options.scales?.y?.grid) {
                    chart.options.scales.y.grid.color = this.getGridColor();
                }
                if (chart.options.scales?.y?.ticks) {
                    chart.options.scales.y.ticks.color = this.getTextColor();
                }
                if (chart.options.scales?.x?.ticks) {
                    chart.options.scales.x.ticks.color = this.getTextColor();
                }
                chart.update();
            });

            [this.statusChart, this.entityChart, this.priorityChart].forEach(
                (chart) => {
                    if (!chart) return;
                    if (chart.options.plugins?.legend?.labels) {
                        chart.options.plugins.legend.labels.color =
                            this.getTextColor();
                        // Regenerate labels agar warna teks generateLabels ikut update
                        if (
                            chart.options.plugins.legend.labels.generateLabels
                        ) {
                            chart.options.plugins.legend.labels.generateLabels =
                                chart.options.plugins.legend.labels.generateLabels;
                        }
                    }
                    // Update tooltip colors
                    if (chart.options.plugins?.tooltip) {
                        chart.options.plugins.tooltip.backgroundColor = this
                            .isDark
                            ? "#1e293b"
                            : "#ffffff";
                        chart.options.plugins.tooltip.titleColor = this.isDark
                            ? "#fff"
                            : "#0f172a";
                        chart.options.plugins.tooltip.bodyColor = this.isDark
                            ? "#cbd5e1"
                            : "#334155";
                        chart.options.plugins.tooltip.borderColor = this.isDark
                            ? "#334155"
                            : "#e2e8f0";
                    }
                    chart.update();
                },
            );
        },
    }),
);

Alpine.data(
    "sidebarCounter",
    (initialWaiting = 0, initialAssigned = 0, fetchUrl = "") => ({
        waitingCount: initialWaiting,
        assignedProgressCount: initialAssigned,

        init() {
            if (!fetchUrl) return;
            setInterval(() => {
                fetch(fetchUrl)
                    .then((res) => res.json())
                    .then((data) => {
                        this.waitingCount = data.waitingCount;
                        this.assignedProgressCount = data.assignedProgressCount;
                    })
                    .catch((err) =>
                        console.error("Gagal mengambil data tiket:", err),
                    );
            }, 15000);
        },
    }),
);

Alpine.data(
    "notificationManager",
    (initialCount = 0, initialNotifs = [], fetchUrl = "") => ({
        notifOpen: false,
        unreadCount: initialCount,
        notifications: initialNotifs,

        init() {
            if (!fetchUrl) return;
            setInterval(() => {
                fetch(fetchUrl, {
                    headers: {
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                })
                    .then((res) => res.json())
                    .then((data) => {
                        this.unreadCount = data.unreadCount;
                        this.notifications = data.notifications;
                    })
                    .catch((err) =>
                        console.error("Gagal mengambil data notifikasi:", err),
                    );
            }, 15000);
        },
    }),
);

Alpine.start();
