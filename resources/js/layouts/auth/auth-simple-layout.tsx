import { Link } from '@inertiajs/react';
import { home } from '@/routes';
import type { AuthLayoutProps } from '@/types';

export default function AuthSimpleLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    return (
        <div className="dark relative min-h-svh overflow-hidden bg-[#0b0820] text-white">
            {/* glowing radial backdrop */}
            <div
                className="pointer-events-none absolute inset-0 -z-10"
                style={{
                    background:
                        'radial-gradient(ellipse 70% 60% at 20% 30%, rgba(236,72,153,0.25), transparent 60%), radial-gradient(ellipse 70% 60% at 85% 75%, rgba(124,58,237,0.30), transparent 60%), radial-gradient(ellipse 100% 80% at 50% 110%, rgba(59,130,246,0.20), transparent 70%)',
                }}
            />
            {/* faint grid */}
            <div
                className="pointer-events-none absolute inset-0 -z-10 opacity-[0.07]"
                style={{
                    backgroundImage:
                        'linear-gradient(rgba(255,255,255,0.5) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.5) 1px, transparent 1px)',
                    backgroundSize: '32px 32px',
                }}
            />

            <div className="grid min-h-svh lg:grid-cols-[7fr_3fr]">
                {/* hero side */}
                <div className="relative hidden flex-col justify-between overflow-hidden p-10 lg:flex">
                    <Link
                        href={home()}
                        className="z-10 inline-flex items-center gap-2 self-start text-5xl font-bold tracking-tight"
                    >
                        <span className="bg-gradient-to-br from-pink-400 via-fuchsia-400 to-violet-400 bg-clip-text text-transparent">
                            phpdeck
                        </span>
                    </Link>

                    <div className="relative z-10 flex w-full items-center justify-center">
                        <div className="absolute inset-0 -z-10 blur-3xl opacity-60" style={{ background: 'radial-gradient(circle at center, rgba(236,72,153,0.35), transparent 60%)' }} />
                        <div className="w-full overflow-hidden rounded-2xl">
                            <img
                                src="/hero.png"
                                alt="phpdeck"
                                className="w-full rounded-2xl shadow-[0_0_60px_-15px_rgba(236,72,153,0.5)] transition-transform duration-700 ease-out hover:scale-[1.04]"
                            />
                        </div>
                    </div>

                    <div className="z-10 whitespace-nowrap text-xl font-medium text-white/80">
                        Карточки для подготовки к собеседованиям по PHP-стеку.
                    </div>
                </div>

                {/* form side */}
                <div className="flex items-center justify-center p-6 sm:p-10">
                    <div className="w-full max-w-sm">
                        {/* mobile-only hero compact */}
                        <Link
                            href={home()}
                            className="mb-8 flex justify-center lg:hidden"
                        >
                            <span className="text-3xl font-bold tracking-tight bg-gradient-to-br from-pink-400 via-fuchsia-400 to-violet-400 bg-clip-text text-transparent">
                                phpdeck
                            </span>
                        </Link>

                        <div className="rounded-2xl border border-white/10 bg-white/[0.03] p-8 shadow-2xl backdrop-blur-md">
                            <div className="mb-6 space-y-1.5 text-center">
                                <h1 className="text-2xl font-semibold tracking-tight">
                                    {title}
                                </h1>
                                {description ? (
                                    <p className="text-sm text-white/60">
                                        {description}
                                    </p>
                                ) : null}
                            </div>
                            {children}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
