import { Form, Head, Link, router } from '@inertiajs/react';
import {
    ChevronLeft,
    ChevronRight,
    GraduationCap,
    Layers,
    PencilLine,
    Puzzle,
    RotateCcw,
    Search,
    Spline,
    X,
} from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';
import { CategoryBadge } from '@/components/category-badge';
import { CodeBlock } from '@/components/code-block';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { categoryStyle } from '@/lib/category-colors';
import { topicLabel } from '@/lib/topic-labels';
import { cn } from '@/lib/utils';
import flashcards from '@/routes/flashcards';
import study from '@/routes/study';
import type { Flashcard, FlashcardStats, Paginated } from '@/types';

type CategoryStat = {
    name: string;
    total: number;
    learned: number;
};

type Filters = {
    q: string;
    status: 'all' | 'due' | 'learned';
    category: string;
};

type Props = {
    flashcards: Paginated<Flashcard>;
    stats: FlashcardStats;
    categoryStats: CategoryStat[];
    filters: Filters;
};

const statusOptions: { value: Filters['status']; label: string }[] = [
    { value: 'all', label: 'Все' },
    { value: 'due', label: 'К повтору' },
    { value: 'learned', label: 'Выучено' },
];

export default function FlashcardsIndex({
    flashcards: cards,
    stats,
    categoryStats,
    filters,
}: Props) {
    const [search, setSearch] = useState(filters.q);
    const learnedPercent =
        stats.total === 0 ? 0 : Math.round((stats.learned / stats.total) * 100);

    const applyFilter = useCallback(
        (next: Partial<Filters>) => {
            const merged: Record<string, string> = {
                q: next.q ?? filters.q,
                status: next.status ?? filters.status,
                category: next.category ?? filters.category,
            };

            if (merged.q === '') {
                delete merged.q;
            }

            if (merged.status === 'all') {
                delete merged.status;
            }

            if (merged.category === 'all' || merged.category === '') {
                delete merged.category;
            }

            router.get(flashcards.index().url, merged, {
                preserveScroll: true,
                preserveState: true,
                replace: true,
            });
        },
        [filters.q, filters.status, filters.category],
    );

    useEffect(() => {
        if (search === filters.q) {
            return;
        }

        const t = window.setTimeout(() => {
            applyFilter({ q: search });
        }, 250);

        return () => window.clearTimeout(t);
    }, [search, filters.q, applyFilter]);

    const hasFilters =
        filters.q !== '' ||
        filters.status !== 'all' ||
        (filters.category !== 'all' && filters.category !== '');

    return (
        <>
            <Head title="Карточки" />

            <div className="flex flex-1 flex-col gap-5 px-4 pt-4 pb-24 sm:gap-6 sm:px-6 sm:pt-6 sm:pb-12">
                <Hero
                    stats={stats}
                    learnedPercent={learnedPercent}
                    categoryStats={categoryStats}
                    activeCategory={filters.category}
                    onCategoryClick={(c) => applyFilter({ category: c })}
                />

                <div className="flex flex-col gap-3">
                    <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3">
                        <div className="relative flex-1">
                            <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Поиск по вопросу, ответу, категории…"
                                className="h-10 pr-9 pl-9"
                            />
                            {search && (
                                <button
                                    type="button"
                                    onClick={() => setSearch('')}
                                    className="absolute top-1/2 right-2 -translate-y-1/2 rounded-md p-1 text-muted-foreground hover:bg-accent"
                                >
                                    <X className="size-4" />
                                </button>
                            )}
                        </div>
                        <div className="flex w-full gap-1 rounded-lg border bg-muted/40 p-1 sm:w-auto">
                            {statusOptions.map((opt) => (
                                <button
                                    key={opt.value}
                                    type="button"
                                    onClick={() =>
                                        applyFilter({ status: opt.value })
                                    }
                                    className={cn(
                                        'flex-1 rounded-md px-3 py-1.5 text-sm whitespace-nowrap transition-colors sm:flex-none',
                                        filters.status === opt.value
                                            ? 'bg-background text-foreground shadow-sm'
                                            : 'text-muted-foreground hover:text-foreground',
                                    )}
                                >
                                    {opt.label}
                                </button>
                            ))}
                        </div>
                    </div>

                    {filters.category !== 'all' && filters.category !== '' && (
                        <div className="flex flex-wrap items-center gap-2">
                            <span className="text-xs text-muted-foreground">
                                Категория:
                            </span>
                            <Badge
                                variant="outline"
                                className={cn(
                                    'gap-1.5',
                                    categoryStyle(filters.category).badge,
                                )}
                            >
                                {filters.category}
                                <button
                                    type="button"
                                    onClick={() =>
                                        applyFilter({ category: 'all' })
                                    }
                                    className="ml-1 rounded-full"
                                >
                                    <X className="size-3" />
                                </button>
                            </Badge>
                        </div>
                    )}
                </div>

                {cards.data.length === 0 ? (
                    <EmptyResult hasFilters={hasFilters} />
                ) : (
                    <>
                        <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                            {cards.data.map((card) => (
                                <FlashcardCard key={card.id} card={card} />
                            ))}
                        </div>
                        <Pagination page={cards} filters={filters} />
                    </>
                )}
            </div>
        </>
    );
}

