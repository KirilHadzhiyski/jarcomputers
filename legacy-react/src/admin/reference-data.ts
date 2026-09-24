// Reference content from the supplied site build. Template prices are planning examples, not live offers.
export const PC_TEMPLATES = [
    {
        key: "entry-esports-rtx3050",
        name: "Entry Esports Ryzen 5 / RTX 3050",
        segment: "Esports 1080p",
        cpu: "AMD Ryzen 5 5500",
        gpu: "NVIDIA RTX 3050 6GB",
        ram: "16GB DDR4",
        storage: "1TB NVMe SSD",
        motherboard: "B550M",
        psu: "550W Bronze",
        caseType: "mATX airflow",
        cooling: "Stock / tower air",
        targetUse: "Fortnite, CS2, Valorant, League of Legends",
        buyCost: 430,
        localSalePrice: 520,
        targetSalePrice: 699,
        marketBenchmarks: {
            greece: 720,
            czechia: 700,
            romania: 710,
        },
        shippingCost: 22,
        marketplaceFeePercent: 8,
        minMarginPercent: 12,
        demandScore: 7,
        competitionScore: 6,
    },
    {
        key: "budget-rtx4060",
        name: "Budget Gaming Ryzen 5 / RTX 4060",
        segment: "Best value 1080p",
        cpu: "AMD Ryzen 5 5600",
        gpu: "NVIDIA RTX 4060 8GB",
        ram: "16GB DDR4",
        storage: "1TB NVMe SSD",
        motherboard: "B550M",
        psu: "600W Bronze",
        caseType: "mATX tempered glass",
        cooling: "Tower air",
        targetUse: "1080p high settings gaming",
        buyCost: 500,
        localSalePrice: 575,
        targetSalePrice: 862,
        marketBenchmarks: {
            greece: 880,
            czechia: 850,
            romania: 860,
        },
        shippingCost: 24,
        marketplaceFeePercent: 8,
        minMarginPercent: 12,
        demandScore: 8,
        competitionScore: 5,
    },
    {
        key: "mainstream-4060ti",
        name: "Mainstream Ryzen 7 / RTX 4060 Ti",
        segment: "1080p ultra / light 1440p",
        cpu: "AMD Ryzen 7 5700X",
        gpu: "NVIDIA RTX 4060 Ti 8GB",
        ram: "32GB DDR4",
        storage: "1TB NVMe SSD",
        motherboard: "B550",
        psu: "650W Bronze",
        caseType: "ATX airflow",
        cooling: "Tower air",
        targetUse: "1080p ultra, streaming start",
        buyCost: 650,
        localSalePrice: 760,
        targetSalePrice: 1049,
        marketBenchmarks: {
            greece: 1090,
            czechia: 1060,
            romania: 1075,
        },
        shippingCost: 26,
        marketplaceFeePercent: 8.5,
        minMarginPercent: 13,
        demandScore: 7,
        competitionScore: 6,
    },
    {
        key: "1440p-4070-super",
        name: "1440p Pro Ryzen 7 / RTX 4070 Super",
        segment: "1440p high FPS",
        cpu: "AMD Ryzen 7 7700",
        gpu: "NVIDIA RTX 4070 Super 12GB",
        ram: "32GB DDR5",
        storage: "2TB NVMe SSD",
        motherboard: "B650",
        psu: "750W Gold",
        caseType: "ATX premium airflow",
        cooling: "240mm AIO / premium air",
        targetUse: "1440p high refresh gaming",
        buyCost: 980,
        localSalePrice: 1140,
        targetSalePrice: 1590,
        marketBenchmarks: {
            greece: 1640,
            czechia: 1585,
            romania: 1620,
        },
        shippingCost: 30,
        marketplaceFeePercent: 9,
        minMarginPercent: 14,
        demandScore: 8,
        competitionScore: 5,
    },
    {
        key: "radeon-7800xt",
        name: "Radeon Value Ryzen 5 / RX 7800 XT",
        segment: "1440p value",
        cpu: "AMD Ryzen 5 7600",
        gpu: "AMD Radeon RX 7800 XT 16GB",
        ram: "32GB DDR5",
        storage: "1TB NVMe SSD",
        motherboard: "B650",
        psu: "750W Gold",
        caseType: "ATX airflow",
        cooling: "Tower air",
        targetUse: "1440p gaming with strong price/performance",
        buyCost: 870,
        localSalePrice: 1020,
        targetSalePrice: 1390,
        marketBenchmarks: {
            greece: 1440,
            czechia: 1395,
            romania: 1425,
        },
        shippingCost: 30,
        marketplaceFeePercent: 8.5,
        minMarginPercent: 14,
        demandScore: 7,
        competitionScore: 5,
    },
    {
        key: "creator-gaming-4070ti",
        name: "Creator Gaming i7 / RTX 4070 Ti Super",
        segment: "Gaming + creator",
        cpu: "Intel Core i7-14700F",
        gpu: "NVIDIA RTX 4070 Ti Super 16GB",
        ram: "32GB DDR5",
        storage: "2TB NVMe SSD",
        motherboard: "B760 / Z790",
        psu: "850W Gold",
        caseType: "ATX workstation airflow",
        cooling: "360mm AIO / high-end air",
        targetUse: "Gaming, video editing, CAD, Adobe workflow",
        buyCost: 1250,
        localSalePrice: 1460,
        targetSalePrice: 1990,
        marketBenchmarks: {
            greece: 2070,
            czechia: 1980,
            romania: 2030,
        },
        shippingCost: 34,
        marketplaceFeePercent: 9,
        minMarginPercent: 15,
        demandScore: 6,
        competitionScore: 5,
    },
    {
        key: "high-end-4080-super",
        name: "High-End Ryzen 7 / RTX 4080 Super",
        segment: "4K gaming",
        cpu: "AMD Ryzen 7 7800X3D",
        gpu: "NVIDIA RTX 4080 Super 16GB",
        ram: "32GB DDR5",
        storage: "2TB NVMe SSD",
        motherboard: "B650E",
        psu: "850W Gold",
        caseType: "Premium ATX",
        cooling: "360mm AIO",
        targetUse: "4K gaming, simulator, premium clients",
        buyCost: 1650,
        localSalePrice: 1900,
        targetSalePrice: 2590,
        marketBenchmarks: {
            greece: 2690,
            czechia: 2590,
            romania: 2650,
        },
        shippingCost: 38,
        marketplaceFeePercent: 9.5,
        minMarginPercent: 15,
        demandScore: 6,
        competitionScore: 4,
    },
    {
        key: "compact-itx-4060",
        name: "Compact ITX RTX 4060",
        segment: "Small form factor",
        cpu: "AMD Ryzen 5 7600",
        gpu: "NVIDIA RTX 4060 8GB",
        ram: "32GB DDR5",
        storage: "1TB NVMe SSD",
        motherboard: "B650 ITX",
        psu: "650W SFX Gold",
        caseType: "Mini ITX",
        cooling: "Low-profile / compact AIO",
        targetUse: "Compact desk setup, office + gaming",
        buyCost: 760,
        localSalePrice: 920,
        targetSalePrice: 1290,
        marketBenchmarks: {
            greece: 1350,
            czechia: 1280,
            romania: 1320,
        },
        shippingCost: 24,
        marketplaceFeePercent: 9,
        minMarginPercent: 14,
        demandScore: 5,
        competitionScore: 4,
    },
    {
        key: "streaming-4070",
        name: "Streaming PC Ryzen 7 / RTX 4070",
        segment: "Gaming + streaming",
        cpu: "AMD Ryzen 7 7700X",
        gpu: "NVIDIA RTX 4070 12GB",
        ram: "32GB DDR5",
        storage: "2TB NVMe SSD",
        motherboard: "B650",
        psu: "750W Gold",
        caseType: "ATX airflow RGB",
        cooling: "240mm AIO",
        targetUse: "Twitch, YouTube, 1440p gaming",
        buyCost: 1030,
        localSalePrice: 1200,
        targetSalePrice: 1690,
        marketBenchmarks: {
            greece: 1760,
            czechia: 1690,
            romania: 1725,
        },
        shippingCost: 32,
        marketplaceFeePercent: 9,
        minMarginPercent: 14,
        demandScore: 7,
        competitionScore: 5,
    },
    {
        key: "refurb-gaming-value",
        name: "Refurb Value i5 / RTX 3060 Ti",
        segment: "Refurbished value",
        cpu: "Intel Core i5-12400F",
        gpu: "NVIDIA RTX 3060 Ti 8GB refurbished",
        ram: "16GB DDR4",
        storage: "1TB NVMe SSD",
        motherboard: "B660",
        psu: "650W Bronze",
        caseType: "Refurbished ATX",
        cooling: "Tower air",
        targetUse: "Budget 1080p/1440p with warranty",
        buyCost: 390,
        localSalePrice: 510,
        targetSalePrice: 690,
        marketBenchmarks: {
            greece: 740,
            czechia: 700,
            romania: 720,
        },
        shippingCost: 24,
        marketplaceFeePercent: 8,
        minMarginPercent: 15,
        demandScore: 6,
        competitionScore: 7,
    },
] as const;

