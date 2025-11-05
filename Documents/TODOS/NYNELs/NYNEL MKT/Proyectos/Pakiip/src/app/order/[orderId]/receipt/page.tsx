// src/app/order/[orderId]/receipt/page.tsx
import { AuthGuard } from "@/components/AuthGuard";
import ReceiptPageContent from './ReceiptPageContent';

// This is a pure Server Component. Its only job is to get the params.
function ReceiptPageWithParams({ params }: { params: { orderId: string } }) {
    const { orderId } = params;

    // It then passes the orderId as a simple prop to the Client Component.
    return <ReceiptPageContent orderId={orderId} />;
}

export default function ReceiptPage({ params }: { params: { orderId: string } }) {
    return (
        <AuthGuard requireAuth={true} requireRole="customer" redirectTo="/login">
            <ReceiptPageWithParams params={params} />
        </AuthGuard>
    );
}
