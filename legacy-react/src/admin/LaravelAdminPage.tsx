import { CROSS_BORDER_MVP } from "./reference-data";
import {
    Portfolio,
    ReviewWorkflow,
    Templates,
    ExtraPricingFields,
    ValidationPanel,
    OutputPreview,
    ReviewRecord,
    ReferenceSections,
} from "./PricingMvp";
import {
    type MarketKey,
    type OpportunityDraft,
    type OpportunityRecord,
    type OpportunityDecision,
    DEFAULT_OPPORTUNITY_DRAFT,
    DEFAULT_MARKET_BENCHMARKS,
    formatMoney,
    analyzeOpportunity,
    buildMarketRecommendation,
} from "./pricing";
import { useEffect, useMemo, useState } from "react";
import { adminApi } from "./api";
import SEOHead from "@/components/SEOHead";
import { Button } from "@/components/ui/button";

import type { Tables } from "@/integrations/supabase/types";
import {
    ADMIN_BUSINESS_ACTIONS,
    ADMIN_MENU_ITEMS,
    ADMIN_PRICING_CHECKLIST,
    BRAND,
    CONTACT_METHOD_LABELS,
    PRICING_TABLE,
    REPAIR_STATUS_LABELS,
} from "@/lib/data";
import {
    AlertTriangle,
    BarChart3,
    Calculator,
    CheckCircle2,
    ClipboardList,
    Cpu,
    Database,
    Euro,
    ExternalLink,
    Inbox,
    LogOut,
    MessageSquare,
    PackagePlus,
    Plus,
    RefreshCw,
    ShieldCheck,
    Target,
    Trash2,
    TrendingUp,
    XCircle,
} from "lucide-react";
import { toast } from "sonner";

type AdminTab = (typeof ADMIN_MENU_ITEMS)[number]["key"];
type RepairRequest = Tables<"repair_requests">;
type ContactInquiry = Tables<"contact_inquiries">;
type RepairStatus = keyof typeof REPAIR_STATUS_LABELS;
const statusGroups: Record<string, RepairStatus[]> = {
    new: ["pending", "contacted", "courier_sent"],
    active: ["received", "diagnosing", "awaiting_approval", "repairing"],
    done: ["completed", "returned"],
};

const statusTone: Record<RepairStatus, string> = {
    pending: "bg-amber-100 text-amber-800",
    contacted: "bg-blue-100 text-blue-800",
    courier_sent: "bg-cyan-100 text-cyan-800",
    received: "bg-indigo-100 text-indigo-800",
    diagnosing: "bg-violet-100 text-violet-800",
    awaiting_approval: "bg-orange-100 text-orange-800",
    repairing: "bg-sky-100 text-sky-800",
    completed: "bg-emerald-100 text-emerald-800",
    returned: "bg-slate-200 text-slate-800",
    cancelled: "bg-red-100 text-red-800",
};

const opportunityDecisionMeta: Record<
    OpportunityDecision,
    { label: string; className: string; icon: typeof CheckCircle2 }
> = {
    sell: {
        label: "Продавай",
        className: "border-emerald-200 bg-emerald-50 text-emerald-950",
        icon: CheckCircle2,
    },
    test: {
        label: "Тествай внимателно",
        className: "border-amber-200 bg-amber-50 text-amber-950",
        icon: AlertTriangle,
    },
    skip: {
        label: "Не си струва",
        className: "border-red-200 bg-red-50 text-red-950",
        icon: XCircle,
    },
};

function formatDate(value: string | null | undefined) {
    if (!value) return "няма дата";
    return new Intl.DateTimeFormat("bg-BG", {
        dateStyle: "medium",
        timeStyle: "short",
    }).format(new Date(value));
}

function countByStatus(requests: RepairRequest[], statuses: RepairStatus[]) {
    return requests.filter((request) =>
        statuses.includes((request.status || "pending") as RepairStatus),
    ).length;
}

