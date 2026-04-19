import { useLocation, Link } from "react-router-dom";
import Layout from "@/components/Layout";
import SEOHead from "@/components/SEOHead";
import TrustBar from "@/components/TrustBar";
import FAQSection from "@/components/FAQSection";
import { CTASection } from "@/components/CTA";
import { Button } from "@/components/ui/button";
import { BRAND, MODELS, SERVICES, CITIES, STEPS, PRICING_TABLE } from "@/lib/data";
import { Phone, ArrowRight } from "lucide-react";

const modelProblems: Record<string, string[]> = {
  "11": ["Счупен дисплей", "Бърза разрядка на батерия", "Face ID спира да работи", "Проблеми с камерата", "Заглушен звук"],
  "12": ["Пукнато стъкло", "Влошена батерия", "Проблем с Face ID", "Замъглена камера", "Проблеми с Wi-Fi"],
  "13": ["Счупен OLED дисплей", "Бърз разряд", "Face ID грешки", "Камера не фокусира", "Мигащ екран"],
  "14": ["Счупен дисплей", "Батерия под 80%", "Face ID проблеми", "Камера шум", "Проблеми със зареждане"],
};

export default function ModelPage() {
  const { pathname } = useLocation();
  const slug = pathname.slice(1);
  const model = MODELS.find((m) => m.slug === slug);

  if (!model) return null;

  const problems = modelProblems[model.series] || modelProblems["11"];
  const pricing = PRICING_TABLE.map((row) => ({
    service: row.service,
    price: row[`iphone${model.series}` as keyof typeof row],
  }));

  const faq = [
    { q: `Колко струва ремонт на ${model.name}?`, a: `Цените за ремонт на ${model.name} започват от 49 лв за смяна на батерия. Окончателната цена зависи от вида на ремонта.` },
    { q: `Колко време отнема ремонт на ${model.name}?`, a: "Повечето ремонти се извършват в рамките на 24–48 часа." },
    { q: "Предлагате ли куриер?", a: "Да, изпращаме куриер до вашия адрес безплатно в двете посоки." },
    { q: "Какви части използвате?", a: "Използваме качествени съвместими части с гаранция до 12 месеца." },
  ];

  return (
    <Layout>
      <SEOHead
        title={`Ремонт ${model.name} – бързо и с гаранция | ${BRAND}`}
        description={`Професионален ремонт на ${model.name} от ${BRAND}. Смяна на дисплей, батерия, Face ID, камера. Гаранция до 12 месеца, куриер в цяла България.`}
      />

      <section className="hero-section py-16 md:py-24">
        <div className="container max-w-4xl">
          <h1 className="text-3xl md:text-4xl lg:text-5xl font-bold mb-6">
            Ремонт на {model.name} – бързо и с гаранция от{" "}
            <span className="gradient-text">{BRAND}</span>
          </h1>
          <p className="text-lg text-hero-muted mb-8">
            Професионален ремонт на {model.name} с куриерска услуга в цяла България. Безплатна диагностика.
          </p>
          <div className="flex flex-col sm:flex-row gap-4">
            <Link to="/kontakti"><Button variant="hero" size="lg">Поръчай ремонт</Button></Link>
            <a href="tel:+359888888888"><Button variant="hero-outline" size="lg" className="gap-2"><Phone className="h-5 w-5" />Обади се</Button></a>
          </div>
        </div>
      </section>

      <TrustBar />

      {/* Common problems */}
      <section className="py-16">
        <div className="container max-w-3xl">
          <h2 className="text-2xl font-bold mb-6">Чести проблеми с {model.name}</h2>
          <div className="grid gap-3 sm:grid-cols-2">
            {problems.map((p) => (
              <div key={p} className="card-service flex items-center gap-3 py-4">
                <span className="text-primary font-bold">•</span>
                <span>{p}</span>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Services for model */}
      <section className="py-16 bg-muted/50">
        <div className="container">
          <h2 className="text-2xl font-bold text-center mb-8">Услуги за {model.name}</h2>
          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {SERVICES.map((service) => {
              const seoSlug = `${service.slug.replace("-iphone", "")}-iphone-${model.series}`;
              const price = pricing.find((p) => p.service === service.name);
              return (
                <Link key={service.slug} to={`/${seoSlug}`} className="card-service text-center group">
                  <h3 className="font-semibold group-hover:text-primary transition-colors">{service.name}</h3>
                  <p className="text-2xl font-bold text-primary my-2">{price?.price}</p>
                  <p className="text-xs text-muted-foreground">с гаранция до 12 мес.</p>
                  <span className="inline-flex items-center gap-1 text-sm text-primary mt-3">Виж повече <ArrowRight className="h-4 w-4" /></span>
                </Link>
              );
            })}
          </div>
        </div>
      </section>

      {/* How it works */}
      <section className="py-16">
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

      {/* Cities */}
      <section className="py-16 bg-muted/50">
        <div className="container">
          <h2 className="text-2xl font-bold text-center mb-8">Ремонт на {model.name} по градове</h2>
          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
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
