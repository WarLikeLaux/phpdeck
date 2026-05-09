import { Link } from '@inertiajs/react';
import {
    AlertTriangle,
    BarChart3,
    BookOpen,
    GraduationCap,
    Layers,
    Repeat,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import flashcards from '@/routes/flashcards';
import learn from '@/routes/learn';
import review from '@/routes/review';
import stats from '@/routes/stats';
import study from '@/routes/study';
import troubled from '@/routes/troubled';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Карточки',
        href: flashcards.index().url,
        icon: Layers,
    },
    {
        title: 'Изучение',
        href: learn.show().url,
        icon: BookOpen,
    },
    {
        title: 'Проверка',
        href: study.show().url,
        icon: GraduationCap,
    },
    {
        title: 'Повторение',
        href: review.show().url,
        icon: Repeat,
    },
    {
        title: 'Проблемные',
        href: troubled.show().url,
        icon: AlertTriangle,
    },
    {
        title: 'Статистика',
        href: stats.show().url,
        icon: BarChart3,
    },
];

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={flashcards.index().url} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
