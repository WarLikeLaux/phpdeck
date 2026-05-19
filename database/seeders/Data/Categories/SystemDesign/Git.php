<?php

namespace Database\Seeders\Data\Categories\SystemDesign;

class Git
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Архитектура систем',
                'question' => 'Как git устроен внутри — что такое blob, tree, commit, tag?',
                'answer' => 'Git — это content-addressable filesystem поверх directed acyclic graph (DAG) из четырёх типов объектов. Каждый объект идентифицируется SHA-1 (или SHA-256) хешем своего содержимого и хранится в .git/objects/. 1) Blob — содержимое одного файла (без имени, без прав). Один и тот же файл, лежащий в разных местах, хранится как один blob — отсюда эффективность по диску. 2) Tree — снимок каталога: список записей "права | тип | sha | имя", где sha указывает либо на blob (файл), либо на другое tree (подкаталог). Это рекурсивная структура. 3) Commit — объект с указателем на корневой tree (snapshot всего проекта), указателями на parent commit(s) (один для обычного, два+ для merge), author, committer, message. Содержит хеши, а не патчи — git хранит ПОЛНЫЕ снимки, а не diff. 4) Tag (annotated) — именованный указатель на commit с подписью и сообщением (lightweight tag — просто файл-ссылка в refs/tags/). Ветки и HEAD — это refs (файлы в .git/refs/), указывающие на commit-ы. История = DAG коммитов с parent-указателями. Когда вы делаете checkout, git восстанавливает рабочий каталог из tree корневого commit-а через blob-ы. Compression: packfile в .git/objects/pack/ дельта-сжимает похожие объекты, экономя место на диске.',
                'difficulty' => 4,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое staging area (index) и зачем она нужна?',
                'answer' => 'Git имеет три зоны состояния файла: working directory (что ты редактируешь), staging area / index (файл .git/index — «что попадёт в следующий коммит») и repository (зафиксированные объекты в .git/objects). git add копирует содержимое файла из working directory в staging, git commit берёт состояние из index и создаёт commit. Это даёт атомарно собрать коммит из выборочных изменений: git add -p позволяет интерактивно добавить отдельные hunk-и из файла, остальное оставить в working tree. Распространённая ловушка: после git add file ты ещё раз отредактировал тот же файл — в index лежит старая версия, в working tree новая; git status покажет файл и в "Changes to be committed", и в "Changes not staged for commit", лечится повторным git add. Полезные команды: git reset HEAD <file> (или git restore --staged <file> в Git 2.23+) убирает из staging, не трогая working tree; git diff показывает working vs index, git diff --staged — index vs HEAD.',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'В чём разница между git merge и git rebase?',
                'answer' => 'Оба объединяют ветки, но по-разному. git merge берёт две истории и создаёт новый merge-коммит с двумя родителями: история ветвится и сохраняет факт параллельной работы. git rebase «перенакладывает» коммиты твоей ветки поверх target — каждый коммит переписывается с новым parent и новым SHA, граф становится линейным, merge-коммитов нет. Rebase удобен для feature-веток, чтобы локальная история выглядела чистой и легко проходила review; merge — для интеграции в main, особенно с --no-ff, чтобы видеть в graph границы фич. Главные «нельзя»: rebase публичной истории (после push) ломает чужие копии — все force-push должны идти только в свою ветку с предупреждением. Конфликты решаются по-разному: merge — один раз в финальном коммите; rebase — по коммиту, и каждый шаг приходится разруливать отдельно (--abort откатит весь rebase, --continue идёт дальше). Squash-merge / rebase -i — отдельные техники: дают линейную историю с одним коммитом на фичу.',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое git reflog и зачем он нужен?',
                'answer' => 'Reflog — локальный журнал движений HEAD и ссылок: git запоминает, куда указывал HEAD после каждого checkout, commit, reset, rebase, merge. Доступ через git reflog (для HEAD) или git reflog <ref>. Главное применение — восстановление «потерянных» коммитов: после неудачного git reset --hard, --amend, rebase или удаления ветки коммиты ещё живы в .git/objects, и reflog даёт их SHA с описанием операции (HEAD@{2}, HEAD@{1.hour.ago}). Восстановление: git checkout HEAD@{2} или git branch recovered <sha>. Особенности: reflog локальный — не пушится, не передаётся, лежит в .git/logs/. По умолчанию записи о достижимых коммитах живут 90 дней, о недостижимых — 30 (gc.reflogExpire/gc.reflogExpireUnreachable), потом git gc их физически удалит. На свежеклонированном репо reflog пустой — критичные ветки лучше пушить заранее.',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое fast-forward merge и когда нужен --no-ff?',
                'answer' => 'Fast-forward — простейший случай merge: если main является прямым предком feature (с момента ответвления в main не было новых коммитов), git просто двигает указатель main на последний коммит feature. Никакого merge-коммита, история остаётся линейной. git merge по умолчанию делает FF, когда может. --no-ff заставляет git создать явный merge-commit даже если FF возможен: 1) видно в git log --graph границы фичи («эти 5 коммитов — одна фича»), 2) откат всей фичи делается одним git revert -m 1 <merge-sha>, без --no-ff пришлось бы реверть 5 коммитов, 3) GitHub/GitLab merge buttons обычно используют --no-ff. Когда хочется FF — trunk-based с быстрыми мерджами, либо после rebase feature на свежий main для линейной истории без merge-коммитов. Принудить: --ff-only (упасть, если FF невозможен — защита от сюрпризных merge-коммитов в main), --no-ff (всегда создавать merge-commit).',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'В чём разница между git reset, git revert и git restore?',
                'answer' => 'Три операции с разной семантикой. git reset двигает указатель branch назад и переписывает HEAD; три режима: --soft (только двигает HEAD, изменения остаются в index — удобно склеить несколько коммитов), --mixed (default, двигает HEAD и сбрасывает index, working tree цел — изменения становятся unstaged), --hard (двигает HEAD и сбрасывает И index И working tree — теряет незакоммиченные изменения, опасно). Reset переписывает историю — нельзя на уже опубликованных коммитах. git revert — создаёт новый коммит, инвертирующий изменения целевого; история не переписывается, безопасно для public-веток (git revert <sha>, для merge-коммита нужен -m 1). git restore (Git 2.23+) — работа с файлами, не с веткой: git restore <file> откатывает к HEAD, --staged убирает из staging (бывший git reset HEAD <file>), --source=HEAD~2 <file> — взять файл из конкретного коммита. Правило: личная ветка — reset для переписывания, public-ветка — только revert, restore — для отката файлов без затрагивания истории.',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое cherry-pick и когда им пользоваться?',
                'answer' => 'git cherry-pick <sha> берёт один (или несколько) коммитов из другой ветки и применяет к текущей — создаёт новый коммит с тем же содержимым и сообщением, но другим SHA (parent другой). Типовые сценарии: hot-fix flow (фикс готов в feature, но релизить можно только его — cherry-pick в release), backport (взять баг-фикс из main и применить к старой LTS-ветке), вытащить пару коммитов из неправильной ветки в правильную. Полезные опции: --no-commit (применить в index, дать поправить и закоммитить вручную), -x (добавить в сообщение упоминание исходного SHA — must для backport-логов), A..B — диапазон (A исключая). Подводные камни: получаются ДВА коммита с одинаковым содержимым и разными SHA — при последующем merge между ветками легко словить конфликт; cherry-pick merge-коммита требует -m 1 (какую ветку считать «main»); если у исходного коммита есть скрытые зависимости от предыдущих — будут конфликты. Альтернатива для серии коммитов — rebase --onto.',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как работает git bisect и когда он спасает?',
                'answer' => 'git bisect — бинарный поиск по истории для нахождения коммита, который ввёл баг. Сценарий: «сегодня сломалось, неделю назад работало, между ними 500 коммитов» — вручную перебирать долго, bisect найдёт виновника за log2(500) ≈ 9 шагов. Алгоритм: git bisect start → git bisect bad (текущий HEAD сломан) → git bisect good <sha-неделю-назад> (там работало). Git checkout-ит середину диапазона, вы тестируете и говорите good/bad, он делит оставшийся диапазон и так далее, в конце показывает виновный коммит. git bisect reset возвращает HEAD. Автоматизация: git bisect run ./test.sh — git сам гоняет скрипт, exit 0 = good, 1 = bad, 125 = skip (не собирается). Подводные камни: тест должен быть детерминированным (flaky тесты ломают bisect), merge-коммиты от не собиравшейся ветки — skip, регрессия может оказаться в зависимостях, и bisect укажет на коммит с composer.lock.',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Зачем нужен git stash и какие у него гайдлайны?',
                'answer' => 'git stash временно прячет незакоммиченные изменения и даёт чистый working tree — классический сценарий: пишешь фичу, прилетел срочный bug-fix, коммитить полуработу не хочется. git stash push (или просто git stash) убирает tracked-файлы в стек (под капотом — скрытые коммиты в refs/stash/), переключаешься, делаешь фикс, возвращаешься, git stash pop. Полезные опции: -u — включить untracked, -m "msg" — пометка, git stash list/apply stash@{N}/drop — навигация по стеку. Подводные камни: untracked по умолчанию не стэшатся (забыл -u — checkout перекроет), stash локален и не пушится, при конфликте на pop stash остаётся в списке и накапливается. Гайдлайн: для минутных переключений stash удобен, для работы на час+ лучше WIP-коммит в отдельную ветку (git commit -m "WIP", потом git reset HEAD~1 вернёт изменения) — безопаснее, чем stash.',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое detached HEAD и как из него выйти?',
                'answer' => 'Обычно HEAD → ветка → коммит. Detached HEAD — состояние, когда HEAD указывает прямо на коммит, минуя ветку. Возникает при git checkout <sha>, git checkout v1.2.0 (тег) или git checkout HEAD~5. Опасность: новые коммиты в detached HEAD ни к какой ветке не привязаны — при переключении на любую другую ветку они становятся unreachable и через 30 дней попадают под git gc. Как выйти безболезненно: если ничего не коммитили — git switch main и забыть; если поработали и нужно сохранить — git switch -c new-branch (или git checkout -b new-branch), и новая ветка укажет на эти коммиты. Зачем detached HEAD вообще нужен: посмотреть, как код выглядел в v1.0.0, прогнать тест на старом коммите для bisect, билд из конкретного SHA в CI (Jenkins/GitHub Actions делают checkout по SHA — это нормально для одноразового билда). Если коммиты случайно потеряли — git reflog покажет SHA, git branch recovered <sha> вернёт.',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое git worktree и когда он лучше нескольких клонов?',
                'answer' => 'git worktree позволяет иметь несколько РАБОЧИХ деревьев из одного репозитория одновременно — каждое со своей веткой и checked-out файлами, разделяя единый .git/objects (история, blob-ы, packfile-ы). Команды: git worktree add ../feature-x feature-x — создаёт каталог ../feature-x с checkout-ом ветки feature-x, дополнительный .git внутри — это просто ссылка на основной репозиторий. git worktree list — посмотреть все рабочие деревья. git worktree remove ../feature-x — удалить. Зачем: 1) Параллельная работа над двумя задачами — пишете фичу в ./repo на main, прилетел code review на чужой PR, делаете worktree add ../review-pr review-pr-branch и в другом терминале/окне IDE ревьюите и тестируете, не теряя контекста. 2) Длинный rebase или конфликтный merge — занимает основной checkout надолго. Worktree даёт продолжать работу. 3) Запуск тестов на одной ветке параллельно с разработкой на другой. Преимущества vs git clone: 1) Экономия места — общий .git/objects, иногда десятки гигабайт на больших монорепо (Linux kernel, Chromium). 2) Скорость — не нужно повторно скачивать всё. 3) Локальная отвязка — push/pull в одном worktree автоматически виден в других. Ограничения: одна и та же ветка не может быть checked out одновременно в двух worktree (git напоминает «branch is already checked out at ...»). main worktree удалить нельзя — это вся репа.',
                'difficulty' => 4,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое submodule, в чём проблемы и какие альтернативы?',
                'answer' => 'git submodule — способ включить ОТДЕЛЬНЫЙ git-репозиторий внутрь вашего как вложенный каталог, с привязкой к конкретному коммиту того репозитория. git submodule add <url> path/to/sub создаёт .gitmodules с URL и сохраняет SHA вложенного коммита. При клоне нужен --recursive или потом git submodule update --init --recursive. При обновлении вложенный репо checked-out на сохранённом SHA — НЕ на последний commit ветки. Чтобы подтянуть свежее, нужно cd в submodule, git pull, вернуться, git add path/to/sub, git commit — обновляется указатель в родителе. Проблемы: 1) Сложно для команды: новички постоянно забывают --recursive и видят пустые подкаталоги. 2) Detached HEAD по умолчанию внутри submodule — случайный коммит в submodule теряется. 3) Merge-конфликты в submodule-указателе (gitlink) — два человека обновили на разные коммиты, нужно вручную разрешать. 4) Branch-tracking сложен: submodule.<name>.branch + git submodule update --remote — но это редко настраивают. Альтернативы: 1) git subtree — буквально включает историю поддерева в основной репозиторий, проще для пользователей, сложнее для редких обновлений. 2) Monorepo + workspace tools (Yarn workspaces, pnpm workspaces, Nx, Turborepo) — всё лежит вместе, ничего не вкладывают. 3) Composer/npm пакеты — если общий код можно версионировать как библиотеку, это лучший путь — package versioning + lock-файл вместо git-привязки. Submodule оправдан, когда вложенный репо — настоящий внешний проект с собственной командой (vendor-овый, форк-овый).',
                'difficulty' => 4,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'В чём разница между git fetch и git pull?',
                'answer' => 'git fetch скачивает новые коммиты, ветки и теги с remote и обновляет только remote-tracking-ветки (origin/main, origin/feature) — твоя локальная ветка и working tree остаются нетронутыми. Можно сначала глянуть git log main..origin/main и решить, что с этим делать. git pull = git fetch + автоматический merge (или rebase, если настроено pull.rebase=true) в текущую ветку. По умолчанию pull делает merge — это может создать неожиданный merge-commit, если ваши локальные коммиты разошлись с remote. Безопасные привычки: git pull --ff-only (откажется, если нужен реальный merge) для main и git pull --rebase для feature-веток, чтобы держать линейную историю.',
                'code_example' => '# посмотреть, что прилетит, не применяя
