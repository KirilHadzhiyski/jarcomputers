import { Link, useLocation } from "react-router-dom";
import { BRAND, NAV_ITEMS, PHONE, PHONE_HREF } from "@/lib/data";
import { Button } from "@/components/ui/button";
import { MoreHorizontal, Phone, X } from "lucide-react";
import { useState } from "react";
import logo from "@/assets/jar-computers-logo-blue.svg";

export default function Header() {
  const [open, setOpen] = useState(false);
  const { pathname } = useLocation();

  const isActive = (href: string) => (href === "/" ? pathname === "/" : pathname === href);

  return (
    <header className="sticky top-0 z-50 border-b border-primary/10 bg-background/90 shadow-sm backdrop-blur supports-[backdrop-filter]:bg-background/75">
      <div className="container flex min-h-16 items-center justify-between gap-4 py-3">
        <Link to="/" className="inline-flex shrink-0 items-center" aria-label={BRAND}>
          <img src={logo} alt={BRAND} className="h-auto w-[9.5rem] sm:w-[11rem] lg:w-[12.75rem]" />
        </Link>

        <nav className="hidden lg:flex items-center gap-1">
          {NAV_ITEMS.map((item) => (
            <Link
              key={item.href}
              to={item.href}
              className={`rounded-md px-3 py-2 text-sm font-medium transition-colors hover:text-primary ${
                isActive(item.href)
                  ? "bg-primary/10 text-primary"
                  : "text-muted-foreground"
              }`}
            >
              {item.label}
            </Link>
          ))}
        </nav>

        <div className="flex shrink-0 items-center gap-3">
          <a href={`tel:${PHONE_HREF}`} className="hidden sm:flex">
            <Button variant="hero-outline" size="sm" className="rounded-full px-4">
              <Phone className="h-4 w-4" />
              <span>{PHONE}</span>
            </Button>
          </a>
          <Link to="/zaqvka_za_remont" className="hidden md:block">
            <Button variant="cta" size="sm" className="rounded-full px-5">
              Поръчай ремонт
            </Button>
          </Link>
          <button
            className="lg:hidden inline-flex h-11 w-11 items-center justify-center rounded-full border border-primary/20 bg-white text-primary shadow-sm transition hover:-translate-y-0.5 hover:bg-accent"
            onClick={() => setOpen(!open)}
            aria-label="Меню"
          >
            {open ? <X className="h-5 w-5" /> : <MoreHorizontal className="h-6 w-6" />}
          </button>
        </div>
      </div>

      {open && (
        <div className="lg:hidden border-t bg-background pb-4 shadow-lg">
          <nav className="container flex flex-col gap-2 pt-4">
            {NAV_ITEMS.map((item) => (
              <Link
                key={item.href}
                to={item.href}
                onClick={() => setOpen(false)}
                className={`justify-center rounded-md px-3 py-2 text-center text-sm font-medium transition-colors hover:text-primary ${
                  isActive(item.href)
                    ? "bg-primary/10 text-primary"
                    : "text-muted-foreground"
                }`}
              >
                {item.label}
              </Link>
            ))}
            <a href={`tel:${PHONE_HREF}`} className="mt-2 inline-flex justify-center rounded-md border border-primary/20 bg-background px-5 py-2.5 text-sm font-medium text-primary shadow-sm">
              {PHONE}
            </a>
          </nav>
        </div>
      )}
    </header>
  );
}
