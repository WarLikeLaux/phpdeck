export default function AppLogo() {
    return (
        <div className="flex items-center gap-2">
            <span className="text-2xl leading-none font-bold tracking-tight bg-gradient-to-br from-pink-400 via-fuchsia-400 to-violet-400 bg-clip-text text-transparent">
                phpdeck
            </span>
            <span className="text-[10px] leading-tight text-muted-foreground hidden group-data-[collapsible=icon]:hidden">
                карточки
            </span>
        </div>
    );
}
