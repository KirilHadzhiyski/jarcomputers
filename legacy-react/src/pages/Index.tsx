import { Link } from "react-router-dom";
import Layout from "@/components/Layout";
import SEOHead from "@/components/SEOHead";
import TrustBar from "@/components/TrustBar";
import FAQSection from "@/components/FAQSection";
import { CTASection } from "@/components/CTA";
import { Button } from "@/components/ui/button";
import { BRAND, SERVICES, CITIES, STEPS, WHY_US, PHONE } from "@/lib/data";
import {
  Smartphone, Battery, ScanFace, Camera,
  Award, CheckCircle, Gem, Receipt, Image, MessageCircle,
  Phone as PhoneIcon, ArrowRight, Truck
} from "lucide-react";
import heroImg from "@/assets/hero-repair.jpg";

const serviceIcons: Record<string, React.FC<{ className?: string }>> = {
  Smartphone, Battery, ScanFace, Camera,
};

const whyIcons: Record<string, React.FC<{ className?: string }>> = {
  Award, CheckCircle, Gem, Receipt, Image, MessageCircle,
};

export default function Index() {
  return (
    <Layout>
      <SEOHead
        title="Професионален ремонт на iPhone | JAR Computers Благоевград"
        description="Бърз и професионален ремонт на iPhone от JAR Computers Благоевград. Гаранция до 12 месеца, куриерска услуга в цяла България, експресен ремонт 24-48 часа."
      />

      {/* Hero */}
      <section className="hero-section relative overflow-hidden">
        <div className="absolute inset-0 z-0">
          <img src={heroImg} alt="Ремонт на iPhone" className="w-full h-full object-cover opacity-20" loading="eager" />
        </div>
        <div className="container relative z-10 py-16 md:py-24 lg:py-32">
          <div className="max-w-3xl">
            <div className="trust-badge mb-6 text-xs">
              <Truck className="h-4 w-4" /> Куриер в цяла България
            </div>
            <h1 className="text-3xl md:text-4xl lg:text-5xl font-bold leading-tight mb-6 text-balance">
              Професионален ремонт на iPhone от{" "}
              <span className="gradient-text">{BRAND}</span>
            </h1>
            <p className="text-lg md:text-xl text-hero-muted mb-8 max-w-2xl">
              Бързо обслужване, гаранция до 12 месеца и куриерска услуга в цяла България.
            </p>
            <div className="flex flex-col sm:flex-row gap-4">
              <Link to="/kontakti">
                <Button variant="hero" size="lg" className="gap-2 text-base px-8 py-6">
                  Поръчай ремонт <ArrowRight className="h-5 w-5" />
                </Button>
              </Link>
              <a href={`tel:${PHONE}`}>
                <Button variant="hero-outline" size="lg" className="gap-2 text-base px-8 py-6">
                  <PhoneIcon className="h-5 w-5" /> Попитай за цена
                </Button>
              </a>
            </div>
            <div className="mt-8 flex flex-wrap gap-4 text-sm text-hero-muted">
              <span>✓ Безплатна диагностика</span>
              <span>✓ -10% при онлайн поръчка</span>
              <span>✓ Плащаш само при одобрение</span>
            </div>
          </div>
        </div>
      </section>

      <TrustBar />

      {/* Services */}
      <section className="py-16">
        <div className="container">
          <h2 className="text-2xl md:text-3xl font-bold text-center mb-4">Най-търсени услуги</h2>
          <p className="text-muted-foreground text-center mb-10 max-w-xl mx-auto">
            Специализирани в ремонт на iPhone – от смяна на дисплей до Face ID.
          </p>
          <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            {SERVICES.map((service) => {
              const Icon = serviceIcons[service.icon];
              return (
                <Link key={service.slug} to={`/${service.slug}`} className="card-service group">
                  <div className="h-12 w-12 rounded-lg bg-accent flex items-center justify-center mb-4 group-hover:bg-primary group-hover:text-primary-foreground transition-colors">
                    {Icon && <Icon className="h-6 w-6" />}
                  </div>
                  <h3 className="font-semibold mb-2">{service.name}</h3>
                  <p className="text-sm text-muted-foreground mb-3">{service.description}</p>
                  <p className="text-sm font-semibold text-primary">от {service.priceFrom} лв</p>
                </Link>
              );
            })}
          </div>
        </div>
      </section>

      {/* How it works */}
      <section className="py-16 bg-muted/50">
        <div className="container">
          <h2 className="text-2xl md:text-3xl font-bold text-center mb-4">Как работи?</h2>
          <p className="text-muted-foreground text-center mb-10 max-w-xl mx-auto">
            5 лесни стъпки от заявка до получаване на ремонтирано устройство.
          </p>
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

      {/* Why choose us */}
      <section className="py-16">
        <div className="container">
          <h2 className="text-2xl md:text-3xl font-bold text-center mb-4">
            Защо да изберете {BRAND}?
          </h2>
          <p className="text-muted-foreground text-center mb-10 max-w-xl mx-auto">
            Премиум услуга с гаранция на достъпни цени.
          </p>
          <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {WHY_US.map((item) => {
              const Icon = whyIcons[item.icon];
              return (
                <div key={item.title} className="card-service">
                  <div className="h-10 w-10 rounded-lg bg-accent flex items-center justify-center mb-3">
                    {Icon && <Icon className="h-5 w-5 text-accent-foreground" />}
                  </div>
                  <h3 className="font-semibold mb-1">{item.title}</h3>
                  <p className="text-sm text-muted-foreground">{item.desc}</p>
                </div>
              );
            })}
          </div>
        </div>
      </section>

      {/* Cities */}
      <section className="py-16 bg-muted/50">
        <div className="container">
          <h2 className="text-2xl md:text-3xl font-bold text-center mb-4">
            Ремонт на iPhone в цяла България
          </h2>
          <p className="text-muted-foreground text-center mb-10 max-w-xl mx-auto">
            Куриерска услуга в двете посоки – без значение къде се намирате.
          </p>
          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {CITIES.map((city) => (
              <Link key={city.slug} to={`/${city.slug}`} className="card-service text-center group">
                <h3 className="font-semibold mb-2 group-hover:text-primary transition-colors">
                  Ремонт на iPhone {city.name}
                </h3>
                <p className="text-sm text-muted-foreground">
                  Куриер до {city.name} и обратно
                </p>
                <span className="inline-flex items-center gap-1 text-sm text-primary mt-3">
                  Научи повече <ArrowRight className="h-4 w-4" />
                </span>
              </Link>
            ))}
          </div>
        </div>
      </section>

      {/* Pricing Teaser */}
      <section className="py-16">
        <div className="container text-center">
          <h2 className="text-2xl md:text-3xl font-bold mb-4">Ориентировъчни цени</h2>
          <p className="text-muted-foreground mb-8 max-w-xl mx-auto">
            Окончателната цена зависи от диагностиката. Безплатна диагностика при всеки ремонт.
          </p>
          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 max-w-4xl mx-auto">
            {SERVICES.map((s) => (
              <div key={s.slug} className="card-service text-center">
                <h3 className="font-semibold mb-2">{s.shortName}</h3>
                <p className="text-3xl font-bold text-primary mb-1">от {s.priceFrom} лв</p>
                <p className="text-xs text-muted-foreground">с гаранция до 12 мес.</p>
              </div>
            ))}
          </div>
          <Link to="/ceni" className="inline-block mt-8">
            <Button variant="outline" className="gap-2">Виж всички цени <ArrowRight className="h-4 w-4" /></Button>
          </Link>
        </div>
      </section>

      <FAQSection />

      <CTASection />
    </Layout>
  );
}
