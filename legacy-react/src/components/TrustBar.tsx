import { Link } from "react-router-dom";
import { TRUST_ITEMS } from "@/lib/data";

export default function TrustBar() {
  return (
    <section className="border-y border-slate-200/70 bg-white py-5">
      <div className="container">
        <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
          {TRUST_ITEMS.map((item) => {
            const content = (
              <span className="flex min-h-14 items-center justify-center rounded-xl border bg-card px-4 py-4 text-center text-sm font-medium text-foreground shadow-sm transition hover:-translate-y-0.5 hover:border-primary/30 hover:text-primary hover:shadow-md">
                {item.text}
              </span>
            );

            if (!item.href) {
              return <div key={item.text}>{content}</div>;
            }

            if (item.href.startsWith("/#")) {
              return (
                <a key={item.text} href={item.href}>
                  {content}
                </a>
              );
            }

            return (
              <Link key={item.text} to={item.href}>
                {content}
              </Link>
            );
          })}
        </div>
      </div>
    </section>
  );
}