git fetch origin
git log main..origin/main

# безопасный pull в main
git pull --ff-only

# pull с rebase в feature-ветку
git pull --rebase origin main

# сделать --ff-only поведением по умолчанию
git config --global pull.ff only',
                'code_language' => 'bash',
                'difficulty' => 2,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что делает interactive rebase (git rebase -i) и какие операции в нём есть?',
                'answer' => 'git rebase -i HEAD~5 (или git rebase -i main) открывает редактор со списком коммитов и набором операций для каждого — можно переписать историю серии коммитов. Операции: 1) pick — оставить как есть (default). 2) reword — оставить изменения, изменить сообщение. 3) edit — пауза на этом коммите, можно добавить файлы (git add) или поправить и git commit --amend, потом git rebase --continue. 4) squash — слить с ПРЕДЫДУЩИМ коммитом, сообщения объединить (даст редактировать). 5) fixup — то же, что squash, но выкинуть сообщение текущего (оставить только предыдущее) — удобно для «fix typo» коммитов. 6) drop — выкинуть коммит совсем. 7) Можно ПОМЕНЯТЬ ПОРЯДОК — просто переставить строки в редакторе. Типовые сценарии: 1) Перед PR — schissel 7 «WIP», «fix», «more», «typo» коммитов в 2 осмысленных через squash/fixup. 2) Изменить сообщение старого коммита (reword). 3) Откатить случайно закоммиченный файл — edit на нужном коммите, git reset HEAD~ <file>, --continue. Подводные камни: 1) Только на личной ветке — переписывание уже push-нутой истории требует force-push и ломает чужие checkout-ы. 2) Конфликты по каждому шагу — можно --abort полностью или --skip отдельный коммит. 3) Эксклюзивный диапазон: rebase -i HEAD~3 редактирует ПОСЛЕДНИЕ 3 коммита (HEAD~3..HEAD), не включая HEAD~3. Современные удобства: --autosquash + commit --fixup=<sha> автоматически расставляет fixup-метки.',
                'difficulty' => 4,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как git разрешает merge-конфликты и что значат <<<<<<< HEAD маркеры?',
                'answer' => 'Конфликт возникает, когда два коммита изменили один и тот же участок файла по-разному — git не может выбрать сам. Файл помечается как unmerged, в нём появляются маркеры: <<<<<<< HEAD — твоя версия, ======= — разделитель, >>>>>>> feature — версия другой стороны. Разрешение: открыть файл, заменить весь блок (от <<< до >>>) на нужный результат, git add <file> чтобы пометить разрешённым, потом git commit (для merge) или git rebase --continue (для rebase). Полезное: git checkout --ours/--theirs <file> — взять целиком одну сторону (в rebase ours/theirs инвертированы), git mergetool — визуальный diff-tool, git status показывает «both modified». Профилактика: часто sync с main (daily pull --rebase), маленькие короткоживущие feature-ветки, общий форматтер (Pint/Prettier) — убирает конфликты по пробелам/кавычкам, git config --global rerere.enabled true запомнит твои разрешения и применит при повторении (спасает в длинном rebase).',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое git hooks и какие самые полезные на практике?',
                'answer' => 'Git hooks — обычные исполняемые скрипты в .git/hooks/ со стандартными именами; git автоматически запускает их на событиях. Локальные (на машине разработчика): pre-commit — перед commit, падение блокирует (идеален для pint/prettier/eslint); commit-msg — валидация сообщения (Conventional Commits, issue-id, длина); pre-push — финальная проверка (тесты, запрет push в main); prepare-commit-msg — автоподстановка шаблона (номер ветки). Серверные (self-hosted GitLab/Gitea): pre-receive — последний шанс отказать push, post-receive — триггер CI/deploy. Чтобы хуки не лежали только локально, используют менеджеры — Husky (Node), pre-commit (Python, кросс-язык), Lefthook (Go); конфиг хранится в репо и автоустанавливается. Подводные камни: хуки локальные — кто-то отключит, поэтому CI должен дублировать проверку; --no-verify обходит всё локально (для emergency, не для нормы); тяжёлые проверки в pre-commit раздражают — там только быстрое (lint/format), полный test в pre-push или CI.',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое .gitignore, как работают правила и что делать с уже отслеживаемым файлом?',
                'answer' => '.gitignore — файл со списком паттернов, которые git игнорирует при git add и git status. Главный нюанс: правила действуют только на untracked-файлы — если файл уже tracked, добавление его в .gitignore ничего не изменит. Синтаксис похож на glob: node_modules/ — папка в любом месте, *.log — файлы по расширению, /build — только в корне, !important.log — исключение из игнора, # — комментарий. Глобальный личный игнор (IDE-мусор) — ~/.gitignore_global через git config --global core.excludesFile. Если уже закоммитили лишний файл — git rm --cached <file>, потом коммит; для утёкших секретов этого мало: придётся переписывать историю (git filter-repo / BFG) и обязательно ротировать секрет.',
                'code_example' => '# .gitignore
