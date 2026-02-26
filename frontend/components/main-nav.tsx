"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { Mic2, LayoutDashboard, Settings, User } from "lucide-react";
import { cn } from "@/lib/utils";

export function MainNav({
  className,
  ...props
}: React.HTMLAttributes<HTMLElement>) {
  const pathname = usePathname();

  const routes = [
    {
      href: "/",
      label: "Practice",
      icon: Mic2,
      active: pathname === "/",
    },
    {
      href: "/practice-hub",
      label: "Practice Hub",
      icon: LayoutDashboard,
      active: pathname === "/practice-hub" || pathname.startsWith("/practice-hub/"),
    },
  ];

  return (
    <nav
      className={cn("flex items-center space-x-2 lg:space-x-4", className)}
      {...props}
    >
      {routes.map((route) => (
        <Link
          key={route.href}
          href={route.href}
          className={cn(
            "flex items-center gap-2 px-3 py-2 text-sm font-medium transition-all duration-200 rounded-lg group",
            route.active
              ? "bg-primary text-primary-foreground shadow-sm"
              : "text-muted-foreground hover:bg-muted hover:text-foreground"
          )}
        >
          <route.icon className={cn("h-4 w-4 transition-transform group-hover:scale-110", route.active ? "text-primary-foreground" : "text-muted-foreground")} />
          <span>{route.label}</span>
        </Link>
      ))}
    </nav>
  );
}
