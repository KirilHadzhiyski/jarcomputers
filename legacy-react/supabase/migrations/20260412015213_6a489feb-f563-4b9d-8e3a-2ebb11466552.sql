CREATE TYPE public.repair_status AS ENUM ('pending', 'contacted', 'courier_sent', 'received', 'diagnosing', 'awaiting_approval', 'repairing', 'completed', 'returned', 'cancelled');

CREATE TYPE public.contact_method AS ENUM ('phone', 'viber', 'whatsapp', 'email');

CREATE TABLE public.repair_requests (
  id UUID NOT NULL DEFAULT gen_random_uuid() PRIMARY KEY,
  name TEXT NOT NULL,
  phone TEXT NOT NULL,
  city TEXT NOT NULL,
  model TEXT NOT NULL,
  issue TEXT NOT NULL,
  preferred_contact contact_method DEFAULT 'phone',
  status repair_status DEFAULT 'pending',
  admin_notes TEXT,
  source_page TEXT,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now(),
  updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now()
);

CREATE TABLE public.contact_inquiries (
  id UUID NOT NULL DEFAULT gen_random_uuid() PRIMARY KEY,
  name TEXT NOT NULL,
  phone TEXT,
  email TEXT,
  message TEXT NOT NULL,
  source_page TEXT,
  is_read BOOLEAN DEFAULT false,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now()
);

ALTER TABLE public.repair_requests ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.contact_inquiries ENABLE ROW LEVEL SECURITY;

CREATE POLICY "Anyone can submit repair request"
  ON public.repair_requests FOR INSERT
  TO anon, authenticated
  WITH CHECK (true);

CREATE POLICY "Anyone can submit contact inquiry"
  ON public.contact_inquiries FOR INSERT
  TO anon, authenticated
  WITH CHECK (true);

CREATE OR REPLACE FUNCTION public.update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at = now();
  RETURN NEW;
END;
$$ LANGUAGE plpgsql SET search_path = public;

CREATE TRIGGER update_repair_requests_updated_at
  BEFORE UPDATE ON public.repair_requests
  FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();