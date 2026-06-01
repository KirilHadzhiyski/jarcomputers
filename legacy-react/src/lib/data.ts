export const BRAND = "JAR Computers Благоевград";
export const COMPANY_NAME = "JAR Computers";
export const PHONE = "0878 369 024";
export const PHONE_E164 = "+359878369024";
export const PHONE_HREF = "359878369024";
export const LANDLINE = "073 831 212";
export const LANDLINE_HREF = "35973831212";
export const EMAIL = "office@jarbl.bg";
export const SUPPORT_EMAIL = "office@jarbl.bg";
export const ADDRESS = "bul. James Baucher 10, Blagoevgrad 2700";
export const SHORT_ADDRESS = "bul. James Baucher 10";
export const CITY_NAME = "Благоевград";
export const GOOGLE_MAPS_URL = "https://www.google.com/maps/search/?api=1&query=42.0161815,23.0954484";

export const HOURS = [
  "Понеделник – Петък: 10:00 – 18:00",
  "Събота: 10:00 – 14:00",
  "Неделя: Почивен ден",
] as const;

export const NAV_ITEMS = [
  { label: "Начало", href: "/" },
  { label: "Услуги", href: "/remont-iphone" },
  { label: "Модели", href: "/remont-iphone-16" },
  { label: "Заявка за ремонт", href: "/zaqvka_za_remont" },
  { label: "Цени", href: "/ceni" },
  { label: "За нас", href: "/za-nas" },
  { label: "ЧЗВ", href: "/chzv" },
  { label: "Контакти", href: "/kontakti" },
] as const;

export const SOCIALS = [
  { key: "facebook", label: "Facebook", href: "https://www.facebook.com/JARComputersBLG" },
  { key: "instagram", label: "Instagram", href: "https://www.instagram.com/jarcomputersblagoevgrad/" },
  { key: "tiktok", label: "TikTok", href: "https://www.tiktok.com/@jarcomputers_blagoevgrad" },
] as const;

export const MESSAGING_CHANNELS = [
  { key: "whatsapp", label: "WhatsApp", href: `https://wa.me/${PHONE_HREF}` },
  { key: "viber", label: "Viber", href: `viber://chat?number=${encodeURIComponent(PHONE_E164)}` },
  { key: "facebook-messenger", label: "Messenger", href: "https://m.me/JAR.bg" },
] as const;

export const SERVICES = [
  {
    slug: "smqna-displei-iphone",
    name: "Смяна на дисплей",
    shortName: "Дисплей",
    description: "Професионална смяна на оригинален и съвместим дисплей за всички модели iPhone.",
    priceFrom: 89,
    icon: "Smartphone",
    badge: "SD",
    keywords: "смяна дисплей iphone, смяна стъкло iphone",
  },
  {
    slug: "smqna-bateria-iphone",
    name: "Смяна на батерия",
    shortName: "Батерия",
    description: "Бърза смяна на батерия с качествени части и до 12 месеца гаранция.",
    priceFrom: 49,
    icon: "Battery",
    badge: "SB",
    keywords: "смяна батерия iphone, батерия iphone",
  },
  {
    slug: "remont-face-id-iphone",
    name: "Ремонт Face ID",
    shortName: "Face ID",
    description: "Специализиран ремонт на Face ID модула за възстановяване на функционалността.",
    priceFrom: 119,
    icon: "ScanFace",
    badge: "FI",
    keywords: "ремонт face id iphone, face id не работи",
  },
  {
    slug: "remont-kamera-iphone",
    name: "Ремонт камера",
    shortName: "Камера",
    description: "Ремонт и смяна на предна и задна камера за всички модели iPhone.",
    priceFrom: 69,
    icon: "Camera",
    badge: "RK",
    keywords: "ремонт камера iphone, смяна камера iphone",
  },
] as const;