function Hero({
    stats,
    learnedPercent,
    categoryStats,
    activeCategory,
    onCategoryClick,
}: {
    stats: FlashcardStats;
    learnedPercent: number;
    categoryStats: CategoryStat[];
    activeCategory: string;
    onCategoryClick: (c: string) => void;
}) {
    return (
        <div className="overflow-hidden rounded-2xl border bg-gradient-to-br from-violet-500/10 via-background to-fuchsia-500/10 p-5 sm:p-7">
            <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between sm:gap-6">
                <div className="flex flex-col gap-1">
                    <h1 className="text-2xl font-semibold tracking-tight sm:text-3xl">
                        Карточки
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        Тренажёр для подготовки к собеседованиям. Семь режимов
                        изучения чередуются автоматически.
                    </p>
                </div>

                <div className="flex flex-wrap items-center gap-2">
                    <Button
                        asChild
                        size="lg"
                        className="bg-gradient-to-r from-violet-600 to-fuchsia-600 text-white shadow-md hover:from-violet-700 hover:to-fuchsia-700"
                    >
                        <Link href={study.show().url}>
                            <GraduationCap />
                            Учить
                            {stats.due > 0 && (
                                <Badge
                                    variant="secondary"
                                    className="ml-1 bg-white/20 text-white"
                                >
                                    {stats.due}
                                </Badge>
                            )}
                        </Link>
                    </Button>
                </div>
            </div>

            <div className="mt-5 grid grid-cols-3 gap-3 sm:max-w-md">
                <Stat label="Всего" value={stats.total} />
                <Stat label="К повтору" value={stats.due} />
                <Stat
                    label="Выучено"
                    value={`${stats.learned} (${learnedPercent}%)`}
                />
            </div>

            {categoryStats.length > 0 && (
                <div className="-mx-1 mt-5 flex gap-2 overflow-x-auto px-1 pb-1 sm:flex-wrap sm:overflow-visible sm:pb-0">
                    <CategoryChip
                        active={
                            activeCategory === 'all' || activeCategory === ''
                        }
                        onClick={() => onCategoryClick('all')}
                        label={`Все · ${stats.total}`}
                        progress={
                            stats.total === 0
                                ? 0
                                : (stats.learned / stats.total) * 100
                        }
                    />
                    {categoryStats.map((c) => (
                        <CategoryChip
                            key={c.name}
                            active={activeCategory === c.name}
                            onClick={() => onCategoryClick(c.name)}
                            label={`${c.name} · ${c.learned}/${c.total}`}
                            progress={
                                c.total === 0 ? 0 : (c.learned / c.total) * 100
                            }
                            color={categoryStyle(c.name).dot}
                        />
                    ))}
                </div>
            )}

            {stats.total > 0 && (
                <div className="mt-5">
                    <Form action={flashcards.reset().url} method="post">
                        <Button
                            type="submit"
                            variant="ghost"
                            size="sm"
                            className="text-muted-foreground"
                        >
                            <RotateCcw />
                            Сбросить прогресс
                        </Button>
                    </Form>
                </div>
            )}
        </div>
    );
}

function Stat({ label, value }: { label: string; value: number | string }) {
    return (
        <div className="rounded-xl border bg-background/60 p-3 backdrop-blur">
            <p className="text-[11px] tracking-wide text-muted-foreground uppercase">
                {label}
            </p>
            <p className="mt-1 text-lg font-semibold tabular-nums">{value}</p>
        </div>
    );
}

