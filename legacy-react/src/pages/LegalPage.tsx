import { useLocation } from "react-router-dom";
import Layout from "@/components/Layout";
import SEOHead from "@/components/SEOHead";
import { BRAND, EMAIL } from "@/lib/data";

export default function LegalPage() {
  const { pathname } = useLocation();
  const isPrivacy = pathname.includes("poveritelnost");
  const title = isPrivacy ? "Политика за поверителност" : "Общи условия";

  return (
    <Layout>
      <SEOHead title={`${title} | ${BRAND}`} description={`${title} за сайта на ${BRAND}.`} />

      <section className="hero-section py-16">
        <div className="container max-w-4xl text-center">
          <h1 className="text-4xl font-bold md:text-5xl">{title}</h1>
        </div>
      </section>

      <section className="py-16">
        <div className="container max-w-3xl">
          <div className="card-service space-y-4 text-sm leading-7 text-muted-foreground">
            {isPrivacy ? (
              <>
                <p>
                  Данните, изпратени през формите за контакт и заявка за ремонт, се използват само за комуникация
                  по конкретното запитване, диагностика и организация на сервизната услуга.
                </p>
                <p>
                  За въпроси относно обработката на данни можете да се свържете с нас на{" "}
                  <a className="font-semibold text-primary underline underline-offset-4" href={`mailto:${EMAIL}`}>
                    {EMAIL}
                  </a>.
                </p>
              </>
            ) : (
              <>
                <p>
                  Цените в сайта са ориентировъчни и се потвърждават след диагностика на устройството.
                  Ремонт започва след одобрение от клиента.
                </p>
                <p>
                  Гаранцията покрива извършената работа и използваните части според конкретния ремонт и договорените условия.
                </p>
              </>
            )}
          </div>
        </div>
      </section>
    </Layout>
  );
}
