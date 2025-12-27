// src/app/driver/orders/[orderId]/map/page.tsx
import DriverMapPageContent from './DriverMapPageContent';
import { AuthGuard } from "@/components/AuthGuard";

// Server Component - params es una Promise en Next.js 15+
export default async function DriverMapPage({ params }: { params: Promise<{ orderId: string }> }) {
    const { orderId } = await params;

    return (
        <AuthGuard requireAuth={true} requireRole="driver" redirectTo="/driver/login">
            <DriverMapPageContent orderId={orderId} />
        </AuthGuard>
    );
}
