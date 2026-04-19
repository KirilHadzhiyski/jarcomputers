import { useState } from "react";
import { Button } from "@/components/ui/button";
import { BRAND, MODELS } from "@/lib/data";
import { supabase } from "@/integrations/supabase/client";
import { toast } from "sonner";

export default function RepairForm({ sourcePage }: { sourcePage?: string }) {
  const [submitted, setSubmitted] = useState(false);
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setLoading(true);

    const form = e.currentTarget;
    const formData = new FormData(form);

    const { error } = await supabase.from("repair_requests").insert({
      name: formData.get("name") as string,
      phone: formData.get("phone") as string,
      city: (formData.get("city") as string) || "",
      model: (formData.get("model") as string) || "",
      issue: formData.get("issue") as string,
      preferred_contact: (formData.get("contact") as "phone" | "viber" | "whatsapp") || "phone",
      source_page: sourcePage || window.location.pathname,
    });

    setLoading(false);

    if (error) {
      toast.error("Грешка при изпращане. Моля, опитайте отново или се обадете.");
      return;
    }

    setSubmitted(true);
  };

  if (submitted) {
    return (
      <div className="rounded-xl border bg-card p-8 text-center">
        <div className="text-4xl mb-4">✅</div>
        <h3 className="text-xl font-bold mb-2">Благодарим за заявката!</h3>
        <p className="text-muted-foreground">Ще се свържем с вас в рамките на 1 час в работно време.</p>
      </div>
    );
  }

  return (
    <form onSubmit={handleSubmit} className="rounded-xl border bg-card p-6 md:p-8 space-y-4">
      <h3 className="text-xl font-bold mb-2">Заявка за ремонт</h3>
      <p className="text-sm text-muted-foreground mb-4">Попълнете формата и ние ще се свържем с вас.</p>

      <div className="grid gap-4 sm:grid-cols-2">
        <div>
          <label className="text-sm font-medium mb-1 block">Име *</label>
          <input name="name" required type="text" maxLength={100} className="w-full rounded-md border bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ring" placeholder="Вашето име" />
        </div>
        <div>
          <label className="text-sm font-medium mb-1 block">Телефон *</label>
          <input name="phone" required type="tel" maxLength={20} className="w-full rounded-md border bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ring" placeholder="087..." />
        </div>
      </div>

      <div className="grid gap-4 sm:grid-cols-2">
        <div>
          <label className="text-sm font-medium mb-1 block">Град</label>
          <input name="city" type="text" maxLength={50} className="w-full rounded-md border bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ring" placeholder="Напр. София" />
        </div>
        <div>
          <label className="text-sm font-medium mb-1 block">Модел iPhone</label>
          <select name="model" className="w-full rounded-md border bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
            <option value="">Изберете модел</option>
            {MODELS.map((m) => (
              <option key={m.slug} value={m.name}>{m.name}</option>
            ))}
            <option value="Друг">Друг модел</option>
          </select>
        </div>
      </div>

      <div>
        <label className="text-sm font-medium mb-1 block">Описание на проблема *</label>
        <textarea name="issue" required maxLength={1000} rows={3} className="w-full rounded-md border bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ring resize-none" placeholder="Опишете проблема..." />
      </div>

      <div>
        <label className="text-sm font-medium mb-1 block">Предпочитан контакт</label>
        <div className="flex gap-4 text-sm">
          <label className="flex items-center gap-1"><input type="radio" name="contact" value="phone" defaultChecked /> Телефон</label>
          <label className="flex items-center gap-1"><input type="radio" name="contact" value="viber" /> Viber</label>
          <label className="flex items-center gap-1"><input type="radio" name="contact" value="whatsapp" /> WhatsApp</label>
        </div>
      </div>

      <Button type="submit" variant="cta" className="w-full" size="lg" disabled={loading}>
        {loading ? "Изпращане..." : "Изпрати заявка за ремонт"}
      </Button>
      <p className="text-xs text-muted-foreground text-center">
        Безплатна диагностика • Плащаш само при одобрение • {BRAND}
      </p>
    </form>
  );
}
