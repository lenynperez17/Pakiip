// ═══════════════════════════════════════════════════════════════════════════
// 🗄️ CONFIGURACIÓN DE BASE DE DATOS - PRISMA CLIENT
// ═══════════════════════════════════════════════════════════════════════════

import { PrismaClient } from '@prisma/client';
import { logger } from '#/utils/logger.js';

// Singleton pattern para Prisma Client
declare global {
  // eslint-disable-next-line no-var
  var prisma: PrismaClient | undefined;
}

// Configuración de Prisma con logging
export const prisma = global.prisma || new PrismaClient({
  log: process.env.NODE_ENV === 'development'
    ? ['query', 'info', 'warn', 'error']
    : ['error'],
});

if (process.env.NODE_ENV !== 'production') {
  global.prisma = prisma;
}

// Conectar a la base de datos
export async function connectDatabase() {
  try {
    await prisma.$connect();
    logger.info('✅ Conexión a PostgreSQL establecida correctamente');

    // Verificar conexión
    await prisma.$queryRaw`SELECT 1`;
    logger.info('✅ Base de datos operativa');

  } catch (error) {
    logger.error('❌ Error al conectar a PostgreSQL:', error);
    process.exit(1);
  }
}

// Desconectar de la base de datos
export async function disconnectDatabase() {
  try {
    await prisma.$disconnect();
    logger.info('✅ Desconexión de PostgreSQL exitosa');
  } catch (error) {
    logger.error('❌ Error al desconectar de PostgreSQL:', error);
  }
}

// Graceful shutdown
process.on('beforeExit', async () => {
  await disconnectDatabase();
});

process.on('SIGINT', async () => {
  await disconnectDatabase();
  process.exit(0);
});

process.on('SIGTERM', async () => {
  await disconnectDatabase();
  process.exit(0);
});
