import { Form, router } from '@inertiajs/react';
import { Check, Eye, RotateCcw, X } from 'lucide-react';
import { useState } from 'react';
import { CategoryBadge } from '@/components/category-badge';
import { AnswerForm } from '@/components/study/answer-form';
import { CardCode } from '@/components/study/card-code';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { useKeyboardShortcut } from '@/hooks/use-keyboard-shortcut';
import study from '@/routes/study';
import type { Flashcard } from '@/types';

export function RevealMode({ flashcard }: { flashcard: Flashcard }) {
    const [revealed, setRevealed] = useState(false);

    useKeyboardShortcut(' ', () => setRevealed(true), !revealed);
    useKeyboardShortcut(
        '1',
        () =>
            router.post(study.answer(flashcard.id).url, {
                result: 'incorrect',
                mode: 'reveal',
            }),
        revealed,
    );
    useKeyboardShortcut(
        '2',
        () => router.post(study.skip(flashcard.id).url),
        revealed,
    );
    useKeyboardShortcut(
        '3',
        () =>
            router.post(study.answer(flashcard.id).url, {
                result: 'correct',
                mode: 'reveal',
            }),
        revealed,
    );

    return (
        <Card>
            <CardHeader className="gap-2">
                <CategoryBadge category={flashcard.category} />
                <CardTitle className="text-lg leading-snug sm:text-xl">
                    {flashcard.question}
                </CardTitle>
            </CardHeader>
            <CardContent className="flex flex-col gap-4">
                {revealed ? (
                    <>
                        <p className="text-base whitespace-pre-line">
                            {flashcard.answer}
                        </p>
                        <CardCode
                            code={flashcard.code_example}
                            language={flashcard.code_language}
                        />
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
                    <CardFooter className="grid grid-cols-3 gap-2 px-3 sm:flex sm:justify-end sm:px-6 [&_svg]:hidden sm:[&_svg]:inline-block">
                        <AnswerForm
                            flashcardId={flashcard.id}
                            result="incorrect"
                            mode="reveal"
                            variant="destructive"
                            label="Не знал"
                            icon={<X />}
                            fullWidthOnMobile
                        />
                        <Form
                            action={study.skip(flashcard.id).url}
                            method="post"
                            className="w-full sm:w-auto"
                        >
                            <Button
                                type="submit"
                                variant="outline"
                                className="w-full sm:w-auto"
                            >
                                <RotateCcw />
                                Повторить
                            </Button>
                        </Form>
                        <AnswerForm
                            flashcardId={flashcard.id}
                            result="correct"
                            mode="reveal"
                            label="Знал"
                            icon={<Check />}
                            fullWidthOnMobile
                        />
                    </CardFooter>
                </>
            )}
        </Card>
    );
}