function CategoryChip({
    label,
    active,
    onClick,
    progress,
    color,
}: {
    label: string;
    active: boolean;
    onClick: () => void;
    progress: number;
    color?: string;
}) {
    return (
        <button
            type="button"
            onClick={onClick}
            className={cn(
                'group flex shrink-0 flex-col gap-1.5 rounded-xl border bg-background/60 px-3 py-2 text-left text-xs backdrop-blur transition-all sm:shrink',
                active
                    ? 'border-foreground/40 shadow-sm'
                    : 'hover:border-foreground/20',
            )}
        >
            <div className="flex items-center gap-1.5">
                {color && (
                    <span className={cn('size-1.5 rounded-full', color)} />
                )}
                <span
                    className={cn(
                        'text-xs font-medium',
                        active
                            ? 'text-foreground'
                            : 'text-muted-foreground group-hover:text-foreground',
                    )}
                >
                    {label}
                </span>
            </div>
            <div className="h-1 w-24 overflow-hidden rounded-full bg-muted sm:w-32">
                <div
                    className="h-full bg-gradient-to-r from-violet-500 to-fuchsia-500 transition-all"
                    style={{ width: `${Math.min(100, progress)}%` }}
                />
            </div>
        </button>
    );
}

function Pagination({
    page,
    filters,
}: {
    page: Paginated<Flashcard>;
    filters: Filters;
}) {
    if (page.last_page <= 1) {
        return null;
    }

    const pages = pageRange(page.current_page, page.last_page);
    const buildHref = (n: number) => {
        const params: Record<string, string> = {};

        if (filters.q !== '') {
            params.q = filters.q;
        }

        if (filters.status !== 'all') {
            params.status = filters.status;
        }

        if (filters.category !== 'all' && filters.category !== '') {
            params.category = filters.category;
        }

        if (n > 1) {
            params.page = String(n);
        }

        const qs = new URLSearchParams(params).toString();

        return flashcards.index().url + (qs ? `?${qs}` : '');
    };

    return (
        <nav className="mt-2 flex flex-col items-center gap-3 sm:flex-row sm:justify-between">
            <p className="text-xs text-muted-foreground tabular-nums">
                {page.from ?? 0}–{page.to ?? 0} из {page.total}
            </p>
            <div className="flex items-center gap-1">
                <PageLink
                    href={
                        page.current_page > 1
                            ? buildHref(page.current_page - 1)
                            : null
                    }
                    aria-label="Назад"
                >
                    <ChevronLeft className="size-4" />
                </PageLink>
                {pages.map((p, i) =>
                    p === 'ellipsis' ? (
                        <span
                            key={`e-${i}`}
                            className="px-1 text-sm text-muted-foreground"
                        >
                            …
                        </span>
                    ) : (
                        <PageNumber
                            key={p}
                            href={buildHref(p)}
                            number={p}
                            active={p === page.current_page}
                        />
                    ),
                )}
                <PageLink
                    href={
                        page.current_page < page.last_page
                            ? buildHref(page.current_page + 1)
                            : null
                    }
                    aria-label="Вперёд"
                >
                    <ChevronRight className="size-4" />
                </PageLink>
            </div>
        </nav>
    );
}

function PageLink({
    href,
    children,
    ...rest
}: {
    href: string | null;
    children: React.ReactNode;
} & React.AriaAttributes) {
    const className = cn(
        'inline-flex h-8 min-w-8 items-center justify-center rounded-md border bg-background px-2 text-sm transition-colors',
        href
            ? 'hover:bg-accent hover:text-accent-foreground'
            : 'pointer-events-none opacity-40',
    );

    if (!href) {
        return (
            <span className={className} {...rest}>
                {children}
            </span>
        );
    }

    return (
        <Link
            href={href}
            preserveScroll={false}
            preserveState
            className={className}
            {...rest}
        >
            {children}
        </Link>
    );
}

function PageNumber({
    href,
    number,
    active,
}: {
    href: string;
    number: number;
    active: boolean;
}) {
    return (
        <Link
            href={href}
            preserveScroll={false}
            preserveState
            aria-current={active ? 'page' : undefined}
            className={cn(
                'inline-flex h-8 min-w-8 items-center justify-center rounded-md px-2 text-sm tabular-nums transition-colors',
                active
                    ? 'border bg-foreground text-background'
                    : 'border bg-background hover:bg-accent hover:text-accent-foreground',
            )}
        >
            {number}
        </Link>
    );
}