export const MODELS = [
  { slug: "remont-iphone-16", name: "iPhone 16", series: "16", image: "/images/models/iphone-16.svg", photo: "https://fdn2.gsmarena.com/vv/bigpic/apple-iphone-16.jpg", accent: "#0ea5e9" },
  { slug: "remont-iphone-15", name: "iPhone 15", series: "15", image: "/images/models/iphone-15.svg", photo: "https://fdn2.gsmarena.com/vv/bigpic/apple-iphone-15.jpg", accent: "#ec4899" },
  { slug: "remont-iphone-14", name: "iPhone 14", series: "14", image: "/images/models/iphone-14.svg", photo: "https://fdn2.gsmarena.com/vv/bigpic/apple-iphone-14.jpg", accent: "#f97316" },
  { slug: "remont-iphone-13", name: "iPhone 13", series: "13", image: "/images/models/iphone-13.svg", photo: "https://fdn2.gsmarena.com/vv/bigpic/apple-iphone-13.jpg", accent: "#14b8a6" },
  { slug: "remont-iphone-12", name: "iPhone 12", series: "12", image: "/images/models/iphone-12.svg", photo: "https://fdn2.gsmarena.com/vv/bigpic/apple-iphone-12.jpg", accent: "#2563eb" },
  { slug: "remont-iphone-11", name: "iPhone 11", series: "11", image: "/images/models/iphone-11.svg", photo: "https://fdn2.gsmarena.com/vv/bigpic/apple-iphone-11.jpg", accent: "#7c3aed" },
] as const;

export const CITIES = [
  { slug: "remont-iphone-sofia", name: "София", nameEn: "Sofia" },
  { slug: "remont-iphone-plovdiv", name: "Пловдив", nameEn: "Plovdiv" },
  { slug: "remont-iphone-varna", name: "Варна", nameEn: "Varna" },
  { slug: "remont-iphone-burgas", name: "Бургас", nameEn: "Burgas" },
] as const;

export const STEPS = [
  { num: 1, title: "Поръчваш онлайн", desc: "Попълваш кратка форма с модел и проблем." },
  { num: 2, title: "Взимаме телефона с куриер", desc: "Куриер идва до теб безплатно в двете посоки." },
  { num: 3, title: "Диагностицираме", desc: "Проверяваме устройството и потвърждаваме цената." },
  { num: 4, title: "Ремонтираме", desc: "Извършваме ремонта в рамките на 24–48 часа." },
  { num: 5, title: "Връщаме устройството", desc: "Получаваш телефона си ремонтиран с гаранция." },
] as const;

export const TRUST_ITEMS = [
  { icon: "Star", text: "Реални отзиви", href: "/#reviews" },
  { icon: "Shield", text: "Гаранция до 12 мес.", href: "/chzv" },
  { icon: "Truck", text: "Куриер в двете посоки", href: "/chzv" },
  { icon: "Zap", text: "Експресен ремонт 24-48ч", href: "/zaqvka_za_remont" },
] as const;

export const WHY_US = [
  { icon: "Award", title: "Над 10 години опит", desc: "Доверие, изградено с хиляди успешни ремонти." },
  { icon: "CheckCircle", title: "5000+ ремонтирани устройства", desc: "Доказан опит с всички модели iPhone." },
  { icon: "Gem", title: "Качествени части", desc: "Използваме само проверени и тествани компоненти." },
  { icon: "Receipt", title: "Ясно ценообразуване", desc: "Без скрити такси – знаеш цената предварително." },
  { icon: "Image", title: "Реални снимки и ревюта", desc: "Вижте реални резултати от нашата работа." },
  { icon: "MessageCircle", title: "Бърза комуникация", desc: "Отговаряме бързо на всяко запитване." },
] as const;

