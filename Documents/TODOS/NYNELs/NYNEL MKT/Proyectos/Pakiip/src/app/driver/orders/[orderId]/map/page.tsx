// src/app/driver/orders/[orderId]/map/page.tsx
import DriverMapPageContent from './DriverMapPageContent';
import { AuthGuard } from "@/components/AuthGuard";

// This is a pure Server Component. Its only job is to get the params.
export default function DriverMapPage({ params }: { params: { orderId: string } }) {
    const { orderId } = params;

    // It then passes the orderId as a simple prop to the Client Component.
    return (
        <AuthGuard requireAuth={true} requireRole="driver" redirectTo="/driver/login">
            <DriverMapPageContent orderId={orderId} />
        </AuthGuard>
    );
}
