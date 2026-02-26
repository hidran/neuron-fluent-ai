import type { Metadata } from "next";
import { Inter } from "next/font/google";
import Link from "next/link";
import "./globals.css";
import { MainNav } from "@/components/main-nav";
import { User, Bell } from "lucide-react";
import { Button } from "@/components/ui/button";
import { ToastProvider } from "@/components/ui/use-toast";

const inter = Inter({ subsets: ["latin"], variable: "--font-sans" });

export const metadata: Metadata = {
  title: "Fluent AI | Master Your Pronunciation",
  description: "Advanced AI-powered language learning and pronunciation coach.",
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en">
      <body className={`${inter.variable} font-sans antialiased min-h-screen bg-background text-foreground`}>
        <ToastProvider>
          <header className="sticky top-0 z-50 w-full border-b bg-background/80 backdrop-blur-xl supports-[backdrop-filter]:bg-background/60">
            <div className="container mx-auto flex h-16 items-center justify-between px-4">
              <div className="flex items-center gap-8">
                <Link href="/" className="flex items-center space-x-2 group">
                  <div className="bg-primary rounded-lg p-1.5 transition-transform group-hover:rotate-12">
                     <User className="h-5 w-5 text-primary-foreground" />
                  </div>
                  <span className="text-xl font-bold tracking-tight">
                    Fluent<span className="text-primary">AI</span>
                  </span>
                </Link>
                <MainNav className="hidden md:flex" />
              </div>
              
              <div className="flex items-center gap-3">
                <Button variant="ghost" size="icon" className="rounded-full">
                  <Bell className="h-5 w-5 text-muted-foreground" />
                </Button>
                <div className="h-8 w-px bg-border mx-1" />
                <Button variant="ghost" className="gap-2 rounded-full pl-2 pr-4">
                  <div className="h-8 w-8 rounded-full bg-secondary flex items-center justify-center border shadow-sm">
                    <User className="h-4 w-4 text-muted-foreground" />
                  </div>
                  <span className="text-sm font-medium hidden sm:inline-block">Hidran</span>
                </Button>
              </div>
            </div>
          </header>
          
          <main className="relative flex flex-col flex-1">
            <div className="flex-1 w-full max-w-7xl mx-auto">
              {children}
            </div>
          </main>
          
          <footer className="border-t py-6 md:py-0 bg-muted/30">
            <div className="container mx-auto flex flex-col items-center justify-between gap-4 md:h-16 md:flex-row px-4">
              <p className="text-sm text-muted-foreground">
                &copy; 2026 Fluent AI. Powered by Neuron AI.
              </p>
              <div className="flex items-center gap-4 text-sm text-muted-foreground">
                <Link href="#" className="hover:text-primary transition-colors">Privacy</Link>
                <Link href="#" className="hover:text-primary transition-colors">Terms</Link>
                <Link href="#" className="hover:text-primary transition-colors">Help</Link>
              </div>
            </div>
          </footer>
        </ToastProvider>
      </body>
    </html>
  );
}
