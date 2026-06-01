import Layout from "@/components/Layout";
import SEOHead from "@/components/SEOHead";
import RepairForm from "@/components/RepairForm";
import { BRAND } from "@/lib/data";

export default function RepairRequestPage() {
  return (
    <Layout>
      <SEOHead
        title={`Заявка за ремонт | ${BRAND}`}
        description={`Онлайн заявка за ремонт на iPhone от ${BRAND}. Безплатна диагностика, куриер в двете посоки и гаранция до 12 месеца.`}
      />

      <section className="hero-section py-16">
        <div className="container max-w-4xl text-center">
          <h1 className="text-4xl font-bold md:text-5xl">Заявка за ремонт</h1>
        </div>
      </section>

      <section className="py-16">
        <div className="container max-w-3xl">
          <RepairForm sourcePage="/zaqvka_za_remont" />
        </div>
      </section>
    </Layout>
  );
}
