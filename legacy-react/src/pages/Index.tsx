import type { ComponentType } from "react";
import { Link } from "react-router-dom";
import Layout from "@/components/Layout";
import SEOHead from "@/components/SEOHead";
import TrustBar from "@/components/TrustBar";
import FAQSection from "@/components/FAQSection";
import { CTASection } from "@/components/CTA";
import ReviewSummary from "@/components/ReviewSummary";
import { Button } from "@/components/ui/button";
import { BRAND, CITIES, REVIEW_PLATFORMS, SERVICES, STEPS, TRUST_CONVERSION_POINTS, WHY_US } from "@/lib/data";
import {
  ArrowRight,
  Award,
  Battery,
  Camera,
  CheckCircle,
  Gem,
  Image,
  MessageCircle,
  Receipt,
  ScanFace,
  Smartphone,
} from "lucide-react";
import heroImg from "@/assets/hero-repair.jpg";

const serviceIcons: Record<string, ComponentType<{ className?: string }>> = {
  Smartphone,
  Battery,
  ScanFace,
  Camera,
};

const whyIcons: Record<string, ComponentType<{ className?: string }>> = {
  Award,
  CheckCircle,
  Gem,
  Receipt,
  Image,
  MessageCircle,
};

const primaryReview = REVIEW_PLATFORMS.find((platform) => platform.primary) ?? REVIEW_PLATFORMS[0];

const heroProofs = [
  "Безплатна диагностика",
  "Проследима комуникация",
  "Плащаш само при одобрение",
  `${primaryReview.ratingValue.toFixed(1)}/${primaryReview.ratingScale} ${primaryReview.label} · ${primaryReview.reviewsCount} оценки`,
];

