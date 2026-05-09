import { Form, Head, Link, router } from '@inertiajs/react';
import { ArrowRight, BookOpen, Check, GraduationCap } from 'lucide-react';
import { CategoryBadge } from '@/components/category-badge';
import { CodeBlock } from '@/components/code-block';
import { NoteBlock } from '@/components/note-block';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { useKeyboardShortcut } from '@/hooks/use-keyboard-shortcut';
import { topicLabel } from '@/lib/topic-labels';
import { cn } from '@/lib/utils';
import learn from '@/routes/learn';
import study from '@/routes/study';
import type { Flashcard } from '@/types';

type Filters = {
    category: string;
    topic: string | null;
};

type Stats = {
    total: number;
    unstudied: number;
    studied: number;
    learned: number;
};

type Props = {
    flashcard: Flashcard | null;
    stats: Stats;
    categories: string[];
    filters: Filters;
};

export default function LearnIndex({
    flashcard,
    stats,
    categories,
    filters,
}: Props) {
    const apply = (next: Partial<Filters>) => {
        const params: Record<string, string> = {};
        const category = next.category ?? filters.category;
        const topic = next.topic ?? filters.topic ?? '';

        if (category && category !== 'all') {
            params.category = category;
        }

        if (topic) {
            params.topic = topic;
        }

        router.get(learn.show().url, params, {
            preserveScroll: false,
            preserveState: true,
            replace: true,
        });
    };

    return (
        <>
            <Head title="Изучение" />
            <div className="mx-auto flex w-full max-w-2xl flex-col gap-4 px-4 pt-4 pb-6 sm:px-6 sm:pt-6">
                <div className="flex flex-col gap-3 rounded-2xl border border-violet-500/30 bg-violet-500/10 p-3 sm:flex-row sm:items-center sm:justify-between sm:p-4">
                    <div className="flex items-center gap-3">
                        <div className="flex size-9 shrink-0 items-center justify-center rounded-xl bg-background/80 text-violet-600 ring-1 ring-violet-500/40 dark:text-violet-300">
                            <BookOpen className="size-5" />
                        </div>
                        <div className="flex flex-col">
                            <span className="text-sm leading-tight font-semibold">
                                Изучение
                            </span>
                            <span className="text-xs text-muted-foreground">
                                Сначала запомни карточку — потом проверь себя.
                            </span>
                        </div>
                    </div>

                    <div className="flex items-center justify-between gap-2 text-xs text-muted-foreground sm:justify-end">
                        <Badge
                            variant="outline"
                            className="bg-background/60 tabular-nums"
                            title="Не изучено / всего"
                        >
                            {stats.unstudied}/{stats.total} осталось
                        </Badge>
                        <Badge
                            variant="outline"
                            className="bg-background/60 tabular-nums"
                            title="Готово к проверке"
                        >
                            {stats.studied} к проверке
                        </Badge>
                        <Button asChild size="sm" variant="outline">
                            <Link href={study.show().url}>
                                <GraduationCap />К проверке
                            </Link>
                        </Button>
                    </div>
                </div>

                <CategoryFilter
                    categories={categories}
                    active={filters.category}
                    topic={filters.topic}
                    onPick={(c) => apply({ category: c, topic: null })}
                    onClearTopic={() => apply({ topic: null })}
                />

                {flashcard ? (
                    <LearnCard flashcard={flashcard} filters={filters} />
                ) : (
                    <EmptyState stats={stats} />
                )}
            </div>
        </>
    );
}

function LearnCard({
    flashcard,
    filters,
}: {
    flashcard: Flashcard;
    filters: Filters;
}) {
    const studiedAction = `${learn.studied(flashcard.id).url}`;
    const skipAction = `${learn.skip(flashcard.id).url}`;

    const filterPayload: Record<string, string> = {};

    if (filters.category && filters.category !== 'all') {
        filterPayload.category = filters.category;
    }

    if (filters.topic) {
        filterPayload.topic = filters.topic;
    }

    useKeyboardShortcut('1', () => router.post(skipAction, filterPayload));
    useKeyboardShortcut('2', () => router.post(studiedAction, filterPayload));

    return (
        <Card>
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
                    {flashcard.is_learned && (
                        <Badge className="border-emerald-500/30 bg-emerald-500/15 text-emerald-700 dark:text-emerald-300">
                            выучено
                        </Badge>
                    )}
                </div>
                <CardTitle className="text-lg leading-snug sm:text-xl">
                    {flashcard.question}
                </CardTitle>
            </CardHeader>
            <CardContent className="flex flex-col gap-4">
                <div className="flex flex-col gap-2 rounded-md border border-emerald-500/30 bg-emerald-500/5 p-3">
                    <span className="text-[10px] font-semibold tracking-wide text-emerald-700 uppercase dark:text-emerald-300">
                        Ответ
                    </span>
                    <p className="text-base whitespace-pre-line">
                        {flashcard.answer}
                    </p>
                </div>

                {flashcard.short_answer && (
                    <div className="flex items-center gap-2 text-sm">
                        <span className="text-muted-foreground">
                            Короткий ответ:
                        </span>
                        <code className="rounded bg-muted px-2 py-0.5 font-mono text-sm">
                            {flashcard.short_answer}
                        </code>
                    </div>
                )}

                {flashcard.cloze_text && (
                    <ClozePreview text={flashcard.cloze_text} />
                )}

                {flashcard.assemble_chunks &&
                    flashcard.assemble_chunks.length > 0 && (
                        <AssemblePreview chunks={flashcard.assemble_chunks} />
                    )}

                {flashcard.code_example && (
                    <CodeBlock
                        code={flashcard.code_example}
                        language={flashcard.code_language}
                    />
                )}

                <NoteBlock note={flashcard.note} />
            </CardContent>
            <CardFooter className="grid grid-cols-2 gap-2 sm:flex sm:justify-end">
                <Form
                    action={skipAction}
                    method="post"
                    className="w-full sm:w-auto"
                >
                    <FilterInputs filters={filters} />
                    <Button
                        type="submit"
                        variant="outline"
                        className="w-full sm:w-auto"
                    >
                        Дальше
                        <ArrowRight />
                    </Button>
                </Form>
                <Form
                    action={studiedAction}
                    method="post"
                    className="w-full sm:w-auto"
                >
                    <FilterInputs filters={filters} />
                    <Button type="submit" className="w-full sm:w-auto">
                        <Check />
                        Изучил
                    </Button>
                </Form>
            </CardFooter>
        </Card>
    );
}

