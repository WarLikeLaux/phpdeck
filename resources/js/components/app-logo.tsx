export default function AppLogo() {
    return (
        <>
            <div className="flex aspect-square size-8 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-pink-500 via-fuchsia-500 to-violet-500 shadow-lg shadow-fuchsia-500/30">
                <img
                    src="/elephpant.svg"
                    alt="phpdeck"
                    className="size-6 drop-shadow"
                    draggable={false}
                />
            </div>
            <div className="ml-1 grid flex-1 text-left">
                <span className="truncate text-base leading-tight font-bold bg-gradient-to-br from-pink-400 via-fuchsia-400 to-violet-400 bg-clip-text text-transparent">
                    phpdeck
                </span>
            </div>
        </>
    );
}
