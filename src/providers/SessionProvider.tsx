"use client";

/**
 * Provider de sesión para NextAuth.js
 */

import { SessionProvider as NextAuthSessionProvider } from "next-auth/react";
import { ReactNode } from "react";

interface SessionProviderProps {
  children: ReactNode;
}

export function SessionProvider({ children }: SessionProviderProps) {
  return (
    <NextAuthSessionProvider refetchInterval={5 * 60}>
      {children}
    </NextAuthSessionProvider>
  );
}
