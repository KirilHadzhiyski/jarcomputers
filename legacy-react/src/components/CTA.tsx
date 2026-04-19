import { Link } from "react-router-dom";
import { Button } from "@/components/ui/button";
import { MessageCircle, Phone } from "lucide-react";
import { PHONE } from "@/lib/data";

export function FloatingCTA() {
  return (
    <div className="fixed bottom-4 right-4 z-50 flex flex-col gap-2">
      <a
        href={`tel:${PHONE}`}
        className="floating-cta flex items-center justify-center h-14 w-14 rounded-full"
        aria-label="Обади се"
      >
        <Phone className="h-6 w-6" />
      </a>
      <Link
        to="/kontakti"
        className="floating-cta flex items-center justify-center h-14 w-14 rounded-full"
        aria-label="Пиши ни"
      >
        <MessageCircle className="h-6 w-6" />
      </Link>
    </div>
  );
}

export function CTASection({ title, subtitle }: { title?: string; subtitle?: string }) {
  return (
    <section className="hero-section py-16">
      <div className="container text-center">
        <h2 className="text-2xl md:text-3xl font-bold mb-4">
          {title || "Поръчай ремонт още днес с JAR Computers Благоевград"}
        </h2>
        <p className="text-hero-muted mb-8 max-w-2xl mx-auto">
          {subtitle || "Безплатна диагностика, куриер в двете посоки и гаранция до 12 месеца."}
        </p>
        <div className="flex flex-col sm:flex-row gap-4 justify-center">
          <Link to="/kontakti">
            <Button variant="hero" size="lg">Поръчай ремонт</Button>
          </Link>
          <a href={`tel:${PHONE}`}>
            <Button variant="hero-outline" size="lg" className="gap-2">
              <Phone className="h-5 w-5" />Обади се
            </Button>
          </a>
        </div>
      </div>
    </section>
  );
}
