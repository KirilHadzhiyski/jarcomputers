import Layout from "@/components/Layout";
import SEOHead from "@/components/SEOHead";
import { CTASection } from "@/components/CTA";
import { BRAND } from "@/lib/data";
import { Award, Users, Wrench, Shield, Truck, Heart } from "lucide-react";

const stats = [
  { icon: Award, label: "Години опит", value: "10+" },
  { icon: Users, label: "Доволни клиенти", value: "5000+" },
  { icon: Wrench, label: "Ремонтирани устройства", value: "5000+" },
  { icon: Shield, label: "Месеца гаранция", value: "до 12" },
];

export default function AboutPage() {
  return (
    <Layout>
      <SEOHead
        title={`За нас | ${BRAND}`}
        description={`${BRAND} – професионален сервиз за ремонт на iPhone с над 10 години опит. Гаранция, качествени части, куриер в цяла България.`}
      />

      <section className="hero-section py-16">
        <div className="container max-w-4xl text-center">
          <h1 className="text-3xl md:text-4xl font-bold mb-4">За {BRAND}</h1>
          <p className="text-lg text-hero-muted">Професионален ремонт на iPhone с гаранция и доверие.</p>
        </div>
      </section>

      <section className="py-16">
        <div className="container max-w-3xl">
          <p className="text-lg text-muted-foreground mb-6">
            {BRAND} е специализиран сервиз за ремонт на iPhone устройства, базиран в Благоевград. 
            С над 10 години опит в ремонта на мобилни устройства, ние предлагаме бързо и надеждно обслужване 
            за клиенти от цяла България.
          </p>
          <p className="text-muted-foreground mb-6">
            Нашата мисия е да направим професионалния ремонт на iPhone достъпен за всеки, 
            независимо от местоположението. Чрез нашата куриерска услуга обслужваме клиенти от 
            София, Пловдив, Варна, Бургас и всички останали градове в страната.
          </p>
          <p className="text-muted-foreground mb-12">
            Работим само с качествени части, предлагаме гаранция до 12 месеца и се отличаваме 
            с прозрачно ценообразуване – без скрити такси и допълнителни разходи.
          </p>

          <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            {stats.map((stat) => (
              <div key={stat.label} className="card-service text-center">
                <stat.icon className="h-8 w-8 text-primary mx-auto mb-3" />
                <p className="text-3xl font-bold text-primary">{stat.value}</p>
                <p className="text-sm text-muted-foreground">{stat.label}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="py-16 bg-muted/50">
        <div className="container max-w-3xl">
          <h2 className="text-2xl font-bold text-center mb-8">Нашите ценности</h2>
          <div className="grid gap-6 sm:grid-cols-3">
            <div className="card-service text-center">
              <Heart className="h-8 w-8 text-primary mx-auto mb-3" />
              <h3 className="font-semibold mb-2">Доверие</h3>
              <p className="text-sm text-muted-foreground">Прозрачност и честност във всеки ремонт.</p>
            </div>
            <div className="card-service text-center">
              <Wrench className="h-8 w-8 text-primary mx-auto mb-3" />
              <h3 className="font-semibold mb-2">Качество</h3>
              <p className="text-sm text-muted-foreground">Само качествени части и професионален ремонт.</p>
            </div>
            <div className="card-service text-center">
              <Truck className="h-8 w-8 text-primary mx-auto mb-3" />
              <h3 className="font-semibold mb-2">Достъпност</h3>
              <p className="text-sm text-muted-foreground">Куриер до всяка точка на България.</p>
            </div>
          </div>
        </div>
      </section>

      <CTASection />
    </Layout>
  );
}
