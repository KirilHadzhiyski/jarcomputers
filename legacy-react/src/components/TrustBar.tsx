import { TRUST_ITEMS } from "@/lib/data";
import { Star, Shield, Truck, Zap } from "lucide-react";

const iconMap: Record<string, React.FC<{ className?: string }>> = {
  Star, Shield, Truck, Zap,
};

export default function TrustBar() {
  return (
    <section className="border-b bg-card py-4">
      <div className="container">
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
          {TRUST_ITEMS.map((item) => {
            const Icon = iconMap[item.icon];
            return (
              <div key={item.text} className="trust-badge justify-center text-center">
                {Icon && <Icon className="h-5 w-5 shrink-0" />}
                <span>{item.text}</span>
              </div>
            );
          })}
        </div>
      </div>
    </section>
  );
}