export const SCHEMA_FIELDS = [
    {
        group: "Идентичност",
        field: "internalSku",
        label: "Вътрешен SKU",
        required: true,
        validation:
            "Уникален код за конфигурацията, например JAR-GPC-4060-001.",
    },
    {
        group: "Идентичност",
        field: "condition",
        label: "Състояние",
        required: true,
        validation:
            "new, refurbished или mixed; refurbished трябва да има бележка за гаранцията.",
    },
    {
        group: "Компоненти",
        field: "cpu/gpu/ram/storage/motherboard/psu/case/cooling",
        label: "Пълна компонентна спецификация",
        required: true,
        validation:
            "Без празни ключови компоненти; GPU, RAM и storage трябва да имат капацитет/модел.",
    },
    {
        group: "Разходи",
        field: "buyCostEur",
        label: "Себестойност в EUR",
        required: true,
        validation:
            "По-голяма от 0 и включва части, сглобяване и вътрешни разходи.",
    },
    {
        group: "Разходи",
        field: "shippingCostEur",
        label: "Доставка / fulfillment в EUR",
        required: true,
        validation: "Отделно от себестойността; не може да е отрицателна.",
    },
    {
        group: "Пазар",
        field: "targetMarket",
        label: "Целеви пазар",
        required: true,
        validation:
            "greece, romania или czechia; ДДС ставката се взима от market config.",
    },
    {
        group: "Пазар",
        field: "benchmarkGrossPriceEur",
        label: "Benchmark крайна цена с ДДС",
        required: true,
        validation:
            "Въвежда се като видима клиентска цена, не като нетна B2B цена.",
    },
    {
        group: "Пазар",
        field: "benchmarkSourceUrl",
        label: "Линк към benchmark източник",
        required: true,
        validation:
            "URL към Skroutz, BestPrice, eMAG, Heureka или друг проверим източник.",
    },
    {
        group: "Пазар",
        field: "benchmarkCapturedAt",
        label: "Дата на benchmark проверка",
        required: true,
        validation: "MVP счита benchmark за свеж до 14 дни.",
    },
    {
        group: "Риск",
        field: "warrantyMonths",
        label: "Гаранция в месеци",
        required: true,
        validation:
            "Минимум 12 месеца за нови конфигурации; refurbished се ревизира отделно.",
    },
    {
        group: "Риск",
        field: "benchmarkConfidence",
        label: "Доверие в benchmark",
        required: true,
        validation:
            "0-10; под 6 блокира автоматизация и остава само ръчна ревизия.",
    },
] as const;

