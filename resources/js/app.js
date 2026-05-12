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
                if (chartData.monthly_labels?.length)
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
                        },
                        {
                            label: "Selesai",
                            data: data.services_done ?? [],
                            backgroundColor: "#10b981",
                            borderRadius: 4,
                        },
                        {
                            label: "Ditolak",
                            data: data.services_reject ?? [],
                            backgroundColor: "#ef4444",
                            borderRadius: 4,
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
                                padding: 12,
                            },
                        },
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: {
                                color: this.getGridColor(),
                                borderDash: [5, 5],
                            },
                            ticks: { color: this.getTextColor(), stepSize: 1 },
                        },
                        y: {
                            grid: { display: false },
                            ticks: {
                                color: this.getTextColor(),
                                font: { size: 11 },
                            },
                        },
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

            this.monthlyTrendChart = new Chart(ctx, {
                type: "bar",
                data: {
                    labels: data.monthly_labels,
                    datasets: [
                        {
                            label: "Tiket per Bulan",
                            data: data.monthly_totals,
                            backgroundColor: (ctx) => {
                                const g = ctx.chart.ctx.createLinearGradient(
                                    0,
                                    0,
                                    0,
                                    300,
                                );
                                g.addColorStop(0, "rgba(99, 102, 241, 0.85)");
                                g.addColorStop(1, "rgba(99, 102, 241, 0.3)");
                                return g;
                            },
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
                            ticks: { color: this.getTextColor(), stepSize: 1 },
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: this.getTextColor() },
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
            const colors = {
                HIGH: "#ef4444",
                MEDIUM: "#f59e0b",
                LOW: "#10b981",
            };
            const bgColors = labels.map((l) => colors[l] ?? "#9ca3af");

            this.priorityChart = new Chart(ctx, {
                type: "doughnut",
                data: {
                    labels,
                    datasets: [
                        {
                            data: values,
                            backgroundColor: bgColors,
                            borderWidth: 0,
                            hoverOffset: 6,
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
                                padding: 12,
                                font: { size: 11 },
                            },
                        },
                    },
                    cutout: "68%",
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

Alpine.start();
