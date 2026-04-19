import { Link } from "react-router-dom";
import Layout from "@/components/Layout";
import SEOHead from "@/components/SEOHead";
import TrustBar from "@/components/TrustBar";
import FAQSection from "@/components/FAQSection";
import { CTASection } from "@/components/CTA";
import { Button } from "@/components/ui/button";
import { BRAND, SERVICES, MODELS, CITIES, STEPS } from "@/lib/data";
import { Phone, ArrowRight } from "lucide-react";

export default function MainServicePage() {
  return (
    <Layout>
      <SEOHead
        title={`Ремонт на iPhone – професионален сервиз | ${BRAND}`}
        description={`Професионален ремонт на iPhone от ${BRAND}. Смяна на дисплей, батерия, Face ID, камера. Гаранция до 12 месеца, куриер в цяла България.`}
      />

      <section className="hero-section py-16 md:py-24">
        <div className="container max-w-4xl">
          <h1 className="text-3xl md:text-4xl lg:text-5xl font-bold mb-6">
            Ремонт на iPhone – професионален сервиз от{" "}
            <span className="gradient-text">{BRAND}</span>
          </h1>
          <p className="text-lg text-hero-muted mb-8">
            Всички услуги за ремонт на iPhone на едно място. Безплатна диагностика, куриер и гаранция.
          </p>
          <div className="flex flex-col sm:flex-row gap-4">
            <Link to="/kontakti"><Button variant="hero" size="lg">Поръчай ремонт</Button></Link>
            <a href="tel:+359888888888"><Button variant="hero-outline" size="lg" className="gap-2"><Phone className="h-5 w-5" />Обади се</Button></a>
          </div>
        </div>
      </section>

      <TrustBar />

      <section className="py-16">
        <div className="container">
          <h2 className="text-2xl font-bold text-center mb-8">Нашите услуги</h2>
          <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            {SERVICES.map((service) => (
              <Link key={service.slug} to={`/${service.slug}`} className="card-service text-center group">
                <h3 className="font-semibold group-hover:text-primary transition-colors mb-2">{service.name}</h3>
                <p className="text-sm text-muted-foreground mb-3">{service.description}</p>
                <p className="text-2xl font-bold text-primary">от {service.priceFrom} лв</p>
                <span className="inline-flex items-center gap-1 text-sm text-primary mt-3">Научи повече <ArrowRight className="h-4 w-4" /></span>
              </Link>
            ))}
          </div>
        </div>
      </section>

      <section className="py-16 bg-muted/50">
        <div className="container">
          <h2 className="text-2xl font-bold text-center mb-8">По модел</h2>
          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {MODELS.map((model) => (
              <Link key={model.slug} to={`/${model.slug}`} className="card-service text-center group">
                <h3 className="font-semibold group-hover:text-primary transition-colors">Ремонт {model.name}</h3>
                <span className="inline-flex items-center gap-1 text-sm text-primary mt-2">Виж повече <ArrowRight className="h-4 w-4" /></span>
              </Link>
            ))}
          </div>
        </div>
      </section>

      <section className="py-16">
        <div className="container">
          <h2 className="text-2xl font-bold text-center mb-10">Как работи?</h2>
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

      <section className="py-16 bg-muted/50">
        <div className="container">
          <h2 className="text-2xl font-bold text-center mb-8">Обслужвани градове</h2>
          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {CITIES.map((city) => (
              <Link key={city.slug} to={`/${city.slug}`} className="card-service text-center group">
                <h3 className="font-semibold group-hover:text-primary transition-colors">iPhone ремонт {city.name}</h3>
                <p className="text-sm text-muted-foreground mt-1">Куриер до {city.name}</p>
              </Link>
            ))}
          </div>
        </div>
      </section>

      <FAQSection />
      <CTASection />
    </Layout>
  );
}