function FilterInputs({ filters }: { filters: Filters }) {
    return (
        <>
            {filters.category && filters.category !== 'all' && (
                <input type="hidden" name="category" value={filters.category} />
            )}
            {filters.topic && (
                <input type="hidden" name="topic" value={filters.topic} />
            )}
        </>
    );
}

function ClozePreview({ text }: { text: string }) {
    const parts = text.split(/(\{\{.+?\}\})/g);

    return (
        <div className="flex flex-col gap-1.5">
            <span className="text-[10px] font-semibold tracking-wide text-muted-foreground uppercase">
                Пропуски (cloze)
            </span>
            <pre className="rounded-md border bg-muted/40 p-3 text-sm whitespace-pre-wrap">
                {parts.map((p, i) => {
                    const m = p.match(/^\{\{(.+?)\}\}$/);

                    if (m) {
                        return (
                            <code
                                key={i}
                                className="rounded bg-cyan-500/15 px-1 font-mono text-cyan-700 dark:text-cyan-300"
                            >
                                {m[1]}
                            </code>
                        );
                    }

                    return <span key={i}>{p}</span>;
                })}
            </pre>
        </div>
    );
}

function AssemblePreview({ chunks }: { chunks: string[] }) {
    return (
        <div className="flex flex-col gap-1.5">
            <span className="text-[10px] font-semibold tracking-wide text-muted-foreground uppercase">
                Сборка (порядок)
            </span>
            <pre className="rounded-md border bg-muted/40 p-3 font-mono text-sm whitespace-pre-wrap">
                {chunks.join('')}
            </pre>
        </div>
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

function CategoryFilter({
    categories,
    active,
    topic,
    onPick,
    onClearTopic,
}: {
    categories: string[];
    active: string;
    topic: string | null;
    onPick: (c: string) => void;
    onClearTopic: () => void;
}) {
    return (
        <div className="flex flex-wrap items-center gap-2">
            <button
                type="button"
                onClick={() => onPick('all')}
                className={cn(
                    'rounded-full border px-3 py-1 text-xs transition-colors',
                    active === 'all'
                        ? 'border-foreground/40 bg-foreground text-background'
                        : 'bg-background hover:border-foreground/20',
                )}
            >
                Все
            </button>
            {categories.map((c) => (
                <button
                    key={c}
                    type="button"
                    onClick={() => onPick(c)}
                    className={cn(
                        'rounded-full border px-3 py-1 text-xs transition-colors',
                        active === c
                            ? 'border-foreground/40 bg-foreground text-background'
                            : 'bg-background hover:border-foreground/20',
                    )}
                >
                    {c}
                </button>
            ))}
            {topic && (
                <Badge variant="outline" className="gap-1 text-[10px]">
                    {topicLabel(topic)}
                    <button
                        type="button"
                        onClick={onClearTopic}
                        className="ml-1 rounded-full hover:text-destructive"
                        aria-label="Сбросить топик"
                    >
                        ×
                    </button>
                </Badge>
            )}
        </div>
    );
}

function EmptyState({ stats }: { stats: Stats }) {
    const noCards = stats.total === 0;
    const allStudied = !noCards && stats.unstudied === 0;

    return (
        <Card>
            <CardContent className="flex flex-col items-center gap-3 py-12 text-center">
                {noCards && (
                    <>
                        <CardTitle>Нет карточек по фильтрам</CardTitle>
                        <CardDescription>
                            Сбрось фильтры или загрузи карточки сидером
                            (php artisan db:seed).
                        </CardDescription>
                        <Button asChild variant="outline">
                            <Link href={learn.show().url}>Сбросить</Link>
                        </Button>
                    </>
                )}
                {allStudied && (
                    <>
                        <CardTitle>Все карточки изучены</CardTitle>
                        <CardDescription>
                            Переходи к проверке, чтобы закрепить материал.
                        </CardDescription>
                        <Button asChild>
                            <Link href={study.show().url}>
                                <GraduationCap />К проверке
                            </Link>
                        </Button>
                    </>
                )}
            </CardContent>
        </Card>
    );
}
