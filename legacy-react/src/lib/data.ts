export const BRAND = "JAR Computers Благоевград";
export const PHONE = "087 836 9024";
export const EMAIL = "info@jarcomputers.bg";
export const ADDRESS = "ул. Примерна 1, Благоевград 2700";

export const SERVICES = [
  {
    slug: "smqna-displei-iphone",
    name: "Смяна на дисплей",
    shortName: "Дисплей",
    description: "Професионална смяна на оригинален и съвместим дисплей за всички модели iPhone.",
    priceFrom: 89,
    icon: "Smartphone",
    keywords: "смяна дисплей iphone, смяна стъкло iphone",
  },
  {
    slug: "smqna-bateria-iphone",
    name: "Смяна на батерия",
    shortName: "Батерия",
    description: "Бърза смяна на батерия с качествени части и до 12 месеца гаранция.",
    priceFrom: 49,
    icon: "Battery",
    keywords: "смяна батерия iphone, батерия iphone",
  },
  {
    slug: "remont-face-id-iphone",
    name: "Ремонт Face ID",
    shortName: "Face ID",
    description: "Специализиран ремонт на Face ID модула за възстановяване на функционалността.",
    priceFrom: 119,
    icon: "ScanFace",
    keywords: "ремонт face id iphone, face id не работи",
  },
  {
    slug: "remont-kamera-iphone",
    name: "Ремонт камера",
    shortName: "Камера",
    description: "Ремонт и смяна на предна и задна камера за всички модели iPhone.",
    priceFrom: 69,
    icon: "Camera",
    keywords: "ремонт камера iphone, смяна камера iphone",
  },
] as const;

export const MODELS = [
  { slug: "remont-iphone-11", name: "iPhone 11", series: "11" },
  { slug: "remont-iphone-12", name: "iPhone 12", series: "12" },
  { slug: "remont-iphone-13", name: "iPhone 13", series: "13" },
  { slug: "remont-iphone-14", name: "iPhone 14", series: "14" },
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
  { icon: "Star", text: "Реални отзиви" },
  { icon: "Shield", text: "Гаранция до 12 мес." },
  { icon: "Truck", text: "Куриер в двете посоки" },
  { icon: "Zap", text: "Експресен ремонт 24–48ч" },
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
  { q: "Как работи куриерската услуга?", a: "Поръчвате ремонт онлайн, изпращаме куриер до вашия адрес, ремонтираме устройството и го връщаме – всичко без да излизате от дома." },
  { q: "Колко време отнема ремонтът?", a: "Повечето ремонти се извършват в рамките на 24–48 часа след получаване на устройството." },
  { q: "Какви части използвате?", a: "Използваме качествени съвместими и оригинални части с гаранция до 12 месеца." },
  { q: "Трябва ли да плащам, ако не одобря ремонта?", a: "Не. Диагностиката е безплатна и плащате само ако одобрите предложената цена." },
  { q: "Обслужвате ли цяла България?", a: "Да, предлагаме куриерска услуга в двете посоки за цяла България." },
  { q: "Какво покрива гаранцията?", a: "Гаранцията покрива дефекти в използваните части и извършената работа за срок до 12 месеца." },
] as const;

// Generate combined SEO slugs
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

export const PRICING_TABLE = [
  { service: "Смяна на дисплей", iphone11: "от 89 лв", iphone12: "от 109 лв", iphone13: "от 129 лв", iphone14: "от 149 лв" },
  { service: "Смяна на батерия", iphone11: "от 49 лв", iphone12: "от 55 лв", iphone13: "от 59 лв", iphone14: "от 65 лв" },
  { service: "Ремонт Face ID", iphone11: "от 119 лв", iphone12: "от 129 лв", iphone13: "от 139 лв", iphone14: "от 149 лв" },
  { service: "Ремонт камера", iphone11: "от 69 лв", iphone12: "от 79 лв", iphone13: "от 89 лв", iphone14: "от 99 лв" },
] as const;
