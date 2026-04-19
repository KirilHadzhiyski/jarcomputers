import { Link } from "react-router-dom";
import { BRAND, PHONE } from "@/lib/data";
import { Button } from "@/components/ui/button";
import { Phone, Menu, X } from "lucide-react";
import { useState } from "react";
import logo from "@/assets/logo.png";

const NAV_ITEMS = [
  { label: "Начало", href: "/" },
  { label: "Услуги", href: "/remont-iphone" },
  { label: "Модели", href: "/remont-iphone-11" },
  { label: "Градове", href: "/remont-iphone-sofia" },
  { label: "Цени", href: "/ceni" },
  { label: "За нас", href: "/za-nas" },
  { label: "ЧЗВ", href: "/chzv" },
  { label: "Контакти", href: "/kontakti" },
];

export default function Header() {
  const [open, setOpen] = useState(false);

  return (
    <header className="sticky top-0 z-50 border-b bg-card/95 backdrop-blur supports-[backdrop-filter]:bg-card/80">
      <div className="container flex h-16 items-center justify-between">
        <Link to="/" className="flex items-center gap-2 font-bold text-lg">
          <img src={logo} alt="JAR Computers" className="h-10 w-auto" />
          <span className="hidden sm:inline text-foreground">Благоевград</span>
        </Link>

        <nav className="hidden lg:flex items-center gap-1">
          {NAV_ITEMS.map((item) => (
            <Link
              key={item.href}
              to={item.href}
              className="px-3 py-2 text-sm font-medium text-muted-foreground hover:text-foreground transition-colors rounded-md hover:bg-accent"
            >
              {item.label}
            </Link>
          ))}
        </nav>

        <div className="flex items-center gap-2">
          <a href={`tel:${PHONE}`} className="hidden sm:flex">
            <Button variant="outline" size="sm" className="gap-2">
              <Phone className="h-4 w-4" />
              <span className="hidden md:inline">{PHONE}</span>
            </Button>
          </a>
          <Link to="/kontakti">
            <Button variant="cta" size="sm">Поръчай ремонт</Button>
          </Link>
          <button
            className="lg:hidden p-2 text-foreground"
            onClick={() => setOpen(!open)}
            aria-label="Меню"
          >
            {open ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
          </button>
        </div>
      </div>

      {open && (
        <div className="lg:hidden border-t bg-card pb-4">
          <nav className="container flex flex-col gap-1 pt-2">
            {NAV_ITEMS.map((item) => (
              <Link
                key={item.href}
                to={item.href}
                onClick={() => setOpen(false)}
                className="px-3 py-2 text-sm font-medium text-muted-foreground hover:text-foreground transition-colors rounded-md hover:bg-accent"
              >
                {item.label}
              </Link>
            ))}
            <a href={`tel:${PHONE}`} className="sm:hidden px-3 py-2 text-sm font-medium text-primary">
              <Phone className="h-4 w-4 inline mr-2" />{PHONE}
            </a>
          </nav>
        </div>
      )}
    </header>
  );
}
