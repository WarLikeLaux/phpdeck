import { Form, Head, Link, router } from '@inertiajs/react';
import {
    Check,
    Eye,
    GraduationCap,
    PartyPopper,
    Repeat,
    RotateCcw,
    X,
} from 'lucide-react';
import { useState } from 'react';
import { CategoryBadge } from '@/components/category-badge';
import { CodeBlock } from '@/components/code-block';
import { MarkdownAnswer } from '@/components/markdown-answer';
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
import { Separator } from '@/components/ui/separator';
import { useKeyboardShortcut } from '@/hooks/use-keyboard-shortcut';
import { topicLabel } from '@/lib/topic-labels';
import { cn } from '@/lib/utils';
import flashcards from '@/routes/flashcards';
import review from '@/routes/review';
import study from '@/routes/study';
import type { Flashcard } from '@/types';

type Stats = {
    total: number;
    seen: number;
    remaining: number;
};

type Props = {
    flashcard: Flashcard | null;
    stats: Stats;
};

export default function ReviewIndex({ flashcard, stats }: Props) {
    return (
        <>
            <Head title="Повторение" />
            <div className="mx-auto flex w-full max-w-2xl flex-col gap-4 px-4 pt-4 pb-6 sm:px-6 sm:pt-6">
                <Header stats={stats} />
                {flashcard ? (
                    <ReviewCard
                        flashcard={flashcard}
                        key={flashcard.id}
                        position={stats.seen + 1}
                        total={stats.total}
                    />
                ) : (
                    <EmptyState stats={stats} />
                )}
            </div>
        </>
    );
}

function Header({ stats }: { stats: Stats }) {
    return (
        <div className="flex items-center gap-2 rounded-2xl border border-amber-500/30 bg-amber-500/10 px-3 py-2 sm:px-4 sm:py-2.5">
            <div className="flex size-8 shrink-0 items-center justify-center rounded-lg bg-background/80 text-amber-600 ring-1 ring-amber-500/40 dark:text-amber-300">
                <Repeat className="size-4" />
            </div>
            <span className="text-sm font-semibold">Повторение</span>

            <div className="ml-auto flex items-center gap-2 text-xs text-muted-foreground">
                <Badge
                    variant="outline"
                    className="bg-background/60 tabular-nums"
                    title="Пройдено / всего выученных"
                >
                    {stats.seen}/{stats.total}
                </Badge>
                {stats.seen > 0 && (
                    <Form action={review.reset().url} method="post">
                        <Button
                            type="submit"
                            size="sm"
                            variant="ghost"
                            aria-label="Сброс"
                        >
                            <RotateCcw />
                            <span className="hidden sm:inline">Сброс</span>
                        </Button>
                    </Form>
                )}
            </div>
        </div>
    );
}

function ReviewCard({
    flashcard,
    position,
    total,
}: {
    flashcard: Flashcard;
    position: number;
    total: number;
}) {
    const [revealed, setRevealed] = useState(false);

    useKeyboardShortcut(' ', () => setRevealed(true), !revealed);
    useKeyboardShortcut(
        '1',
        () => router.post(review.forgot(flashcard.id).url),
        revealed,
    );
    useKeyboardShortcut(
        '2',
        () => router.post(review.skip(flashcard.id).url),
        revealed,
    );
    useKeyboardShortcut(
        '3',
        () => router.post(review.remember(flashcard.id).url),
        revealed,
    );

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
                    <Badge
                        variant="outline"
                        className="ml-auto bg-background/60 tabular-nums"
                    >
                        {position}/{total}
                    </Badge>
                </div>
                <CardTitle className="text-lg leading-snug sm:text-xl">
                    {flashcard.question}
                </CardTitle>
            </CardHeader>
            <CardContent className="flex flex-col gap-4">
                {revealed ? (
                    <>
                        <div className="flex flex-col gap-2 rounded-md border border-emerald-500/30 bg-emerald-500/5 p-3">
                            <span className="text-[10px] font-semibold tracking-wide text-emerald-700 uppercase dark:text-emerald-300">
                                Ответ
                            </span>
                            <MarkdownAnswer content={flashcard.answer} />
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
                        {flashcard.code_example && (
                            <CodeBlock
                                code={flashcard.code_example}
                                language={flashcard.code_language}
                            />
                        )}
                        <NoteBlock note={flashcard.note} />
                    </>
                ) : (
                    <Button
                        type="button"
                        variant="outline"
                        onClick={() => setRevealed(true)}
                        className="w-full"
                    >
                        <Eye />
                        Показать ответ
                    </Button>
                )}
            </CardContent>
            {revealed && (
                <>
                    <Separator />
                    <CardFooter className="grid grid-cols-3 gap-2 px-3 [&_svg]:hidden sm:flex sm:justify-end sm:px-6 sm:[&_svg]:inline-block">
                        <Form
                            action={review.forgot(flashcard.id).url}
                            method="post"
                            className="w-full sm:w-auto"
                        >
                            <Button
                                type="submit"
                                variant="destructive"
                                className="w-full sm:w-auto"
                            >
                                <X />
                                Забыл
                            </Button>
                        </Form>
                        <Form
                            action={review.skip(flashcard.id).url}
                            method="post"
                            className="w-full sm:w-auto"
                        >
                            <Button
                                type="submit"
                                variant="outline"
                                className="w-full sm:w-auto"
                            >
                                <Repeat />
                                Повторить
                            </Button>
                        </Form>
                        <Form
                            action={review.remember(flashcard.id).url}
                            method="post"
                            className="w-full sm:w-auto"
                        >
                            <Button type="submit" className="w-full sm:w-auto">
                                <Check />
                                Помню
                            </Button>
                        </Form>
                    </CardFooter>
                </>
            )}
        </Card>
    );
}

function EmptyState({ stats }: { stats: Stats }) {
    const noLearned = stats.total === 0;
    const allSeen = !noLearned && stats.remaining === 0;

    return (
        <Card>
            <CardContent className="flex flex-col items-center gap-3 py-12 text-center">
                {noLearned && (
                    <>
                        <CardTitle>Пока нет выученных карточек</CardTitle>
                        <CardDescription>
                            Сначала пройди карточки в проверке, чтобы они
                            появились здесь.
                        </CardDescription>
                        <Button asChild>
                            <Link href={study.show().url}>
                                <GraduationCap />К проверке
                            </Link>
                        </Button>
                    </>
                )}
                {allSeen && (
                    <>
                        <PartyPopper className="size-10 text-primary" />
                        <CardTitle>Все выученные пройдены</CardTitle>
                        <CardDescription>
                            Сбрось сессию, чтобы прогнать колоду заново.
                        </CardDescription>
                        <div className="flex gap-2">
                            <Form action={review.reset().url} method="post">
                                <Button type="submit">
                                    <RotateCcw />
                                    Начать заново
                                </Button>
                            </Form>
                            <Button asChild variant="outline">
                                <Link href={flashcards.index().url}>
                                    К карточкам
                                </Link>
                            </Button>
                        </div>
                    </>
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
