import { useLocation, Link } from "react-router-dom";
import Layout from "@/components/Layout";
import SEOHead from "@/components/SEOHead";
import TrustBar from "@/components/TrustBar";
import FAQSection from "@/components/FAQSection";
import { CTASection } from "@/components/CTA";
import { Button } from "@/components/ui/button";
import { BRAND, SERVICES, MODELS, CITIES, STEPS } from "@/lib/data";
import { ArrowRight, Phone } from "lucide-react";

export default function ServicePage() {
  const { pathname } = useLocation();
  const slug = pathname.slice(1);
  const service = SERVICES.find((s) => s.slug === slug);

  if (!service) return null;

  const faq = [
    { q: `Колко струва ${service.name.toLowerCase()} на iPhone?`, a: `Цената за ${service.name.toLowerCase()} започва от ${service.priceFrom} лв. Окончателната цена зависи от модела и състоянието на устройството. Диагностиката е безплатна.` },
    { q: "Колко време отнема ремонтът?", a: "Повечето ремонти се извършват в рамките на 24–48 часа след получаване на устройството." },
    { q: "Предлагате ли гаранция?", a: `Да, предлагаме гаранция до 12 месеца за ${service.name.toLowerCase()}.` },
    { q: "Как работи куриерската услуга?", a: "Изпращаме куриер до вашия адрес, за да вземе устройството. След ремонта го връщаме по същия начин – безплатно в двете посоки." },
    { q: "Какви части използвате?", a: "Използваме качествени съвместими и оригинални части, тествани за надеждност и дълготрайност." },
  ];

  return (
    <Layout>
      <SEOHead
        title={`${service.name} iPhone – от ${service.priceFrom} лв | ${BRAND}`}
        description={`${service.description} Гаранция до 12 месеца, безплатна диагностика и куриерска услуга в цяла България от ${BRAND}.`}
      />

      <section className="hero-section py-16 md:py-24">
        <div className="container max-w-4xl">
          <h1 className="text-3xl md:text-4xl lg:text-5xl font-bold mb-6">
            {service.name} iPhone – бързо и с гаранция от{" "}
            <span className="gradient-text">{BRAND}</span>
          </h1>
          <p className="text-lg text-hero-muted mb-8 max-w-2xl">
            {service.description} Безплатна диагностика, куриер в двете посоки и гаранция до 12 месеца.
          </p>
          <div className="flex flex-col sm:flex-row gap-4">
            <Link to="/kontakti"><Button variant="hero" size="lg">Поръчай ремонт</Button></Link>
            <a href={`tel:+359888888888`}><Button variant="hero-outline" size="lg" className="gap-2"><Phone className="h-5 w-5" />Обади се</Button></a>
          </div>
        </div>
      </section>

      <TrustBar />

      {/* Introduction */}
      <section className="py-16">
        <div className="container max-w-3xl prose prose-lg">
          <h2>Професионален ремонт с куриерска услуга в цяла България</h2>
          <p>
            {BRAND} предлага професионална услуга „{service.name}" за всички модели iPhone. 
            Независимо дали се намирате в София, Пловдив, Варна, Бургас или друг град – 
            ние изпращаме куриер до вашия адрес, извършваме ремонта и връщаме устройството ви.
          </p>
          <p>
            С над 10 години опит и повече от 5000 успешно ремонтирани устройства, ние гарантираме 
            качество и надеждност. Всеки ремонт идва с гаранция до 12 месеца.
          </p>
          <h3>Какво включва услугата?</h3>
          <ul>
            <li>Безплатна диагностика на устройството</li>
            <li>Качествени части с гаранция</li>
            <li>Експресен ремонт 24–48 часа</li>
            <li>Куриер в двете посоки – безплатно</li>
            <li>Плащаш само ако одобриш цената</li>
            <li>Възможност за -10% при онлайн поръчка</li>
          </ul>
        </div>
      </section>

      {/* How it works */}
      <section className="py-16 bg-muted/50">
        <div className="container">
          <h2 className="text-2xl font-bold text-center mb-10">Как работи процесът?</h2>
          <div className="grid gap-6 md:grid-cols-5">
            {STEPS.map((step) => (
              <div key={step.num} className="text-center">
                <div className="mx-auto h-14 w-14 rounded-full bg-primary text-primary-foreground flex items-center justify-center text-xl font-bold mb-4">
                  {step.num}
                </div>
                <h3 className="font-semibold mb-2 text-sm">{step.title}</h3>
                <p className="text-xs text-muted-foreground">{step.desc}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Price */}
      <section className="py-16">
        <div className="container text-center">
          <h2 className="text-2xl font-bold mb-4">Цена за {service.name.toLowerCase()}</h2>
          <p className="text-4xl font-bold text-primary mb-2">от {service.priceFrom} лв</p>
          <p className="text-muted-foreground mb-8">Окончателната цена зависи от модела и диагностиката.</p>
        </div>
      </section>

      {/* Models */}
      <section className="py-16 bg-muted/50">
        <div className="container">
          <h2 className="text-2xl font-bold text-center mb-8">{service.name} по модел</h2>
          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {MODELS.map((model) => {
              const seoSlug = `${service.slug.replace("-iphone", "")}-iphone-${model.series}`;
              return (
                <Link key={model.slug} to={`/${seoSlug}`} className="card-service text-center group">
                  <h3 className="font-semibold group-hover:text-primary transition-colors">{service.name} {model.name}</h3>
                  <span className="inline-flex items-center gap-1 text-sm text-primary mt-2">Виж повече <ArrowRight className="h-4 w-4" /></span>
                </Link>
              );
            })}
          </div>
        </div>
      </section>

      {/* Cities */}
      <section className="py-16">
        <div className="container">
          <h2 className="text-2xl font-bold text-center mb-8">Обслужвани градове</h2>
          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {CITIES.map((city) => (
              <Link key={city.slug} to={`/${city.slug}`} className="card-service text-center group">
                <h3 className="font-semibold group-hover:text-primary transition-colors">{service.name} iPhone {city.name}</h3>
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
