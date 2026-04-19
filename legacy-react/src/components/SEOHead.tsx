import { useEffect } from "react";
import { useLocation } from "react-router-dom";

interface SEOHeadProps {
  title: string;
  description: string;
  canonical?: string;
}

export default function SEOHead({ title, description, canonical }: SEOHeadProps) {
  const { pathname } = useLocation();
  const url = canonical || `https://jarcomputers.bg${pathname}`;

  useEffect(() => {
    document.title = title;
    
    const setMeta = (name: string, content: string) => {
      let el = document.querySelector(`meta[name="${name}"]`) || document.querySelector(`meta[property="${name}"]`);
      if (!el) {
        el = document.createElement("meta");
        if (name.startsWith("og:")) {
          el.setAttribute("property", name);
        } else {
          el.setAttribute("name", name);
        }
        document.head.appendChild(el);
      }
      el.setAttribute("content", content);
    };

    setMeta("description", description);
    setMeta("og:title", title);
    setMeta("og:description", description);
    setMeta("og:url", url);
    setMeta("og:type", "website");

    let link = document.querySelector('link[rel="canonical"]') as HTMLLinkElement;
    if (!link) {
      link = document.createElement("link");
      link.setAttribute("rel", "canonical");
      document.head.appendChild(link);
    }
    link.setAttribute("href", url);
  }, [title, description, url]);

  return null;
}