export const FAQ_HOME = [
  { q: "Колко време отнема ремонтът?", a: "Повечето ремонти се извършват в рамките на 24–48 часа след получаване на устройството." },
  { q: "Трябва ли да плащам, ако не одобря ремонта?", a: "Не. Диагностиката е безплатна и плащате само ако одобрите предложената цена." },
  { q: "Какво покрива гаранцията?", a: "Гаранцията покрива дефекти в използваните части и извършената работа за срок до 12 месеца." },
  { q: "Какви части използвате?", a: "Използваме качествени съвместими и оригинални части с гаранция до 12 месеца." },
  { q: "Обслужвате ли цяла България?", a: "Да, предлагаме куриерска услуга в двете посоки за цяла България." },
  { q: "Как работи куриерската услуга?", a: "Поръчвате ремонт онлайн, изпращаме куриер до вашия адрес, ремонтираме устройството и го връщаме – всичко без да излизате от дома." },
] as const;

export const FAQ_EXTRA = [
  { q: "Какви модели iPhone ремонтирате?", a: "Ремонтираме всички модели iPhone – от iPhone 6 до най-новите модели. Специализирани сме в iPhone 11, 12, 13, 14, 15 и 16." },
  { q: "Мога ли да следя статуса на ремонта?", a: "Да, ще ви уведомяваме на всяка стъпка – от получаването на устройството до неговото изпращане обратно." },
  { q: "Имате ли физически магазин?", a: "Да, нашият сервиз се намира в Благоевград. Можете да ни посетите лично или да използвате куриерската ни услуга." },
  { q: "Колко е отстъпката при онлайн поръчка?", a: "При онлайн поръчка получавате 10% отстъпка от стойността на ремонта." },
] as const;

export const PRICING_TABLE = [
  { service: "Смяна на дисплей", iphone11: "от 89 €", iphone12: "от 109 €", iphone13: "от 129 €", iphone14: "от 149 €", iphone15: "от 169 €", iphone16: "от 189 €" },
  { service: "Смяна на батерия", iphone11: "от 49 €", iphone12: "от 55 €", iphone13: "от 59 €", iphone14: "от 65 €", iphone15: "от 69 €", iphone16: "от 75 €" },
  { service: "Ремонт Face ID", iphone11: "от 119 €", iphone12: "от 129 €", iphone13: "от 139 €", iphone14: "от 149 €", iphone15: "от 159 €", iphone16: "от 169 €" },
  { service: "Ремонт камера", iphone11: "от 69 €", iphone12: "от 79 €", iphone13: "от 89 €", iphone14: "от 99 €", iphone15: "от 109 €", iphone16: "от 119 €" },
] as const;

export const MODEL_PROBLEMS: Record<string, string[]> = {
  "11": ["Счупен дисплей", "Бърза разрядка на батерия", "Face ID спира да работи", "Проблеми с камерата", "Заглушен звук"],
  "12": ["Пукнато стъкло", "Влошена батерия", "Проблем с Face ID", "Замъглена камера", "Проблеми с Wi-Fi"],
  "13": ["Счупен OLED дисплей", "Бърз разряд", "Face ID грешки", "Камера не фокусира", "Мигащ екран"],
  "14": ["Счупен дисплей", "Батерия под 80%", "Face ID проблеми", "Камера шум", "Проблеми със зареждане"],
  "15": ["Напукан гръб", "Батерия с бърз разряд", "Dynamic Island проблеми", "Камера без автофокус", "USB-C порт проблеми"],
  "16": ["Пукнат дисплей", "Проблеми с вертикалната камера", "Батерия с кратък живот", "Face ID и сензори", "Проблеми със зареждане през USB-C"],
};

export const AGGREGATE_REVIEW = {
  label: "Обща оценка",
  ratingValue: 8.6,
  ratingScale: 10,
  reviewsCount: 147,
  sourceLabel: "Орли Електроника",
  sourceUrl: "https://www.orlielektronika.eu/profile-7626-jar-computers",
  scanDate: "2025-08-19",
} as const;

export const REVIEW_PLATFORMS = [
  {
    key: "google-maps",
    label: "Google Maps",
    ratingValue: 4.7,
    ratingScale: 5,
    reviewsCount: 62,
    primary: true,
    scanDate: "2025-08-19",
    sourceUrl: "https://www.google.com/search?q=JAR+Computers+Reviews",
  },
  {
    key: "facebook",
    label: "Facebook",
    ratingValue: 4.0,
    ratingScale: 5,
    reviewsCount: 85,
    primary: false,
    scanDate: "2025-08-19",
    sourceUrl: "https://bg-bg.facebook.com/JAR.bg/",
  },
] as const;

