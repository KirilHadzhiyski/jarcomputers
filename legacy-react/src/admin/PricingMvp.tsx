import {
    BarChart3,
    CheckCircle2,
    Cpu,
    Database,
    Plus,
    ShieldCheck,
} from "lucide-react";
import { Button } from "@/components/ui/button";
import {
    ADMIN_WORKFLOW,
    AUTOMATION_RULES,
    BENCHMARK_RULES,
    PC_TEMPLATES,
    SCHEMA_FIELDS,
    TAX_RULES,
    OUTPUT_FIELDS,
    API_APPROACH,
    MVP_STATUS,
} from "./reference-data";
import {
    DEFAULT_OPPORTUNITY_DRAFT,
    REVIEW_LABELS,
    analyzeOpportunity,
    formatMoney,
    sensitivity,
    validateOpportunity,
    type OpportunityDraft,
    type OpportunityRecord,
} from "./pricing";

const card = "rounded-lg border bg-white p-6 shadow-sm";
const small = "rounded-lg border bg-slate-50 p-4";

export function Portfolio({ records }: { records: OpportunityRecord[] }) {
    const rows = records.map((draft) => ({
        draft,
        analysis: analyzeOpportunity(draft),
        validation: validateOpportunity(draft),
    }));
    const ready = rows.filter((r) => r.validation.ready).length;
    const downside = rows.some((r) =>
        sensitivity(r.draft).some(
            (s) => s.analysis.marginPercent < r.draft.minMarginPercent,
        ),
    );
    const metrics = [
        [
            "Капитал в проверки",
            formatMoney(
                rows.reduce(
                    (sum, r) => sum + r.draft.buyCost + r.draft.shippingCost,
                    0,
                ),
            ),
        ],
        [
            "Очаквана печалба",
            formatMoney(rows.reduce((sum, r) => sum + r.analysis.profit, 0)),
        ],
        [
            "Средна оценка",
            `${rows.length ? Math.round(rows.reduce((sum, r) => sum + r.analysis.score, 0) / rows.length) : 0}/100`,
        ],
        ["Готови за API", ready],
        [
            "Остарели benchmark",
            rows.filter((r) => r.validation.age > 14).length,
        ],
        [
            "Нисък confidence",
            rows.filter((r) => (r.draft.benchmarkConfidence ?? 0) < 6).length,
        ],
    ];
    return (
        <div className={card}>
            <div className="grid gap-6 lg:grid-cols-[1.05fr_0.95fr]">
                <div>
                    <h2 className="flex items-center gap-3 text-xl font-bold">
                        <BarChart3 className="h-5 w-5 text-primary" />
                        PM и финансов контрол на портфейла
                    </h2>
                    <p className="mt-3 text-sm leading-6 text-muted-foreground">
                        Това е управленският слой: колко капитал е вързан, колко
                        записи са готови за API, колко benchmark данни са
                        рискови и дали портфейлът има достатъчна очаквана
                        печалба.
                    </p>
                </div>
                <p className="self-start rounded-lg border border-primary/15 bg-primary/5 p-4 text-sm font-semibold text-primary">
                    Следващо действие:{" "}
                    {rows.length === 0
                        ? "Добавете конфигурации и потвърдени данни за разходите."
                        : downside
                          ? "Одобрете само малък тест, защото downside сценарий пада под маржа."
                          : ready
                            ? "Прегледайте готовите конфигурации в раздел Skroutz."
                            : "Завършете ревизията и проверката на benchmark данните."}
                </p>
            </div>
            <div className="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-6">
                {metrics.map(([label, value], i) => (
                    <div
                        key={label}
                        className={small.replace(
                            "bg-slate-50",
                            i === 3
                                ? "bg-emerald-50 text-emerald-900"
                                : i === 4
                                  ? "bg-amber-50 text-amber-900"
                                  : i === 5
                                    ? "bg-rose-50 text-rose-900"
                                    : "bg-slate-50",
                        )}
                    >
                        <p className="text-xs font-semibold uppercase tracking-[0.14em] text-muted-foreground">
                            {label}
                        </p>
                        <p className="mt-2 text-2xl font-extrabold">{value}</p>
                    </div>
                ))}
            </div>
        </div>
    );
}

