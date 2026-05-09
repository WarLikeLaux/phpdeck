import { Head } from '@inertiajs/react';
import { BarChart3, Flame, GraduationCap, Layers, Target } from 'lucide-react';
import { CategoryBadge } from '@/components/category-badge';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { topicLabel } from '@/lib/topic-labels';
import { cn } from '@/lib/utils';

type DailyEntry = {
    date: string;
    studied: number;
    correct: number;
    incorrect: number;
    remembered: number;
    forgot: number;
};

type Today = {
    studied: number;
    correct: number;
    incorrect: number;
    remembered: number;
    forgot: number;
};

type Totals = {
    total: number;
    studied: number;
    learned: number;
    graduated: number;
    due_now: number;
};

type CategoryRow = {
    name: string;
    total: number;
    learned: number;
    accuracy: number | null;
};

type WeakTopic = {
    topic: string;
    errors: number;
    total: number;
    error_rate: number;
};

type Props = {
    streak: number;
    today: Today;
    daily: DailyEntry[];
    totals: Totals;
    categories: CategoryRow[];
    weak_topics: WeakTopic[];
};

function entryTotal(d: DailyEntry): number {
    return d.studied + d.correct + d.incorrect + d.remembered + d.forgot;
}

function formatDayLabel(iso: string): string {
    const [, m, d] = iso.split('-');

    return `${d}.${m}`;
}

function formatPercent(value: number): string {
    return `${Math.round(value * 100)}%`;
}

function accuracyTone(accuracy: number): string {
    if (accuracy >= 0.8) {
        return 'bg-emerald-500';
    }

    if (accuracy >= 0.5) {
        return 'bg-amber-500';
    }

    return 'bg-rose-500';
}

function errorRateTone(rate: number): string {
    if (rate >= 0.5) {
        return 'border-rose-500/30 bg-rose-500/10 text-rose-700 dark:text-rose-300';
    }

    if (rate >= 0.25) {
        return 'border-amber-500/30 bg-amber-500/10 text-amber-700 dark:text-amber-300';
    }

    return 'border-emerald-500/30 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300';
}

