import { CROSS_BORDER_MVP } from "./reference-data";

export type MarketKey = (typeof CROSS_BORDER_MVP.markets)[number]["key"];
export type OpportunityKind = "configuration" | "direct-product";
export type OpportunityDecision = "sell" | "test" | "skip";

export type OpportunityDraft = {
    configurationId?: number;
    benchmarkSource?: string;
    feeBasis?: "gross" | "net";
    benchmarkSourceUrl?: string;
    benchmarkCheckedAt?: string;
    benchmarkConfidence?: number;
    reviewStatus?: "draft" | "review" | "approved" | "rejected";
    taxVerified?: boolean;
    isTemplate?: boolean;
    internalSku?: string;
    condition?: "new" | "refurbished";
    warrantyMonths?: number;
    cpu?: string;
    gpu?: string;
    ram?: string;
    storage?: string;
    motherboard?: string;
    psu?: string;
    caseType?: string;
    cooling?: string;
    targetUse?: string;
    name: string;
    itemType: OpportunityKind;
    targetMarket: MarketKey;
    buyCost: number;
    localSalePrice: number;
    targetSalePrice: number;
    competitorPrice: number;
    marketBenchmarkPrices: Record<MarketKey, number>;
    shippingCost: number;
    marketplaceFeePercent: number;
    minMarginPercent: number;
    demandScore: number;
    competitionScore: number;
    notes: string;
};

export type OpportunityRecord = OpportunityDraft & {
    id: string;
    createdAt: string;
};

export const DEFAULT_MARKET_BENCHMARKS: Record<MarketKey, number> = {
    greece: 0,
    czechia: 0,
    romania: 0,
};
export const DEFAULT_OPPORTUNITY_DRAFT: OpportunityDraft = {
    feeBasis: "gross",
    benchmarkSourceUrl: "",
    benchmarkCheckedAt: "",
    benchmarkConfidence: 0,
    reviewStatus: "draft",
    taxVerified: false,
    condition: "new",
    warrantyMonths: 12,
    name: "",
    itemType: "configuration",
    targetMarket: "greece",
    buyCost: 0,
    localSalePrice: 0,
    targetSalePrice: 0,
    competitorPrice: 0,
    marketBenchmarkPrices: DEFAULT_MARKET_BENCHMARKS,
    shippingCost: 0,
    marketplaceFeePercent: 0,
    minMarginPercent: 12,
    demandScore: 0,
    competitionScore: 0,
    notes: "",
};

export function getMarketInfo(marketKey: MarketKey) {
    return (
        CROSS_BORDER_MVP.markets.find((market) => market.key === marketKey) ??
        CROSS_BORDER_MVP.markets[0]
    );
}

export function formatMoney(value: number) {
    return `€${value.toFixed(2)}`;
}

export function clampScore(value: number) {
    return Math.min(10, Math.max(0, value));
}

