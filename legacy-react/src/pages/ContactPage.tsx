import Layout from "@/components/Layout";
import SEOHead from "@/components/SEOHead";
import RepairForm from "@/components/RepairForm";
import { BRAND, PHONE, EMAIL, ADDRESS } from "@/lib/data";
import { Phone as PhoneIcon, Mail, MapPin, MessageCircle } from "lucide-react";

export default function ContactPage() {
  return (
    <Layout>
      <SEOHead
        title={`Контакти | ${BRAND}`}
        description={`Свържете се с ${BRAND} за ремонт на iPhone. Телефон, имейл, онлайн заявка. Куриерска услуга в цяла България.`}
      />

      <section className="hero-section py-16">
        <div className="container max-w-4xl text-center">
          <h1 className="text-3xl md:text-4xl font-bold mb-4">Свържете се с нас</h1>
          <p className="text-lg text-hero-muted">Готови сме да помогнем с ремонта на вашия iPhone.</p>
        </div>
      </section>

      <section className="py-16">
        <div className="container">
          <div className="grid gap-12 lg:grid-cols-2">
            <div>
              <h2 className="text-2xl font-bold mb-6">Заявка за ремонт</h2>
              <RepairForm />
            </div>

            <div>
              <h2 className="text-2xl font-bold mb-6">Контактна информация</h2>
              <div className="space-y-6">
                <a href={`tel:${PHONE}`} className="card-service flex items-center gap-4">
                  <div className="h-12 w-12 rounded-lg bg-accent flex items-center justify-center shrink-0">
                    <PhoneIcon className="h-6 w-6 text-accent-foreground" />
                  </div>
                  <div>
                    <p className="font-semibold">Телефон</p>
                    <p className="text-primary">{PHONE}</p>
                  </div>
                </a>

                <a href={`mailto:${EMAIL}`} className="card-service flex items-center gap-4">
                  <div className="h-12 w-12 rounded-lg bg-accent flex items-center justify-center shrink-0">
                    <Mail className="h-6 w-6 text-accent-foreground" />
                  </div>
                  <div>
                    <p className="font-semibold">Имейл</p>
                    <p className="text-primary">{EMAIL}</p>
                  </div>
                </a>

                <div className="card-service flex items-center gap-4">
                  <div className="h-12 w-12 rounded-lg bg-accent flex items-center justify-center shrink-0">
                    <MapPin className="h-6 w-6 text-accent-foreground" />
                  </div>
                  <div>
                    <p className="font-semibold">Адрес</p>
                    <p className="text-muted-foreground">{ADDRESS}</p>
                  </div>
                </div>

                <div className="card-service">
                  <p className="font-semibold mb-3">Свържете се чрез</p>
                  <div className="flex gap-3">
                    <span className="inline-flex items-center gap-2 rounded-full bg-accent px-4 py-2 text-sm font-medium text-accent-foreground">
                      <MessageCircle className="h-4 w-4" /> Viber
                    </span>
                    <span className="inline-flex items-center gap-2 rounded-full bg-accent px-4 py-2 text-sm font-medium text-accent-foreground">
                      <MessageCircle className="h-4 w-4" /> WhatsApp
                    </span>
                  </div>
                </div>

                <div className="card-service">
                  <p className="font-semibold mb-3">Работно време</p>
                  <p className="text-sm text-muted-foreground">Понеделник – Петък: 09:00 – 18:00</p>
                  <p className="text-sm text-muted-foreground">Събота: 10:00 – 14:00</p>
                  <p className="text-sm text-muted-foreground">Неделя: Затворено</p>
                </div>

                {/* Map placeholder */}
                <div className="rounded-xl border bg-muted h-64 flex items-center justify-center">
                  <p className="text-muted-foreground text-sm">Google Maps – {ADDRESS}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </Layout>
  );
}
