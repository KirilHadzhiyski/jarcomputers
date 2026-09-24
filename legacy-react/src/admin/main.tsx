import React from "react";
import { createRoot } from "react-dom/client";
import { BrowserRouter } from "react-router-dom";
import { Toaster } from "sonner";
import AdminPage from "./LaravelAdminPage";
import "../index.css";

createRoot(document.getElementById("root")!).render(
    <BrowserRouter>
        <AdminPage />
        <Toaster richColors />
    </BrowserRouter>,
);