export const MVP_STATUS = [
    {
        title: "Tax handling за Румъния и Гърция",
        status: "Вградено",
        evidence:
            "Пазарните цени се третират като крайни B2C цени с ДДС; UI показва gross-to-net формула и източник.",
    },
    {
        title: "Schema за първите 10 gaming PC конфигурации",
        status: "Вградено",
        evidence:
            "Има 10 template-а плюс задължителни schema полета и validation правила.",
    },
    {
        title: "Scraping/API подход за Skroutz, BestPrice и eMAG",
        status: "Вградено",
        evidence:
            "MVP използва ръчно benchmark въвеждане; API/feed се допуска само след одобрен запис и официален merchant достъп.",
    },
    {
        title: "MVP output формат и място в UI",
        status: "Вградено",
        evidence:
            "AI препоръката показва човешки preview и машинно четим output обект.",
    },
    {
        title: "Admin workflow за ръчна ревизия",
        status: "Вградено",
        evidence:
            "Записите имат ревизионен статус, validation и automation gate преди API/feed.",
    },
] as const;

export const API_APPROACH = [
    {
        key: "skroutz",
        label: "Skroutz",
        market: "Гърция",
        mvpMethod:
            "Ръчно въвеждане на benchmark цена + бележка за линк/източник в admin.",
        preferredAutomation:
            "Официален merchant API токен от Skroutz Merchants Panel за inventory/price updates и Smart Cart orders.",
        scrapingDecision:
            "Не стартираме агресивен scraping. Scraping се допуска само след проверка на условията и с rate limit/кеширане.",
        uiUse: "benchmark цена за гръцкия пазар и бъдещо валидиране на наличност/цена.",
        sourceLabel: "Skroutz Developer Documentation",
        sourceUrl: "https://developer.skroutz.gr/products/api/",
    },
    {
        key: "bestprice",
        label: "BestPrice.gr",
        market: "Гърция",
        mvpMethod:
            "Ръчно benchmark сравнение и/или merchant XML feed, когато имаме собствен магазин/акаунт.",
        preferredAutomation:
            "Merchant XML/partner workflow за собствени данни; за конкурентни цени - първо писмено разрешение или легален data partner.",
        scrapingDecision:
            "Не се използва като marketplace API в MVP. BestPrice е price-comparison платформа, не обработва поръчки.",
        uiUse: "втори benchmark за Гърция и проверка на крайната цена с доставка.",
        sourceLabel: "BestPrice Terms / Merchant FAQ",
        sourceUrl: "https://www.bestprice.gr/policies/terms",
    },
    {
        key: "emag",
        label: "eMAG Marketplace",
        market: "Румъния",
        mvpMethod:
            "Ръчно benchmark въвеждане от eMAG и ревизия на такси/доставка в admin.",
        preferredAutomation:
            "Официален eMAG Marketplace API / Feed за продукти, оферти и поръчки след seller account.",
        scrapingDecision:
            "Не scraping. eMAG има официален API и условия за използване през seller платформата.",
        uiUse: "основен benchmark и бъдещ канал за листинг в Румъния.",
        sourceLabel: "eMAG Marketplace API documentation",
        sourceUrl:
            "https://marketplace.emag.ro/infocenter/emag-academy/cum-se-adauga-un-produs/importul-prin-feed/documentatie-tehnica/",
    },
] as const;

