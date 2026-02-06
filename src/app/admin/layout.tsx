import { cookies } from 'next/headers';
import AdminLayoutClient from './AdminLayoutClient';

// Forzar renderizado dinamico usando cookies() de next/headers
// Esto hace que Next.js NO pre-renderice esta ruta como estatica
export const dynamic = 'force-dynamic';

export default async function AdminLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  // Acceder a cookies fuerza el renderizado dinamico en el servidor
  // Esto es una tecnica oficial de Next.js para forzar SSR
  const cookieStore = await cookies();
  const _ = cookieStore.getAll(); // Solo para forzar dynamic

  return <AdminLayoutClient>{children}</AdminLayoutClient>;
}
