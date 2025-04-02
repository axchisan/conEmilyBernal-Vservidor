import { createClient } from "https://cdn.jsdelivr.net/npm/@supabase/supabase-js/+esm";

// credenciales de Supabase
const SUPABASE_URL = "https://vibjsljiziruymhhdvoq.supabase.co";
const SUPABASE_ANON_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InZpYmpzbGppemlydXltaGhkdm9xIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDA3NzA3ODcsImV4cCI6MjA1NjM0Njc4N30.F6qioB5dXMZk4bRLusf0naXYzX_jy1ni1lOHIg0D3Wc";

export const supabase = createClient(SUPABASE_URL, SUPABASE_ANON_KEY);