export const OUTPUT_FIELDS = [
    {
        key: "decision",
        label: "Решение",
        description: "Продавай, тествай внимателно или не си струва.",
        uiLocation: "дясната AI препоръка и работният списък",
    },
    {
        key: "recommendedGrossPrice",
        label: "Препоръчителна крайна цена",
        description: "Цена в EUR с ДДС, подходяща за клиента в избрания пазар.",
        uiLocation: "AI препоръка, market cards и запазена проверка",
    },
    {
        key: "netRevenue",
        label: "Нетна цена след ДДС",
        description: "Крайна цена, нормализирана по местната ДДС ставка.",
        uiLocation: "AI препоръка и VAT-aware калкулатор",
    },
    {
        key: "costBreakdown",
        label: "Разбивка на разходите",
        description:
            "Себестойност, доставка, marketplace fee, ДДС компонент и landed cost.",
        uiLocation: "MVP output preview и запазена проверка",
    },
    {
        key: "profitability",
        label: "Печалба и марж",
        description:
            "Очаквана печалба, марж и разлика спрямо локалната продажба.",
        uiLocation: "AI препоръка и работен списък",
    },
    {
        key: "benchmarkPosition",
        label: "Позиция спрямо benchmark",
        description:
            "Разлика спрямо Skroutz/BestPrice/eMAG benchmark и конкурентност.",
        uiLocation: "market cards и MVP output preview",
    },
    {
        key: "reviewStatus",
        label: "Ръчна ревизия",
        description: "Чернова, за ревизия, одобрено за тест или отказано.",
        uiLocation: "работният списък преди всяка автоматизация",
    },
] as const;

