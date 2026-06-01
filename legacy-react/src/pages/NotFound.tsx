import { useLocation } from "react-router-dom";
import { useEffect } from "react";
import { BRAND } from "@/lib/data";

const NotFound = () => {
  const location = useLocation();

  useEffect(() => {
    console.error("404: несъществуващ маршрут:", location.pathname);
  }, [location.pathname]);

  return (
    <div className="flex min-h-screen items-center justify-center bg-muted">
      <div className="mx-auto max-w-md px-6 text-center">
        <h1 className="mb-4 text-4xl font-bold">404</h1>
        <p className="mb-3 text-xl font-semibold text-foreground">Страницата не е намерена</p>
        <p className="mb-6 text-sm leading-6 text-muted-foreground">
          Линкът може да е променен или страницата вече да не съществува. Върнете се към началната страница на {BRAND}.
        </p>
        <a href="/" className="inline-flex rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground hover:bg-primary/90">
          Към началото
        </a>
      </div>
    </div>
  );
};

export default NotFound;
