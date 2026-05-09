import { Form, Head, Link } from '@inertiajs/react';
import {
    AlertTriangle,
    BookOpen,
    Check,
    ChevronLeft,
    ChevronRight,
} from 'lucide-react';
import { CategoryBadge } from '@/components/category-badge';
import { CodeBlock } from '@/components/code-block';
import { NoteBlock } from '@/components/note-block';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { topicLabel } from '@/lib/topic-labels';
import { cn } from '@/lib/utils';
import learn from '@/routes/learn';
import troubled from '@/routes/troubled';
import type { Flashcard } from '@/types';

type Metrics = {
    total: number;
    bad: number;
    incorrect: number;
    matching_incorrect: number;
    forgot: number;
    skipped: number;
    error_rate: number;
    last_seen: string;
};

type Row = {
    flashcard: Flashcard;
    metrics: Metrics;
};

type Pagination = {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
};

type Props = {
    rows: Row[];
    pagination: Pagination;
    window_days: number;
    min_events: number;
};

export default function TroubledIndex({
    rows,
    pagination,
    window_days,
    min_events,
}: Props) {
    return (
        <>
            <Head title="Проблемные" />
            <div className="mx-auto flex w-full max-w-4xl flex-col gap-4 px-4 pt-4 pb-6 sm:px-6 sm:pt-6">
                <Header total={pagination.total} windowDays={window_days} />

                {rows.length === 0 ? (
                    <EmptyState minEvents={min_events} />
                ) : (
                    <>
                        <div className="flex flex-col gap-3">
                            {rows.map((row) => (
                                <TroubledRow key={row.flashcard.id} row={row} />
                            ))}
                        </div>
                        <PaginationNav pagination={pagination} />
                    </>
                )}
            </div>
        </>
    );
}

function Header({ total, windowDays }: { total: number; windowDays: number }) {
    return (
        <div className="flex items-center gap-2 rounded-2xl border border-rose-500/30 bg-rose-500/10 px-3 py-2 sm:px-4 sm:py-2.5">
            <div className="flex size-8 shrink-0 items-center justify-center rounded-lg bg-background/80 text-rose-600 ring-1 ring-rose-500/40 dark:text-rose-300">
                <AlertTriangle className="size-4" />
            </div>
            <span className="text-sm font-semibold">Проблемные</span>

            <div className="ml-auto flex items-center gap-2 text-xs text-muted-foreground">
                <Badge
                    variant="outline"
                    className="hidden bg-background/60 tabular-nums sm:inline-flex"
                    title="Окно анализа"
                >
                    {windowDays} дн
                </Badge>
                <Badge
                    variant="outline"
                    className="bg-background/60 tabular-nums"
                    title="Всего проблемных карточек"
                >
                    {total}
                </Badge>
            </div>
        </div>
    );
}

function TroubledRow({ row }: { row: Row }) {
    const { flashcard, metrics } = row;
    const errorPct = Math.round(metrics.error_rate * 100);

    return (
        <Card className="overflow-hidden">
            <CardHeader className="gap-2">
                <div className="flex flex-wrap items-center gap-2">
                    <CategoryBadge category={flashcard.category} />
                    <DifficultyBadge level={flashcard.difficulty} />
                    {flashcard.topic && (
                        <Badge
                            variant="outline"
                            className="text-[10px]"
                            title={flashcard.topic}
                        >
                            {topicLabel(flashcard.topic)}
                        </Badge>
                    )}
                    <ErrorRateBadge percent={errorPct} />
                </div>
                <CardTitle className="text-base leading-snug">
                    {flashcard.question}
                </CardTitle>
                <MetricsRow metrics={metrics} />
            </CardHeader>
            <CardContent className="flex flex-col gap-4">
                <p className="text-sm whitespace-pre-line text-muted-foreground">
                    {flashcard.answer}
                </p>
                {flashcard.code_example && (
                    <CodeBlock
                        code={flashcard.code_example}
                        language={flashcard.code_language}
                    />
                )}
                <NoteBlock note={flashcard.note} />
                <div className="flex flex-wrap gap-2">
                    <Form
                        action={troubled.clear(flashcard.id).url}
                        method="post"
                    >
                        <Button type="submit" size="sm">
                            <Check />
                            Проработал
                        </Button>
                    </Form>
                    <Button asChild size="sm" variant="outline">
                        <Link href={learn.show().url}>
                            <BookOpen />К изучению
                        </Link>
                    </Button>
                </div>
            </CardContent>
        </Card>
    );
}