export const ADMIN_WORKFLOW = [
    {
        step: "01",
        title: "Ръчно въведи продукт или конфигурация",
        owner: "Админ",
        output: "Име, компоненти, себестойност, целеви пазар и бележка.",
    },
    {
        step: "02",
        title: "Въведи benchmark цени",
        owner: "Админ",
        output: "Skroutz/BestPrice за Гърция, eMAG за Румъния, плюс Чехия като контролен пазар.",
    },
    {
        step: "03",
        title: "Провери ДДС, доставка и marketplace fee",
        owner: "Админ + счетоводител при нужда",
        output: "Потвърдена крайна цена с ДДС и нетна печалба.",
    },
    {
        step: "04",
        title: "Прегледай AI препоръката",
        owner: "Админ",
        output: "Продавай, тествай внимателно или не си струва.",
    },
    {
        step: "05",
        title: "Маркирай ревизионен статус",
        owner: "Админ",
        output: "Чернова, за ревизия, одобрено за тест или отказано.",
    },
    {
        step: "06",
        title: "Автоматизирай само одобрени записи",
        owner: "Automation",
        output: "API/feed интеграция след доказана печалба и проверени правила.",
    },
] as const;

export const BENCHMARK_RULES = [
    "Въвеждай крайна клиентска цена с ДДС, а не нетна цена.",
    "Запази източник: линк към продукта или категорията, която е най-близка до конфигурацията.",
    "Запиши дата на проверката; benchmark над 14 дни се счита за остарял.",
    "Ако няма точен match, намали confidence и добави бележка защо сравнението е приблизително.",
    "Преди API/feed автоматизация, провери marketplace fee, доставка, валута и ДДС със счетоводител.",
] as const;

export const AUTOMATION_RULES = [
    "Статусът трябва да е „Одобрено за тест“.",
    "AI решението трябва да е „Продавай“ или „Тествай внимателно“.",
    "Себестойност, доставка, marketplace fee и benchmark цена трябва да са въведени.",
    "Benchmark confidence трябва да е минимум 6/10.",
    "Benchmark датата трябва да е свежа до 14 дни.",
    "При Гърция и Румъния ДДС assumption трябва да е проверен преди листинг.",
] as const;

