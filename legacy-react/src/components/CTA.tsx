import { Link } from "react-router-dom";
import { Button } from "@/components/ui/button";
import { MessageCircle, Phone } from "lucide-react";
import { PHONE_HREF } from "@/lib/data";

export function FloatingCTA() {
  return (
    <div className="fixed bottom-4 right-4 z-50 flex flex-col gap-2">
      <Link
        to="/zaqvka_za_remont"
        className="floating-cta flex h-14 w-14 items-center justify-center rounded-full"
        aria-label="Пиши ни"
      >
        <MessageCircle className="h-6 w-6" />
      </Link>
    </div>
  );
}

export function CTASection({ title, subtitle }: { title?: string; subtitle?: string }) {
  return (
    <section className="hero-section py-14 md:py-16">
      <div className="container text-center">
        <h2 className="text-2xl md:text-3xl font-extrabold leading-tight mb-3 text-foreground">
          {title || "Поръчай ремонт още днес с JAR Computers Благоевград"}
        </h2>
        <div className="section-rule mb-6" />
        <p className="text-muted-foreground mb-8 max-w-2xl mx-auto">
          {subtitle || "Безплатна диагностика, куриер в двете посоки и гаранция до 12 месеца."}
        </p>
        <div className="flex flex-col sm:flex-row gap-4 justify-center">
          <Link to="/zaqvka_za_remont">
            <Button variant="hero" size="lg" className="w-full sm:w-auto rounded-lg px-9">
              Поръчай ремонт
            </Button>
          </Link>
          <a href={`tel:${PHONE_HREF}`}>
            <Button variant="hero-outline" size="lg" className="w-full sm:w-auto gap-2 rounded-lg px-9">
              <Phone className="h-5 w-5" />
              Обади се
            </Button>
          </a>
        </div>
      </div>
    </section>
  );
}
