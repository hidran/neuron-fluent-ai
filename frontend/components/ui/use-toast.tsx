"use client";

import * as React from "react";
import { X, CheckCircle2, AlertCircle, Info, Loader2 } from "lucide-react";
import { cn } from "@/lib/utils";

type ToastType = "success" | "error" | "info" | "loading";

interface Toast {
  id: string;
  title?: string;
  message: string;
  type: ToastType;
}

interface ToastContextType {
  toast: (message: string, type?: ToastType, title?: string) => void;
  dismiss: (id: string) => void;
}

const ToastContext = React.createContext<ToastContextType | undefined>(undefined);

export function ToastProvider({ children }: { children: React.ReactNode }) {
  const [toasts, setToasts] = React.useState<Toast[]>([]);

  const toast = React.useCallback((message: string, type: ToastType = "info", title?: string) => {
    const id = Math.random().toString(36).substring(2, 9);
    setToasts((prev) => [...prev, { id, message, type, title }]);

    if (type !== "loading") {
      setTimeout(() => {
        dismiss(id);
      }, 5000);
    }
  }, []);

  const dismiss = React.useCallback((id: string) => {
    setToasts((prev) => prev.filter((t) => t.id !== id));
  }, []);

  return (
    <ToastContext.Provider value={{ toast, dismiss }}>
      {children}
      <div className="fixed bottom-4 right-4 z-[100] flex flex-col gap-2 w-full max-w-[400px]">
        {toasts.map((t) => (
          <div
            key={t.id}
            className={cn(
              "flex w-full items-start gap-4 rounded-xl p-4 shadow-2xl animate-in slide-in-from-right-full duration-300 border backdrop-blur-md",
              t.type === "success" && "bg-emerald-50 border-emerald-200 text-emerald-900 dark:bg-emerald-950/50 dark:border-emerald-800 dark:text-emerald-200",
              t.type === "error" && "bg-rose-50 border-rose-200 text-rose-900 dark:bg-rose-950/50 dark:border-rose-800 dark:text-rose-200",
              t.type === "info" && "bg-blue-50 border-blue-200 text-blue-900 dark:bg-blue-950/50 dark:border-blue-800 dark:text-blue-200",
              t.type === "loading" && "bg-white border-primary/20 text-foreground dark:bg-zinc-900 dark:border-zinc-800"
            )}
          >
            <div className="shrink-0 pt-0.5">
              {t.type === "success" && <CheckCircle2 className="h-5 w-5 text-emerald-500" />}
              {t.type === "error" && <AlertCircle className="h-5 w-5 text-rose-500" />}
              {t.type === "info" && <Info className="h-5 w-5 text-blue-500" />}
              {t.type === "loading" && <Loader2 className="h-5 w-5 text-primary animate-spin" />}
            </div>
            <div className="flex-1 space-y-1">
              {t.title && <p className="font-bold text-sm leading-none tracking-tight">{t.title}</p>}
              <p className="text-sm opacity-90">{t.message}</p>
            </div>
            <button
              onClick={() => dismiss(t.id)}
              className="shrink-0 rounded-lg p-1 hover:bg-black/5 dark:hover:bg-white/10 transition-colors"
            >
              <X className="h-4 w-4 opacity-50" />
            </button>
          </div>
        ))}
      </div>
    </ToastContext.Provider>
  );
}

export function useToast() {
  const context = React.useContext(ToastContext);
  if (!context) {
    throw new Error("useToast must be used within a ToastProvider");
  }
  return context;
}