function MetricsRow({ metrics }: { metrics: Metrics }) {
    const items: { label: string; value: number; tone: string }[] = [];

    if (metrics.incorrect > 0) {
        items.push({
            label: 'неверно',
            value: metrics.incorrect,
            tone: 'text-rose-700 dark:text-rose-300',
        });
    }

    if (metrics.matching_incorrect > 0) {
        items.push({
            label: 'matching ошибки',
            value: metrics.matching_incorrect,
            tone: 'text-rose-700 dark:text-rose-300',
        });
    }

    if (metrics.forgot > 0) {
        items.push({
            label: 'забыл',
            value: metrics.forgot,
            tone: 'text-rose-700 dark:text-rose-300',
        });
    }

    if (metrics.skipped > 0) {
        items.push({
            label: 'пропусков',
            value: metrics.skipped,
            tone: 'text-amber-700 dark:text-amber-300',
        });
    }

    return (
        <div className="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-muted-foreground tabular-nums">
            <span>
                {metrics.bad}/{metrics.total} событий
            </span>
            {items.map((it) => (
                <span key={it.label} className={cn('font-medium', it.tone)}>
                    {it.value} {it.label}
                </span>
            ))}
        </div>
    );
}

function ErrorRateBadge({ percent }: { percent: number }) {
    const tone =
        percent >= 60
            ? 'border-rose-500/40 bg-rose-500/15 text-rose-700 dark:text-rose-300'
            : percent >= 40
              ? 'border-amber-500/40 bg-amber-500/15 text-amber-700 dark:text-amber-300'
              : 'border-amber-500/30 bg-amber-500/10 text-amber-700 dark:text-amber-300';

    return (
        <Badge
            variant="outline"
            className={cn('font-mono tabular-nums', tone)}
            title="Доля плохих событий за окно"
        >
            {percent}%
        </Badge>
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

function PaginationNav({ pagination }: { pagination: Pagination }) {
    if (pagination.last_page <= 1) {
        return null;
    }

    const pages = pageRange(pagination.current_page, pagination.last_page);

    const buildHref = (n: number) => {
        const url = troubled.show().url;

        return n > 1 ? `${url}?page=${n}` : url;
    };

    return (
        <nav className="mt-2 flex flex-col items-center gap-3 sm:flex-row sm:justify-between">
            <p className="text-xs text-muted-foreground tabular-nums">
                {pagination.from}–{pagination.to} из {pagination.total}
            </p>
            <div className="flex items-center gap-1">
                <PageLink
                    href={
                        pagination.current_page > 1
                            ? buildHref(pagination.current_page - 1)
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
                            active={p === pagination.current_page}
                        />
                    ),
                )}
                <PageLink
                    href={
                        pagination.current_page < pagination.last_page
                            ? buildHref(pagination.current_page + 1)
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

function EmptyState({ minEvents }: { minEvents: number }) {
    return (
        <Card>
            <CardContent className="flex flex-col items-center gap-3 py-12 text-center">
                <CardTitle>Проблемных пока нет</CardTitle>
                <CardDescription>
                    Карточка попадает сюда, когда у неё минимум {minEvents}{' '}
                    событий за 30 дней и хотя бы одно из них — ошибка, «забыл»
                    или пропуск.
                </CardDescription>
            </CardContent>
        </Card>
    );
}
