import type { Metadata } from "next";
import { Geist, Geist_Mono } from "next/font/google";
import "./globals.css";
import { Toaster } from "@/components/ui/toaster";

const geistSans = Geist({
  variable: "--font-geist-sans",
  subsets: ["latin"],
});

const geistMono = Geist_Mono({
  variable: "--font-geist-mono",
  subsets: ["latin"],
});

export const metadata: Metadata = {
  title: "PensionVox Chile - Asesoría Previsional Experta",
  description: "Asesoría profesional en pensiones, jubilación, AFP, renta vitalicia y retiro programado. Obtén una evaluación gratuita de tu pensión estimada.",
  keywords: ["pensiones Chile", "asesoría previsional", "AFP", "renta vitalicia", "retiro programado", "jubilación", "PGU", "ahorro previsional"],
  authors: [{ name: "PensionVox Chile" }],
  icons: {
    icon: "/logo.svg",
  },
  openGraph: {
    title: "PensionVox Chile - Asesoría Previsional Experta",
    description: "Asesoría profesional en pensiones. Evaluación gratuita de tu pensión estimada.",
    url: "https://pensionvox.cl",
    siteName: "PensionVox Chile",
    type: "website",
  },
  twitter: {
    card: "summary_large_image",
    title: "PensionVox Chile - Asesoría Previsional Experta",
    description: "Asesoría profesional en pensiones. Evaluación gratuita de tu pensión estimada.",
  },
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="es" suppressHydrationWarning>
      <body
        className={`${geistSans.variable} ${geistMono.variable} antialiased bg-background text-foreground`}
      >
        {children}
        <Toaster />
      </body>
    </html>
  );
}
