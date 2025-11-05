// src/app/vendor/[vendorId]/page.tsx
import VendorPageContent from './VendorPageContent';

// This is a pure Server Component. Its only job is to get the params.
export default function VendorPage({ params }: { params: { vendorId: string } }) {
    const { vendorId } = params;
    
    // It then passes the vendorId as a simple prop to the Client Component.
    return <VendorPageContent vendorId={vendorId} />;
}
