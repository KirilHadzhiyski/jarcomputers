import { afterEach, describe, expect, it, vi } from "vitest";
import {
    DEFAULT_OPPORTUNITY_DRAFT,
    analyzeOpportunity,
    calculateMinimumGrossForMargin,
    getMarketInfo,
    validateOpportunity,
} from "./pricing";

const scenario = {
    ...DEFAULT_OPPORTUNITY_DRAFT,
    name: "PC",
    buyCost: 500,
    shippingCost: 20,
    targetSalePrice: 800,
    competitorPrice: 900,
    marketBenchmarkPrices: { greece: 900, czechia: 900, romania: 900 },
    marketplaceFeePercent: 8,
    benchmarkSourceUrl: "https://example.com/pc",
    benchmarkCheckedAt: "2026-09-23",
    benchmarkConfidence: 8,
    demandScore: 7,
    competitionScore: 5,
    taxVerified: true,
    reviewStatus: "approved" as const,
};

afterEach(() => vi.useRealTimers());
describe("Cross-border financial scenarios", () => {
    it("uses the selected gross or net fee basis and removes VAT once", () => {
        const gross = analyzeOpportunity(scenario),
            net = analyzeOpportunity({ ...scenario, feeBasis: "net" });
        expect(gross.netRevenue).toBeCloseTo(645.16129, 5);
        expect(gross.marketplaceFee).toBe(64);
        expect(gross.profit).toBeCloseTo(61.16129, 5);
        expect(net.marketplaceFee).toBeCloseTo(51.6129, 5);
        expect(net.profit).toBeCloseTo(73.54839, 5);
    });
    it("solves the minimum gross price for the required margin in each market and fee basis", () => {
        for (const targetMarket of ["greece", "czechia", "romania"] as const) {
            for (const feeBasis of ["gross", "net"] as const) {
                const draft = { ...scenario, targetMarket, feeBasis };
                const targetSalePrice = calculateMinimumGrossForMargin(
                    draft,
                    getMarketInfo(targetMarket),
                );
                expect(
                    analyzeOpportunity({ ...draft, targetSalePrice })
                        .marginPercent,
                ).toBeCloseTo(12, 7);
            }
        }
    });
    it("blocks incomplete, expired, unreviewed and below-margin scenarios", () => {
        vi.useFakeTimers().setSystemTime(new Date("2026-09-23T12:00:00Z"));
        const valid = { ...scenario, targetSalePrice: 850 };
        expect(validateOpportunity(valid).ready).toBe(true);
        for (const change of [
            { benchmarkCheckedAt: "2026-09-08" },
            { benchmarkSourceUrl: "" },
            { benchmarkConfidence: 5 },
            { taxVerified: false },
            { reviewStatus: "draft" as const },
            { isTemplate: true },
            { targetSalePrice: 300 },
        ])
            expect(validateOpportunity({ ...valid, ...change }).ready).toBe(
                false,
            );
        expect(validateOpportunity(scenario).ready).toBe(false);
    });
});
