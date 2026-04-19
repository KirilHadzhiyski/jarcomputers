import { useLocation, Link } from "react-router-dom";
import Layout from "@/components/Layout";
import SEOHead from "@/components/SEOHead";
import TrustBar from "@/components/TrustBar";
import FAQSection from "@/components/FAQSection";
import { CTASection } from "@/components/CTA";
import { Button } from "@/components/ui/button";
import { BRAND, SERVICES, MODELS, CITIES, STEPS, generateSeoPages } from "@/lib/data";
import { Phone, ArrowRight } from "lucide-react";

export default function SeoPage() {
  const { pathname } = useLocation();
  const slug = pathname.slice(1);
  const pages = generateSeoPages();
  const page = pages.find((p) => p.slug === slug);

  if (!page) return null;

  const { service, model } = page;

  const faq = [
    { q: `Колко струва ${service.name.toLowerCase()} на ${model.name}?`, a: `Цената за ${service.name.toLowerCase()} на ${model.name} зависи от диагностиката. Цените започват от ${service.priceFrom} лв. Диагностиката е безплатна.` },
    { q: "Колко време отнема?", a: "Повечето ремонти се извършват в рамките на 24–48 часа." },
    { q: "Предлагате ли гаранция?", a: `Да, гаранция до 12 месеца за ${service.name.toLowerCase()} на ${model.name}.` },
    { q: "Как да изпратя телефона си?", a: "Попълвате онлайн заявка и ние изпращаме куриер до вашия адрес безплатно." },
  ];

  return (
    <Layout>
      <SEOHead
        title={`${service.name} ${model.name} – от ${service.priceFrom} лв | ${BRAND}`}
        description={`${service.name} ${model.name} от ${BRAND}. Бързо, с гаранция до 12 месеца и куриерска услуга в цяла България. Безплатна диагностика.`}
      />

      <section className="hero-section py-16 md:py-24">
        <div className="container max-w-4xl">
          <h1 className="text-3xl md:text-4xl lg:text-5xl font-bold mb-6">
            {service.name} {model.name} – бързо и с гаранция от{" "}
            <span className="gradient-text">{BRAND}</span>
          </h1>
          <h2 className="text-xl text-hero-muted mb-8">
            Професионален ремонт на {model.name} с куриерска услуга в цяла България
          </h2>
          <div className="flex flex-col sm:flex-row gap-4">
            <Link to="/kontakti"><Button variant="hero" size="lg">Поръчай ремонт</Button></Link>
            <a href="tel:+359888888888"><Button variant="hero-outline" size="lg" className="gap-2"><Phone className="h-5 w-5" />Обади се</Button></a>
          </div>
        </div>
      </section>

      <TrustBar />

      <section className="py-16">
        <div className="container max-w-3xl">
          <h2 className="text-2xl font-bold mb-6">Професионална {service.name.toLowerCase()} за {model.name}</h2>
          <p className="text-muted-foreground mb-4">
            Вашият {model.name} се нуждае от {service.name.toLowerCase()}? {BRAND} предлага бърза и надеждна услуга 
            с качествени части и гаранция до 12 месеца. Благодарение на нашата куриерска услуга, можете да 
            ни изпратите устройството от всяка точка на България.
          </p>
          <p className="text-muted-foreground mb-4">
            С над 10 години опит в ремонта на Apple устройства и повече от 5000 успешно ремонтирани телефона, 
            ние сме специалисти в {service.name.toLowerCase()} на {model.name}. Диагностиката е безплатна и 
            плащате само ако одобрите предложената цена.
          </p>

          <h3 className="text-xl font-bold mt-8 mb-4">Какво включва услугата?</h3>
          <ul className="space-y-2 text-muted-foreground">
            <li className="flex items-center gap-2"><span className="text-primary">✓</span> Безплатна диагностика</li>
            <li className="flex items-center gap-2"><span className="text-primary">✓</span> Качествени части с гаранция</li>
            <li className="flex items-center gap-2"><span className="text-primary">✓</span> Експресен ремонт 24–48 часа</li>
            <li className="flex items-center gap-2"><span className="text-primary">✓</span> Куриер в двете посоки безплатно</li>
            <li className="flex items-center gap-2"><span className="text-primary">✓</span> Плащане само при одобрение</li>
            <li className="flex items-center gap-2"><span className="text-primary">✓</span> -10% при онлайн поръчка</li>
          </ul>
        </div>
      </section>

      <section className="py-16 bg-muted/50">
        <div className="container">
          <h2 className="text-2xl font-bold text-center mb-10">Как работи процесът?</h2>
          <div className="grid gap-6 md:grid-cols-5">
            {STEPS.map((step) => (
              <div key={step.num} className="text-center">
                <div className="mx-auto h-14 w-14 rounded-full bg-primary text-primary-foreground flex items-center justify-center text-xl font-bold mb-4">{step.num}</div>
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
          <h2 className="text-2xl font-bold mb-4">Цена за {service.name.toLowerCase()} на {model.name}</h2>
          <p className="text-4xl font-bold text-primary mb-2">от {service.priceFrom} лв</p>
          <p className="text-muted-foreground mb-4">Окончателната цена зависи от диагностиката.</p>
          <p className="text-sm text-muted-foreground">Време за ремонт: 24–48 часа • Гаранция: до 12 месеца</p>
        </div>
      </section>

      {/* Related */}
      <section className="py-16 bg-muted/50">
        <div className="container">
          <h2 className="text-2xl font-bold text-center mb-8">Свързани услуги</h2>
          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {SERVICES.filter((s) => s.slug !== service.slug).map((s) => {
              const seoSlug = `${s.slug.replace("-iphone", "")}-iphone-${model.series}`;
              return (
                <Link key={s.slug} to={`/${seoSlug}`} className="card-service text-center group">
                  <h3 className="font-semibold group-hover:text-primary transition-colors">{s.name} {model.name}</h3>
                  <p className="text-primary font-bold mt-2">от {s.priceFrom} лв</p>
                </Link>
              );
            })}
            <Link to={`/${model.slug}`} className="card-service text-center group">
              <h3 className="font-semibold group-hover:text-primary transition-colors">Всички услуги за {model.name}</h3>
              <span className="inline-flex items-center gap-1 text-sm text-primary mt-2">Виж повече <ArrowRight className="h-4 w-4" /></span>
            </Link>
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
                <h3 className="font-semibold group-hover:text-primary transition-colors text-sm">{service.name} {model.name} {city.name}</h3>
                <p className="text-xs text-muted-foreground mt-1">Куриер до {city.name}</p>
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
