/**
 * Configuración de NextAuth.js v5 para Pakiip
 * Reemplaza Firebase Auth con autenticación self-hosted
 */

import NextAuth from "next-auth";
import Google from "next-auth/providers/google";
import Credentials from "next-auth/providers/credentials";
import { prisma } from "./prisma";
import bcrypt from "bcryptjs";
import type { UserRole } from "@/generated/prisma";

// Extender tipos de NextAuth
declare module "next-auth" {
  interface Session {
    user: {
      id: string;
      email: string;
      name: string;
      image?: string;
      role: UserRole;
      roles: UserRole[];
    };
  }

  interface User {
    role?: UserRole;
    roles?: UserRole[];
  }
}

export const { handlers, signIn, signOut, auth } = NextAuth({
  trustHost: true,
  session: {
    strategy: "jwt",
  },
  pages: {
    signIn: "/login",
    error: "/login",
  },
  providers: [
    // Google OAuth
    Google({
      clientId: process.env.GOOGLE_CLIENT_ID!,
      clientSecret: process.env.GOOGLE_CLIENT_SECRET!,
      authorization: {
        params: {
          prompt: "consent",
          access_type: "offline",
          response_type: "code",
        },
      },
    }),

    // Email y Password
    Credentials({
      name: "credentials",
      credentials: {
        email: { label: "Email", type: "email" },
        password: { label: "Password", type: "password" },
      },
      async authorize(credentials) {
        if (!credentials?.email || !credentials?.password) {
          throw new Error("Email y contraseña son requeridos");
        }

        const email = credentials.email as string;
        const password = credentials.password as string;

        // Buscar usuario
        const user = await prisma.user.findUnique({
          where: { email },
          include: {
            roles: true,
            settings: true,
          },
        });

        if (!user) {
          throw new Error("Usuario no encontrado");
        }

        if (!user.passwordHash) {
          throw new Error("Este usuario usa inicio de sesión con Google");
        }

        // Verificar contraseña
        const isValid = await bcrypt.compare(password, user.passwordHash);
        if (!isValid) {
          throw new Error("Contraseña incorrecta");
        }

        // Obtener roles del usuario
        const roles = user.roles.map((r) => r.role);
        const selectedRole = user.settings?.selectedRole || "customer";

        return {
          id: user.id,
          email: user.email,
          name: user.name,
          image: user.profileImageUrl,
          role: selectedRole,
          roles: roles,
        };
      },
    }),
  ],
  callbacks: {
    async jwt({ token, user, trigger, session }) {
      // Primera vez que el usuario inicia sesión
      if (user) {
        token.id = user.id;
        token.role = user.role || "customer";
        token.roles = user.roles || ["customer"];
      }

      // Si se actualiza la sesión (cambio de rol)
      if (trigger === "update" && session?.role) {
        token.role = session.role;
      }

      return token;
    },

    async session({ session, token }) {
      if (token) {
        session.user.id = token.id as string;
        session.user.role = token.role as UserRole;
        session.user.roles = (token.roles as UserRole[]) || ["customer"];
      }
      return session;
    },

    async signIn({ user, account }) {
      // Si es OAuth (Google), asegurar que el usuario existe en la BD
      if (account?.provider === "google" && user.email) {
        const existingUser = await prisma.user.findUnique({
          where: { email: user.email },
          include: { roles: true },
        });

        if (!existingUser) {
          // Crear usuario nuevo con rol customer
          const newUser = await prisma.user.create({
            data: {
              email: user.email,
              name: user.name || "Usuario",
              profileImageUrl: user.image,
              roles: {
                create: { role: "customer" },
              },
              settings: {
                create: { selectedRole: "customer" },
              },
            },
          });
          user.id = newUser.id;
          user.role = "customer";
          user.roles = ["customer"];
        } else {
          user.id = existingUser.id;
          user.roles = existingUser.roles.map((r) => r.role);
          user.role = user.roles[0] || "customer";
        }
      }

      return true;
    },
  },
});

// ============================================
// Funciones auxiliares de autenticación
// ============================================

/**
 * Registrar nuevo usuario con email y contraseña
 */
export async function registerUser(data: {
  email: string;
  password: string;
  name: string;
  phone?: string;
}) {
  // Verificar si el email ya existe
  const existing = await prisma.user.findUnique({
    where: { email: data.email },
  });

  if (existing) {
    throw new Error("El email ya está registrado");
  }

  // Hash de la contraseña
  const passwordHash = await bcrypt.hash(data.password, 12);

  // Crear usuario
  const user = await prisma.user.create({
    data: {
      email: data.email,
      passwordHash,
      name: data.name,
      phone: data.phone,
      roles: {
        create: { role: "customer" },
      },
      settings: {
        create: { selectedRole: "customer" },
      },
    },
    include: {
      roles: true,
      settings: true,
    },
  });

  return user;
}

/**
 * Cambiar rol activo del usuario
 */
export async function switchUserRole(userId: string, newRole: UserRole) {
  // Verificar que el usuario tiene ese rol
  const userRole = await prisma.userRoleAssignment.findFirst({
    where: {
      userId,
      role: newRole,
      isActive: true,
    },
  });

  if (!userRole) {
    throw new Error("No tienes permiso para ese rol");
  }

  // Actualizar rol seleccionado
  await prisma.userSettings.upsert({
    where: { userId },
    update: { selectedRole: newRole },
    create: { userId, selectedRole: newRole },
  });

  return { success: true, role: newRole };
}

/**
 * Obtener usuario completo con roles
 */
export async function getUserWithRoles(userId: string) {
  return prisma.user.findUnique({
    where: { id: userId },
    include: {
      roles: true,
      settings: true,
      vendor: true,
      driver: true,
      admin: true,
    },
  });
}

/**
 * Verificar si el usuario es admin
 */
export async function isUserAdmin(userId: string): Promise<boolean> {
  const adminRole = await prisma.userRoleAssignment.findFirst({
    where: {
      userId,
      role: "admin",
      isActive: true,
    },
  });
  return !!adminRole;
}

/**
 * Agregar rol a usuario
 */
export async function addRoleToUser(userId: string, role: UserRole) {
  return prisma.userRoleAssignment.upsert({
    where: {
      userId_role: { userId, role },
    },
    update: { isActive: true },
    create: { userId, role, isActive: true },
  });
}
