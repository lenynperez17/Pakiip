// src/app/driver/orders/[orderId]/chat/page.tsx
import DriverChatPageContent from './DriverChatPageContent';
import { AuthGuard } from "@/components/AuthGuard";

// Server Component - params es una Promise en Next.js 15+
export default async function DriverChatPage({ params }: { params: Promise<{ orderId: string }> }) {
    const { orderId } = await params;

    return (
        <AuthGuard requireAuth={true} requireRole="driver" redirectTo="/driver/login">
            <DriverChatPageContent orderId={orderId} />
        </AuthGuard>
    );
}
