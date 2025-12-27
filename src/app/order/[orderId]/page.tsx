// src/app/order/[orderId]/page.tsx
import { AuthGuard } from "@/components/AuthGuard";
import OrderTrackingPageContent from './OrderTrackingPageContent';

// Server Component - params es una Promise en Next.js 15+
export default async function OrderTrackingPage({ params }: { params: Promise<{ orderId: string }> }) {
    const { orderId } = await params;

    return (
        <AuthGuard requireAuth={true} requireRole="customer" redirectTo="/login">
            <OrderTrackingPageContent orderId={orderId} />
        </AuthGuard>
    );
}
