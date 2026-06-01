import { Link } from "react-router-dom";
import {
  ADDRESS,
  BRAND,
  EMAIL,
  LANDLINE,
  LANDLINE_HREF,
  PHONE,
  PHONE_HREF,
  SERVICES,
  SOCIALS,
} from "@/lib/data";
import logo from "@/assets/jar-computers-logo-blue.svg";

export default function Footer() {
  return (
    <footer className="section-dark mt-16 border-t border-white/10">
      <div className="container py-14">
        <div className="grid gap-10 lg:grid-cols-4">
          <div className="space-y-4">
            <div>
              <Link to="/" className="inline-flex items-center" aria-label={BRAND}>
                <img src={logo} alt={BRAND} className="h-auto w-full max-w-[13.5rem]" />
              </Link>
              <p className="mt-3 text-sm leading-7 text-slate-300">
                Професионален ремонт на iPhone с гаранция, проследима комуникация и куриерска услуга в цяла България.
              </p>
            </div>
            <div className="space-y-2 text-sm text-slate-300">
              <a className="block hover:text-white" href={`tel:${PHONE_HREF}`}>{PHONE}</a>
              <a className="block hover:text-white" href={`tel:${LANDLINE_HREF}`}>{LANDLINE}</a>
              <a className="block hover:text-white" href={`mailto:${EMAIL}`}>{EMAIL}</a>
              <p>{ADDRESS}</p>
            </div>
          </div>

          <div>
            <p className="mb-4 text-sm font-semibold uppercase tracking-[0.2em] text-slate-400">Услуги</p>
            <div className="space-y-2 text-sm text-slate-300">
              <Link className="block hover:text-white" to="/remont-iphone">Ремонт на iPhone</Link>
              {SERVICES.map((service) => (
                <Link key={service.slug} className="block hover:text-white" to={`/${service.slug}`}>
                  {service.name}
                </Link>
              ))}
            </div>
          </div>

          <div>
            <p className="mb-4 text-sm font-semibold uppercase tracking-[0.2em] text-slate-400">Социални мрежи</p>
            <div className="space-y-2 text-sm text-slate-300">
              {SOCIALS.map((social) => (
                <a key={social.key} className="block hover:text-white" href={social.href} target="_blank" rel="noreferrer">
                  {social.label}
                </a>
              ))}
            </div>
          </div>

          <div>
            <p className="mb-4 text-sm font-semibold uppercase tracking-[0.2em] text-slate-400">Информация</p>
            <div className="space-y-2 text-sm text-slate-300">
              <Link className="block hover:text-white" to="/za-nas">За нас</Link>
              <Link className="block hover:text-white" to="/chzv">Често задавани въпроси</Link>
              <Link className="block hover:text-white" to="/ceni">Цени</Link>
              <Link className="block hover:text-white" to="/kontakti">Контакти</Link>
              <Link className="block hover:text-white" to="/politika-za-poveritelnost">Политика за поверителност</Link>
              <Link className="block hover:text-white" to="/obshti-usloviya">Общи условия</Link>
            </div>
          </div>
        </div>

        <div className="mt-12 flex flex-col gap-3 border-t border-white/10 pt-6 text-sm text-slate-400 sm:flex-row sm:items-center sm:justify-between">
          <p>© {new Date().getFullYear()} {BRAND}. Всички права запазени.</p>
          <p>Физически обект в Благоевград · Куриерска услуга за цяла България</p>
        </div>
      </div>
    </footer>
  );
}
