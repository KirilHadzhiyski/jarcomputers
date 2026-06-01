import { useState } from "react";
import { Button } from "@/components/ui/button";
import { BRAND, CONTACT_METHOD_LABELS, MODELS } from "@/lib/data";
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
    const email = (formData.get("email") as string | null)?.trim() || "";

    const { error } = await supabase.from("repair_requests").insert({
      name: formData.get("name") as string,
      phone: formData.get("phone") as string,
      city: (formData.get("city") as string) || "",
      model: (formData.get("model") as string) || "",
      issue: formData.get("issue") as string,
      preferred_contact: (formData.get("preferred_contact") as keyof typeof CONTACT_METHOD_LABELS) || "phone",
      source_page: sourcePage || window.location.pathname,
      admin_notes: email ? `Email от заявка: ${email}` : null,
    });

    setLoading(false);

    if (error) {
      toast.error("Грешка при изпращане. Моля, опитайте отново или се обадете.");
      return;
    }

    setSubmitted(true);
    form.reset();
  };

  if (submitted) {
    return (
      <div className="success-panel rounded-xl border bg-card p-8 text-center shadow-sm">
        <div className="mx-auto mb-4 inline-flex h-14 w-14 items-center justify-center rounded-full bg-green-100 text-2xl text-green-700">
          ✓
        </div>
        <h3 className="text-xl font-bold mb-2">Заявката е приета</h3>
        <p className="text-sm leading-7 text-muted-foreground">
          Ще се свържем с вас в рамките на 1 час в работно време.
        </p>
      </div>
    );
  }

  return (
    <form onSubmit={handleSubmit} className="card-service space-y-4">
      <div>
        <h3 className="text-xl font-bold text-foreground">Заявка за ремонт</h3>
        <p className="mt-2 text-sm leading-7 text-muted-foreground">
          Попълнете формата с модел и проблем. Ще получите потвърждение и следваща стъпка от сервиза.
        </p>
      </div>

      <div className="grid gap-4 sm:grid-cols-2">
        <label className="block text-sm font-medium text-foreground">
          Име *
          <input name="name" required type="text" maxLength={100} className="input-shell mt-2" placeholder="Вашето име" />
        </label>

        <label className="block text-sm font-medium text-foreground">
          Телефон *
          <input name="phone" required type="tel" maxLength={20} className="input-shell mt-2" placeholder="0878 369 024" />
        </label>
      </div>

      <div className="grid gap-4 sm:grid-cols-2">
        <label className="block text-sm font-medium text-foreground">
          Имейл
          <input name="email" type="email" maxLength={120} className="input-shell mt-2" placeholder="name@example.com" />
        </label>

        <label className="block text-sm font-medium text-foreground">
          Град
          <input name="city" type="text" maxLength={50} className="input-shell mt-2" placeholder="Напр. София" />
        </label>
      </div>

      <label className="block text-sm font-medium text-foreground">
        Модел iPhone
        <select name="model" className="input-shell mt-2">
          <option value="">Изберете модел</option>
          {MODELS.map((model) => (
            <option key={model.slug} value={model.name}>{model.name}</option>
          ))}
          <option value="Друг модел">Друг модел</option>
        </select>
      </label>

      <label className="block text-sm font-medium text-foreground">
        Описание на проблема *
        <textarea
          name="issue"
          required
          maxLength={1000}
          rows={4}
          className="input-shell mt-2 min-h-32 resize-none"
          placeholder="Опишете проблема, кога се проявява и дали има следи от удар, вода или предишен ремонт."
        />
      </label>

      <fieldset>
        <legend className="text-sm font-medium text-foreground">Предпочитан контакт</legend>
        <div className="mt-3 flex flex-wrap gap-3">
          {Object.entries(CONTACT_METHOD_LABELS).map(([value, label]) => (
            <label key={value} className="inline-flex items-center gap-2 rounded-full border border-border px-4 py-2 text-sm font-medium text-muted-foreground">
              <input type="radio" name="preferred_contact" value={value} defaultChecked={value === "phone"} />
              {label}
            </label>
          ))}
        </div>
      </fieldset>

      <label className="flex items-start gap-3 rounded-2xl border border-border bg-white px-4 py-4 text-sm leading-6 text-muted-foreground">
        <input type="checkbox" name="gdpr_consent" value="1" className="mt-1" required />
        <span>
          Съгласен/на съм данните ми да бъдат обработени за целите на заявката и комуникацията по ремонта според{" "}
          <a href="/politika-za-poveritelnost" className="font-semibold text-primary underline underline-offset-4">
            политиката за поверителност
          </a>.
        </span>
      </label>

      <Button type="submit" variant="cta" className="w-full" size="lg" disabled={loading}>
        {loading ? "Изпращане..." : "Изпрати заявка за ремонт"}
      </Button>

      <p className="text-xs leading-6 text-muted-foreground text-center">
        Безплатна диагностика · Проследяване на заявката · {BRAND}
      </p>
    </form>
  );
}
