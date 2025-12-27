// src/app/vendor/[vendorId]/page.tsx
import VendorPageContent from './VendorPageContent';

// Server Component - params es una Promise en Next.js 15+
export default async function VendorPage({ params }: { params: Promise<{ vendorId: string }> }) {
    const { vendorId } = await params;

    // Pasa el vendorId como prop al componente cliente
    return <VendorPageContent vendorId={vendorId} />;
}
