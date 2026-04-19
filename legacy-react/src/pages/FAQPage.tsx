import Layout from "@/components/Layout";
import SEOHead from "@/components/SEOHead";
import FAQSection from "@/components/FAQSection";
import { CTASection } from "@/components/CTA";
import { BRAND, FAQ_HOME } from "@/lib/data";

const extendedFaq = [
  ...FAQ_HOME,
  { q: "Какви модели iPhone ремонтирате?", a: "Ремонтираме всички модели iPhone – от iPhone 6 до най-новите модели. Специализирани сме в iPhone 11, 12, 13 и 14." },
  { q: "Мога ли да следя статуса на ремонта?", a: "Да, ще ви уведомяваме на всяка стъпка – от получаването на устройството до неговото изпращане обратно." },
  { q: "Имате ли физически магазин?", a: `Да, нашият сервиз се намира в Благоевград. Можете да ни посетите лично или да използвате куриерската ни услуга.` },
  { q: "Колко е отстъпката при онлайн поръчка?", a: "При онлайн поръчка получавате 10% отстъпка от стойността на ремонта." },
  { q: "Какво се случва, ако повредата е неремонтируема?", a: "Ако установим, че устройството не може да бъде ремонтирано, ще ви уведомим и ще го върнем безплатно." },
];

export default function FAQPage() {
  return (
    <Layout>
      <SEOHead
        title={`Често задавани въпроси | ${BRAND}`}
        description={`Отговори на често задавани въпроси за ремонт на iPhone от ${BRAND}. Куриер, гаранция, цени, процес.`}
      />

      <section className="hero-section py-16">
        <div className="container text-center">
          <h1 className="text-3xl md:text-4xl font-bold mb-4">Често задавани въпроси</h1>
          <p className="text-lg text-hero-muted">Всичко, което трябва да знаете за нашите услуги.</p>
        </div>
      </section>

      <FAQSection items={extendedFaq} title="" />

      <CTASection />
    </Layout>
  );
}
