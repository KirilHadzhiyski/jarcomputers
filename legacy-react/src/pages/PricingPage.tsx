import Layout from "@/components/Layout";
import SEOHead from "@/components/SEOHead";
import { CTASection } from "@/components/CTA";
import { BRAND, PRICING_TABLE, SERVICES } from "@/lib/data";

export default function PricingPage() {
  return (
    <Layout>
      <SEOHead
        title={`Цени за ремонт на iPhone | ${BRAND}`}
        description={`Ориентировъчни цени за ремонт на iPhone от ${BRAND}. Смяна на дисплей, батерия, Face ID, камера. Безплатна диагностика.`}
      />

      <section className="hero-section py-16">
        <div className="container text-center">
          <h1 className="text-3xl md:text-4xl font-bold mb-4">Цени за ремонт на iPhone</h1>
          <p className="text-lg text-hero-muted max-w-2xl mx-auto">
            Ориентировъчни цени. Окончателната цена зависи от диагностиката. Безплатна диагностика при всеки ремонт.
          </p>
        </div>
      </section>

      <section className="py-16">
        <div className="container max-w-4xl">
          {/* Cards for mobile */}
          <div className="grid gap-6 sm:grid-cols-2 lg:hidden">
            {SERVICES.map((service) => (
              <div key={service.slug} className="card-service">
                <h3 className="font-semibold mb-4">{service.name}</h3>
                <div className="space-y-2 text-sm">
                  {PRICING_TABLE.filter((row) => row.service === service.name).map((row) => (
                    <div key={row.service}>
                      <div className="flex justify-between"><span>iPhone 11</span><span className="font-semibold text-primary">{row.iphone11}</span></div>
                      <div className="flex justify-between"><span>iPhone 12</span><span className="font-semibold text-primary">{row.iphone12}</span></div>
                      <div className="flex justify-between"><span>iPhone 13</span><span className="font-semibold text-primary">{row.iphone13}</span></div>
                      <div className="flex justify-between"><span>iPhone 14</span><span className="font-semibold text-primary">{row.iphone14}</span></div>
                    </div>
                  ))}
                </div>
              </div>
            ))}
          </div>

          {/* Table for desktop */}
          <div className="hidden lg:block rounded-xl border bg-card overflow-hidden">
            <table className="w-full">
              <thead>
                <tr className="border-b bg-muted/50">
                  <th className="px-6 py-4 text-left font-semibold">Услуга</th>
                  <th className="px-6 py-4 text-center font-semibold">iPhone 11</th>
                  <th className="px-6 py-4 text-center font-semibold">iPhone 12</th>
                  <th className="px-6 py-4 text-center font-semibold">iPhone 13</th>
                  <th className="px-6 py-4 text-center font-semibold">iPhone 14</th>
                </tr>
              </thead>
              <tbody>
                {PRICING_TABLE.map((row, i) => (
                  <tr key={i} className="border-b last:border-0">
                    <td className="px-6 py-4 font-medium">{row.service}</td>
                    <td className="px-6 py-4 text-center text-primary font-semibold">{row.iphone11}</td>
                    <td className="px-6 py-4 text-center text-primary font-semibold">{row.iphone12}</td>
                    <td className="px-6 py-4 text-center text-primary font-semibold">{row.iphone13}</td>
                    <td className="px-6 py-4 text-center text-primary font-semibold">{row.iphone14}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          <div className="mt-8 card-service text-center">
            <p className="text-sm text-muted-foreground">
              * Цените са ориентировъчни. Окончателната цена се определя след безплатна диагностика.
              <br />Всички ремонти идват с гаранция до 12 месеца.
              <br />-10% отстъпка при онлайн поръчка.
            </p>
          </div>
        </div>
      </section>

      <CTASection />
    </Layout>
  );
}
