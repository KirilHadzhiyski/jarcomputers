import Layout from "@/components/Layout";
import SEOHead from "@/components/SEOHead";
import { CTASection } from "@/components/CTA";
import { BRAND, MODELS, PRICING_TABLE, SERVICES } from "@/lib/data";

export default function PricingPage() {
  const modelColumns = MODELS.filter((model) => ["11", "12", "13", "14", "15", "16"].includes(model.series)).reverse();

  return (
    <Layout>
      <SEOHead
        title={`Цени за ремонт на iPhone | ${BRAND}`}
        description={`Ориентировъчни цени в евро за ремонт на iPhone 11 до iPhone 16 от ${BRAND}. Окончателната цена се потвърждава след безплатна диагностика.`}
      />

      <section className="hero-section py-16">
        <div className="container max-w-4xl text-center">
          <h1 className="text-3xl md:text-5xl font-bold mb-4">Цени за ремонт на iPhone</h1>
          <p className="text-lg leading-8 text-hero-muted max-w-3xl mx-auto">
            Ориентировъчни цени в евро за най-честите ремонти на iPhone 11 до iPhone 16.
            Окончателната цена се потвърждава след безплатна диагностика.
          </p>
        </div>
      </section>

      <section className="py-16">
        <div className="container max-w-7xl">
          <div className="grid gap-6 lg:hidden">
            {SERVICES.map((service) => {
              const row = PRICING_TABLE.find((priceRow) => priceRow.service === service.name);

              return (
                <div key={service.slug} className="card-service">
                  <h3 className="text-xl font-semibold text-foreground">{service.name}</h3>
                  <div className="mt-5 space-y-3 text-sm">
                    {modelColumns.map((model) => {
                      const priceKey = `iphone${model.series}` as keyof typeof PRICING_TABLE[number];
                      return (
                        <div key={model.slug} className="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                          <span>{model.name}</span>
                          <span className="font-semibold text-primary">{row?.[priceKey]}</span>
                        </div>
                      );
                    })}
                  </div>
                </div>
              );
            })}
          </div>

          <div className="hidden overflow-hidden rounded-[2rem] border bg-white shadow-[0_24px_55px_-32px_rgba(15,23,42,0.35)] lg:block">
            <table className="w-full text-sm">
              <thead className="bg-slate-50 text-slate-700">
                <tr>
                  <th className="px-6 py-4 text-left font-semibold">Услуга</th>
                  {modelColumns.map((model) => (
                    <th key={model.slug} className="px-6 py-4 text-center font-semibold">
                      {model.name}
                    </th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {PRICING_TABLE.map((row) => (
                  <tr key={row.service} className="border-t border-slate-100">
                    <td className="px-6 py-4 font-semibold text-foreground">{row.service}</td>
                    {modelColumns.map((model) => {
                      const priceKey = `iphone${model.series}` as keyof typeof row;
                      return (
                        <td key={model.slug} className="px-6 py-4 text-center font-semibold text-primary">
                          {row[priceKey]}
                        </td>
                      );
                    })}
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          <div className="card-soft mt-8 text-center text-sm leading-7 text-muted-foreground">
            <p>
              * Цените са ориентировъчни и са изписани в евро.
              <br />Окончателната цена се определя след безплатна диагностика.
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