export default function StatsIndex({
    streak,
    today,
    daily,
    totals,
    categories,
    weak_topics: weakTopics,
}: Props) {
    const todayTotal = today.studied + today.correct;
    const maxBar = Math.max(1, ...daily.map(entryTotal));

    return (
        <>
            <Head title="Статистика" />

            <div className="mx-auto flex w-full max-w-6xl flex-col gap-5 px-4 pt-4 pb-12 sm:gap-6 sm:px-6 sm:pt-6">
                <div className="flex flex-col gap-1">
                    <h1 className="text-2xl font-semibold tracking-tight sm:text-3xl">
                        Статистика
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        Активность, точность и слабые места — за последние 14 и
                        30 дней.
                    </p>
                </div>

                <div className="grid grid-cols-2 gap-3 md:grid-cols-4">
                    <KpiCard
                        label="Стрик"
                        value={streak}
                        suffix={streak === 1 ? 'день' : 'дней'}
                        icon={<Flame className="size-5 text-orange-500" />}
                        accent="from-orange-500/10 to-rose-500/10"
                    />
                    <KpiCard
                        label="Сегодня"
                        value={todayTotal}
                        hint={`${today.correct} верных · ${today.studied} новых`}
                        icon={<Target className="size-5 text-violet-500" />}
                        accent="from-violet-500/10 to-fuchsia-500/10"
                    />
                    <KpiCard
                        label="Выучено"
                        value={totals.learned}
                        hint={`из ${totals.total}`}
                        icon={
                            <GraduationCap className="size-5 text-emerald-500" />
                        }
                        accent="from-emerald-500/10 to-teal-500/10"
                    />
                    <KpiCard
                        label="К повтору"
                        value={totals.due_now}
                        hint={`${totals.graduated} завершено`}
                        icon={<Layers className="size-5 text-sky-500" />}
                        accent="from-sky-500/10 to-indigo-500/10"
                    />
                </div>

                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between gap-2">
                            <div className="min-w-0">
                                <CardTitle>Активность за 14 дней</CardTitle>
                                <CardDescription>
                                    Сумма всех действий за день: новые, ответы,
                                    повторение.
                                </CardDescription>
                            </div>
                            <BarChart3 className="size-5 shrink-0 text-muted-foreground" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <DailyChart entries={daily} maxValue={maxBar} />
                    </CardContent>
                </Card>

                <div className="grid gap-4 lg:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>По категориям</CardTitle>
                            <CardDescription>
                                Точность за последние 30 дней (study-режимы).
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            {categories.length === 0 ? (
                                <p className="py-6 text-center text-sm text-muted-foreground">
                                    Пока нет категорий.
                                </p>
                            ) : (
                                <ul className="flex flex-col divide-y">
                                    {categories.map((c) => (
                                        <CategoryRowItem key={c.name} row={c} />
                                    ))}
                                </ul>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Слабые темы</CardTitle>
                            <CardDescription>
                                Топ-5 тем с наивысшим процентом ошибок (≥ 3
                                событий за 30 дней).
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            {weakTopics.length === 0 ? (
                                <p className="py-6 text-center text-sm text-muted-foreground">
                                    Слабых тем пока не выявлено — продолжайте
                                    практиковаться.
                                </p>
                            ) : (
                                <ul className="flex flex-col gap-2">
                                    {weakTopics.map((t, idx) => (
                                        <WeakTopicRow
                                            key={t.topic}
                                            rank={idx + 1}
                                            topic={t}
                                        />
                                    ))}
                                </ul>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}

function KpiCard({
    label,
    value,
    hint,
    suffix,
    icon,
    accent,
}: {
    label: string;
    value: number;
    hint?: string;
    suffix?: string;
    icon: React.ReactNode;
    accent: string;
}) {
    return (
        <Card className={cn('overflow-hidden bg-gradient-to-br', accent)}>
            <CardContent className="flex flex-col gap-2 p-3 sm:p-4">
                <div className="flex items-center justify-between gap-1">
                    <span className="text-[11px] tracking-wide text-muted-foreground uppercase truncate">
                        {label}
                    </span>
                    <span className="shrink-0">{icon}</span>
                </div>
                <div className="flex flex-wrap items-baseline gap-x-1.5">
                    <span className="text-xl font-semibold tabular-nums sm:text-2xl">
                        {value}
                    </span>
                    {suffix && (
                        <span className="text-xs text-muted-foreground sm:text-sm">
                            {suffix}
                        </span>
                    )}
                </div>
                {hint && (
                    <span className="text-[11px] text-muted-foreground sm:text-xs">
                        {hint}
                    </span>
                )}
            </CardContent>
        </Card>
    );
}

function DailyChart({
    entries,
    maxValue,
}: {
    entries: DailyEntry[];
    maxValue: number;
}) {
    return (
        <div className="flex items-end gap-1.5 sm:gap-2">
            {entries.map((d, idx) => {
                const total = entryTotal(d);
                const heightPct =
                    total === 0 ? 4 : Math.max(8, (total / maxValue) * 100);
                const tooltip = [
                    `Дата: ${d.date}`,
                    `Всего: ${total}`,
                    `Новые: ${d.studied}`,
                    `Верно: ${d.correct}`,
                    `Ошибки: ${d.incorrect}`,
                    `Повторено: ${d.remembered}`,
                    `Забыто: ${d.forgot}`,
                ].join('\n');
                const isLast = idx === entries.length - 1;
                const showLabelMobile = idx % 3 === 0 || isLast;

                return (
                    <div
                        key={d.date}
                        className="flex flex-1 flex-col items-center gap-1.5"
                    >
                        <div
                            className="flex h-32 w-full items-end justify-center sm:h-40"
                            title={tooltip}
                        >
                            <div
                                className={cn(
                                    'w-full rounded-t-md transition-all',
                                    total === 0
                                        ? 'bg-muted'
                                        : 'bg-gradient-to-t from-violet-500 to-fuchsia-500',
                                )}
                                style={{ height: `${heightPct}%` }}
                            />
                        </div>
                        <span
                            className={cn(
                                'text-[10px] text-muted-foreground tabular-nums sm:text-xs sm:inline',
                                showLabelMobile ? 'inline' : 'hidden',
                            )}
                        >
                            {formatDayLabel(d.date)}
                        </span>
                    </div>
                );
            })}
        </div>
    );
}

function CategoryRowItem({ row }: { row: CategoryRow }) {
    const learnedPercent =
        row.total === 0 ? 0 : Math.round((row.learned / row.total) * 100);
    const accuracyPercent =
        row.accuracy === null ? 0 : Math.round(row.accuracy * 100);

    return (
        <li className="flex flex-col gap-2 py-3 first:pt-0 last:pb-0">
            <div className="flex items-center justify-between gap-3">
                <CategoryBadge category={row.name} />
                <span className="text-xs text-muted-foreground tabular-nums">
                    {row.learned}/{row.total} · {learnedPercent}%
                </span>
            </div>
            <div className="flex items-center gap-2">
                <div className="h-1.5 flex-1 overflow-hidden rounded-full bg-muted">
                    {row.accuracy === null ? (
                        <div className="h-full w-0" />
                    ) : (
                        <div
                            className={cn(
                                'h-full rounded-full transition-all',
                                accuracyTone(row.accuracy),
                            )}
                            style={{ width: `${accuracyPercent}%` }}
                        />
                    )}
                </div>
                <span className="w-14 text-right text-xs text-muted-foreground tabular-nums">
                    {row.accuracy === null ? '—' : `${accuracyPercent}%`}
                </span>
            </div>
        </li>
    );
}

function WeakTopicRow({ rank, topic }: { rank: number; topic: WeakTopic }) {
    const label = topicLabel(topic.topic) ?? topic.topic;

    return (
        <li className="flex items-center justify-between gap-3 rounded-lg border bg-background/60 p-3">
            <div className="flex min-w-0 items-center gap-3">
                <span className="flex size-7 shrink-0 items-center justify-center rounded-md bg-muted text-xs font-semibold text-muted-foreground tabular-nums">
                    {rank}
                </span>
                <div className="flex min-w-0 flex-col">
                    <span className="truncate text-sm font-medium">
                        {label}
                    </span>
                    <span className="text-xs text-muted-foreground tabular-nums">
                        {topic.errors} из {topic.total} событий
                    </span>
                </div>
            </div>
            <Badge
                variant="outline"
                className={cn(
                    'shrink-0 font-mono tabular-nums',
                    errorRateTone(topic.error_rate),
                )}
            >
                {formatPercent(topic.error_rate)}
            </Badge>
        </li>
    );
}