node_modules/
vendor/
.env
.env.*
!.env.example
*.log
/storage/*.key
.idea/
.vscode/

# убрать уже tracked файл из git, оставив на диске
git rm --cached .env
git commit -m "stop tracking .env"',
                'code_language' => 'bash',
                'difficulty' => 2,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'В чём разница между git checkout, git switch и git restore?',
                'answer' => 'Исторически checkout делал всё подряд: переключение веток, восстановление файлов, создание ветки, detached HEAD — перегруженная команда, которую легко применить не к тому. С Git 2.23 (2019) её разделили на две по смыслу. git switch — только для работы с ветками: git switch main — переключиться, -c new-feature — создать и переключиться, git switch - — на предыдущую ветку, --detach <sha> — явный detached checkout. git restore — только для файлов: git restore <file> — откатить в working tree к HEAD, --staged <file> — убрать из staging (бывший git reset HEAD <file>), --source=HEAD~3 <file> — взять файл из конкретного коммита. Старый checkout продолжает работать для совместимости, но опасен: git checkout <name> угадывает «ветка или файл» по контексту — если ветка и файл одноимённые, легко сделать не то. Рекомендация: на новых проектах switch/restore нагляднее. В CI и старых окружениях (Debian stable, CentOS 7) checkout оставляют — git до 2.23 ничего другого не знает.',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое force push и какие у него безопасные варианты?',
                'answer' => 'git push --force перезаписывает remote-ветку локальной, игнорируя коммиты на remote, которых у вас нет. Нужен после операций, переписывающих историю: rebase, amend, reset назад, filter-repo. Опасность: если коллега запушил коммиты, которых вы не видели — вы их сотрёте, и его pull даст катастрофу; на main/release force-push — катастрофа для всей команды. Безопасный вариант — git push --force-with-lease: операция проходит только если remote-указатель совпадает с тем, что вы видели последний fetch (origin/feature). Если кто-то запушил между вашим fetch и push — отказывает, и вы это замечаете. Это must-have alias: git config --global alias.fp "push --force-with-lease". Когда force нормален — своя личная feature-ветка после rebase/amend, по договорённости в команде. Когда никогда — main, release-ветки, любая ветка, на которую подписаны коммиты других. На уровне платформы — включить branch protection на main: запретить force-push, требовать PR-ревью.',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое git tag и в чём разница между lightweight и annotated тегом?',
                'answer' => 'Tag — именованный указатель на конкретный коммит, обычно для маркировки релизов (v1.0.0). В отличие от ветки, tag не двигается — он навсегда привязан к одному коммиту. Lightweight tag — просто файл в .git/refs/tags/ со SHA, без метаданных, создаётся git tag v1.0.0; по сути закладка. Annotated tag — полноценный объект в .git/objects: автор тега, дата, сообщение, опционально GPG-подпись; создаётся git tag -a v1.0.0 -m "..." или -s для подписи. Для публичных релизов всегда annotated: даёт аудит «кто и когда зарелизил», возможность GPG-подписи и работу команды git describe (она ищет ближайший annotated tag). Команды: git tag -l "v1.*" — фильтр, git show v1.0.0 — содержимое, git push origin v1.0.0 — пуш одного тега, git push --tags — все (обычный push теги не пушит). Удаление: git tag -d (локально), git push origin --delete v1.0.0 (на remote). Двигать тег не рекомендуется — у клонировавших остаётся старый SHA. Стандарт версий — SemVer (v<MAJOR>.<MINOR>.<PATCH>).',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Какие workflow в git популярны и в чём их различия (git-flow, github-flow, trunk-based)?',
                'answer' => 'Git-flow (Vincent Driessen, 2010) — тяжеловесная иерархия из 5 типов веток: master (production), develop (интеграционная), feature/*, release/*, hotfix/*. Подходит проектам с явными релизами (mobile, библиотеки, продукты с версиями), но медленный и шумный для веба с CD. GitHub Flow — упрощение: только main + короткие feature-ветки → PR → review → merge → deploy. Никаких develop и release. Стандарт для веб-приложений с continuous deployment. Trunk-based development — все коммитят в main (или ветки < 1 дня), незавершённые фичи прячут за feature flags. Требует сильного CI и культуры; используется Google, Meta — даёт максимальную скорость. GitLab Flow — компромисс: main + environment-ветки (staging, production), деплой = merge main → staging → production. Выбор зависит от частоты релизов и зрелости CI/CD: для типового веб-стартапа — GitHub Flow или trunk-based, для mobile/библиотек — git-flow.',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Чем git log полезен в продвинутых сценариях — фильтры, поиск, граф?',
                'answer' => 'git log умеет гораздо больше, чем «показать историю». Базовая навигация: --oneline для компактного вывода, --graph --all --oneline для ASCII-графа со всеми ветками, --stat для summary по файлам. Фильтры: --author="Vasya", --since="2 weeks ago", --grep="JIRA-123" по тексту сообщения, --merges/--no-merges. По файлам: git log -- path/to/file (после -- separator), --follow для отслеживания через переименования, -p для полного diff. Pickaxe search: git log -S"oldFunction" находит коммиты, добавившие или удалившие эту строку (mvp для «когда исчезла эта переменная»), -G — regex-версия. Диапазоны: git log main..feature — что добавит PR, feature..main — что прилетело в main с ответвления. Парные: git blame file для построчного авторства (-w игнорит whitespace, -L 50,60 — диапазон), git show <sha> — полные изменения коммита.',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое shallow clone (--depth) и когда он полезен?',
                'answer' => 'git clone --depth=1 <url> клонирует только последний коммит каждой ветки, без всей истории — размер на диске драматически меньше (Linux kernel: 3 GB full vs ~300 MB shallow). Применение: CI/CD pipelines (билду нужны только файлы текущего коммита, GitHub Actions делает shallow по умолчанию), Docker-образы (копирование кода в image), read-only деплои. Ограничения: git push обычно невозможен (история неполная), git log/blame ограничены клонированной глубиной; в случае необходимости git fetch --unshallow доскачивает всё, git fetch --depth=100 — конкретную глубину. Альтернатива для огромных монорепо — partial clone (Git 2.19+): git clone --filter=blob:none клонирует историю commit/tree, но не blob-ы файлов; они подкачиваются on-demand при checkout. Используется Google в Android, Microsoft в Windows-репозитории — даёт быстрый blame и log без полной выкачки контента.',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое git LFS и зачем он нужен?',
                'answer' => 'Git LFS (Large File Storage) — расширение, для хранения больших бинарных файлов вне основного git-репозитория. Без LFS: каждое изменение бинарного файла (PSD, видео, ML-модель) добавляет ПОЛНУЮ его копию в .git/objects — репо растёт лавиной, клон становится медленным, packfile-сжатие на бинарях не работает. С LFS: в git коммитится не сам файл, а маленький pointer-file типа "version sha256 12345 size 100MB"; реальное содержимое лежит на отдельном LFS-сервере (GitHub LFS, GitLab LFS, S3, MinIO с lfs-bridge). При checkout LFS-клиент подтягивает нужные версии. Использование: git lfs install (один раз на машину), git lfs track "*.psd" (создаёт .gitattributes с правилом), git add файлы и .gitattributes, обычный commit/push. Когда нужен: 1) Дизайн-ассеты в репо разработки (макеты, видео). 2) ML-проекты с обученными моделями. 3) Game-development (текстуры, аудио). 4) Документация с большими PDF. Подводные камни: 1) LFS не бесплатен — GitHub/GitLab лимитируют размер и трафик; для большого LFS нужен план или self-hosted. 2) При clone нужен LFS клиент на машине — без него получите pointer-файлы вместо реальных. 3) Старые системы без LFS-поддержки не работают (CI/CD должен ставить git-lfs). 4) Переключение существующего файла на LFS требует rewriting history (git lfs migrate import) — это force-push. Альтернативы: 1) Хранить ассеты в S3, в репо только ссылки (как референсы в DAM). 2) DVC (Data Version Control) для ML — отдельный инструмент поверх git.',
                'difficulty' => 4,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как настроить подписание коммитов GPG/SSH и зачем это нужно?',
                'answer' => 'Без подписи коммита поле "Author" в git — это просто строка из git config user.email, которую любой может выставить любую. То есть «коммит от bill@gates.com» легко подделать. Подписание криптографически доказывает, что коммит сделан владельцем приватного ключа. Виды подписи: 1) GPG — старый стандарт, требует gpg-инфраструктуры (gpg --gen-key, экспорт публичного ключа на GitHub в Settings → SSH and GPG keys). git config commit.gpgsign true делает подпись автоматической. git commit -S явно. Verification на GitHub/GitLab показывает «Verified» бейдж. 2) SSH-keys (Git 2.34+) — современный простой путь: тот же SSH-ключ, который вы используете для git push, можно использовать для подписи. git config gpg.format ssh + git config user.signingkey ~/.ssh/id_ed25519.pub + git config commit.gpgsign true. Проще, чем GPG, не нужна отдельная инфраструктура. 3) S/MIME — для корпоративной PKI. Зачем подписывать: 1) Security-критичные проекты (Linux kernel, релизы СУБД) — must, иначе нельзя отличить malicious-коммит. 2) Compliance — некоторые регуляторные требования (SOC 2, ISO 27001) требуют криптографической атрибуции изменений. 3) Open-source — защита от спуфинга мейнтейнеров. 4) Защита от компрометации аккаунта — даже если злоумышленник получил GitHub-токен, без приватного ключа подписанные коммиты не сделает. GitHub branch protection может ТРЕБОВАТЬ verified-коммиты — тогда неподписанные просто не пройдут в main. Это становится дефолтом в зрелых командах. Боль: настройка для всей команды (особенно Windows-разработчиков с GPG), потеря приватного ключа = невозможность подписи под старым identity.',
                'difficulty' => 4,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое git gc, packfile и почему репозиторий иногда нужно «сжать»?',
                'answer' => 'Git хранит объекты двумя способами: loose objects (по файлу на объект в .git/objects/xx/<rest-of-sha>) и packfiles (.git/objects/pack/pack-*.pack — упакованные с дельта-сжатием). Каждая операция (commit, fetch, write-tree) создаёт loose objects. Со временем их становятся тысячи, диск занимают неэффективно, listing замедляется. git gc (garbage collect): 1) Упаковывает loose objects в новый packfile (огромная экономия места — похожие версии файлов хранятся как дельты). 2) Удаляет недостижимые объекты старше gc.pruneExpire (по умолчанию 2 недели). 3) Сжимает refs/. 4) Запускает packfiles repack для слияния маленьких pack-ов в большой. Запускается автоматически при некоторых операциях (gc.auto=6700 loose objects по умолчанию). Ручной запуск: git gc для штатного, git gc --aggressive --prune=now для полного — пересчитывает дельты с большими бюджетами времени, обычно даёт +5-15% экономии для давно живущих репо. Но --aggressive занимает часы на больших репо. Когда стоит вмешаться: 1) Репо ощутимо разросся (du -sh .git/), особенно после массовых перемещений файлов. 2) git операции (status, log) стали медленнее. 3) После git filter-repo или BFG — обязательно git gc --prune=now --aggressive, чтобы реально удалить старые объекты, иначе они физически останутся. Реальный практический случай: на CI shared cache git-репо может разрастись до десятков ГБ — периодический git gc по cron спасает место и скорость. На GitHub автоматически делается на их стороне; вы видите только клиентскую сторону.',
                'difficulty' => 4,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое sparse checkout и когда он спасает в больших монорепо?',
                'answer' => 'Обычный git checkout восстанавливает в working tree ВСЕ файлы из tree коммита. В монорепо на сотни тысяч файлов и десятки ГБ это значит долгий checkout, медленный git status, занят диск. Sparse checkout позволяет иметь в working tree только ПОДМНОЖЕСТВО файлов, при этом репо знает обо всех (история и индекс полные). Современный синтаксис (Git 2.25+): 1) git sparse-checkout init --cone — включить sparse-режим в cone-mode (быстрый). 2) git sparse-checkout set frontend/ services/api/ shared/ — checkout только эти каталоги (включая родительские файлы корня для контекста). 3) git sparse-checkout disable — вернуться к обычному режиму. Cone-mode оптимизирован под «целые каталоги» — git может быстро рассчитать, что выкладывать. Non-cone-mode позволяет gitignore-подобные паттерны (с *, !), но медленнее. Случаи использования: 1) Большие монорепо (Google, Microsoft, Meta) — разработчик одного сервиса не хочет тащить все 100 сервисов. 2) CI/CD job, который билдит только frontend, не нужен backend код. 3) Локальная экономия диска при ограниченном SSD. Ограничения: 1) git status и git log по-прежнему работают со всем индексом (хотя есть partial index для ускорения с Git 2.32+). 2) git pull может затронуть файлы за границей sparse-checkout, нужно следить. 3) Используется вместе с partial clone (--filter=blob:none) для максимальной экономии — не качаем blob-ы файлов, которые не выкладываем. Microsoft развивал scalar (теперь часть git) для гигантских репо — sparse-checkout — её ключевой компонент.',
                'difficulty' => 4,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как восстановить случайно удалённую ветку или сброшенные коммиты?',
                'answer' => 'Самое успокаивающее свойство git: удалить что-то НАВСЕГДА на самом деле сложно. Сценарии и решения: 1) Удалили ветку (git branch -D feature), помните примерное содержимое. Команда: git reflog (или git reflog show --all) — найти последний коммит ветки. git branch feature <sha> — пересоздать ветку на этом коммите. Готово. 2) Сделали git reset --hard и потеряли последние коммиты. git reflog HEAD@{...} покажет позицию до reset. git reset --hard HEAD@{1} (или конкретный entry) — вернуть. 3) Промахнулись в rebase, всё переписали неправильно. git reflog → найти ORIG_HEAD (git сохраняет в нём состояние ДО rebase/merge). git reset --hard ORIG_HEAD — откатить весь rebase. 4) Закоммитили в detached HEAD и переключились на ветку, коммиты «потерялись». git reflog покажет их SHA. git branch save <sha> — создать ветку и спасти. 5) Файл случайно удалён и закоммичен. git log --all -- path/to/file найдёт коммиты, затронувшие его. git checkout <sha>^ -- path/to/file — восстановить версию до удаления. 6) Force-push снёс remote-ветку. На вашей машине: git reflog в порядке. На remote: GitHub/GitLab имеют API для восстановления (события push с прежним SHA доступны несколько недель). Без админ-доступа — у кого-то из команды есть локальная копия → git push --force-with-lease обратно. ПОСЛЕДНЯЯ надежда — git fsck --lost-found ищет все unreachable объекты в .git/objects (даже без reflog-записи) и складывает в .git/lost-found/. Профилактика: 1) git config --global rerere.enabled true (помнит решения конфликтов). 2) Перед опасной операцией: git branch backup-$(date +%s) — мгновенная страховка. 3) Не использовать --hard и --force без хотя бы --dry-run.',
                'difficulty' => 4,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое git blame с -w и --since и почему первая буква "blame" обманчива?',
                'answer' => 'git blame <file> для каждой строки показывает SHA коммита, автора и дату последнего изменения — но используется не для поиска виновного, а для понимания контекста: «откуда взялась эта строка → message коммита → PR → задача». Главные опции: -L 50,100 — только диапазон строк (быстрее), -w — игнорировать whitespace (формат-коммит от Pint/Prettier иначе перебивает весь blame), -C / -CC — детектировать перемещение/копирование кода внутри файла и между файлами, <sha> file — blame на исторической версии. Подвох: разовый reformatting-коммит «перебивает» blame для всего файла — лечится .git-blame-ignore-revs с SHA таких коммитов (git config blame.ignoreRevsFile .git-blame-ignore-revs, GitHub читает его автоматом). Для удалённых строк blame не работает — там git log -S"text". В IDE (PhpStorm, VSCode) inline-blame обычно удобнее командной строки.',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое git простыми словами?',
                'answer' => 'Распределённая система контроля версий. Сохраняет «снимки» проекта (коммиты), позволяет смотреть историю изменений, откатываться назад, работать параллельно в ветках и сливать изменения. У каждого разработчика своя полная копия истории.',
                'difficulty' => 1,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что делает git init?',
                'answer' => 'Создаёт пустой git-репозиторий в текущей папке. Появляется скрытая папка .git, где хранится вся история и метаданные. Делается один раз для нового проекта.',
                'difficulty' => 1,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что делает git clone?',
                'answer' => 'Скачивает удалённый репозиторий локально: git clone https://github.com/user/repo.git. Создаёт папку repo с полной копией истории и автоматически настраивает remote origin на указанный URL.',
                'difficulty' => 1,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что делает git add?',
                'answer' => 'Помещает изменения в «область подготовки» (staging area) — это файлы, которые попадут в следующий коммит. git add file.php — конкретный файл, git add . — всё изменённое в текущей папке.',
                'difficulty' => 1,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что делает git commit?',
                'answer' => 'Фиксирует подготовленные через git add изменения в историю с описанием: git commit -m "Описание". Создаётся новый коммит со своим SHA-хэшем, ссылающийся на предыдущий. Только после commit изменения «сохранены» в истории.',
                'difficulty' => 1,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что делает git push?',
                'answer' => 'Отправляет локальные коммиты на удалённый репозиторий: git push origin main. Без него никто другой не увидит твои изменения. Первый push в новую ветку — git push -u origin feature-x (-u запоминает связь).',
                'difficulty' => 1,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что делает git pull?',
                'answer' => 'Скачивает изменения с удалённого репо и сразу сливает их в твою локальную ветку. git pull = git fetch + git merge. Используется чтобы подтянуть свежие изменения коллег перед началом работы.',
                'difficulty' => 1,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что показывает git status?',
                'answer' => 'Текущее состояние рабочей папки: какие файлы изменены, какие в staging, какие untracked, на какой ветке находишься, отстаёт/опережает ли она удалённую. Самая используемая команда.',
                'difficulty' => 1,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что делает git diff?',
                'answer' => 'Показывает разницу между версиями файлов. Без аргументов — что изменено в working directory против staging. git diff --staged — staging против последнего коммита. git diff main feature — разница между ветками.',
                'difficulty' => 1,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что показывает git log?',
                'answer' => 'Историю коммитов: SHA, автор, дата, сообщение. git log --oneline — компактно по одной строке. git log --graph --all — со всеми ветками и графиком. Стрелки ↑↓ листают, q — выход.',
                'difficulty' => 1,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое ветка (branch) в git простыми словами?',
                'answer' => 'Независимая линия разработки. Создаёшь ветку — продолжаешь экспериментировать, не трогая main. Когда готово — сливаешь обратно. По сути это просто указатель на коммит, который двигается с каждым новым коммитом.',
                'difficulty' => 1,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как создать и переключиться на новую ветку?',
                'answer' => 'Современный способ: git switch -c feature-x (создать и переключиться). Старый: git checkout -b feature-x. Просто переключиться на существующую: git switch feature-x (или git checkout feature-x). Список веток: git branch.',
                'difficulty' => 1,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое remote простыми словами?',
                'answer' => 'Удалённый репозиторий, с которым связан твой локальный (обычно GitHub/GitLab/Bitbucket). Стандартное имя — origin. git remote -v — посмотреть список и URL. git push origin main — отправить ветку main в origin.',
                'difficulty' => 1,
                'topic' => 'system_design.git',
            ],
        ];
    }
}
