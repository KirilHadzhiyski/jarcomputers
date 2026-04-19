import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { BrowserRouter, Route, Routes } from "react-router-dom";
import { Toaster as Sonner } from "@/components/ui/sonner";
import { Toaster } from "@/components/ui/toaster";
import { TooltipProvider } from "@/components/ui/tooltip";
import Index from "./pages/Index";
import NotFound from "./pages/NotFound";
import MainServicePage from "./pages/MainServicePage";
import ServicePage from "./pages/ServicePage";
import ModelPage from "./pages/ModelPage";
import CityPage from "./pages/CityPage";
import SeoPage from "./pages/SeoPage";
import ContactPage from "./pages/ContactPage";
import PricingPage from "./pages/PricingPage";
import AboutPage from "./pages/AboutPage";
import FAQPage from "./pages/FAQPage";
import { useEffect } from "react";
import { useLocation } from "react-router-dom";

function ScrollToTop() {
  const { pathname } = useLocation();
  useEffect(() => { window.scrollTo(0, 0); }, [pathname]);
  return null;
}

const queryClient = new QueryClient();

const App = () => (
  <QueryClientProvider client={queryClient}>
    <TooltipProvider>
      <Toaster />
      <Sonner />
      <BrowserRouter>
        <ScrollToTop />
        <Routes>
          <Route path="/" element={<Index />} />
          <Route path="/remont-iphone" element={<MainServicePage />} />
          
          {/* Service pages */}
          <Route path="/smqna-displei-iphone" element={<ServicePage />} />
          <Route path="/smqna-bateria-iphone" element={<ServicePage />} />
          <Route path="/remont-face-id-iphone" element={<ServicePage />} />
          <Route path="/remont-kamera-iphone" element={<ServicePage />} />
          
          {/* Model pages */}
          <Route path="/remont-iphone-11" element={<ModelPage />} />
          <Route path="/remont-iphone-12" element={<ModelPage />} />
          <Route path="/remont-iphone-13" element={<ModelPage />} />
          <Route path="/remont-iphone-14" element={<ModelPage />} />
          
          {/* City pages */}
          <Route path="/remont-iphone-sofia" element={<CityPage />} />
          <Route path="/remont-iphone-plovdiv" element={<CityPage />} />
          <Route path="/remont-iphone-varna" element={<CityPage />} />
          <Route path="/remont-iphone-burgas" element={<CityPage />} />
          
          {/* SEO combination pages */}
          <Route path="/smqna-displei-iphone-:series" element={<SeoPage />} />
          <Route path="/smqna-bateria-iphone-:series" element={<SeoPage />} />
          <Route path="/remont-face-id-iphone-:series" element={<SeoPage />} />
          <Route path="/remont-kamera-iphone-:series" element={<SeoPage />} />
          
          {/* Info pages */}
          <Route path="/kontakti" element={<ContactPage />} />
          <Route path="/ceni" element={<PricingPage />} />
          <Route path="/za-nas" element={<AboutPage />} />
          <Route path="/chzv" element={<FAQPage />} />
          
          <Route path="*" element={<NotFound />} />
        </Routes>
      </BrowserRouter>
    </TooltipProvider>
  </QueryClientProvider>
);

export default App;
