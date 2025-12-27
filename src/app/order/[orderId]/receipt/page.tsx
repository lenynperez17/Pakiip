// src/app/order/[orderId]/receipt/page.tsx
import { AuthGuard } from "@/components/AuthGuard";
import ReceiptPageContent from './ReceiptPageContent';

// Server Component - params es una Promise en Next.js 15+
export default async function ReceiptPage({ params }: { params: Promise<{ orderId: string }> }) {
    const { orderId } = await params;

    return (
        <AuthGuard requireAuth={true} requireRole="customer" redirectTo="/login">
            <ReceiptPageContent orderId={orderId} />
        </AuthGuard>
    );
}