export function analyzeOpportunity(input: OpportunityDraft) {
    const market = getMarketInfo(input.targetMarket);
    const grossSale = Math.max(0, input.targetSalePrice);
    const netRevenue = grossSale / (1 + market.vatRate / 100);
    const marketplaceFee =
        (input.feeBasis === "net" ? netRevenue : grossSale) *
        (Math.max(0, input.marketplaceFeePercent) / 100);
    const landedCost =
        Math.max(0, input.buyCost) +
        Math.max(0, input.shippingCost) +
        marketplaceFee;
    const profit = netRevenue - landedCost;
    const marginPercent = landedCost > 0 ? (profit / landedCost) * 100 : 0;
    const localProfit =
        Math.max(0, input.localSalePrice) / 1.2 - Math.max(0, input.buyCost);
    const upliftVsLocal = profit - localProfit;
    const hasCompetitorPrice = input.competitorPrice > 0;
    const competitorGap = hasCompetitorPrice
        ? input.competitorPrice - grossSale
        : 0;
    const competitorGapPercent = hasCompetitorPrice
        ? (competitorGap / input.competitorPrice) * 100
        : 0;
    const demandScore = clampScore(input.demandScore);
    const competitionScore = clampScore(input.competitionScore);

    const marginScore =
        marginPercent >= input.minMarginPercent
            ? 30
            : marginPercent >= input.minMarginPercent - 3
              ? 14
              : 0;
    const priceScore = !hasCompetitorPrice
        ? 12
        : grossSale <= input.competitorPrice
          ? 22
          : grossSale <= input.competitorPrice * 1.05
            ? 10
            : 0;
    const score =
        input.buyCost <= 0
            ? 0
            : Math.min(
                  100,
                  Math.round(
                      (profit > 0 ? 18 : 0) +
                          marginScore +
                          priceScore +
                          demandScore * 3 +
                          (10 - competitionScore) * 2,
                  ),
              );

    let decision: OpportunityDecision = "skip";
    if (
        hasCompetitorPrice &&
        input.buyCost > 0 &&
        score >= 75 &&
        profit > 0 &&
        marginPercent >= input.minMarginPercent &&
        priceScore >= 10
    ) {
        decision = "sell";
    } else if (input.buyCost > 0 && score >= 50 && profit > 0) {
        decision = "test";
    }

    const reasons: string[] = [];
    if (profit <= 0) {
        reasons.push("Цената не покрива себестойност, доставка, такси и ДДС.");
    } else {
        reasons.push(
            `Очаквана печалба след ДДС и такси: ${formatMoney(profit)}.`,
        );
    }

    if (marginPercent < input.minMarginPercent) {
        reasons.push(
            `Маржът е ${marginPercent.toFixed(1)}%, под зададения минимум ${input.minMarginPercent}%.`,
        );
    } else {
        reasons.push(
            `Маржът е ${marginPercent.toFixed(1)}% и покрива минималната цел.`,
        );
    }

    if (hasCompetitorPrice && grossSale > input.competitorPrice * 1.05) {
        reasons.push(
            "Цената е над конкурентната с повече от 5%, затова офертата вероятно ще е трудна.",
        );
    } else if (hasCompetitorPrice) {
        reasons.push(
            `Позицията спрямо benchmark цена е ${competitorGapPercent.toFixed(1)}%.`,
        );
    } else {
        reasons.push(
            "Няма актуална конкурентна цена. Резултатът е само сценарий по въведените разходи.",
        );
    }

    if (demandScore <= 4) {
        reasons.push(
            "Сигналът за търсене е слаб; по-добре първо тествайте с малко количество.",
        );
    }

    if (competitionScore >= 8) {
        reasons.push(
            "Конкуренцията е висока; нужен е по-нисък входен разход или по-силен bundle.",
        );
    }

    return {
        market,
        grossSale,
        netRevenue,
        marketplaceFee,
        landedCost,
        profit,
        marginPercent,
        localProfit,
        upliftVsLocal,
        competitorGap,
        competitorGapPercent,
        score,
        decision,
        reasons,
    };
}

export function calculateMinimumGrossForMargin(
    input: OpportunityDraft,
    market: ReturnType<typeof getMarketInfo>,
) {
    const costBase =
        Math.max(0, input.buyCost) + Math.max(0, input.shippingCost);
    const feeRate = Math.max(0, input.marketplaceFeePercent) / 100;
    const marginTarget = Math.max(0, input.minMarginPercent) / 100;
    const vatMultiplier = 1 + market.vatRate / 100;
    const denominator =
        1 / vatMultiplier -
        (feeRate * (1 + marginTarget)) /
            (input.feeBasis === "net" ? vatMultiplier : 1);

    if (denominator <= 0) return Number.POSITIVE_INFINITY;

    return (costBase * (1 + marginTarget)) / denominator;
}