export const TRUST_CONVERSION_POINTS = [
  {
    title: "Ясна цена преди ремонт",
    desc: "Клиентът вижда ориентировъчни цени предварително и одобрява финалната сума след диагностика.",
  },
  {
    title: "Проследим сервизен процес",
    desc: "Заявката минава през приемане, диагностика, одобрение, ремонт и връщане с ясна комуникация.",
  },
  {
    title: "Физически адрес и реален контакт",
    desc: "Телефон, стационарен номер, имейл, адрес в Благоевград и директни канали за бърза връзка.",
  },
  {
    title: "Публични отзиви и гаранция",
    desc: "Показваме реални оценки, гаранционни условия и отговаряме ясно какво се случва при проблем.",
  },
] as const;

export const ADMIN_MENU_ITEMS = [
  { key: "overview", label: "Табло" },
  { key: "requests", label: "Заявки за ремонт" },
  { key: "inquiries", label: "Контактни запитвания" },
  { key: "business", label: "Доверие и продажби" },
  { key: "pricing", label: "AI ценообразуване" },
] as const;

export const REPAIR_STATUS_LABELS = {
  pending: "Нова заявка",
  contacted: "Свързано с клиента",
  courier_sent: "Изпратен куриер",
  received: "Устройството е получено",
  diagnosing: "В диагностика",
  awaiting_approval: "Очаква одобрение",
  repairing: "В ремонт",
  completed: "Готово",
  returned: "Върнато на клиента",
  cancelled: "Отказано",
} as const;

export const CONTACT_METHOD_LABELS = {
  phone: "Телефон",
  viber: "Viber",
  whatsapp: "WhatsApp",
  email: "Имейл",
} as const;

export const ADMIN_BUSINESS_ACTIONS = [
  {
    title: "Показвайте конкретни обещания, не общи фрази",
    desc: "Всяка страница трябва да казва срок, гаранция, цена от, какво включва услугата и какво плаща клиентът.",
    impact: "Повишава доверието преди първия контакт.",
  },
  {
    title: "Събирайте отзив след всяка върната поръчка",
    desc: "В админ процеса отбелязвайте клиентите, които са получили устройството си, и изпращайте кратка молба за Google/Facebook отзив.",
    impact: "Повече публични оценки и по-силно локално SEO.",
  },
  {
    title: "Отговаряйте бързо на нови заявки",
    desc: "Целта е първи контакт до 15 минути в работно време. Това е най-важната метрика за онлайн заявки.",
    impact: "Намалява загубените клиенти към конкуренти.",
  },
  {
    title: "Публикувайте реални снимки от сервиза",
    desc: "Добавяйте снимки на работна среда, части и готови ремонти без лични данни на клиента.",
    impact: "Премахва съмненията, че сайтът е анонимен посредник.",
  },
  {
    title: "Обяснявайте риска предварително",
    desc: "При Face ID, вода, платка и камера добавяйте ясни условия за диагностика, възможни ограничения и гаранционен обхват.",
    impact: "По-малко спорове и по-високо възприемано качество.",
  },
] as const;

export const ADMIN_PRICING_CHECKLIST = [
  "Сравнете цените за iPhone 11-16 поне веднъж месечно.",
  "Отбелязвайте кои услуги имат най-много запитвания и най-добър марж.",
  "Показвайте цена „от“, но финалната цена винаги потвърждавайте след диагностика.",
  "Добавете отделна бележка за гаранция, срок и вид части към всяка услуга.",
] as const;