export function ReviewWorkflow() {
    return (
        <div className={card}>
            <h2 className="flex items-center gap-3 text-xl font-bold">
                <ShieldCheck className="h-5 w-5 text-primary" />
                Admin workflow преди автоматизация
            </h2>
            <p className="mt-2 text-sm leading-6 text-muted-foreground">
                MVP започва с ръчно въвеждане и ръчна ревизия. Само записи със
                статус „Одобрено за тест“ трябва да стигат до API/feed
                автоматизация.
            </p>
            <div className="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                {ADMIN_WORKFLOW.map((step) => (
                    <div key={step.step} className={small}>
                        <span className="text-sm font-bold text-primary">
                            {step.step}
                        </span>
                        <h3 className="mt-2 font-bold">{step.title}</h3>
                        <p className="mt-2 text-xs font-semibold uppercase text-primary">
                            {step.owner}
                        </p>
                        <p className="mt-2 text-sm text-muted-foreground">
                            {step.output}
                        </p>
                    </div>
                ))}
            </div>
            <div className="mt-6 grid gap-6 lg:grid-cols-2">
                {[
                    ["Benchmark capture правила", BENCHMARK_RULES],
                    ["Automation gate", AUTOMATION_RULES],
                ].map(([title, items]) => (
                    <div key={title as string}>
                        <h3 className="font-bold">{title as string}</h3>
                        <ul className="mt-3 space-y-2">
                            {(items as readonly string[]).map((item) => (
                                <li
                                    key={item}
                                    className="flex gap-2 text-sm leading-6 text-muted-foreground"
                                >
                                    <CheckCircle2 className="mt-1 h-4 w-4 shrink-0 text-primary" />
                                    {item}
                                </li>
                            ))}
                        </ul>
                    </div>
                ))}
            </div>
        </div>
    );
}

