// src/app/order/[orderId]/page.tsx
import { AuthGuard } from "@/components/AuthGuard";
import OrderTrackingPageContent from './OrderTrackingPageContent';

// This is a pure Server Component. Its only job is to get the params.
function OrderTrackingPageWithParams({ params }: { params: { orderId: string } }) {
    const { orderId } = params;

    // It then passes the orderId as a simple prop to the Client Component.
    return <OrderTrackingPageContent orderId={orderId} />;
}

export default function OrderTrackingPage({ params }: { params: { orderId: string } }) {
    return (
        <AuthGuard requireAuth={true} requireRole="customer" redirectTo="/login">
            <OrderTrackingPageWithParams params={params} />
        </AuthGuard>
    );
}