export function buildMarketRecommendation(
    input: OpportunityDraft,
    market: ReturnType<typeof getMarketInfo>,
) {
    const benchmarks = input.marketBenchmarkPrices ?? DEFAULT_MARKET_BENCHMARKS;
    const benchmarkPrice = Math.max(0, benchmarks[market.key] ?? 0);
    const minimumGross = calculateMinimumGrossForMargin(input, market);
    const benchmarkUndercut = benchmarkPrice > 0 ? benchmarkPrice * 0.98 : 0;
    const calculatedPrice =
        Number.isFinite(minimumGross) && benchmarkUndercut >= minimumGross
            ? benchmarkUndercut
            : Number.isFinite(minimumGross)
              ? minimumGross
              : 0;
    // Round upwards to cents so rounding cannot drop the price below the target margin.
    const recommendedPrice = Math.ceil(calculatedPrice * 100 - 1e-8) / 100;
    const analysis = analyzeOpportunity({
        ...input,
        targetMarket: market.key,
        targetSalePrice: recommendedPrice,
        competitorPrice: benchmarkPrice,
    });
    const benchmarkGap =
        benchmarkPrice > 0 ? benchmarkPrice - recommendedPrice : 0;

    return {
        market,
        benchmarkPrice,
        minimumGross,
        recommendedPrice,
        benchmarkGap,
        analysis,
        competitive:
            benchmarkPrice === 0 || recommendedPrice <= benchmarkPrice * 1.02,
    };
}

export const REVIEW_LABELS = {
    draft: "Чернова",
    review: "За ревизия",
    approved: "Одобрено за тест",
    rejected: "Отказано",
} as const;

export function validateOpportunity(input: OpportunityDraft) {
    const errors: string[] = [],
        warnings: string[] = [];
    if (!input.name.trim())
        errors.push("Добавете име на продукт или конфигурация.");
    if (input.buyCost <= 0) errors.push("Въведете реална себестойност.");
    if (input.targetSalePrice <= 0) errors.push("Въведете крайна цена с ДДС.");
    const parsed = Date.parse(input.benchmarkCheckedAt ?? "");
    const age = Number.isFinite(parsed)
        ? Math.floor((Date.now() - parsed) / 86400000)
        : Infinity;
    if (age < 0 || age > 14)
        warnings.push(
            "Benchmark датата липсва, е бъдеща или е по-стара от 14 дни.",
        );
    if ((input.benchmarkConfidence ?? 0) < 6)
        warnings.push("Benchmark confidence трябва да е минимум 6/10.");
    try {
        if (new URL(input.benchmarkSourceUrl ?? "").protocol !== "https:")
            throw new Error();
    } catch {
        warnings.push("Добавете HTTPS линк към източника на benchmark цената.");
    }
    if (input.competitorPrice <= 0)
        warnings.push("Липсва конкурентна цена за избрания пазар.");
    if (!input.taxVerified)
        warnings.push("Потвърдете данъчната основа и marketplace fee.");
    const analysis = analyzeOpportunity(input);
    if (
        analysis.profit <= 0 ||
        analysis.marginPercent + 0.001 < input.minMarginPercent
    )
        warnings.push("Цената трябва да покрива разходите и минималния марж.");
    if (analysis.decision === "skip")
        warnings.push(
            "Оценката е твърде ниска за автоматизация; прегледайте конкурентната цена и търсенето.",
        );
    return {
        errors,
        warnings,
        age,
        ready:
            !errors.length &&
            !warnings.length &&
            !input.isTemplate &&
            input.reviewStatus === "approved",
    };
}

export function sensitivity(input: OpportunityDraft) {
    return [
        { label: "База", draft: input },
        {
            label: "Benchmark -5%",
            draft: { ...input, targetSalePrice: input.targetSalePrice * 0.95 },
        },
        {
            label: "Себестойност +7%",
            draft: { ...input, buyCost: input.buyCost * 1.07 },
        },
        {
            label: "Fee +2 п.п.",
            draft: {
                ...input,
                marketplaceFeePercent: Math.min(
                    99,
                    input.marketplaceFeePercent + 2,
                ),
            },
        },
        {
            label: "Доставка +10 EUR",
            draft: { ...input, shippingCost: input.shippingCost + 10 },
        },
    ].map((row) => ({
        label: row.label,
        analysis: analyzeOpportunity(row.draft),
    }));
}
