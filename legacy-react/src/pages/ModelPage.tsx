import { useLocation, Link } from "react-router-dom";
import Layout from "@/components/Layout";
import SEOHead from "@/components/SEOHead";
import TrustBar from "@/components/TrustBar";
import FAQSection from "@/components/FAQSection";
import { CTASection } from "@/components/CTA";
import { Button } from "@/components/ui/button";
import { BRAND, MODELS, SERVICES, CITIES, STEPS, PRICING_TABLE, MODEL_PROBLEMS, PHONE_HREF } from "@/lib/data";
import { ChevronLeft, ChevronRight, Phone } from "lucide-react";

export default function ModelPage() {
  const { pathname } = useLocation();
  const slug = pathname.slice(1);
  const model = MODELS.find((m) => m.slug === slug);

  if (!model) return null;

  const problems = MODEL_PROBLEMS[model.series] || MODEL_PROBLEMS["11"];
  const pricing = PRICING_TABLE.map((row) => ({
    service: row.service,
    price: row[`iphone${model.series}` as keyof typeof row],
  }));

  const faq = [
    {
      q: `Колко струва ремонт на ${model.name}?`,
      a: `Цените за ремонт на ${model.name} започват от 49 € за смяна на батерия. Окончателната цена зависи от вида на ремонта.`,
    },
    { q: `Колко време отнема ремонт на ${model.name}?`, a: "Повечето ремонти се извършват в рамките на 24-48 часа." },
    { q: "Предлагате ли куриер?", a: "Да, изпращаме куриер до вашия адрес безплатно в двете посоки." },
    { q: "Какви части използвате?", a: "Използваме качествени съвместими части с гаранция до 12 месеца." },
  ];

  return (
    <Layout>
      <SEOHead
        title={`Ремонт ${model.name} - бързо и с гаранция | ${BRAND}`}
        description={`Професионален ремонт на ${model.name} от ${BRAND}. Смяна на дисплей, батерия, Face ID, камера. Гаранция до 12 месеца, куриер в цяла България.`}
      />

      <section className="hero-section overflow-hidden py-16 md:py-20">
        <div className="container grid gap-10 lg:grid-cols-[1.06fr_0.94fr] lg:items-center">
          <div className="max-w-3xl">
            <p className="mb-4 text-xs font-extrabold uppercase tracking-[0.24em] text-slate-500">
              Премиум сервиз за iPhone
            </p>
            <h1 className="text-balance text-3xl font-extrabold leading-tight text-foreground md:text-5xl lg:text-[3.4rem]">
              Ремонт на {model.name} с бърза диагностика и гаранция от{" "}
              <span className="gradient-text">{BRAND}</span>
            </h1>
            <p className="mt-6 max-w-2xl text-base leading-8 text-hero-muted md:text-lg">
              Професионален ремонт на {model.name} с куриерска услуга в цяла България. Получавате ясна оценка,
              безплатна диагностика и сервизен процес, който следим от заявката до връщането на телефона.
            </p>
            <div className="mt-8 flex flex-col gap-4 sm:flex-row">
              <Link to="/zaqvka_za_remont">
                <Button variant="hero" size="lg" className="w-full rounded-lg px-8 sm:w-auto">
                  Поръчай ремонт
                </Button>
              </Link>
              <a href={`tel:${PHONE_HREF}`}>
                <Button variant="hero-outline" size="lg" className="w-full gap-2 rounded-lg px-8 sm:w-auto">
                  <Phone className="h-5 w-5" />
                  Обади се
                </Button>
              </a>
            </div>
          </div>

          <div className="relative mx-auto w-full max-w-[34rem]">
            <div className="absolute inset-x-10 bottom-2 h-20 rounded-full bg-primary/20 blur-3xl" aria-hidden="true" />
            <div className="hero-glass relative overflow-hidden rounded-2xl p-6 text-center shadow-2xl shadow-sky-950/10 sm:p-8">
              <p className="mb-4 text-xs font-extrabold uppercase tracking-[0.2em] text-slate-500">
                Модел {model.series}
              </p>
              <div className="mx-auto flex min-h-[21rem] items-center justify-center">
                <img
                  src={model.photo}
                  alt={model.name}
                  className="max-h-[20rem] w-auto drop-shadow-2xl"
                  loading="eager"
                />
              </div>
              <div className="mt-5 flex flex-wrap justify-center gap-2">
                {["Безплатна диагностика", "Гаранция до 12 месеца", "Куриер в двете посоки"].map((item) => (
                  <span key={item} className="rounded-full border border-primary/15 bg-white/75 px-4 py-2 text-xs font-semibold text-primary shadow-sm">
                    {item}
                  </span>
                ))}
              </div>
            </div>
          </div>
        </div>

        <div className="container mt-12">
          <div className="rounded-2xl border border-white/80 bg-white/75 p-4 shadow-xl shadow-sky-950/5 backdrop-blur">
            <div className="mb-4 flex items-end justify-between gap-4">
              <div>
                <p className="text-xs font-extrabold uppercase tracking-[0.22em] text-slate-500">Избери модел</p>
                <h2 className="mt-1 text-2xl font-extrabold text-foreground">iPhone 16 до iPhone 11</h2>
              </div>
              <div className="hidden gap-2 md:flex">
                <button
                  type="button"
                  className="flex h-10 w-10 items-center justify-center rounded-full border bg-white text-slate-500 shadow-sm"
                  aria-label="Предишен модел"
                >
                  <ChevronLeft className="h-4 w-4" />
                </button>
                <button
                  type="button"
                  className="flex h-10 w-10 items-center justify-center rounded-full border bg-white text-slate-500 shadow-sm"
                  aria-label="Следващ модел"
                >
                  <ChevronRight className="h-4 w-4" />
                </button>
              </div>
            </div>

            <div className="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-6">
              {MODELS.map((item) => {
                const isCurrent = item.slug === model.slug;

                return (
                  <Link
                    key={item.slug}
                    to={`/${item.slug}`}
                    className={`group rounded-xl border bg-white p-4 text-center shadow-sm transition hover:-translate-y-0.5 hover:border-primary/30 hover:shadow-md ${
                      isCurrent ? "border-primary/40 ring-2 ring-primary/10" : "border-slate-200"
                    }`}
                  >
                    <div className="mx-auto mb-3 flex h-24 items-center justify-center">
                      <img src={item.photo} alt={item.name} className="max-h-24 w-auto object-contain" loading="lazy" />
                    </div>
                    <p className="font-semibold text-foreground group-hover:text-primary">{item.name}</p>
                    {isCurrent && (
                      <p className="mt-1 text-[0.66rem] font-extrabold uppercase tracking-[0.16em] text-primary">
                        Текущ модел
                      </p>
                    )}
                  </Link>
                );
              })}
            </div>
          </div>
        </div>
      </section>

      <TrustBar />

      <section className="py-16 md:py-20">
        <div className="container max-w-3xl text-center">
          <h2 className="text-2xl font-extrabold md:text-3xl">Чести проблеми с {model.name}</h2>
          <p className="mt-4 text-muted-foreground">
            Това са най-честите симптоми, с които пристига {model.name} в сервиза. Ако проблемът ви е различен,
            можем да го установим при диагностика.
          </p>
          <div className="mt-8 grid gap-3 text-left sm:grid-cols-2">
            {problems.map((p) => (
              <Link key={p} to="/zaqvka_za_remont" className="card-service flex items-center gap-3 py-4">
                <span className="text-primary font-bold">•</span>
                <span>{p}</span>
              </Link>
            ))}
          </div>
        </div>
      </section>

      <section className="bg-muted/50 py-16 md:py-20">
        <div className="container">
          <div className="mx-auto mb-9 max-w-2xl text-center">
            <h2 className="text-2xl font-extrabold md:text-3xl">Услуги за {model.name}</h2>
            <p className="mt-4 text-muted-foreground">
              Най-поръчваните ремонти за {model.name} с ориентировъчни цени в евро.
            </p>
          </div>
          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {SERVICES.map((service) => {
              const seoSlug = `${service.slug.replace("-iphone", "")}-iphone-${model.series}`;
              const price = pricing.find((p) => p.service === service.name);
              return (
                <Link key={service.slug} to={`/${seoSlug}`} className="card-service text-center group">
                  <h3 className="font-semibold group-hover:text-primary transition-colors">{service.name}</h3>
                  <p className="my-3 text-3xl font-extrabold text-primary">{price?.price}</p>
                  <p className="text-xs font-bold uppercase tracking-[0.14em] text-muted-foreground">
                    с гаранция до 12 мес.
                  </p>
                </Link>
              );
            })}
          </div>
        </div>
      </section>

      <section className="py-16 md:py-20">
        <div className="container">
          <h2 className="text-center text-2xl font-extrabold md:text-3xl">Как работи процесът?</h2>
          <div className="mt-10 grid gap-5 md:grid-cols-5">
            {STEPS.map((step) => (
              <div key={step.num} className="card-service text-center">
                <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-primary text-lg font-extrabold text-primary-foreground">
                  {step.num}
                </div>
                <h3 className="mb-2 text-sm font-bold">{step.title}</h3>
                <p className="text-xs leading-6 text-muted-foreground">{step.desc}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="bg-muted/50 py-16 md:py-20">
        <div className="container">
          <h2 className="text-center text-2xl font-extrabold md:text-3xl">Ремонт на {model.name} по градове</h2>
          <div className="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {CITIES.map((city) => (
              <Link key={city.slug} to={`/${city.slug}`} className="card-service text-center group">
                <h3 className="font-semibold group-hover:text-primary transition-colors">Ремонт {model.name} {city.name}</h3>
                <p className="text-sm text-muted-foreground mt-1">Куриер до {city.name}</p>
              </Link>
            ))}
          </div>
        </div>
      </section>

      <FAQSection items={faq} />
      <CTASection />
    </Layout>
  );
}