export function Templates({
    onLoad,
    onSave,
    custom,
}: {
    onLoad: (draft: OpportunityDraft) => void;
    onSave: () => void;
    custom: OpportunityRecord[];
}) {
    const load = (t: (typeof PC_TEMPLATES)[number]) =>
        onLoad({
            ...DEFAULT_OPPORTUNITY_DRAFT,
            ...t,
            marketBenchmarkPrices: { ...t.marketBenchmarks },
            competitorPrice: t.marketBenchmarks.greece,
            notes: `CPU: ${t.cpu}; GPU: ${t.gpu}; RAM: ${t.ram}; SSD: ${t.storage}; ${t.motherboard}; ${t.psu}; ${t.caseType}. Примерни цени от шаблон — изискват актуална проверка.`,
        });
    return (
        <div className={card}>
            <div className="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 className="flex items-center gap-3 text-xl font-bold">
                        <Cpu className="h-5 w-5 text-primary" />
                        Schema за първите 10 gaming PC конфигурации
                    </h2>
                    <p className="mt-2 max-w-3xl text-sm leading-6 text-muted-foreground">
                        Стартовите 10 конфигурации от оригиналния панел.
                        Компонентите и цените са примерни; заредете, проверете с
                        реални оферти и запазете в базата преди автоматизация.
                    </p>
                </div>
                <div className="flex max-w-full flex-wrap items-center gap-3 text-sm">
                    <span>{PC_TEMPLATES.length + custom.length} templates</span>
                    <Button
                        variant="hero-outline"
                        size="sm"
                        onClick={onSave}
                        className="h-auto min-h-9 max-w-full whitespace-normal py-2"
                    >
                        <Plus className="mr-2 h-4 w-4" />
                        Запази текущата форма като template
                    </Button>
                </div>
            </div>
            <details className="mt-5">
                <summary className="cursor-pointer text-sm font-semibold text-primary">
                    Покажи schema validation полета
                </summary>
                <div className="mt-3 grid gap-3 md:grid-cols-3">
                    {SCHEMA_FIELDS.map((f) => (
                        <div key={f.field} className={small}>
                            <p className="text-xs uppercase text-primary">
                                {f.group}
                            </p>
                            <h3 className="mt-1 font-semibold">{f.label}</h3>
                            <p className="mt-2 text-xs leading-5 text-muted-foreground">
                                {f.validation}
                            </p>
                        </div>
                    ))}
                </div>
            </details>
            <div className="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                {PC_TEMPLATES.map((t, i) => (
                    <article key={t.key} className={small}>
                        <p className="text-xs font-semibold uppercase tracking-[0.14em] text-primary">
                            #{i + 1} · {t.segment}
                        </p>
                        <div className="mt-2 flex items-start justify-between gap-3">
                            <h3 className="font-bold">{t.name}</h3>
                            <Button
                                variant="hero-outline"
                                size="sm"
                                onClick={() => load(t)}
                            >
                                Зареди
                            </Button>
                        </div>
                        <dl className="mt-4 space-y-1 text-xs leading-5 text-muted-foreground">
                            {[
                                ["CPU", t.cpu],
                                ["GPU", t.gpu],
                                ["RAM", t.ram],
                                ["Storage", t.storage],
                                ["PSU", t.psu],
                                ["Use", t.targetUse],
                            ].map(([label, value]) => (
                                <div key={label}>
                                    <dt className="inline font-semibold text-foreground">
                                        {label}:{" "}
                                    </dt>
                                    <dd className="inline">{value}</dd>
                                </div>
                            ))}
                        </dl>
                        <p className="mt-3 text-xs font-semibold">
                            GR €{t.marketBenchmarks.greece} · CZ €
                            {t.marketBenchmarks.czechia} · RO €
                            {t.marketBenchmarks.romania}
                        </p>
                    </article>
                ))}
                {custom.map((t) => (
                    <article className={small} key={t.id}>
                        <p className="text-xs uppercase text-primary">
                            Собствен template
                        </p>
                        <h3 className="mt-2 font-bold">{t.name}</h3>
                        <p className="mt-3 text-sm text-muted-foreground">
                            {t.notes}
                        </p>
                        <Button
                            className="mt-3"
                            variant="hero-outline"
                            size="sm"
                            onClick={() =>
                                onLoad({
                                    ...t,
                                    configurationId: undefined,
                                    isTemplate: false,
                                    reviewStatus: "draft",
                                    taxVerified: false,
                                })
                            }
                        >
                            Зареди
                        </Button>
                    </article>
                ))}
            </div>
        </div>
    );
}

export function ExtraPricingFields({
    draft,
    update,
}: {
    draft: OpportunityDraft;
    update: <K extends keyof OpportunityDraft>(
        key: K,
        value: OpportunityDraft[K],
    ) => void;
}) {
    return (
        <div className="mt-4 grid gap-4 md:grid-cols-2">
            <label className="text-sm font-semibold">
                Fee база
                <select
                    className="input-shell mt-2"
                    value={draft.feeBasis ?? "gross"}
                    onChange={(e) =>
                        update("feeBasis", e.target.value as "gross" | "net")
                    }
                >
                    <option value="gross">Върху крайна цена с ДДС</option>
                    <option value="net">Върху нетна цена без ДДС</option>
                </select>
            </label>
            <label className="text-sm font-semibold">
                Benchmark source URL
                <input
                    className="input-shell mt-2"
                    type="url"
                    value={draft.benchmarkSourceUrl ?? ""}
                    onChange={(e) =>
                        update("benchmarkSourceUrl", e.target.value)
                    }
                />
            </label>
            <label className="text-sm font-semibold">
                Benchmark дата
                <input
                    className="input-shell mt-2"
                    type="date"
                    value={draft.benchmarkCheckedAt ?? ""}
                    onChange={(e) =>
                        update("benchmarkCheckedAt", e.target.value)
                    }
                />
            </label>
            <label className="text-sm font-semibold">
                Benchmark confidence: {draft.benchmarkConfidence ?? 0}/10
                <input
                    className="mt-4 w-full accent-primary"
                    type="range"
                    min="0"
                    max="10"
                    value={draft.benchmarkConfidence ?? 0}
                    onChange={(e) =>
                        update("benchmarkConfidence", Number(e.target.value))
                    }
                />
            </label>
            <label className="flex items-center gap-3 text-sm md:col-span-2">
                <input
                    type="checkbox"
                    checked={draft.taxVerified ?? false}
                    onChange={(e) => update("taxVerified", e.target.checked)}
                />
                Проверих ДДС режима, разходите и основата на marketplace fee за
                този продукт
            </label>
        </div>
    );
}

