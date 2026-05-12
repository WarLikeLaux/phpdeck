import { Form } from '@inertiajs/react';
import { useRef, useState } from 'react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';

export default function ResetStatistics() {
    const passwordInput = useRef<HTMLInputElement>(null);
    const [open, setOpen] = useState(false);

    return (
        <div className="space-y-6">
            <Heading
                variant="small"
                title="Сброс статистики"
                description="Удалите весь прогресс обучения и историю ответов"
            />
            <div className="space-y-4 rounded-lg border border-amber-100 bg-amber-50 p-4 dark:border-amber-200/10 dark:bg-amber-700/10">
                <div className="relative space-y-0.5 text-amber-700 dark:text-amber-100">
                    <p className="font-medium">Внимание</p>
                    <p className="text-sm">
                        Будут безвозвратно удалены прогресс по карточкам и
                        история повторений. Сам аккаунт и карточки сохранятся.
                    </p>
                </div>

                <Dialog open={open} onOpenChange={setOpen}>
                    <DialogTrigger asChild>
                        <Button
                            variant="destructive"
                            data-test="reset-statistics-button"
                        >
                            Сбросить статистику
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogTitle>
                            Сбросить всю статистику обучения?
                        </DialogTitle>
                        <DialogDescription>
                            Прогресс по карточкам, выученные пометки, очередь
                            повторений и история ответов будут удалены без
                            возможности восстановить. Введите пароль, чтобы
                            подтвердить.
                        </DialogDescription>

                        <Form
                            {...ProfileController.resetStats.form()}
                            options={{
                                preserveScroll: true,
                            }}
                            onError={() => passwordInput.current?.focus()}
                            onSuccess={() => setOpen(false)}
                            resetOnSuccess
                            className="space-y-6"
                        >
                            {({ resetAndClearErrors, processing, errors }) => (
                                <>
                                    <div className="grid gap-2">
                                        <Label
                                            htmlFor="reset-stats-password"
                                            className="sr-only"
                                        >
                                            Пароль
                                        </Label>

                                        <PasswordInput
                                            id="reset-stats-password"
                                            name="password"
                                            ref={passwordInput}
                                            placeholder="Пароль"
                                            autoComplete="current-password"
                                        />

                                        <InputError message={errors.password} />
                                    </div>

                                    <DialogFooter className="gap-2">
                                        <DialogClose asChild>
                                            <Button
                                                variant="secondary"
                                                onClick={() =>
                                                    resetAndClearErrors()
                                                }
                                            >
                                                Отмена
                                            </Button>
                                        </DialogClose>

                                        <Button
                                            variant="destructive"
                                            disabled={processing}
                                            asChild
                                        >
                                            <button
                                                type="submit"
                                                data-test="confirm-reset-statistics-button"
                                            >
                                                Сбросить
                                            </button>
                                        </Button>
                                    </DialogFooter>
                                </>
                            )}
                        </Form>
                    </DialogContent>
                </Dialog>
            </div>
        </div>
    );
}