export const TAX_RULES = [
    {
        marketKey: "greece",
        market: "Гърция",
        vatRate: 24,
        customerPriceRule:
            "Всички benchmark и продажни цени в MVP се третират като крайни клиентски цени с ДДС.",
        netFormula: "нетна цена = крайна цена / 1.24",
        profitFormula:
            "печалба = нетна цена - себестойност - доставка - marketplace fee",
        mvpDecision:
            "Потвърдено за MVP: стандартната ставка е 24%; използваме 24% за gaming PC и директни продукти, освен ако счетоводно не се докаже специален режим.",
        reviewNote:
            "Преди автоматизация трябва да се потвърдят OSS режим, фактуриране, B2C/B2B продажби и дали конкретният канал удържа такси върху брутна или нетна цена.",
        sourceLabel: "Ministry of Economy and Finance, Greece",
        sourceUrl:
            "https://minfin.gov.gr/en/tax-policy/tax-guide/value-added-tax-vat/",
    },
    {
        marketKey: "romania",
        market: "Румъния",
        vatRate: 21,
        customerPriceRule:
            "Всички eMAG/пазарни benchmark стойности се въвеждат като крайни клиентски цени с ДДС.",
        netFormula: "нетна цена = крайна цена / 1.21",
        profitFormula:
            "печалба = нетна цена - себестойност - доставка - marketplace fee",
        mvpDecision:
            "Потвърдено за MVP: стандартната ставка е 21% по актуализирания румънски Fiscal Code чрез Legea 141/2025.",
        reviewNote:
            "Преди автоматизация трябва да се провери настройката на VAT rate в eMAG Marketplace, OSS/местна регистрация и валутното обръщане RON/EUR.",
        sourceLabel: "ANAF / Legea nr. 141/2025",
        sourceUrl:
            "https://static.anaf.ro/static/10/Anaf/legislatie/L_141_2025.pdf",
    },
] as const;

export const CROSS_BORDER_MVP = {
    title: "Predictive AI Cross-Border Pricing Tool",
    status: "MVP workflow е фиксиран за ръчна ревизия преди автоматизация",
    problem:
        "Локална продажба на продукт за около 100 лв. може да носи само около 5 лв. марж, докато същият продукт в съседен пазар може да се продава за около 120 лв. с по-добра възвръщаемост.",
    focus: "Първата версия използва 10 предварително дефинирани gaming PC конфигурации плюс директни продукти за ръчна проверка. Автоматизацията започва само след одобрена ревизия в admin.",
    sampleOutput:
        "Показва решение, препоръчителна крайна цена в EUR с ДДС, нетна цена, разходи, такси, печалба, марж и benchmark позиция в admin.",
    markets: [
        {
            key: "greece",
            label: "Гърция",
            vatRate: 24,
            currency: "EUR",
            note: "Стандартна ставка за повечето стоки и услуги.",
            sourceLabel: "Министерство на икономиката и финансите на Гърция",
            sourceUrl:
                "https://minfin.gov.gr/en/tax-policy/tax-guide/value-added-tax-vat/",
        },
        {
            key: "czechia",
            label: "Чехия",
            vatRate: 21,
            currency: "CZK / EUR",
            note: "Стандартна ДДС ставка за повечето стоки и услуги; цените в анализа се нормализират в евро.",
            sourceLabel: "Your Europe / EU VAT rates",
            sourceUrl:
                "https://europa.eu/youreurope/business/finance-and-tax/vat/vat-rules-rates/index_en.htm",
        },
        {
            key: "romania",
            label: "Румъния",
            vatRate: 21,
            currency: "RON / EUR",
            note: "Стандартната ставка е обновена за 2026 г.; MVP третира eMAG benchmark цените като крайни цени с ДДС.",
            sourceLabel: "ANAF / Legea nr. 141/2025",
            sourceUrl:
                "https://static.anaf.ro/static/10/Anaf/legislatie/L_141_2025.pdf",
        },
    ],
    algorithmFactors: [
        "Крайна клиентска цена на конфигурацията в евро",
        "Местна ДДС ставка за целевия пазар",
        "Конкурентни цени от marketplace източници",
        "Минимален марж за жизнеспособна оферта",
        "Сигнали за търсене, конкуренция и канал за продажба",
    ],
} as const;