export function ValidationPanel({ draft }: { draft: OpportunityDraft }) {
    const result = validateOpportunity(draft);
    return (
        <div className="mt-5 rounded-lg border bg-slate-50 p-4">
            <h3 className="font-bold">Validation преди запис</h3>
            <p className="mt-2 text-xs leading-5 text-muted-foreground">
                Черновите могат да са непълни. Грешките и предупрежденията
                блокират одобряването за автоматизация.
            </p>
            <p className="mt-2 text-sm font-semibold">
                {result.errors.length} грешки · {result.warnings.length}{" "}
                предупреждения
            </p>
            {[...result.errors, ...result.warnings].map((x) => (
                <p key={x} className="mt-2 text-xs text-amber-800">
                    {x}
                </p>
            ))}
        </div>
    );
}

export function OutputPreview({ draft }: { draft: OpportunityDraft }) {
    const a = analyzeOpportunity(draft),
        v = validateOpportunity(draft),
        scenarios = sensitivity(draft);
    const output = {
        decision: a.decision,
        market: a.market.label,
        priceWithVat: a.grossSale,
        netPrice: a.netRevenue,
        vat: a.grossSale - a.netRevenue,
        fee: a.marketplaceFee,
        landedCost: a.landedCost,
        profit: a.profit,
        marginPercent: a.marginPercent,
        benchmarkUrl: draft.benchmarkSourceUrl,
        benchmarkDate: draft.benchmarkCheckedAt,
        confidence: draft.benchmarkConfidence,
        automationReady: v.ready,
    };
    return (
        <div className="mt-5 space-y-5">
            <div>
                <h3 className="text-xs font-bold uppercase tracking-[0.14em]">
                    MVP output preview
                </h3>
                <dl className="mt-3 space-y-2 text-sm">
                    {[
                        [
                            "Решение",
                            a.decision === "sell"
                                ? "Продавай"
                                : a.decision === "test"
                                  ? "Тествай внимателно"
                                  : "Не си струва",
                        ],
                        [
                            "Пазар",
                            `${a.market.label} · ДДС ${a.market.vatRate}%`,
                        ],
                        [
                            "Препоръчителна крайна цена",
                            formatMoney(a.grossSale),
                        ],
                        ["Нетна цена след ДДС", formatMoney(a.netRevenue)],
                        [
                            "ДДС компонент",
                            formatMoney(a.grossSale - a.netRevenue),
                        ],
                        [
                            "Marketplace fee",
                            `${formatMoney(a.marketplaceFee)} · ${draft.feeBasis === "net" ? "върху нетна цена" : "върху крайна цена"}`,
                        ],
                        ["Landed cost", formatMoney(a.landedCost)],
                        [
                            "Печалба / марж",
                            `${formatMoney(a.profit)} · ${a.marginPercent.toFixed(1)}%`,
                        ],
                        ["Benchmark позиция", formatMoney(a.competitorGap)],
                        [
                            "Benchmark source",
                            draft.benchmarkSourceUrl || "липсва",
                        ],
                        [
                            "Benchmark свежест",
                            `${Number.isFinite(v.age) ? v.age : "—"} дни · confidence ${draft.benchmarkConfidence ?? 0}/10`,
                        ],
                    ].map(([key, value]) => (
                        <div key={key} className="flex justify-between gap-3">
                            <dt>{key}</dt>
                            <dd className="max-w-[65%] break-all text-right font-semibold">
                                {value}
                            </dd>
                        </div>
                    ))}
                </dl>
                <details className="mt-4">
                    <summary className="cursor-pointer text-xs font-bold uppercase">
                        Покажи technical JSON output
                    </summary>
                    <pre className="mt-2 overflow-auto rounded bg-white/60 p-3 text-xs">
                        {JSON.stringify(output, null, 2)}
                    </pre>
                </details>
            </div>
            <div>
                <h3 className="text-xs font-bold uppercase tracking-[0.14em]">
                    Sensitivity анализ
                </h3>
                <p className="mt-2 text-sm font-semibold">Downside проверка</p>
                <div className="overflow-x-auto">
                    <table className="mt-3 w-full text-left text-xs">
                        <thead>
                            <tr>
                                {[
                                    "Сценарий",
                                    "Цена",
                                    "Печалба",
                                    "Марж",
                                    "Статус",
                                ].map((h) => (
                                    <th key={h} className="py-2">
                                        {h}
                                    </th>
                                ))}
                            </tr>
                        </thead>
                        <tbody>
                            {scenarios.map((s) => (
                                <tr key={s.label} className="border-t">
                                    <td className="py-2">{s.label}</td>
                                    <td>{formatMoney(s.analysis.grossSale)}</td>
                                    <td>{formatMoney(s.analysis.profit)}</td>
                                    <td>
                                        {s.analysis.marginPercent.toFixed(1)}%
                                    </td>
                                    <td>
                                        {s.analysis.profit > 0 &&
                                        s.analysis.marginPercent >=
                                            draft.minMarginPercent
                                            ? "OK"
                                            : "Риск"}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
            <div>
                <h3 className="text-xs font-bold uppercase tracking-[0.14em]">
                    Risk register
                </h3>
                {[...v.errors, ...v.warnings].length ? (
                    [...v.errors, ...v.warnings].map((r) => (
                        <p key={r} className="mt-2 text-sm">
                            {r}
                        </p>
                    ))
                ) : (
                    <p className="mt-2 text-sm">
                        Няма критичен риск в подадените данни. Проверете
                        downside сценариите преди тест.
                    </p>
                )}
            </div>
        </div>
    );
}

export function ReviewRecord({
    record,
    onReview,
}: {
    record: OpportunityRecord;
    onReview: (id: string, status: keyof typeof REVIEW_LABELS) => void;
}) {
    const v = validateOpportunity(record);
    return (
        <div className="mt-4 border-t pt-4">
            <h4 className="text-sm font-bold">Benchmark и automation gate</h4>
            <p className="mt-2 break-all text-xs">
                {record.benchmarkSourceUrl || "Липсва източник"} ·{" "}
                {v.ready ? "Готово за API" : "Остава ръчна ревизия"}
            </p>
            <p className="mt-2 text-xs text-muted-foreground">
                Дата: {record.benchmarkCheckedAt || "—"} · Confidence:{" "}
                {record.benchmarkConfidence ?? 0}/10 · Validation:{" "}
                {v.errors.length} грешки · {v.warnings.length} предупреждения
            </p>
            <p className="mt-2 text-sm font-semibold">
                {REVIEW_LABELS[record.reviewStatus ?? "draft"]}
            </p>
            <div className="mt-3 flex flex-wrap gap-2">
                {Object.entries(REVIEW_LABELS).map(([key, label]) => (
                    <button
                        key={key}
                        className={`rounded-md border px-2.5 py-2 text-xs font-semibold ${record.reviewStatus === key ? "bg-primary text-primary-foreground" : "bg-white text-slate-700"}`}
                        onClick={() =>
                            onReview(
                                record.id,
                                key as keyof typeof REVIEW_LABELS,
                            )
                        }
                    >
                        {label}
                    </button>
                ))}
            </div>
        </div>
    );
}

export function ReferenceSections({
    type,
}: {
    type: "tax" | "output" | "api" | "status";
}) {
    if (type === "tax")
        return (
            <div className={card}>
                <h2 className="text-xl font-bold">
                    Tax handling логика за Румъния и Гърция
                </h2>
                <p className="mt-2 text-sm leading-6 text-muted-foreground">
                    MVP приема всички продажни и benchmark цени като крайни
                    клиентски цени с ДДС. Печалбата се смята след нормализация
                    към нетна цена, доставка и marketplace fee.
                </p>
                <div className="mt-5 grid gap-4 lg:grid-cols-2">
                    {TAX_RULES.map((t) => (
                        <div className={small} key={t.market}>
                            <h3 className="font-bold">
                                {t.market} · {t.vatRate}% ДДС
                            </h3>
                            {Object.entries(t)
                                .filter(
                                    ([k]) =>
                                        ![
                                            "marketKey",
                                            "market",
                                            "vatRate",
                                            "sourceLabel",
                                            "sourceUrl",
                                        ].includes(k),
                                )
                                .map(([k, v]) => (
                                    <p
                                        key={k}
                                        className="mt-3 text-sm leading-6 text-muted-foreground"
                                    >
                                        {v}
                                    </p>
                                ))}
                            <a
                                className="mt-3 inline-block text-xs text-primary underline"
                                href={t.sourceUrl}
                                target="_blank"
                                rel="noreferrer"
                            >
                                Източник: {t.sourceLabel}
                            </a>
                        </div>
                    ))}
                </div>
            </div>
        );
    if (type === "output")
        return (
            <div className={card}>
                <h2 className="text-xl font-bold">
                    MVP output формат и място в UI
                </h2>
                <div className="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    {OUTPUT_FIELDS.map((f) => (
                        <div className={small} key={f.key}>
                            <h3 className="font-bold">{f.label}</h3>
                            <p className="mt-2 text-sm text-muted-foreground">
                                {f.description}
                            </p>
                            <p className="mt-3 text-xs font-semibold uppercase text-primary">
                                UI показва се в: {f.uiLocation}
                            </p>
                        </div>
                    ))}
                </div>
            </div>
        );
    if (type === "api")
        return (
            <div className={card}>
                <h2 className="text-xl font-bold">
                    Scraping/API подход за Skroutz, BestPrice и eMAG
                </h2>
                <div className="mt-5 grid gap-4 lg:grid-cols-3">
                    {API_APPROACH.map((p) => (
                        <div className={small} key={p.key}>
                            <h3 className="font-bold">{p.label}</h3>
                            <p className="mt-1 text-xs uppercase text-primary">
                                {p.market}
                            </p>
                            {[
                                ["MVP", p.mvpMethod],
                                ["Автоматизация", p.preferredAutomation],
                                ["Scraping решение", p.scrapingDecision],
                                ["UI употреба", p.uiUse],
                            ].map(([k, v]) => (
                                <p
                                    key={k}
                                    className="mt-3 text-sm leading-6 text-muted-foreground"
                                >
                                    <strong>{k}: </strong>
                                    {v}
                                </p>
                            ))}
                            <a
                                className="mt-3 inline-block text-xs text-primary underline"
                                href={p.sourceUrl}
                                target="_blank"
                                rel="noreferrer"
                            >
                                Source
                            </a>
                        </div>
                    ))}
                </div>
            </div>
        );
    return (
        <div className={card}>
            <h2 className="text-xl font-bold">Статус по петте MVP точки</h2>
            <div className="mt-5 space-y-4">
                {MVP_STATUS.map((s) => (
                    <div className={small} key={s.title}>
                        <div className="flex justify-between gap-3">
                            <h3 className="font-bold">{s.title}</h3>
                            <span className="text-xs font-semibold text-primary">
                                {s.status}
                            </span>
                        </div>
                        <p className="mt-2 text-sm text-muted-foreground">
                            {s.evidence}
                        </p>
                    </div>
                ))}
            </div>
        </div>
    );
}
