import { Form, Head, Link } from '@inertiajs/react';
import { BookOpen, PartyPopper, RotateCcw } from 'lucide-react';
import { AssembleMode } from '@/components/study/assemble-mode';
import { ClozeMode } from '@/components/study/cloze-mode';
import { MatchingMode } from '@/components/study/matching-mode';
import { MultipleChoiceMode } from '@/components/study/multiple-choice-mode';
import { RevealMode } from '@/components/study/reveal-mode';
import { TrueFalseMode } from '@/components/study/true-false-mode';
import { TypeInMode } from '@/components/study/type-in-mode';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardTitle,
} from '@/components/ui/card';
import { studyModeMeta } from '@/lib/study-modes';
import { cn } from '@/lib/utils';
import flashcards from '@/routes/flashcards';
import learn from '@/routes/learn';
import type {
    Flashcard,
    FlashcardStats,
    StudyAssemble,
    StudyMatching,
    StudyMode,
    StudyOption,
    StudyShown,
} from '@/types';

type Props = {
    mode: StudyMode | null;
    flashcard: Flashcard | null;
    shown: StudyShown | null;
    options: StudyOption[] | null;
    assemble: StudyAssemble | null;
    matching: StudyMatching | null;
    stats: FlashcardStats;
};

export default function StudyIndex({
    mode,
    flashcard,
    shown,
    options,
    assemble,
    matching,
    stats,
}: Props) {
    if (!mode) {
        return <EmptyState stats={stats} />;
    }

    if (mode === 'matching' && matching) {
        return (
            <Wrapper mode={mode} stats={stats}>
                <MatchingMode matching={matching} />
            </Wrapper>
        );
    }

    if (!flashcard) {
        return <EmptyState stats={stats} />;
    }

    return (
        <Wrapper mode={mode} stats={stats} flashcard={flashcard}>
            {mode === 'reveal' && (
                <RevealMode flashcard={flashcard} key={flashcard.id} />
            )}
            {mode === 'true_false' && shown && (
                <TrueFalseMode
                    flashcard={flashcard}
                    shown={shown}
                    key={flashcard.id}
                />
            )}
            {mode === 'multiple_choice' && options && (
                <MultipleChoiceMode
                    flashcard={flashcard}
                    options={options}
                    key={flashcard.id}
                />
            )}
            {mode === 'cloze' && (
                <ClozeMode flashcard={flashcard} key={flashcard.id} />
            )}
            {mode === 'type_in' && (
                <TypeInMode flashcard={flashcard} key={flashcard.id} />
            )}
            {mode === 'assemble' && assemble && (
                <AssembleMode
                    flashcard={flashcard}
                    assemble={assemble}
                    key={flashcard.id}
                />
            )}
        </Wrapper>
    );
}

function Wrapper({
    mode,
    stats,
    flashcard,
    children,
}: {
    mode: StudyMode;
    stats: FlashcardStats;
    flashcard?: Flashcard;
    children: React.ReactNode;
}) {
    const meta = studyModeMeta[mode];
    const Icon = meta.icon;

    return (
        <>
            <Head title="Проверка" />
            <div className="mx-auto flex w-full max-w-2xl flex-col gap-4 px-4 pt-4 pb-6 sm:px-6 sm:pt-6">
                <div
                    className={cn(
                        'flex items-center gap-2 rounded-2xl border px-3 py-2 sm:px-4 sm:py-2.5',
                        meta.accentBg,
                        meta.accentBorder,
                    )}
                >
                    <div
                        className={cn(
                            'flex size-8 shrink-0 items-center justify-center rounded-lg bg-background/80 ring-1',
                            meta.accentRing,
                            meta.accentText,
                        )}
                    >
                        <Icon className="size-4" />
                    </div>
                    <span className="text-sm font-semibold">{meta.label}</span>

                    <div className="ml-auto flex items-center gap-2 text-xs text-muted-foreground">
                        <Badge
                            variant="outline"
                            className="bg-background/60 tabular-nums"
                            title="К повтору"
                        >
                            {stats.due}
                        </Badge>
                        <Badge
                            variant="outline"
                            className="hidden bg-background/60 tabular-nums sm:inline-flex"
                            title="Выучено / всего"
                        >
                            {stats.learned}/{stats.total}
                        </Badge>
                        {flashcard && (
                            <>
                                <Badge
                                    variant="outline"
                                    className="bg-background/60 font-mono tabular-nums"
                                    title="Сложность"
                                >
                                    {'★'.repeat(
                                        Math.max(
                                            1,
                                            Math.min(5, flashcard.difficulty),
                                        ),
                                    )}
                                </Badge>
                                {flashcard.is_learned ? (
                                    <Badge
                                        variant="outline"
                                        className="hidden bg-background/60 tabular-nums sm:inline-flex"
                                        title="Повторение по SRS"
                                    >
                                        повтор {flashcard.srs_step + 1}/4
                                    </Badge>
                                ) : (
                                    <Badge
                                        variant="outline"
                                        className="hidden bg-background/60 font-mono tabular-nums sm:inline-flex"
                                        title="Различных режимов с правильным ответом"
                                    >
                                        {flashcard.correct_modes?.length ?? 0}/
                                        {flashcard.required_correct}
                                    </Badge>
                                )}
                            </>
                        )}
                    </div>
                </div>
                {children}
            </div>
        </>
    );
}

function EmptyState({ stats }: { stats: FlashcardStats }) {
    const noCards = stats.total === 0;
    const allLearned = !noCards && stats.learned === stats.total;
    const nothingStudied = !noCards && !allLearned && stats.due === 0;

    return (
        <>
            <Head title="Проверка" />
            <div className="mx-auto flex w-full max-w-2xl flex-col gap-4 px-4 pt-4 pb-6 sm:px-6 sm:pt-6">
                <Card className="w-full">
                    <CardContent className="flex flex-col items-center gap-3 py-12 text-center">
                        {noCards && (
                            <>
                                <CardTitle>Пока нечего учить</CardTitle>
                                <CardDescription>
                                    База пуста. Запусти php artisan db:seed,
                                    чтобы загрузить карточки.
                                </CardDescription>
                            </>
                        )}
                        {nothingStudied && (
                            <>
                                <BookOpen className="size-10 text-primary" />
                                <CardTitle>Сначала изучи карточки</CardTitle>
                                <CardDescription>
                                    В проверку попадают карточки, которые ты
                                    отметил как «Изучил». Открой режим изучения
                                    и пройдись по новым карточкам.
                                </CardDescription>
                                <Button asChild>
                                    <Link href={learn.show().url}>
                                        <BookOpen />К изучению
                                    </Link>
                                </Button>
                            </>
                        )}
                        {allLearned && (
                            <>
                                <PartyPopper className="size-10 text-primary" />
                                <CardTitle>Все карточки выучены</CardTitle>
                                <CardDescription>
                                    Сбрось прогресс, чтобы пройти колоду заново.
                                </CardDescription>
                                <div className="flex flex-wrap justify-center gap-2">
                                    <Form
                                        action={flashcards.reset().url}
                                        method="post"
                                    >
                                        <Button type="submit">
                                            <RotateCcw />
                                            Сбросить прогресс
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
            </div>
        </>
    );
}
