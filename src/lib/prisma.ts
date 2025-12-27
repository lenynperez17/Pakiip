/**
 * Cliente Prisma singleton para Next.js
 * Usa el adaptador de PostgreSQL para Prisma 7
 */

import { PrismaClient } from '@/generated/prisma';
import { Pool } from 'pg';
import { PrismaPg } from '@prisma/adapter-pg';

// Crear pool de conexiones de PostgreSQL
const connectionString = process.env.DATABASE_URL!;
const pool = new Pool({ connectionString });

// Crear adaptador de Prisma para PostgreSQL
const adapter = new PrismaPg(pool);

// Singleton pattern para evitar múltiples conexiones en desarrollo
const globalForPrisma = globalThis as unknown as {
  prisma: PrismaClient | undefined;
};

export const prisma = globalForPrisma.prisma ?? new PrismaClient({
  adapter,
  log: process.env.NODE_ENV === 'development' ? ['error', 'warn'] : ['error'],
});

if (process.env.NODE_ENV !== 'production') {
  globalForPrisma.prisma = prisma;
}

export default prisma;
