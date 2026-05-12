type CategoryStyle = {
    badge: string;
    dot: string;
};

const palette: Record<string, CategoryStyle> = {
    PHP: {
        badge: 'border-indigo-500/30 bg-indigo-500/10 text-indigo-600 dark:text-indigo-300',
        dot: 'bg-indigo-500',
    },
    Laravel: {
        badge: 'border-rose-500/30 bg-rose-500/10 text-rose-600 dark:text-rose-300',
        dot: 'bg-rose-500',
    },
    'ООП': {
        badge: 'border-amber-500/30 bg-amber-500/10 text-amber-700 dark:text-amber-300',
        dot: 'bg-amber-500',
    },
    'Базы данных': {
        badge: 'border-emerald-500/30 bg-emerald-500/10 text-emerald-600 dark:text-emerald-300',
        dot: 'bg-emerald-500',
    },
    'Архитектура систем': {
        badge: 'border-violet-500/30 bg-violet-500/10 text-violet-600 dark:text-violet-300',
        dot: 'bg-violet-500',
    },
    'Сети': {
        badge: 'border-sky-500/30 bg-sky-500/10 text-sky-600 dark:text-sky-300',
        dot: 'bg-sky-500',
    },
    'Безопасность': {
        badge: 'border-orange-500/30 bg-orange-500/10 text-orange-600 dark:text-orange-300',
        dot: 'bg-orange-500',
    },
    'Тестирование': {
        badge: 'border-teal-500/30 bg-teal-500/10 text-teal-600 dark:text-teal-300',
        dot: 'bg-teal-500',
    },
};

const fallback: CategoryStyle = {
    badge: 'border-border bg-muted text-muted-foreground',
    dot: 'bg-muted-foreground',
};

export function categoryStyle(category: string | null): CategoryStyle {
    if (!category) {
        return fallback;
    }

    return palette[category] ?? fallback;
}

export const knownCategories = Object.keys(palette);
