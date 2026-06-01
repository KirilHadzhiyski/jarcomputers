import { AGGREGATE_REVIEW, REVIEW_PLATFORMS } from "@/lib/data";
import { Button } from "@/components/ui/button";

function formatSnapshotDate(value: string) {
  const date = new Date(`${value}T00:00:00`);

  if (Number.isNaN(date.getTime())) {
    return value;
  }

  return new Intl.DateTimeFormat("bg-BG", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
  }).format(date);
}

export default function ReviewSummary({
  sectionClassName = "py-16 md:py-20 bg-muted/60",
  eyebrow = "Оценки и репутация",
}: {
  sectionClassName?: string;
  eyebrow?: string;
}) {
  const primaryPlatform = REVIEW_PLATFORMS.find((platform) => platform.primary) ?? REVIEW_PLATFORMS[0];

  return (
    <section id="reviews" className={sectionClassName}>
      <div className="container">
        <div className="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
          <div className="card-service">
            <p className="text-sm font-semibold uppercase tracking-[0.18em] text-muted-foreground">
              {eyebrow}
            </p>
            <p className="mt-4 text-5xl font-extrabold text-foreground">
              {AGGREGATE_REVIEW.ratingValue.toFixed(1)}/{AGGREGATE_REVIEW.ratingScale}
            </p>
            <p className="mt-4 text-base leading-7 text-muted-foreground">
              {AGGREGATE_REVIEW.reviewsCount} комбинирани оценки от наличните публични платформи.
              {" "}Последен snapshot: {formatSnapshotDate(AGGREGATE_REVIEW.scanDate)}.
            </p>
            <div className="mt-6 flex flex-col gap-3 sm:flex-row">
              <a href={AGGREGATE_REVIEW.sourceUrl} target="_blank" rel="noreferrer">
                <Button variant="hero-outline" className="w-full sm:w-auto">
                  Виж източник
                </Button>
              </a>
              {primaryPlatform && (
                <a href={primaryPlatform.sourceUrl} target="_blank" rel="noreferrer">
                  <Button variant="hero" className="w-full sm:w-auto">
                    Виж Google отзивите
                  </Button>
                </a>
              )}
            </div>
          </div>

          <div className="grid gap-4 sm:grid-cols-2">
            {REVIEW_PLATFORMS.map((platform) => (
              <a
                key={platform.key}
                href={platform.sourceUrl}
                className="card-service block"
                target="_blank"
                rel="noreferrer"
              >
                <p className="text-sm font-semibold uppercase tracking-[0.18em] text-muted-foreground">
                  {platform.label}
                </p>
                <p className="mt-4 text-4xl font-extrabold text-foreground">
                  {platform.ratingValue.toFixed(1)}/{platform.ratingScale}
                </p>
                <p className="mt-3 text-sm leading-7 text-muted-foreground">
                  {platform.reviewsCount} оценки · snapshot {formatSnapshotDate(platform.scanDate)}
                </p>
              </a>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
}
