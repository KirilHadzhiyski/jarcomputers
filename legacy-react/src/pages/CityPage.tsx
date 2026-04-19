import { useLocation, Link } from "react-router-dom";
import Layout from "@/components/Layout";
import SEOHead from "@/components/SEOHead";
import TrustBar from "@/components/TrustBar";
import FAQSection from "@/components/FAQSection";
import { CTASection } from "@/components/CTA";
import RepairForm from "@/components/RepairForm";
import { Button } from "@/components/ui/button";
import { BRAND, CITIES, SERVICES, MODELS, STEPS } from "@/lib/data";
import { Phone, ArrowRight } from "lucide-react";

export default function CityPage() {
  const { pathname } = useLocation();
  const slug = pathname.slice(1);
  const city = CITIES.find((c) => c.slug === slug);

  if (!city) return null;

  const faq = [
    { q: `Как мога да изпратя телефона си от ${city.name}?`, a: `Попълвате онлайн заявка и ние изпращаме куриер до вашия адрес в ${city.name}. Ремонтираме устройството и го връщаме по куриер – безплатно в двете посоки.` },
    { q: `Колко време отнема доставката от ${city.name}?`, a: `Обикновено куриерът взима устройството на следващия работен ден. Ремонтът отнема 24–48 часа, а връщането – още 1 работен ден.` },
    { q: "Извършвате ли ремонти на място?", a: `Не, всички ремонти се извършват в нашия сервиз в Благоевград. Но благодарение на бързата куриерска услуга, целият процес отнема само 3–5 работни дни.` },
    { q: "Безплатен ли е куриерът?", a: "Да, куриерската услуга е безплатна в двете посоки за клиенти от цяла България." },
    { q: "Какво се случва, ако не одобря ремонта?", a: "Връщаме устройството безплатно. Диагностиката е безплатна и не дължите нищо." },
  ];

  return (
    <Layout>
      <SEOHead
        title={`Ремонт на iPhone ${city.name} – куриер от ${BRAND}`}
        description={`Професионален ремонт на iPhone за ${city.name} с куриерска услуга от ${BRAND}. Безплатна диагностика, гаранция до 12 месеца.`}
      />

      <section className="hero-section py-16 md:py-24">
        <div className="container max-w-4xl">
          <h1 className="text-3xl md:text-4xl lg:text-5xl font-bold mb-6">
            Ремонт на iPhone {city.name} – куриерска услуга от{" "}
            <span className="gradient-text">{BRAND}</span>
          </h1>
          <p className="text-lg text-hero-muted mb-8">
            Живеете в {city.name}? Изпращаме куриер до вашия адрес, ремонтираме iPhone-а ви и го връщаме – бързо, надеждно и с гаранция.
          </p>
          <div className="flex flex-col sm:flex-row gap-4">
            <Link to="/kontakti"><Button variant="hero" size="lg">Поръчай ремонт от {city.name}</Button></Link>
            <a href="tel:+359888888888"><Button variant="hero-outline" size="lg" className="gap-2"><Phone className="h-5 w-5" />Обади се</Button></a>
          </div>
        </div>
      </section>

      <TrustBar />

      {/* Explanation */}
      <section className="py-16">
        <div className="container max-w-3xl">
          <h2 className="text-2xl font-bold mb-6">Как работи ремонтът от {city.name}?</h2>
          <p className="text-muted-foreground mb-6">
            {BRAND} обслужва клиенти от {city.name} чрез надеждна куриерска услуга. 
            Всички ремонти се извършват в нашия специализиран сервиз в Благоевград, 
            оборудван с професионални инструменти и качествени части.
          </p>
          <p className="text-muted-foreground mb-8">
            Процесът е прост: поръчвате онлайн, куриер идва до вас, ние ремонтираме и връщаме. 
            Целият процес отнема 3–5 работни дни. Безплатна диагностика и плащане само при одобрение.
          </p>

          <div className="grid gap-6 md:grid-cols-5 mb-12">
            {STEPS.map((step) => (
              <div key={step.num} className="text-center">
                <div className="mx-auto h-12 w-12 rounded-full bg-primary text-primary-foreground flex items-center justify-center font-bold mb-3">{step.num}</div>
                <h3 className="font-semibold text-sm mb-1">{step.title}</h3>
                <p className="text-xs text-muted-foreground">{step.desc}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Services */}
      <section className="py-16 bg-muted/50">
        <div className="container">
          <h2 className="text-2xl font-bold text-center mb-8">Услуги за {city.name}</h2>
          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {SERVICES.map((service) => (
              <Link key={service.slug} to={`/${service.slug}`} className="card-service text-center group">
                <h3 className="font-semibold group-hover:text-primary transition-colors">{service.name}</h3>
                <p className="text-2xl font-bold text-primary my-2">от {service.priceFrom} лв</p>
                <p className="text-xs text-muted-foreground">с гаранция до 12 мес.</p>
              </Link>
            ))}
          </div>
        </div>
      </section>

      {/* Form */}
      <section className="py-16">
        <div className="container max-w-xl">
          <h2 className="text-2xl font-bold text-center mb-8">Заявка за ремонт от {city.name}</h2>
          <RepairForm />
        </div>
      </section>

      <FAQSection items={faq} />
      <CTASection title={`Ремонт на iPhone от ${city.name} с ${BRAND}`} />
    </Layout>
  );
}
