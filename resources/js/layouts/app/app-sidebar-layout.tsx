import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import { AppSidebar } from '@/components/app-sidebar';
import { AppSidebarHeader } from '@/components/app-sidebar-header';
import type { AppLayoutProps } from '@/types';

export default function AppSidebarLayout({
    children,
    breadcrumbs = [],
}: AppLayoutProps) {
    return (
        <>
            <div
                aria-hidden
                className="pointer-events-none fixed inset-0 -z-10 hidden dark:block"
                style={{
                    background:
                        'radial-gradient(ellipse 60% 50% at 15% 0%, rgba(236,72,153,0.10), transparent 60%), radial-gradient(ellipse 50% 60% at 100% 100%, rgba(124,58,237,0.12), transparent 60%)',
                }}
            />
            <AppShell variant="sidebar">
                <AppSidebar />
                <AppContent variant="sidebar" className="overflow-x-hidden">
                    <AppSidebarHeader breadcrumbs={breadcrumbs} />
                    {children}
                </AppContent>
            </AppShell>
        </>
    );
}