export default function AdminPage() {
    const [activeTab, setActiveTab] = useState<AdminTab>("overview");
    const [requests, setRequests] = useState<RepairRequest[]>([]);
    const [inquiries, setInquiries] = useState<ContactInquiry[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [localPrice, setLocalPrice] = useState(0);
    const [targetMargin, setTargetMargin] = useState(20);
    const [selectedMarket, setSelectedMarket] = useState<MarketKey>(
        CROSS_BORDER_MVP.markets[0].key,
    );
    const [opportunityDraft, setOpportunityDraft] = useState<OpportunityDraft>(
        DEFAULT_OPPORTUNITY_DRAFT,
    );
    const [opportunities, setOpportunities] = useState<OpportunityRecord[]>([]);
    const [templates, setTemplates] = useState<OpportunityRecord[]>([]);
    const [statusCounts, setStatusCounts] = useState<Record<string, number>>(
        {},
    );
    const [unreadCount, setUnreadCount] = useState(0);
    const [saving, setSaving] = useState(false);
    const fetchAdminData = async () => {
        setLoading(true);
        try {
            const data = await adminApi<{
                requests: RepairRequest[];
                inquiries: ContactInquiry[];
                opportunities: OpportunityRecord[];
                templates: OpportunityRecord[];
                statusCounts: Record<string, number>;
                unreadCount: number;
            }>("data");
            setRequests(data.requests);
            setInquiries(data.inquiries);
            setOpportunities(data.opportunities);
            setTemplates(data.templates);
            setStatusCounts(data.statusCounts);
            setUnreadCount(data.unreadCount);
            setError(null);
        } catch (error) {
            setError(
                error instanceof Error ? error.message : "Неуспешно зареждане.",
            );
        } finally {
            setLoading(false);
        }
    };
    useEffect(() => {
        void fetchAdminData();
        const timer = window.setInterval(() => {
            if (!document.hidden) void fetchAdminData();
        }, 60000);
        return () => window.clearInterval(timer);
    }, []);

    const stats = useMemo(() => {
        const newRequests = statusGroups.new.reduce(
            (sum, key) => sum + (statusCounts[key] ?? 0),
            0,
        );
        const activeRequests = statusGroups.active.reduce(
            (sum, key) => sum + (statusCounts[key] ?? 0),
            0,
        );
        const completedRequests = statusGroups.done.reduce(
            (sum, key) => sum + (statusCounts[key] ?? 0),
            0,
        );
        const unreadInquiries = unreadCount;

        return [
            { label: "Нови заявки", value: newRequests, icon: Inbox },
            {
                label: "Активни ремонти",
                value: activeRequests,
                icon: ClipboardList,
            },
            {
                label: "Приключени",
                value: completedRequests,
                icon: CheckCircle2,
            },
            {
                label: "Непрочетени запитвания",
                value: unreadInquiries,
                icon: MessageSquare,
            },
        ];
    }, [statusCounts, unreadCount]);

    const selectedMarketInfo =
        CROSS_BORDER_MVP.markets.find(
            (market) => market.key === selectedMarket,
        ) ?? CROSS_BORDER_MVP.markets[0];

    const pricingPreview = useMemo(() => {
        const grossTarget = localPrice * (1 + targetMargin / 100);
        const netTarget = grossTarget / (1 + selectedMarketInfo.vatRate / 100);
        const vatAmount = grossTarget - netTarget;

        return {
            grossTarget,
            netTarget,
            vatAmount,
            localMarginAmount: grossTarget - localPrice,
        };
    }, [localPrice, selectedMarketInfo.vatRate, targetMargin]);

    const marketRecommendations = useMemo(
        () =>
            CROSS_BORDER_MVP.markets.map((market) =>
                buildMarketRecommendation(opportunityDraft, market),
            ),
        [opportunityDraft],
    );

    const selectedMarketRecommendation =
        marketRecommendations.find(
            (recommendation) =>
                recommendation.market.key === opportunityDraft.targetMarket,
        ) ?? marketRecommendations[0];
    const evaluatedDraft: OpportunityDraft = {
        ...opportunityDraft,
        targetSalePrice:
            opportunityDraft.targetSalePrice > 0
                ? opportunityDraft.targetSalePrice
                : (selectedMarketRecommendation?.recommendedPrice ?? 0),
        competitorPrice:
            selectedMarketRecommendation?.benchmarkPrice ??
            opportunityDraft.competitorPrice,
    };
    const opportunityAnalysis = analyzeOpportunity(evaluatedDraft);

    const savedOpportunityAnalyses = useMemo(
        () =>
            opportunities.map((opportunity) => ({
                opportunity,
                analysis: analyzeOpportunity(opportunity),
            })),
        [opportunities],
    );

    const opportunitySummary = useMemo(() => {
        const sell = savedOpportunityAnalyses.filter(
            (item) => item.analysis.decision === "sell",
        ).length;
        const test = savedOpportunityAnalyses.filter(
            (item) => item.analysis.decision === "test",
        ).length;
        const skip = savedOpportunityAnalyses.filter(
            (item) => item.analysis.decision === "skip",
        ).length;
        const averageProfit =
            savedOpportunityAnalyses.length > 0
                ? savedOpportunityAnalyses.reduce(
                      (sum, item) => sum + item.analysis.profit,
                      0,
                  ) / savedOpportunityAnalyses.length
                : 0;

        return {
            sell,
            test,
            skip,
            averageProfit,
            total: savedOpportunityAnalyses.length,
        };
    }, [savedOpportunityAnalyses]);

    const updateOpportunityDraft = <K extends keyof OpportunityDraft>(
        field: K,
        value: OpportunityDraft[K],
    ) => {
        setOpportunityDraft((current) => ({ ...current, [field]: value }));
    };

    const updateOpportunityMarket = (marketKey: MarketKey) => {
        setOpportunityDraft((current) => ({
            ...current,
            targetMarket: marketKey,
            competitorPrice:
                (current.marketBenchmarkPrices ?? DEFAULT_MARKET_BENCHMARKS)[
                    marketKey
                ] ?? current.competitorPrice,
        }));
    };

    const updateMarketBenchmark = (marketKey: MarketKey, value: number) => {
        setOpportunityDraft((current) => {
            const nextBenchmarks = {
                ...DEFAULT_MARKET_BENCHMARKS,
                ...current.marketBenchmarkPrices,
                [marketKey]: value,
            };

            return {
                ...current,
                marketBenchmarkPrices: nextBenchmarks,
                competitorPrice:
                    current.targetMarket === marketKey
                        ? value
                        : current.competitorPrice,
            };
        });
    };

    const saveOpportunity = async () => {
        if (!opportunityDraft.name.trim()) {
            toast.error("Добавете име на продукт или конфигурация.");
            return;
        }
        setSaving(true);
        try {
            const id = opportunityDraft.configurationId;
            const saved = await adminApi<OpportunityRecord>(
                `opportunities${id ? "/" + id : ""}`,
                id ? "PUT" : "POST",
                { ...evaluatedDraft, isTemplate: false },
            );
            setOpportunityDraft(saved);
            await fetchAdminData();
            toast.success(
                "Конфигурацията е запазена на сървъра. Може да я подготвите за Skroutz.",
            );
        } catch (error) {
            toast.error(
                error instanceof Error
                    ? error.message
                    : "Грешка при запазване.",
            );
        } finally {
            setSaving(false);
        }
    };
    const saveTemplate = async () => {
        if (!opportunityDraft.name.trim()) {
            toast.error("Добавете име на шаблона.");
            return;
        }
        try {
            await adminApi("opportunities", "POST", {
                ...evaluatedDraft,
                isTemplate: true,
            });
            await fetchAdminData();
            toast.success("Шаблонът е запазен.");
        } catch (error) {
            toast.error(
                error instanceof Error
                    ? error.message
                    : "Грешка при запазване.",
            );
        }
    };
    const reviewOpportunity = async (
        id: string,
        status: OpportunityDraft["reviewStatus"],
    ) => {
        try {
            await adminApi(`opportunities/${id}/review`, "PATCH", { status });
            await fetchAdminData();
            toast.success("Ревизионният статус е обновен.");
        } catch (error) {
            toast.error(
                error instanceof Error
                    ? error.message
                    : "Грешка при ревизията.",
            );
        }
    };
    const resetOpportunityDraft = () =>
        setOpportunityDraft(DEFAULT_OPPORTUNITY_DRAFT);
    const removeOpportunity = async (id: string) => {
        try {
            await adminApi(`opportunities/${id}`, "DELETE");
            await fetchAdminData();
            toast.success("Конфигурацията е архивирана.");
        } catch (error) {
            toast.error(
                error instanceof Error
                    ? error.message
                    : "Грешка при архивиране.",
            );
        }
    };
    const updateRequestStatus = async (id: string, status: RepairStatus) => {
        try {
            await adminApi(`requests/${id}`, "PATCH", { status });
            await fetchAdminData();
            toast.success("Статусът е обновен.");
        } catch (error) {
            toast.error(
                error instanceof Error
                    ? error.message
                    : "Грешка при обновяване.",
            );
        }
    };
    const markInquiryRead = async (id: string) => {
        try {
            await adminApi(`inquiries/${id}/read`, "PATCH");
            await fetchAdminData();
            toast.success("Запитването е прочетено.");
        } catch (error) {
            toast.error(
                error instanceof Error
                    ? error.message
                    : "Грешка при обновяване.",
            );
        }
    };

    const currentOpportunityMeta =
        opportunityDecisionMeta[opportunityAnalysis.decision];
    const CurrentOpportunityIcon = currentOpportunityMeta.icon;

    return (
        <div className="min-h-screen bg-slate-50 text-slate-950">
            <SEOHead
                title={`Админ табло | ${BRAND}`}
                description={`Админ табло за заявки, запитвания, доверие и цени на ${BRAND}.`}
            />

            <header className="border-b bg-white">
                <div className="container flex flex-col gap-4 py-5 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p className="text-sm font-semibold uppercase tracking-[0.18em] text-primary">
                            Админ меню
                        </p>
                        <h1 className="mt-1 text-2xl font-extrabold">
                            Оперативно табло
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Всичко е на български и е насочено към бърза реакция
                            по заявките.
                        </p>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        <Button
                            variant="hero-outline"
                            size="sm"
                            className="gap-2"
                            onClick={fetchAdminData}
                            disabled={loading}
                        >
                            <RefreshCw
                                className={`h-4 w-4 ${loading ? "animate-spin" : ""}`}
                            />
                            Обнови
                        </Button>
                        <a href="/">
                            <Button variant="cta" size="sm">
                                Към сайта
                            </Button>
                        </a>
                        <form action="/logout" method="POST">
                            <input
                                type="hidden"
                                name="_token"
                                value={
                                    document.querySelector<HTMLMetaElement>(
                                        'meta[name="csrf-token"]',
                                    )?.content ?? ""
                                }
                                readOnly
                            />
                            <Button
                                variant="hero-outline"
                                size="sm"
                                type="submit"
                                className="gap-2"
                            >
                                <LogOut className="h-4 w-4" />
                                Изход
                            </Button>
                        </form>
                    </div>
                </div>
            </header>

            <main className="container py-8">
                <nav className="mb-8 flex gap-2 overflow-x-auto pb-2">
                    {ADMIN_MENU_ITEMS.map((item) => (
                        <button
                            key={item.key}
                            type="button"
                            onClick={() => setActiveTab(item.key)}
                            className={`shrink-0 rounded-full border px-4 py-2 text-sm font-semibold transition ${
                                activeTab === item.key
                                    ? "border-primary bg-primary text-primary-foreground shadow-sm"
                                    : "border-slate-200 bg-white text-slate-700 hover:border-primary/40 hover:text-primary"
                            }`}
                        >
                            {item.label}
                        </button>
                    ))}
                    <a
                        href="/admin/integrations"
                        className="shrink-0 rounded-full border bg-white px-4 py-2 text-sm font-semibold text-slate-700"
                    >
                        Автоматизации
                    </a>
                    <a
                        href="/admin/skroutz"
                        className="shrink-0 rounded-full border bg-white px-4 py-2 text-sm font-semibold text-slate-700"
                    >
                        Skroutz
                    </a>
                    <a
                        href="/admin/operations"
                        className="shrink-0 rounded-full border bg-white px-4 py-2 text-sm font-semibold text-slate-700"
                    >
                        Управление
                    </a>
                </nav>

                {error && (
                    <div className="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-900">
                        <strong>Неуспешно зареждане:</strong> {error}.
                    </div>
                )}

                {activeTab === "overview" && (
                    <section className="space-y-6">
                        <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            {stats.map((stat) => (
                                <div
                                    key={stat.label}
                                    className="rounded-lg border bg-white p-5 shadow-sm"
                                >
                                    <div className="flex items-center justify-between gap-4">
                                        <p className="text-sm font-medium text-muted-foreground">
                                            {stat.label}
                                        </p>
                                        <stat.icon className="h-5 w-5 text-primary" />
                                    </div>
                                    <p className="mt-4 text-3xl font-extrabold">
                                        {stat.value}
                                    </p>
                                </div>
                            ))}
                        </div>

                        <div className="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
                            <div className="rounded-lg border bg-white p-6 shadow-sm">
                                <h2 className="text-xl font-bold">
                                    Работен поток за доверие
                                </h2>
                                <div className="mt-5 grid gap-3 sm:grid-cols-2">
                                    {Object.entries(REPAIR_STATUS_LABELS).map(
                                        ([status, label]) => (
                                            <div
                                                key={status}
                                                className="flex items-center justify-between rounded-lg border bg-slate-50 px-4 py-3"
                                            >
                                                <span className="text-sm font-medium">
                                                    {label}
                                                </span>
                                                <span
                                                    className={`rounded-full px-2.5 py-1 text-xs font-semibold ${statusTone[status as RepairStatus]}`}
                                                >
                                                    {statusCounts[status] ?? 0}
                                                </span>
                                            </div>
                                        ),
                                    )}
                                </div>
                            </div>

                            <div className="rounded-lg border bg-white p-6 shadow-sm">
                                <h2 className="text-xl font-bold">
                                    Приоритет за днес
                                </h2>
                                <div className="mt-5 space-y-4 text-sm leading-6 text-muted-foreground">
                                    <p>
                                        Първо обработвайте новите заявки и
                                        запитвания. Най-силен ефект върху
                                        конверсията има бързият първи контакт.
                                    </p>
                                    <p>
                                        След всеки приключен ремонт отбележете
                                        статуса и поискайте отзив. Това захранва
                                        доверие, локално SEO и повторни
                                        продажби.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>
                )}

                {activeTab === "requests" && (
                    <section className="rounded-lg border bg-white shadow-sm">
                        <div className="border-b p-5">
                            <h2 className="text-xl font-bold">
                                Заявки за ремонт
                            </h2>
                            <p className="mt-1 text-sm text-muted-foreground">
                                Последните 100 заявки. Статусите се записват в
                                сервизния регистър. Обновяване всяка минута.
                            </p>
                        </div>
                        <div className="divide-y">
                            {loading && (
                                <p className="p-5 text-sm text-muted-foreground">
                                    Зареждане на заявки...
                                </p>
                            )}
                            {!loading && requests.length === 0 && (
                                <p className="p-5 text-sm text-muted-foreground">
                                    Все още няма подадени заявки за ремонт.
                                </p>
                            )}
                            {requests.map((request) => {
                                const status = (request.status ||
                                    "pending") as RepairStatus;

                                return (
                                    <article
                                        key={request.id}
                                        className="grid gap-4 p-5 lg:grid-cols-[1fr_14rem]"
                                    >
                                        <div>
                                            <div className="flex flex-wrap items-center gap-2">
                                                <h3 className="font-bold">
                                                    {request.name}
                                                </h3>
                                                <span
                                                    className={`rounded-full px-2.5 py-1 text-xs font-semibold ${statusTone[status]}`}
                                                >
                                                    {
                                                        REPAIR_STATUS_LABELS[
                                                            status
                                                        ]
                                                    }
                                                </span>
                                            </div>
                                            <div className="mt-3 grid gap-2 text-sm text-muted-foreground md:grid-cols-2">
                                                <p>
                                                    <strong className="text-foreground">
                                                        Телефон:
                                                    </strong>{" "}
                                                    {request.phone}
                                                </p>
                                                <p>
                                                    <strong className="text-foreground">
                                                        Модел:
                                                    </strong>{" "}
                                                    {request.model ||
                                                        "не е посочен"}
                                                </p>
                                                <p>
                                                    <strong className="text-foreground">
                                                        Град:
                                                    </strong>{" "}
                                                    {request.city ||
                                                        "не е посочен"}
                                                </p>
                                                <p>
                                                    <strong className="text-foreground">
                                                        Контакт:
                                                    </strong>{" "}
                                                    {
                                                        CONTACT_METHOD_LABELS[
                                                            request.preferred_contact ||
                                                                "phone"
                                                        ]
                                                    }
                                                </p>
                                            </div>
                                            <p className="mt-3 text-sm leading-6 text-slate-700">
                                                {request.issue}
                                            </p>
                                            {request.admin_notes && (
                                                <p className="mt-2 text-xs text-muted-foreground">
                                                    {request.admin_notes}
                                                </p>
                                            )}
                                            <p className="mt-3 text-xs text-muted-foreground">
                                                Подадена:{" "}
                                                {formatDate(request.created_at)}
                                            </p>
                                        </div>
                                        <label className="text-sm font-semibold text-foreground">
                                            Статус
                                            <select
                                                className="input-shell mt-2"
                                                value={status}
                                                onChange={(event) =>
                                                    updateRequestStatus(
                                                        request.id,
                                                        event.target
                                                            .value as RepairStatus,
                                                    )
                                                }
                                            >
                                                {Object.entries(
                                                    REPAIR_STATUS_LABELS,
                                                ).map(([value, label]) => (
                                                    <option
                                                        key={value}
                                                        value={value}
                                                    >
                                                        {label}
                                                    </option>
                                                ))}
                                            </select>
                                        </label>
                                    </article>
                                );
                            })}
                        </div>
                    </section>
                )}

                {activeTab === "inquiries" && (
                    <section className="rounded-lg border bg-white shadow-sm">
                        <div className="border-b p-5">
                            <h2 className="text-xl font-bold">
                                Контактни запитвания
                            </h2>
                            <p className="mt-1 text-sm text-muted-foreground">
                                Последните 100 разговора от сайта, служебната
                                поща и свързаните канали. Обновяване всяка
                                минута.
                            </p>
                        </div>
                        <div className="divide-y">
                            {loading && (
                                <p className="p-5 text-sm text-muted-foreground">
                                    Зареждане на запитвания...
                                </p>
                            )}
                            {!loading && inquiries.length === 0 && (
                                <p className="p-5 text-sm text-muted-foreground">
                                    Все още няма контактни запитвания.
                                </p>
                            )}
                            {inquiries.map((inquiry) => (
                                <article
                                    key={inquiry.id}
                                    className="grid gap-4 p-5 lg:grid-cols-[1fr_auto]"
                                >
                                    <div>
                                        <div className="flex flex-wrap items-center gap-2">
                                            <h3 className="font-bold">
                                                {inquiry.name}
                                            </h3>
                                            <span
                                                className={`rounded-full px-2.5 py-1 text-xs font-semibold ${inquiry.is_read ? "bg-slate-200 text-slate-700" : "bg-amber-100 text-amber-800"}`}
                                            >
                                                {inquiry.is_read
                                                    ? "Прочетено"
                                                    : "Ново"}
                                            </span>
                                        </div>
                                        <div className="mt-3 grid gap-2 text-sm text-muted-foreground md:grid-cols-2">
                                            <p>
                                                <strong className="text-foreground">
                                                    Телефон:
                                                </strong>{" "}
                                                {inquiry.phone || "няма"}
                                            </p>
                                            <p>
                                                <strong className="text-foreground">
                                                    Имейл:
                                                </strong>{" "}
                                                {inquiry.email || "няма"}
                                            </p>
                                        </div>
                                        <p className="mt-3 text-sm leading-6 text-slate-700">
                                            {inquiry.message}
                                        </p>
                                        <a
                                            className="mt-3 inline-block text-sm font-semibold text-primary"
                                            href={`/admin/inbox/${inquiry.id}`}
                                        >
                                            Целият разговор →
                                        </a>
                                        <p className="mt-3 text-xs text-muted-foreground">
                                            Получено:{" "}
                                            {formatDate(inquiry.created_at)}
                                        </p>
                                    </div>
                                    {!inquiry.is_read && (
                                        <Button
                                            variant="hero-outline"
                                            size="sm"
                                            onClick={() =>
                                                markInquiryRead(inquiry.id)
                                            }
                                        >
                                            Маркирай като прочетено
                                        </Button>
                                    )}
                                </article>
                            ))}
                        </div>
                    </section>
                )}

                {activeTab === "business" && (
                    <section className="grid gap-6 lg:grid-cols-[1fr_0.85fr]">
                        <div className="rounded-lg border bg-white p-6 shadow-sm">
                            <div className="flex items-center gap-3">
                                <ShieldCheck className="h-6 w-6 text-primary" />
                                <h2 className="text-xl font-bold">
                                    Доверие и повече заявки
                                </h2>
                            </div>
                            <div className="mt-6 space-y-4">
                                {ADMIN_BUSINESS_ACTIONS.map((action) => (
                                    <div
                                        key={action.title}
                                        className="rounded-lg border bg-slate-50 p-4"
                                    >
                                        <h3 className="font-bold">
                                            {action.title}
                                        </h3>
                                        <p className="mt-2 text-sm leading-6 text-muted-foreground">
                                            {action.desc}
                                        </p>
                                        <p className="mt-2 text-xs font-semibold uppercase tracking-[0.16em] text-primary">
                                            {action.impact}
                                        </p>
                                    </div>
                                ))}
                            </div>
                        </div>

                        <div className="space-y-6">
                            <div className="rounded-lg border bg-white p-6 shadow-sm">
                                <h2 className="text-xl font-bold">
                                    Принципи от водещи сайтове
                                </h2>
                                <ul className="mt-4 space-y-3 text-sm leading-6 text-muted-foreground">
                                    <li>
                                        Ясна гаранция и условия, видими преди
                                        заявка.
                                    </li>
                                    <li>
                                        Лесна резервация/заявка с минимален брой
                                        полета.
                                    </li>
                                    <li>
                                        Публични отзиви, реални адреси и
                                        проверими канали за контакт.
                                    </li>
                                    <li>
                                        Статус и комуникация по време на
                                        ремонта.
                                    </li>
                                </ul>
                            </div>

                            <div className="rounded-lg border bg-primary p-6 text-primary-foreground shadow-sm">
                                <BarChart3 className="h-6 w-6" />
                                <h2 className="mt-4 text-xl font-bold">
                                    Метрика за седмицата
                                </h2>
                                <p className="mt-3 text-sm leading-6 text-primary-foreground/85">
                                    Следете колко нови заявки получават първи
                                    контакт до 15 минути. Това е по-важно от
                                    броя посещения.
                                </p>
                            </div>
                        </div>
                    </section>
                )}

                {activeTab === "pricing" && (
                    <section className="space-y-6">
                        <div className="rounded-lg border bg-white p-6 shadow-sm">
                            <div className="grid gap-6 lg:grid-cols-[1fr_23rem] lg:items-start">
                                <div>
                                    <div className="flex items-center gap-3">
                                        <Cpu className="h-6 w-6 text-primary" />
                                        <div>
                                            <p className="text-sm font-semibold uppercase tracking-[0.16em] text-primary">
                                                MVP от срещата
                                            </p>
                                            <h2 className="text-2xl font-extrabold">
                                                {CROSS_BORDER_MVP.title}
                                            </h2>
                                        </div>
                                    </div>
                                    <p className="mt-4 text-sm leading-7 text-muted-foreground">
                                        {CROSS_BORDER_MVP.problem}
                                    </p>
                                    <p className="mt-3 rounded-lg border border-primary/15 bg-primary/5 p-4 text-sm leading-7 text-slate-700">
                                        <strong>Фокус:</strong>{" "}
                                        {CROSS_BORDER_MVP.focus}
                                    </p>
                                </div>

                                <div className="rounded-lg bg-slate-950 p-5 text-white">
                                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-sky-200">
                                        Очакван output
                                    </p>
                                    <p className="mt-3 text-sm leading-7 text-slate-200">
                                        {CROSS_BORDER_MVP.sampleOutput}
                                    </p>
                                    <p className="mt-4 text-xs leading-5 text-slate-400">
                                        Първата версия показва price breakdown в
                                        админ панела; клиентският/продуктовият
                                        изглед може да се включи след валидиране
                                        на данните.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <Portfolio records={opportunities} />
                        <ReviewWorkflow />
                        <Templates
                            onLoad={setOpportunityDraft}
                            onSave={saveTemplate}
                            custom={templates}
                        />
                        <div className="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
                            <div className="rounded-lg border bg-white p-6 shadow-sm">
                                <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                    <div>
                                        <div className="flex items-center gap-3">
                                            <Target className="h-5 w-5 text-primary" />
                                            <h2 className="text-xl font-bold">
                                                Анализатор: струва ли си да го
                                                продаваме?
                                            </h2>
                                        </div>
                                        <p className="mt-2 text-sm leading-6 text-muted-foreground">
                                            Въведете директен продукт или цяла
                                            конфигурация. Инструментът смята
                                            ДДС, такси, доставка, конкурентна
                                            позиция и минимален марж, после дава
                                            ясна препоръка. Всички суми са в
                                            евро; себестойност и доставка — без
                                            възстановим ДДС, продажни цени — с
                                            ДДС. Оценките за търсене и
                                            конкуренция са ръчни допускания.
                                            Маржът тук е печалба, разделена на
                                            общите разходи.
                                        </p>
                                    </div>
                                    <div className="flex rounded-lg border bg-slate-50 p-1">
                                        <button
                                            type="button"
                                            onClick={() =>
                                                updateOpportunityDraft(
                                                    "itemType",
                                                    "configuration",
                                                )
                                            }
                                            className={`inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-semibold transition ${
                                                opportunityDraft.itemType ===
                                                "configuration"
                                                    ? "bg-primary text-primary-foreground shadow-sm"
                                                    : "text-slate-700 hover:text-primary"
                                            }`}
                                        >
                                            <Cpu className="h-4 w-4" />
                                            Конфигурация
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() =>
                                                updateOpportunityDraft(
                                                    "itemType",
                                                    "direct-product",
                                                )
                                            }
                                            className={`inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-semibold transition ${
                                                opportunityDraft.itemType ===
                                                "direct-product"
                                                    ? "bg-primary text-primary-foreground shadow-sm"
                                                    : "text-slate-700 hover:text-primary"
                                            }`}
                                        >
                                            <PackagePlus className="h-4 w-4" />
                                            Директен продукт
                                        </button>
                                    </div>
                                </div>

                                <div className="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                                    <label className="text-sm font-semibold text-foreground md:col-span-2 xl:col-span-1">
                                        Име на продукт / конфигурация
                                        <input
                                            className="input-shell mt-2"
                                            value={opportunityDraft.name}
                                            onChange={(event) =>
                                                updateOpportunityDraft(
                                                    "name",
                                                    event.target.value,
                                                )
                                            }
                                            placeholder="Напр. Gaming PC RTX 4060"
                                        />
                                    </label>
                                    <label className="text-sm font-semibold text-foreground">
                                        Целеви пазар
                                        <select
                                            className="input-shell mt-2"
                                            value={
                                                opportunityDraft.targetMarket
                                            }
                                            onChange={(event) =>
                                                updateOpportunityMarket(
                                                    event.target
                                                        .value as MarketKey,
                                                )
                                            }
                                        >
                                            {CROSS_BORDER_MVP.markets.map(
                                                (market) => (
                                                    <option
                                                        key={market.key}
                                                        value={market.key}
                                                    >
                                                        {market.label} - ДДС{" "}
                                                        {market.vatRate}%
                                                    </option>
                                                ),
                                            )}
                                        </select>
                                    </label>
                                    <label className="text-sm font-semibold text-foreground">
                                        Минимален марж %
                                        <input
                                            className="input-shell mt-2"
                                            type="number"
                                            min={0}
                                            step={1}
                                            value={
                                                opportunityDraft.minMarginPercent
                                            }
                                            onChange={(event) =>
                                                updateOpportunityDraft(
                                                    "minMarginPercent",
                                                    Number(
                                                        event.target.value,
                                                    ) || 0,
                                                )
                                            }
                                        />
                                    </label>
                                    <label className="text-sm font-semibold text-foreground">
                                        Себестойност / входна цена (€)
                                        <input
                                            className="input-shell mt-2"
                                            type="number"
                                            min={0}
                                            step={1}
                                            value={opportunityDraft.buyCost}
                                            onChange={(event) =>
                                                updateOpportunityDraft(
                                                    "buyCost",
                                                    Number(
                                                        event.target.value,
                                                    ) || 0,
                                                )
                                            }
                                        />
                                    </label>
                                    <label className="text-sm font-semibold text-foreground">
                                        Локална продажна цена (€)
                                        <input
                                            className="input-shell mt-2"
                                            type="number"
                                            min={0}
                                            step={1}
                                            value={
                                                opportunityDraft.localSalePrice
                                            }
                                            onChange={(event) =>
                                                updateOpportunityDraft(
                                                    "localSalePrice",
                                                    Number(
                                                        event.target.value,
                                                    ) || 0,
                                                )
                                            }
                                        />
                                    </label>
                                    <label className="text-sm font-semibold text-foreground">
                                        Планирана цена с ДДС (€)
                                        <input
                                            className="input-shell mt-2"
                                            type="number"
                                            min={0}
                                            step={1}
                                            value={
                                                opportunityDraft.targetSalePrice
                                            }
                                            onChange={(event) =>
                                                updateOpportunityDraft(
                                                    "targetSalePrice",
                                                    Number(
                                                        event.target.value,
                                                    ) || 0,
                                                )
                                            }
                                        />
                                    </label>
                                    <label className="text-sm font-semibold text-foreground">
                                        Benchmark конкурентна цена за избрания
                                        пазар (€)
                                        <input
                                            className="input-shell mt-2"
                                            type="number"
                                            min={0}
                                            step={1}
                                            value={
                                                opportunityDraft.competitorPrice
                                            }
                                            onChange={(event) =>
                                                updateMarketBenchmark(
                                                    opportunityDraft.targetMarket,
                                                    Number(
                                                        event.target.value,
                                                    ) || 0,
                                                )
                                            }
                                        />
                                    </label>
                                    <label className="text-sm font-semibold text-foreground">
                                        Доставка / fulfilment (€)
                                        <input
                                            className="input-shell mt-2"
                                            type="number"
                                            min={0}
                                            step={1}
                                            value={
                                                opportunityDraft.shippingCost
                                            }
                                            onChange={(event) =>
                                                updateOpportunityDraft(
                                                    "shippingCost",
                                                    Number(
                                                        event.target.value,
                                                    ) || 0,
                                                )
                                            }
                                        />
                                    </label>
                                    <label className="text-sm font-semibold text-foreground">
                                        Marketplace fee %
                                        <input
                                            className="input-shell mt-2"
                                            type="number"
                                            min={0}
                                            step={0.5}
                                            value={
                                                opportunityDraft.marketplaceFeePercent
                                            }
                                            onChange={(event) =>
                                                updateOpportunityDraft(
                                                    "marketplaceFeePercent",
                                                    Number(
                                                        event.target.value,
                                                    ) || 0,
                                                )
                                            }
                                        />
                                    </label>
                                </div>

                                <ExtraPricingFields
                                    draft={opportunityDraft}
                                    update={updateOpportunityDraft}
                                />
                                <div className="mt-5 rounded-lg border bg-slate-50 p-4">
                                    <div className="flex items-center gap-3">
                                        <Euro className="h-5 w-5 text-primary" />
                                        <h3 className="font-bold">
                                            Benchmark цени по пазари в евро
                                        </h3>
                                    </div>
                                    <p className="mt-2 text-sm leading-6 text-muted-foreground">
                                        Въведете реалната marketplace цена,
                                        която виждате в Skroutz/bestprice,
                                        Heureka/Alza и eMAG. Анализаторът ще
                                        сметне при каква цена имате добър марж и
                                        дали офертата остава конкурентна.
                                    </p>
                                    <div className="mt-4 grid gap-3 md:grid-cols-3">
                                        {CROSS_BORDER_MVP.markets.map(
                                            (market) => (
                                                <label
                                                    key={market.key}
                                                    className="text-sm font-semibold text-foreground"
                                                >
                                                    {market.label} benchmark (€)
                                                    <input
                                                        className="input-shell mt-2 bg-white"
                                                        type="number"
                                                        min={0}
                                                        step={1}
                                                        value={
                                                            (opportunityDraft.marketBenchmarkPrices ??
                                                                DEFAULT_MARKET_BENCHMARKS)[
                                                                market.key
                                                            ]
                                                        }
                                                        onChange={(event) =>
                                                            updateMarketBenchmark(
                                                                market.key,
                                                                Number(
                                                                    event.target
                                                                        .value,
                                                                ) || 0,
                                                            )
                                                        }
                                                    />
                                                </label>
                                            ),
                                        )}
                                    </div>
                                </div>

                                <div className="mt-5 grid gap-4 md:grid-cols-2">
                                    <label className="text-sm font-semibold text-foreground">
                                        Сигнал за търсене:{" "}
                                        {opportunityDraft.demandScore}/10
                                        <input
                                            className="mt-3 w-full accent-primary"
                                            type="range"
                                            min={0}
                                            max={10}
                                            step={1}
                                            value={opportunityDraft.demandScore}
                                            onChange={(event) =>
                                                updateOpportunityDraft(
                                                    "demandScore",
                                                    Number(event.target.value),
                                                )
                                            }
                                        />
                                    </label>
                                    <label className="text-sm font-semibold text-foreground">
                                        Ниво на конкуренция:{" "}
                                        {opportunityDraft.competitionScore}/10
                                        <input
                                            className="mt-3 w-full accent-primary"
                                            type="range"
                                            min={0}
                                            max={10}
                                            step={1}
                                            value={
                                                opportunityDraft.competitionScore
                                            }
                                            onChange={(event) =>
                                                updateOpportunityDraft(
                                                    "competitionScore",
                                                    Number(event.target.value),
                                                )
                                            }
                                        />
                                    </label>
                                </div>

                                <label className="mt-5 block text-sm font-semibold text-foreground">
                                    Компоненти / бележка
                                    <textarea
                                        className="input-shell mt-2 min-h-[96px] resize-y"
                                        aria-label="Компоненти / бележка"
                                        value={opportunityDraft.notes}
                                        onChange={(event) =>
                                            updateOpportunityDraft(
                                                "notes",
                                                event.target.value,
                                            )
                                        }
                                        placeholder="CPU, GPU, RAM, SSD, гаранция, наличност, канал за продажба..."
                                    />
                                </label>

                                <ValidationPanel draft={evaluatedDraft} />
                                <div className="mt-6 flex flex-wrap gap-3">
                                    <Button
                                        type="button"
                                        variant="cta"
                                        className="gap-2"
                                        onClick={saveOpportunity}
                                        disabled={saving}
                                    >
                                        <Plus className="h-4 w-4" />
                                        Запази конфигурацията
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="hero-outline"
                                        onClick={resetOpportunityDraft}
                                    >
                                        Нова конфигурация
                                    </Button>
                                </div>
                            </div>

                            <div
                                className={`rounded-lg border p-6 shadow-sm ${currentOpportunityMeta.className}`}
                            >
                                <div className="flex items-start justify-between gap-4">
                                    <div>
                                        <p className="text-sm font-semibold uppercase tracking-[0.16em] opacity-70">
                                            Изчислена препоръка
                                        </p>
                                        <h2 className="mt-2 text-3xl font-extrabold">
                                            {currentOpportunityMeta.label}
                                        </h2>
                                        <p className="mt-2 text-sm leading-6 opacity-80">
                                            Оценка {opportunityAnalysis.score}
                                            /100 за{" "}
                                            {
                                                opportunityAnalysis.market.label
                                            }{" "}
                                            при оценявана цена{" "}
                                            {formatMoney(
                                                opportunityAnalysis.grossSale,
                                            )}
                                            .
                                        </p>
                                    </div>
                                    <CurrentOpportunityIcon className="h-8 w-8 shrink-0" />
                                </div>

                                <div className="mt-6 h-3 rounded-full bg-white/70">
                                    <div
                                        className="h-3 rounded-full bg-current transition-all"
                                        style={{
                                            width: `${opportunityAnalysis.score}%`,
                                        }}
                                    />
                                </div>

                                <div className="mt-6 grid gap-3 sm:grid-cols-2">
                                    <div className="rounded-lg bg-white/70 p-4">
                                        <p className="text-xs font-semibold uppercase tracking-[0.14em] opacity-70">
                                            Продаваема цена
                                        </p>
                                        <p className="mt-2 text-2xl font-extrabold">
                                            {formatMoney(
                                                opportunityAnalysis.grossSale,
                                            )}
                                        </p>
                                    </div>
                                    <div className="rounded-lg bg-white/70 p-4">
                                        <p className="text-xs font-semibold uppercase tracking-[0.14em] opacity-70">
                                            Нетна цена
                                        </p>
                                        <p className="mt-2 text-2xl font-extrabold">
                                            {formatMoney(
                                                opportunityAnalysis.netRevenue,
                                            )}
                                        </p>
                                    </div>
                                    <div className="rounded-lg bg-white/70 p-4">
                                        <p className="text-xs font-semibold uppercase tracking-[0.14em] opacity-70">
                                            Печалба
                                        </p>
                                        <p className="mt-2 text-2xl font-extrabold">
                                            {formatMoney(
                                                opportunityAnalysis.profit,
                                            )}
                                        </p>
                                    </div>
                                    <div className="rounded-lg bg-white/70 p-4">
                                        <p className="text-xs font-semibold uppercase tracking-[0.14em] opacity-70">
                                            Марж
                                        </p>
                                        <p className="mt-2 text-2xl font-extrabold">
                                            {opportunityAnalysis.marginPercent.toFixed(
                                                1,
                                            )}
                                            %
                                        </p>
                                    </div>
                                </div>

                                <div className="mt-6 space-y-3">
                                    {opportunityAnalysis.reasons.map(
                                        (reason) => (
                                            <div
                                                key={reason}
                                                className="flex gap-3 text-sm leading-6"
                                            >
                                                <CheckCircle2 className="mt-0.5 h-4 w-4 shrink-0" />
                                                <span>{reason}</span>
                                            </div>
                                        ),
                                    )}
                                </div>
                                <OutputPreview draft={evaluatedDraft} />
                            </div>
                        </div>

                        <div className="rounded-lg border bg-white p-6 shadow-sm">
                            <div className="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <div className="flex items-center gap-3">
                                        <Euro className="h-5 w-5 text-primary" />
                                        <h2 className="text-xl font-bold">
                                            Изчислени цени по пазари в евро
                                        </h2>
                                    </div>
                                    <p className="mt-2 text-sm leading-6 text-muted-foreground">
                                        Препоръчителната цена е най-ниската цена
                                        с ДДС, която покрива себестойност,
                                        доставка, marketplace fee и минималния
                                        марж. Ако benchmark цената позволява,
                                        системата предлага цена около 2% под
                                        конкурента.
                                    </p>
                                </div>
                                <p className="rounded-lg border border-primary/20 bg-primary/5 px-4 py-3 text-sm font-semibold text-primary">
                                    Цени в EUR · ДДС: Гърция 24%, Чехия 21%,
                                    Румъния 21%
                                </p>
                            </div>

                            <div className="mt-5 grid gap-4 xl:grid-cols-3">
                                {marketRecommendations.map((recommendation) => {
                                    const meta =
                                        opportunityDecisionMeta[
                                            recommendation.analysis.decision
                                        ];
                                    const Icon = meta.icon;

                                    return (
                                        <article
                                            key={recommendation.market.key}
                                            className={`rounded-lg border p-4 ${meta.className}`}
                                        >
                                            <div className="flex items-start justify-between gap-3">
                                                <div>
                                                    <h3 className="text-lg font-extrabold">
                                                        {
                                                            recommendation
                                                                .market.label
                                                        }
                                                    </h3>
                                                    <p className="mt-1 text-xs font-semibold uppercase tracking-[0.14em] opacity-70">
                                                        ДДС{" "}
                                                        {
                                                            recommendation
                                                                .market.vatRate
                                                        }
                                                        % ·{" "}
                                                        {
                                                            recommendation
                                                                .market.currency
                                                        }
                                                    </p>
                                                </div>
                                                <Icon className="h-6 w-6 shrink-0" />
                                            </div>

                                            <div className="mt-4 rounded-lg bg-white/70 p-4">
                                                <p className="text-xs font-semibold uppercase tracking-[0.14em] opacity-70">
                                                    Продаваема цена с добра
                                                    печалба
                                                </p>
                                                <p className="mt-2 text-3xl font-extrabold">
                                                    {formatMoney(
                                                        recommendation.recommendedPrice,
                                                    )}
                                                </p>
                                                <p className="mt-1 text-sm font-semibold">
                                                    {meta.label} ·{" "}
                                                    {
                                                        recommendation.analysis
                                                            .score
                                                    }
                                                    /100
                                                </p>
                                            </div>

                                            <div className="mt-4 grid gap-2 text-sm">
                                                <p>
                                                    <strong>Benchmark:</strong>{" "}
                                                    {recommendation.benchmarkPrice >
                                                    0
                                                        ? formatMoney(
                                                              recommendation.benchmarkPrice,
                                                          )
                                                        : "няма въведена цена"}
                                                </p>
                                                <p>
                                                    <strong>
                                                        Минимум за марж:
                                                    </strong>{" "}
                                                    {Number.isFinite(
                                                        recommendation.minimumGross,
                                                    )
                                                        ? formatMoney(
                                                              recommendation.minimumGross,
                                                          )
                                                        : "невъзможно при тези такси"}
                                                </p>
                                                <p>
                                                    <strong>
                                                        Очаквана печалба:
                                                    </strong>{" "}
                                                    {formatMoney(
                                                        recommendation.analysis
                                                            .profit,
                                                    )}
                                                </p>
                                                <p>
                                                    <strong>Марж:</strong>{" "}
                                                    {recommendation.analysis.marginPercent.toFixed(
                                                        1,
                                                    )}
                                                    %
                                                </p>
                                                <p>
                                                    <strong>Позиция:</strong>{" "}
                                                    {recommendation.benchmarkPrice >
                                                    0
                                                        ? `${formatMoney(recommendation.benchmarkGap)} спрямо benchmark`
                                                        : "въведете benchmark за по-точна оценка"}
                                                </p>
                                            </div>
                                        </article>
                                    );
                                })}
                            </div>
                        </div>

                        <div className="rounded-lg border bg-white p-6 shadow-sm">
                            <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <div className="flex items-center gap-3">
                                        <TrendingUp className="h-5 w-5 text-primary" />
                                        <h2 className="text-xl font-bold">
                                            Работен списък за продукти и
                                            конфигурации
                                        </h2>
                                    </div>
                                    <p className="mt-2 text-sm leading-6 text-muted-foreground">
                                        Запазва се в базата данни. Актуалните
                                        оферти от свързания ценови feed се
                                        прилагат автоматично към съответното
                                        SKU.
                                    </p>
                                </div>
                                <div className="grid grid-cols-4 gap-2 text-center text-sm">
                                    <div className="rounded-lg border bg-slate-50 px-3 py-2">
                                        <p className="font-extrabold">
                                            {opportunitySummary.total}
                                        </p>
                                        <p className="text-xs text-muted-foreground">
                                            общо
                                        </p>
                                    </div>
                                    <div className="rounded-lg border bg-emerald-50 px-3 py-2 text-emerald-900">
                                        <p className="font-extrabold">
                                            {opportunitySummary.sell}
                                        </p>
                                        <p className="text-xs">продавай</p>
                                    </div>
                                    <div className="rounded-lg border bg-amber-50 px-3 py-2 text-amber-900">
                                        <p className="font-extrabold">
                                            {opportunitySummary.test}
                                        </p>
                                        <p className="text-xs">тествай</p>
                                    </div>
                                    <div className="rounded-lg border bg-red-50 px-3 py-2 text-red-900">
                                        <p className="font-extrabold">
                                            {opportunitySummary.skip}
                                        </p>
                                        <p className="text-xs">спри</p>
                                    </div>
                                </div>
                            </div>

                            <div className="mt-5 grid gap-4 xl:grid-cols-2">
                                {savedOpportunityAnalyses.map(
                                    ({ opportunity, analysis }) => {
                                        const meta =
                                            opportunityDecisionMeta[
                                                analysis.decision
                                            ];
                                        const Icon = meta.icon;

                                        return (
                                            <article
                                                key={opportunity.id}
                                                className="rounded-lg border bg-slate-50 p-4"
                                            >
                                                <div className="flex items-start justify-between gap-3">
                                                    <div>
                                                        <div className="flex flex-wrap items-center gap-2">
                                                            <h3 className="font-bold">
                                                                {
                                                                    opportunity.name
                                                                }
                                                            </h3>
                                                            <span className="rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-slate-700">
                                                                {opportunity.itemType ===
                                                                "configuration"
                                                                    ? "Конфигурация"
                                                                    : "Директен продукт"}
                                                            </span>
                                                        </div>
                                                        <p className="mt-1 text-xs text-muted-foreground">
                                                            {
                                                                analysis.market
                                                                    .label
                                                            }{" "}
                                                            · ДДС{" "}
                                                            {
                                                                analysis.market
                                                                    .vatRate
                                                            }
                                                            % · добавено{" "}
                                                            {formatDate(
                                                                opportunity.createdAt,
                                                            )}
                                                        </p>
                                                    </div>
                                                    <button
                                                        type="button"
                                                        className="rounded-md p-2 text-slate-500 transition hover:bg-white hover:text-red-600"
                                                        onClick={() =>
                                                            removeOpportunity(
                                                                opportunity.id,
                                                            )
                                                        }
                                                        title="Архивирай конфигурацията"
                                                        aria-label={`Архивирай ${opportunity.name}`}
                                                    >
                                                        <Trash2 className="h-4 w-4" />
                                                    </button>
                                                </div>

                                                <div
                                                    className={`mt-4 rounded-lg border p-4 ${meta.className}`}
                                                >
                                                    <div className="flex items-center justify-between gap-3">
                                                        <div className="flex items-center gap-2">
                                                            <Icon className="h-5 w-5" />
                                                            <p className="font-extrabold">
                                                                {meta.label}
                                                            </p>
                                                        </div>
                                                        <p className="text-sm font-bold">
                                                            {analysis.score}/100
                                                        </p>
                                                    </div>
                                                    <div className="mt-3 grid gap-2 text-sm sm:grid-cols-2">
                                                        <p>
                                                            <strong>
                                                                Цена с ДДС:
                                                            </strong>{" "}
                                                            {formatMoney(
                                                                analysis.grossSale,
                                                            )}
                                                        </p>
                                                        <p>
                                                            <strong>
                                                                Нетна цена:
                                                            </strong>{" "}
                                                            {formatMoney(
                                                                analysis.netRevenue,
                                                            )}
                                                        </p>
                                                        <p>
                                                            <strong>
                                                                Печалба:
                                                            </strong>{" "}
                                                            {formatMoney(
                                                                analysis.profit,
                                                            )}
                                                        </p>
                                                        <p>
                                                            <strong>
                                                                Марж:
                                                            </strong>{" "}
                                                            {analysis.marginPercent.toFixed(
                                                                1,
                                                            )}
                                                            %
                                                        </p>
                                                    </div>
                                                </div>

                                                <p className="mt-3 text-xs text-muted-foreground">
                                                    {
                                                        opportunity.benchmarkSource
                                                    }
                                                </p>
                                                <div className="mt-3 flex gap-4 text-sm font-semibold text-primary">
                                                    <button
                                                        onClick={() =>
                                                            setOpportunityDraft(
                                                                opportunity,
                                                            )
                                                        }
                                                    >
                                                        Редактирай анализа
                                                    </button>
                                                    <a
                                                        href={`/admin/skroutz/configurations/${opportunity.id}`}
                                                    >
                                                        Подготви за Skroutz →
                                                    </a>
                                                </div>
                                                {opportunity.notes && (
                                                    <p className="mt-3 text-sm leading-6 text-muted-foreground">
                                                        {opportunity.notes}
                                                    </p>
                                                )}
                                                <ReviewRecord
                                                    record={opportunity}
                                                    onReview={reviewOpportunity}
                                                />
                                            </article>
                                        );
                                    },
                                )}
                            </div>
                        </div>

                        <ReferenceSections type="tax" />
                        <div className="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
                            <div className="rounded-lg border bg-white p-6 shadow-sm">
                                <div className="flex items-center gap-3">
                                    <Calculator className="h-5 w-5 text-primary" />
                                    <h2 className="text-xl font-bold">
                                        VAT-aware калкулатор
                                    </h2>
                                </div>
                                <p className="mt-2 text-sm leading-6 text-muted-foreground">
                                    Работна версия за бърза проверка. Цените са
                                    в евро; за реална оферта използвайте
                                    анализатора по-горе с доставка, такси и
                                    benchmark цени.
                                </p>

                                <div className="mt-6 grid gap-4 md:grid-cols-3">
                                    <label className="text-sm font-semibold text-foreground">
                                        Локална цена в €
                                        <input
                                            className="input-shell mt-2"
                                            type="number"
                                            min={0}
                                            step={1}
                                            value={localPrice}
                                            onChange={(event) =>
                                                setLocalPrice(
                                                    Number(
                                                        event.target.value,
                                                    ) || 0,
                                                )
                                            }
                                        />
                                    </label>
                                    <label className="text-sm font-semibold text-foreground">
                                        Желан марж %
                                        <input
                                            className="input-shell mt-2"
                                            type="number"
                                            min={0}
                                            step={1}
                                            value={targetMargin}
                                            onChange={(event) =>
                                                setTargetMargin(
                                                    Number(
                                                        event.target.value,
                                                    ) || 0,
                                                )
                                            }
                                        />
                                    </label>
                                    <label className="text-sm font-semibold text-foreground">
                                        Целеви пазар
                                        <select
                                            className="input-shell mt-2"
                                            value={selectedMarket}
                                            onChange={(event) =>
                                                setSelectedMarket(
                                                    event.target
                                                        .value as MarketKey,
                                                )
                                            }
                                        >
                                            {CROSS_BORDER_MVP.markets.map(
                                                (market) => (
                                                    <option
                                                        key={market.key}
                                                        value={market.key}
                                                    >
                                                        {market.label} - ДДС{" "}
                                                        {market.vatRate}%
                                                    </option>
                                                ),
                                            )}
                                        </select>
                                    </label>
                                </div>

                                <div className="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                    <div className="rounded-lg border bg-slate-50 p-4">
                                        <p className="text-xs font-semibold uppercase tracking-[0.14em] text-muted-foreground">
                                            Цена с ДДС
                                        </p>
                                        <p className="mt-2 text-2xl font-extrabold">
                                            {formatMoney(
                                                pricingPreview.grossTarget,
                                            )}
                                        </p>
                                    </div>
                                    <div className="rounded-lg border bg-slate-50 p-4">
                                        <p className="text-xs font-semibold uppercase tracking-[0.14em] text-muted-foreground">
                                            Цена без ДДС
                                        </p>
                                        <p className="mt-2 text-2xl font-extrabold">
                                            {formatMoney(
                                                pricingPreview.netTarget,
                                            )}
                                        </p>
                                    </div>
                                    <div className="rounded-lg border bg-slate-50 p-4">
                                        <p className="text-xs font-semibold uppercase tracking-[0.14em] text-muted-foreground">
                                            ДДС компонент
                                        </p>
                                        <p className="mt-2 text-2xl font-extrabold">
                                            {formatMoney(
                                                pricingPreview.vatAmount,
                                            )}
                                        </p>
                                    </div>
                                    <div className="rounded-lg border bg-slate-50 p-4">
                                        <p className="text-xs font-semibold uppercase tracking-[0.14em] text-muted-foreground">
                                            Планиран марж
                                        </p>
                                        <p className="mt-2 text-2xl font-extrabold">
                                            {formatMoney(
                                                pricingPreview.localMarginAmount,
                                            )}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div className="rounded-lg border bg-white p-6 shadow-sm">
                                <h2 className="text-xl font-bold">
                                    Целеви пазари
                                </h2>
                                <div className="mt-5 space-y-4">
                                    {CROSS_BORDER_MVP.markets.map((market) => (
                                        <div
                                            key={market.key}
                                            className="rounded-lg border bg-slate-50 p-4"
                                        >
                                            <div className="flex items-start justify-between gap-3">
                                                <div>
                                                    <h3 className="font-bold">
                                                        {market.label}
                                                    </h3>
                                                    <p className="mt-1 text-sm text-muted-foreground">
                                                        {market.note}
                                                    </p>
                                                </div>
                                                <span className="rounded-full bg-primary px-3 py-1 text-sm font-bold text-primary-foreground">
                                                    {market.vatRate}% ДДС
                                                </span>
                                            </div>
                                            <a
                                                href={market.sourceUrl}
                                                target="_blank"
                                                rel="noreferrer"
                                                className="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-primary underline underline-offset-4"
                                            >
                                                Източник: {market.sourceLabel}{" "}
                                                <ExternalLink className="h-3 w-3" />
                                            </a>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>

                        <ReferenceSections type="output" />
                        <ReferenceSections type="api" />
                        <div className="grid gap-6 xl:grid-cols-2">
                            <div className="rounded-lg border bg-white p-6 shadow-sm">
                                <h2 className="text-xl font-bold">
                                    Алгоритъм за MVP
                                </h2>
                                <div className="mt-5 space-y-3">
                                    {CROSS_BORDER_MVP.algorithmFactors.map(
                                        (factor, index) => (
                                            <div
                                                key={factor}
                                                className="flex gap-3 text-sm leading-6 text-muted-foreground"
                                            >
                                                <span className="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-bold text-primary-foreground">
                                                    {index + 1}
                                                </span>
                                                <span>{factor}</span>
                                            </div>
                                        ),
                                    )}
                                </div>
                            </div>
                            <ReferenceSections type="status" />
                        </div>

                        <div className="grid gap-6 xl:grid-cols-[1fr_22rem]">
                            <div className="overflow-hidden rounded-lg border bg-white shadow-sm">
                                <div className="border-b p-5">
                                    <div className="flex items-center gap-3">
                                        <Euro className="h-5 w-5 text-primary" />
                                        <h2 className="text-xl font-bold">
                                            Публични сервизни цени
                                        </h2>
                                    </div>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        Статичен справочник от сайта;
                                        стойностите се редактират в съдържанието
                                        и не се получават от API.
                                    </p>
                                </div>
                                <div className="overflow-x-auto">
                                    <table className="w-full text-sm">
                                        <thead className="bg-slate-100 text-slate-700">
                                            <tr>
                                                <th className="px-4 py-3 text-left">
                                                    Услуга
                                                </th>
                                                <th className="px-4 py-3 text-center">
                                                    iPhone 11
                                                </th>
                                                <th className="px-4 py-3 text-center">
                                                    iPhone 12
                                                </th>
                                                <th className="px-4 py-3 text-center">
                                                    iPhone 13
                                                </th>
                                                <th className="px-4 py-3 text-center">
                                                    iPhone 14
                                                </th>
                                                <th className="px-4 py-3 text-center">
                                                    iPhone 15
                                                </th>
                                                <th className="px-4 py-3 text-center">
                                                    iPhone 16
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {PRICING_TABLE.map((row) => (
                                                <tr
                                                    key={row.service}
                                                    className="border-t"
                                                >
                                                    <td className="px-4 py-3 font-semibold">
                                                        {row.service}
                                                    </td>
                                                    <td className="px-4 py-3 text-center">
                                                        {row.iphone11}
                                                    </td>
                                                    <td className="px-4 py-3 text-center">
                                                        {row.iphone12}
                                                    </td>
                                                    <td className="px-4 py-3 text-center">
                                                        {row.iphone13}
                                                    </td>
                                                    <td className="px-4 py-3 text-center">
                                                        {row.iphone14}
                                                    </td>
                                                    <td className="px-4 py-3 text-center">
                                                        {row.iphone15}
                                                    </td>
                                                    <td className="px-4 py-3 text-center">
                                                        {row.iphone16}
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div className="rounded-lg border bg-white p-6 shadow-sm">
                                <h2 className="text-xl font-bold">
                                    Контролен списък
                                </h2>
                                <div className="mt-5 space-y-3">
                                    {ADMIN_PRICING_CHECKLIST.map((item) => (
                                        <div
                                            key={item}
                                            className="flex gap-3 text-sm leading-6 text-muted-foreground"
                                        >
                                            <CheckCircle2 className="mt-0.5 h-4 w-4 shrink-0 text-primary" />
                                            <span>{item}</span>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>
                    </section>
                )}
            </main>
        </div>
    );
}
