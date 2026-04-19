import { FAQ_HOME } from "@/lib/data";
import {
  Accordion,
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from "@/components/ui/accordion";

interface FAQSectionProps {
  items?: readonly { q: string; a: string }[];
  title?: string;
}

export default function FAQSection({ items = FAQ_HOME, title = "Често задавани въпроси" }: FAQSectionProps) {
  return (
    <section className="py-16">
      <div className="container max-w-3xl">
        <h2 className="text-2xl md:text-3xl font-bold text-center mb-8">{title}</h2>
        <Accordion type="single" collapsible className="space-y-2">
          {items.map((item, i) => (
            <AccordionItem key={i} value={`faq-${i}`} className="border rounded-lg px-4 bg-card">
              <AccordionTrigger className="text-left font-medium">{item.q}</AccordionTrigger>
              <AccordionContent className="text-muted-foreground">{item.a}</AccordionContent>
            </AccordionItem>
          ))}
        </Accordion>
      </div>
    </section>
  );
}