export default function Index() {
  return (
    <Layout>
      <SEOHead
        title={`Професионален ремонт на iPhone | ${BRAND}`}
        description={`Диагностика, сервиз и проследим процес за ремонт на iPhone от ${BRAND}. Ясни цени, публични отзиви и куриерска услуга в двете посоки.`}
      />

      <section className="home-hero relative overflow-hidden border-b">
        <div className="absolute inset-0 z-0">
          <img
            src={heroImg}
            alt="Ремонт на iPhone"
            className="h-full w-full object-cover opacity-20 brightness-125 contrast-75"
            loading="eager"
          />
          <div className="absolute inset-0 bg-gradient-to-r from-background via-background/88 to-background/35" />
        </div>

        <div className="container relative z-10 py-12 md:py-20 lg:py-24">
          <div className="max-w-3xl">
            <div className="trust-badge mb-7 text-sm">
              Физически сервиз в Благоевград · куриер в цяла България
            </div>

            <h1 className="mb-6 text-4xl font-extrabold leading-[1.12] tracking-normal text-foreground md:text-5xl lg:text-6xl text-balance">
              Професионален ремонт на iPhone от{" "}
              <span className="gradient-text">{BRAND}</span>
            </h1>

            <p className="mb-8 max-w-2xl text-lg leading-8 text-hero-muted md:text-xl">
              Диагностика, сервиз и проследим процес за ремонт на iPhone с ясни цени,
              реални публични отзиви и куриерска услуга в двете посоки.
            </p>

            <div className="flex max-w-2xl flex-col gap-4 sm:flex-row">
              <Link to="/zaqvka_za_remont" className="sm:flex-1">
                <Button variant="hero" size="lg" className="h-11 w-full rounded-xl text-base">
                  Поръчай ремонт
                </Button>
              </Link>
              <Link to="/ceni" className="sm:flex-1">
                <Button variant="hero-outline" size="lg" className="h-11 w-full rounded-xl text-base">
                  Виж цени
                </Button>
              </Link>
            </div>

            <div className="mt-8 flex max-w-2xl flex-wrap gap-x-4 gap-y-3 text-[13px] font-medium text-hero-muted sm:text-sm">
              {heroProofs.map((item) => (
                <span key={item}>{item}</span>
              ))}
            </div>
          </div>
        </div>
      </section>

      <TrustBar />

      <CTASection />

      <section className="border-b border-border/70 bg-white py-14 md:py-16">
        <div className="container">
          <div className="grid gap-8 lg:grid-cols-[0.85fr_1.15fr] lg:items-center">
            <div>
              <p className="mb-3 text-sm font-bold uppercase tracking-[0.18em] text-primary">Доверие преди заявка</p>
              <h2 className="text-2xl font-extrabold md:text-3xl">
                Клиентът трябва да знае какво ще стане с телефона му още преди да натисне бутона.
              </h2>
              <p className="mt-4 text-sm leading-7 text-muted-foreground">
                Страницата следва практиките на водещите сервизни сайтове: ясна гаранция, видим процес,
                конкретни цени, реален адрес и лесен следващ ход.
              </p>
            </div>
            <div className="grid gap-4 sm:grid-cols-2">
              {TRUST_CONVERSION_POINTS.map((point) => (
                <div key={point.title} className="rounded-lg border bg-card p-5 shadow-sm">
                  <h3 className="font-bold text-foreground">{point.title}</h3>
                  <p className="mt-2 text-sm leading-6 text-muted-foreground">{point.desc}</p>
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      <section className="py-16 md:py-20">
        <div className="container">
          <div className="mx-auto mb-10 max-w-3xl text-center">
            <h2 className="text-2xl md:text-3xl font-extrabold">
              Защо да изберете {BRAND}?
            </h2>
            <p className="mt-4 text-muted-foreground">
              Реални клиентски оценки, физически обект в Благоевград и ясен сервизен процес
              за клиенти от цяла България.
            </p>
          </div>

          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            {WHY_US.map((item) => {
              const Icon = whyIcons[item.icon];

              return (
                <div key={item.title} className="card-service">
                  <div className="mb-4 flex h-11 w-11 items-center justify-center rounded-lg bg-accent text-primary">
                    {Icon && <Icon className="h-5 w-5" />}
                  </div>
                  <h3 className="mb-2 font-bold">{item.title}</h3>
                  <p className="text-sm leading-6 text-muted-foreground">{item.desc}</p>
                </div>
              );
            })}
          </div>
        </div>
      </section>

      <section className="border-y border-border/70 bg-card py-16 md:py-20">
        <div className="container">
          <div className="mx-auto mb-10 max-w-3xl text-center">
            <h2 className="text-2xl md:text-3xl font-extrabold">Най-търсени услуги</h2>
            <p className="mt-4 text-muted-foreground">
              Специализирани сме в ремонт на iPhone - от смяна на дисплей и батерия до Face ID и камера.
            </p>
          </div>

          <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            {SERVICES.map((service) => {
              const Icon = serviceIcons[service.icon];

              return (
                <Link key={service.slug} to={`/${service.slug}`} className="card-service group block">
                  <div className="mb-5 flex items-center justify-between">
                    <div className="flex h-11 w-11 items-center justify-center rounded-lg bg-accent text-sm font-extrabold text-primary">
                      {service.badge}
                    </div>
                    {Icon && <Icon className="h-5 w-5 text-primary/70" />}
                  </div>
                  <h3 className="mb-3 font-bold transition-colors group-hover:text-primary">
                    {service.name}
                  </h3>
                  <p className="mb-4 text-sm leading-6 text-muted-foreground">{service.description}</p>
                  <span className="text-sm font-bold text-primary">
                    от {service.priceFrom} €
                  </span>
                </Link>
              );
            })}
          </div>
        </div>
      </section>

      <section className="py-16 md:py-20">
        <div className="container">
          <div className="mx-auto mb-10 max-w-3xl text-center">
            <h2 className="text-2xl md:text-3xl font-extrabold">Как работи?</h2>
            <p className="mt-4 text-muted-foreground">
              Всяка заявка минава през ясен процес - от приемането до връщането на ремонтирания телефон.
            </p>
          </div>

          <div className="grid gap-5 md:grid-cols-5">
            {STEPS.map((step) => (
              <div key={step.num} className="rounded-lg border bg-card p-5 text-center shadow-sm">
                <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-primary text-lg font-extrabold text-primary-foreground">
                  {step.num}
                </div>
                <h3 className="mb-2 text-sm font-bold">{step.title}</h3>
                <p className="text-xs leading-5 text-muted-foreground">{step.desc}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="border-y border-border/70 bg-muted/60 py-16 md:py-20">
        <div className="container text-center">
          <h2 className="text-2xl md:text-3xl font-extrabold mb-4">
            Ремонт на iPhone в цяла България
          </h2>
          <p className="text-muted-foreground mb-8 max-w-xl mx-auto">
            Изпращаме и връщаме устройствата с куриер, независимо в кой град се намирате.
          </p>
          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {CITIES.map((city) => (
              <Link key={city.slug} to={`/${city.slug}`} className="card-service text-center group">
                <h3 className="font-bold mb-2 transition-colors group-hover:text-primary">
                  Ремонт на iPhone {city.name}
                </h3>
                <p className="text-sm text-muted-foreground">
                  Куриер до {city.name} и обратно
                </p>
                <span className="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-primary">
                  Научи повече <ArrowRight className="h-4 w-4" />
                </span>
              </Link>
            ))}
          </div>
        </div>
      </section>

      <section className="py-16 md:py-20">
        <div className="container text-center">
          <h2 className="text-2xl md:text-3xl font-extrabold mb-4">Ориентировъчни цени</h2>
          <p className="text-muted-foreground mb-8 max-w-xl mx-auto">
            Окончателната цена зависи от диагностиката. При всеки ремонт получавате ясна оценка преди започване на работа.
          </p>
          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 max-w-4xl mx-auto">
            {SERVICES.map((s) => (
              <Link key={s.slug} to="/ceni" className="card-service text-center">
                <h3 className="font-bold mb-2">{s.shortName}</h3>
                <p className="text-3xl font-extrabold text-primary mb-1">от {s.priceFrom} €</p>
                <p className="text-xs text-muted-foreground">с гаранция до 12 мес.</p>
              </Link>
            ))}
          </div>
          <Link to="/ceni" className="inline-block mt-8">
            <Button variant="hero-outline" className="gap-2 rounded-lg">
              Виж всички цени <ArrowRight className="h-4 w-4" />
            </Button>
          </Link>
        </div>
      </section>

      <FAQSection />

      <ReviewSummary sectionClassName="py-16 md:py-20" eyebrow="Google и публични оценки" />

      <CTASection />
    </Layout>
  );
}
