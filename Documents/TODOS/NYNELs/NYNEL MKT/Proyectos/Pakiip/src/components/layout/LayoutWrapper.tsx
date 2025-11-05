
"use client";

import React from 'react';
import { usePathname } from 'next/navigation';
import { Header } from '@/components/layout/Header';
import { Footer } from '@/components/layout/Footer';
import { useAppData } from '@/hooks/use-app-data';


export function LayoutWrapper({ children }: { children: React.ReactNode }) {
    const pathname = usePathname();
    const { appSettings } = useAppData();

    const isAuthPage = /^\/(login|register|welcome)/.test(pathname) || pathname === '/admin/login' || pathname === '/vendor/login' || pathname === '/driver/login';
    const isReceiptPage = /^\/order\/.*\/receipt/.test(pathname);
    const isAdminPage = /^\/admin/.test(pathname);
    const isVendorPage = /^\/vendor/.test(pathname);
    const isDriverPage = /^\/driver/.test(pathname);

    const shouldShowFooter = !appSettings.hideFooter;

    // 🐛 DEBUG: Verificar tipo de página y configuración de layout
    console.log('🎨 [LAYOUT-WRAPPER] Renderizando página:', {
        pathname,
        isAuthPage,
        isAdminPage,
        isVendorPage,
        isDriverPage,
        shouldShowFooter,
        layoutType: isAuthPage ? 'AUTH' : isAdminPage ? 'ADMIN' : isVendorPage ? 'VENDOR' : isDriverPage ? 'DRIVER' : 'PUBLIC'
    });

    if (isReceiptPage) {
        return <main>{children}</main>;
    }
    
    if (isAuthPage) {
        return (
             <div className="relative flex min-h-screen flex-col w-full">
                <main className="flex-1 w-full">
                    {children}
                </main>
                {shouldShowFooter && <Footer />}
            </div>
        )
    }

    // Default layout for all other pages (public, admin, vendor, driver)
    return (
        <div className="relative flex min-h-screen flex-col w-full">
            <Header />
            <main className="flex-1 w-full" style={{ maxWidth: '1600px', margin: '0 auto', paddingLeft: '1.5rem', paddingRight: '1.5rem' }}>
                {children}
            </main>
            {shouldShowFooter && <Footer />}
            {/* 🐛 Debug visual del diseño responsive (solo en desarrollo) */}
            {/* {process.env.NODE_ENV === 'development' && <DesignDebugger />} */}
        </div>
    );
}
