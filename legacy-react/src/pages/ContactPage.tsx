import Layout from "@/components/Layout";
import SEOHead from "@/components/SEOHead";
import {
  ADDRESS,
  AGGREGATE_REVIEW,
  BRAND,
  EMAIL,
  GOOGLE_MAPS_URL,
  HOURS,
  LANDLINE,
  LANDLINE_HREF,
  PHONE,
  PHONE_E164,
  PHONE_HREF,
  REVIEW_PLATFORMS,
  SOCIALS,
} from "@/lib/data";
import { Clock, ExternalLink, Instagram, Mail, MapPin, MessageCircle, Music2, Phone as PhoneIcon, Star } from "lucide-react";

export default function ContactPage() {
  const googleReview = REVIEW_PLATFORMS.find((platform) => platform.key === "google-maps");
  const viberLink = `viber://chat?number=${encodeURIComponent(PHONE_E164)}`;
  const contactCards = [
    { label: "МОБИЛЕН ТЕЛЕФОН", value: PHONE, href: `tel:${PHONE_HREF}`, icon: PhoneIcon },
    { label: "СТАЦИОНАРЕН ТЕЛЕФОН", value: LANDLINE, href: `tel:${LANDLINE_HREF}`, icon: PhoneIcon },
    { label: "ИМЕЙЛ", value: EMAIL, href: `mailto:${EMAIL}`, icon: Mail },
  ];

  return (
    <Layout>
      <SEOHead
        title={`Контакти | ${BRAND}`}
        description={`Контактна информация за сервиза на ${BRAND} в Благоевград. Телефон, имейл, Viber, социални мрежи и адрес.`}
      />

      <section className="hero-section py-16 md:py-20">
        <div className="container max-w-4xl text-center">
          <h1 className="text-4xl font-extrabold md:text-5xl">Контакти</h1>
          <p className="mx-auto mt-4 max-w-2xl text-lg leading-8 text-hero-muted">
            Контактна информация за сервиза на {BRAND} в Благоевград.
          </p>
        </div>
      </section>

      <section className="bg-slate-50 py-16 md:py-20">
        <div className="container">
          <div className="mx-auto mb-10 max-w-3xl text-center">
            <h2 className="text-3xl font-extrabold md:text-4xl">Контактна информация</h2>
          </div>

          <div className="grid gap-4 md:grid-cols-3">
            {contactCards.map(({ label, value, href, icon: Icon }) => (
              <a key={label} href={href} className="card-service group min-h-40 text-center">
                <div className="mx-auto mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-accent text-accent-foreground transition group-hover:bg-primary group-hover:text-primary-foreground">
                  <Icon className="h-6 w-6" />
                </div>
                <p className="text-xs font-extrabold uppercase tracking-[0.18em] text-muted-foreground">{label}</p>
                <p className="mt-3 text-xl font-extrabold text-primary">{value}</p>
              </a>
            ))}
          </div>

          <div className="mt-6 grid gap-6 lg:grid-cols-[0.95fr_1.05fr]">
            <div className="space-y-6">
              <div className="card-service">
                <div className="flex items-start gap-4">
                  <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-accent text-accent-foreground">
                    <MessageCircle className="h-6 w-6" />
                  </div>
                  <div>
                    <p className="text-xs font-extrabold uppercase tracking-[0.18em] text-muted-foreground">Viber чат</p>
                    <h3 className="mt-2 text-xl font-extrabold">Пишете ни директно във Viber</h3>
                    <p className="mt-2 leading-7 text-muted-foreground">
                      Най-бързият начин да изпратите снимка на проблема, модел на телефона и въпрос за ориентировъчна цена.
                    </p>
                  </div>
                </div>
                <div className="mt-6 flex flex-wrap gap-3">
                  <a href={viberLink} className="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground shadow-sm">
                    Viber mobile
                  </a>
                  <a href={viberLink} className="rounded-lg border border-primary/20 bg-white px-4 py-2 text-sm font-semibold text-primary">
                    Viber desktop
                  </a>
                  <a
                    href="https://www.viber.com/en/download/"
                    target="_blank"
                    rel="noreferrer"
                    className="inline-flex items-center gap-2 rounded-lg border bg-white px-4 py-2 text-sm font-semibold text-muted-foreground hover:text-primary"
                  >
                    Viber download
                    <ExternalLink className="h-4 w-4" />
                  </a>
                </div>
              </div>

              <div className="card-service">
                <div className="flex items-start gap-4">
                  <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-accent text-accent-foreground">
                    <MapPin className="h-6 w-6" />
                  </div>
                  <div>
                    <p className="text-xs font-extrabold uppercase tracking-[0.18em] text-muted-foreground">Адрес</p>
                    <h3 className="mt-2 text-xl font-extrabold">{ADDRESS}</h3>
                    <a
                      href={GOOGLE_MAPS_URL}
                      target="_blank"
                      rel="noreferrer"
                      className="mt-4 inline-flex items-center gap-2 rounded-lg border border-primary/20 bg-white px-4 py-2 text-sm font-semibold text-primary"
                    >
                      Отвори в Google Maps
                      <ExternalLink className="h-4 w-4" />
                    </a>
                  </div>
                </div>
              </div>

              <div className="card-service">
                <div className="flex items-start gap-4">
                  <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-accent text-accent-foreground">
                    <Clock className="h-6 w-6" />
                  </div>
                  <div>
                    <p className="text-xs font-extrabold uppercase tracking-[0.18em] text-muted-foreground">Работно време</p>
                    <div className="mt-3 space-y-2">
                      {HOURS.map((line) => (
                        <p key={line} className="text-sm font-medium text-muted-foreground">
                          {line}
                        </p>
                      ))}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div className="card-service">
              <p className="text-xs font-extrabold uppercase tracking-[0.18em] text-muted-foreground">Социални мрежи</p>
              <h3 className="mt-2 text-2xl font-extrabold">Последвайте {BRAND}</h3>
              <p className="mt-3 leading-7 text-muted-foreground">
                Следете актуални ремонти, промоции и реални сигнали от клиенти в публичните ни канали.
              </p>

              <div className="mt-6 rounded-xl border bg-slate-50 p-5">
                <div className="flex items-center gap-3">
                  <div className="flex h-11 w-11 items-center justify-center rounded-xl bg-primary text-primary-foreground">
                    <Star className="h-5 w-5" />
                  </div>
                  <div>
                    <p className="text-xs font-extrabold uppercase tracking-[0.18em] text-muted-foreground">Рейтинг и отзиви</p>
                    <p className="text-3xl font-extrabold text-primary">
                      {AGGREGATE_REVIEW.ratingValue}/{AGGREGATE_REVIEW.ratingScale}
                    </p>
                  </div>
                </div>
                <p className="mt-4 text-sm leading-7 text-muted-foreground">
                  {AGGREGATE_REVIEW.reviewsCount} комбинирани оценки от публичните платформи.
                  {googleReview ? ` Google: ${googleReview.ratingValue}/${googleReview.ratingScale}.` : ""}
                </p>
              </div>

              <div className="mt-6 grid gap-3 sm:grid-cols-3 lg:grid-cols-1 xl:grid-cols-3">
                {SOCIALS.map((social) => (
                  <a
                    key={social.key}
                    href={social.href}
                    target="_blank"
                    rel="noreferrer"
                    className="rounded-xl border bg-white p-4 text-center shadow-sm transition hover:-translate-y-0.5 hover:border-primary/30 hover:shadow-md"
                  >
                    <div className="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-slate-950 text-white">
                      {social.key === "facebook" && <span className="text-2xl font-extrabold">f</span>}
                      {social.key === "instagram" && <Instagram className="h-6 w-6" />}
                      {social.key === "tiktok" && <Music2 className="h-6 w-6" />}
                    </div>
                    <p className="font-semibold">{social.label}</p>
                  </a>
                ))}
              </div>
            </div>
          </div>
        </div>
      </section>
    </Layout>
  );
}