function pageRange(current: number, last: number): (number | 'ellipsis')[] {
    if (last <= 7) {
        return Array.from({ length: last }, (_, i) => i + 1);
    }

    const result: (number | 'ellipsis')[] = [1];
    const start = Math.max(2, current - 1);
    const end = Math.min(last - 1, current + 1);

    if (start > 2) {
        result.push('ellipsis');
    }

    for (let i = start; i <= end; i++) {
        result.push(i);
    }

    if (end < last - 1) {
        result.push('ellipsis');
    }

    result.push(last);

    return result;
}

function EmptyResult({ hasFilters }: { hasFilters: boolean }) {
    return (
        <Card className="border-dashed">
            <CardContent className="flex flex-col items-center gap-3 py-16 text-center">
                <div className="flex size-12 items-center justify-center rounded-xl bg-muted">
                    <Search className="size-6 text-muted-foreground" />
                </div>
                <CardTitle>
                    {hasFilters ? 'Ничего не найдено' : 'Пока нет карточек'}
                </CardTitle>
                <CardDescription className="max-w-sm">
                    {hasFilters
                        ? 'Попробуй изменить запрос или сбросить фильтры.'
                        : 'Карточки добавляются через сидеры — запусти php artisan db:seed.'}
                </CardDescription>
            </CardContent>
        </Card>
    );
}

function FlashcardCard({ card }: { card: Flashcard }) {
    const distinctModes = card.correct_modes?.length ?? 0;

    return (
        <Card className="flex flex-col overflow-hidden transition-shadow hover:shadow-md">
            <CardHeader className="gap-2">
                <div className="flex items-start justify-between gap-2">
                    <div className="flex flex-wrap items-center gap-2">
                        <CategoryBadge category={card.category} />
                        <DifficultyBadge level={card.difficulty} />
                        {card.topic && (
                            <Badge
                                variant="outline"
                                className="text-[10px]"
                                title={card.topic}
                            >
                                {topicLabel(card.topic)}
                            </Badge>
                        )}
                        {card.is_learned ? (
                            <Badge className="border-emerald-500/30 bg-emerald-500/15 text-emerald-700 dark:text-emerald-300">
                                выучено
                            </Badge>
                        ) : (
                            <Badge
                                variant="outline"
                                className="font-mono tabular-nums"
                                title="Различных режимов с правильным ответом"
                            >
                                {distinctModes}/{card.required_correct}
                            </Badge>
                        )}
                    </div>
                </div>
                <CardTitle className="text-base leading-snug">
                    {card.question}
                </CardTitle>
                <FlashcardModes card={card} />
            </CardHeader>
            <CardContent className="flex flex-1 flex-col gap-4">
                <p className="text-sm whitespace-pre-line text-muted-foreground">
                    {card.answer}
                </p>
                {card.code_example && (
                    <CodeBlock
                        code={card.code_example}
                        language={card.code_language}
                    />
                )}
            </CardContent>
        </Card>
    );
}

function DifficultyBadge({ level }: { level: number }) {
    const clamped = Math.max(1, Math.min(5, level));
    const tone =
        clamped <= 2
            ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300'
            : clamped === 3
              ? 'border-amber-500/30 bg-amber-500/10 text-amber-700 dark:text-amber-300'
              : 'border-rose-500/30 bg-rose-500/10 text-rose-700 dark:text-rose-300';

    return (
        <Badge
            variant="outline"
            className={cn('font-mono tabular-nums', tone)}
            title="Сложность"
        >
            {'★'.repeat(clamped)}
        </Badge>
    );
}

function FlashcardModes({ card }: { card: Flashcard }) {
    const modes: { label: string; icon: typeof PencilLine }[] = [];

    if (card.code_example) {
        modes.push({ label: 'код', icon: Layers });
    }

    if (card.cloze_text) {
        modes.push({ label: 'пропуски', icon: PencilLine });
    }

    if (card.short_answer) {
        modes.push({ label: 'ввод', icon: Spline });
    }

    if (card.assemble_chunks) {
        modes.push({ label: 'сборка', icon: Puzzle });
    }

    if (modes.length === 0) {
        return null;
    }

    return (
        <div className="flex flex-wrap gap-1">
            {modes.map(({ label, icon: Icon }) => (
                <Badge
                    key={label}
                    variant="secondary"
                    className="gap-1 px-1.5 py-0 text-[10px] font-normal"
                >
                    <Icon className="size-3" />
                    {label}
                </Badge>
            ))}
        </div>
    );
}