export const CROSS_BORDER_MVP = {
  title: "Predictive AI Cross-Border Pricing Tool",
  status: "MVP обхватът е дефиниран",
  problem:
    "Локална продажба на продукт за около 100 лв. може да носи само около 5 лв. марж, докато същият продукт в съседен пазар може да се продава за около 120 лв. с по-добра възвръщаемост.",
  focus:
    "Първата версия е ограничена до приблизително 10 gaming PC конфигурации. Индивидуални компоненти не влизат във фаза 1.",
  sampleOutput:
    "Тази конфигурация може да се продава конкурентно в Гърция / Румъния на [цена] - ето разбивката.",
  markets: [
    {
      key: "greece",
      label: "Гърция",
      vatRate: 24,
      currency: "EUR",
      note: "Стандартна ставка за повечето стоки и услуги.",
      sourceLabel: "Министерство на икономиката и финансите на Гърция",
      sourceUrl: "https://minfin.gov.gr/en/tax-policy/tax-guide/value-added-tax-vat/",
    },
    {
      key: "czechia",
      label: "Чехия",
      vatRate: 21,
      currency: "CZK / EUR",
      note: "Стандартна ДДС ставка за повечето стоки и услуги; цените в анализа се нормализират в евро.",
      sourceLabel: "Your Europe / EU VAT rates",
      sourceUrl: "https://europa.eu/youreurope/business/taxation/vat/vat-rules-rates/index_en.htm",
    },
    {
      key: "romania",
      label: "Румъния",
      vatRate: 21,
      currency: "RON / EUR",
      note: "Стандартната ставка е обновена за 2026 г.; преди това в документа беше TBD.",
      sourceLabel: "Your Europe / EU VAT rates",
      sourceUrl: "https://europa.eu/youreurope/business/taxation/vat/vat-rules-rates/index_en.htm",
    },
  ],
  benchmarkSources: [
    { label: "bestprice.gr", market: "Гърция", note: "Ключов benchmark за гръцкия пазар", url: "https://www.bestprice.gr/" },
    { label: "Skroutz", market: "Гърция", note: "Голям продуктов агрегатор", url: "https://www.skroutz.gr/" },
    { label: "Heureka.cz", market: "Чехия", note: "Ценови агрегатор за чешкия пазар", url: "https://www.heureka.cz/" },
    { label: "Alza.cz", market: "Чехия", note: "Голям retailer за сравнение на реални продажни нива", url: "https://www.alza.cz/" },
    { label: "eMAG", market: "Румъния", note: "Marketplace за Румъния и региона", url: "https://www.emag.ro/" },
    { label: "Amazon / eBay", market: "EN пазари", note: "Опционални източници за следваща фаза", url: "https://www.amazon.com/" },
  ],
  algorithmFactors: [
    "Крайна клиентска цена на конфигурацията в евро",
    "Местна ДДС ставка за целевия пазар",
    "Конкурентни цени от marketplace източници",
    "Минимален марж за жизнеспособна оферта",
    "Сигнали за търсене, конкуренция и канал за продажба",
  ],
  configurationFields: [
    "Име на конфигурация",
    "CPU",
    "GPU",
    "RAM",
    "SSD / Storage",
    "Дънна платка",
    "Захранване",
    "Кутия",
    "Крайна цена в евро",
    "Минимален желан марж",
  ],
  nextSteps: [
    "Потвърждаване на tax handling логиката за Румъния и Гърция.",
    "Дефиниране на schema за първите 10 gaming PC конфигурации.",
    "Избор на scraping/API подход за Skroutz, bestprice.gr и eMAG.",
    "Фиксиране на MVP output формат и къде се показва в UI.",
    "Добавяне на admin workflow за ръчно въвеждане и ревизия преди автоматизация.",
  ],
} as const;

export function generateSeoPages() {
  const pages: { slug: string; service: typeof SERVICES[number]; model: typeof MODELS[number] }[] = [];

  for (const service of SERVICES) {
    for (const model of MODELS) {
      const serviceBase = service.slug.replace("-iphone", "");
      pages.push({
        slug: `${serviceBase}-iphone-${model.series}`,
        service,
        model,
      });
    }
  }

  return pages;
}
