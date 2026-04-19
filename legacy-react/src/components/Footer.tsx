import { Link } from "react-router-dom";
import { BRAND, PHONE, EMAIL, ADDRESS, SERVICES, CITIES } from "@/lib/data";
import { Phone, Mail, MapPin } from "lucide-react";

export default function Footer() {
  return (
    <footer className="section-dark border-t">
      <div className="container py-12">
        <div className="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
          <div>
            <h3 className="text-lg font-bold mb-4">{BRAND}</h3>
            <p className="text-sm text-section-dark-foreground/70 mb-4">
              Професионален ремонт на iPhone с гаранция и куриерска услуга в цяла България.
            </p>
            <div className="flex flex-col gap-2 text-sm">
              <a href={`tel:${PHONE}`} className="flex items-center gap-2 text-section-dark-foreground/70 hover:text-primary transition-colors">
                <Phone className="h-4 w-4" />{PHONE}
              </a>
              <a href={`mailto:${EMAIL}`} className="flex items-center gap-2 text-section-dark-foreground/70 hover:text-primary transition-colors">
                <Mail className="h-4 w-4" />{EMAIL}
              </a>
              <span className="flex items-center gap-2 text-section-dark-foreground/70">
                <MapPin className="h-4 w-4" />{ADDRESS}
              </span>
            </div>
          </div>

          <div>
            <h4 className="font-semibold mb-4">Услуги</h4>
            <ul className="space-y-2 text-sm">
              <li><Link to="/remont-iphone" className="text-section-dark-foreground/70 hover:text-primary transition-colors">Ремонт на iPhone</Link></li>
              {SERVICES.map((s) => (
                <li key={s.slug}><Link to={`/${s.slug}`} className="text-section-dark-foreground/70 hover:text-primary transition-colors">{s.name}</Link></li>
              ))}
            </ul>
          </div>

          <div>
            <h4 className="font-semibold mb-4">Градове</h4>
            <ul className="space-y-2 text-sm">
              {CITIES.map((c) => (
                <li key={c.slug}><Link to={`/${c.slug}`} className="text-section-dark-foreground/70 hover:text-primary transition-colors">Ремонт iPhone {c.name}</Link></li>
              ))}
            </ul>
          </div>

          <div>
            <h4 className="font-semibold mb-4">Информация</h4>
            <ul className="space-y-2 text-sm">
              <li><Link to="/za-nas" className="text-section-dark-foreground/70 hover:text-primary transition-colors">За нас</Link></li>
              <li><Link to="/chzv" className="text-section-dark-foreground/70 hover:text-primary transition-colors">Често задавани въпроси</Link></li>
              <li><Link to="/ceni" className="text-section-dark-foreground/70 hover:text-primary transition-colors">Цени</Link></li>
              <li><Link to="/kontakti" className="text-section-dark-foreground/70 hover:text-primary transition-colors">Контакти</Link></li>
            </ul>
          </div>
        </div>

        <div className="mt-12 pt-8 border-t border-section-dark-foreground/10 flex flex-col sm:flex-row justify-between items-center gap-4 text-sm text-section-dark-foreground/50">
          <p>© {new Date().getFullYear()} {BRAND}. Всички права запазени.</p>
          <p>Куриерска услуга за ремонт на iPhone в цяла България</p>
        </div>
      </div>
    </footer>
  );
}
