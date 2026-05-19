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
                'answer' => 'Git имеет три «зоны» состояния файла: 1) Working directory — то, что вы редактируете в файловой системе. 2) Staging area (index) — файл .git/index, описывающий «что попадёт в следующий коммит». 3) Repository — уже зафиксированные объекты в .git/objects. git add копирует текущее содержимое файла из working directory в staging (создаёт blob + добавляет запись в index). git commit берёт состояние из index и создаёт commit-объект. Это даёт возможность атомарно собрать коммит из выборочных изменений: git add -p позволяет интерактивно добавить ОТДЕЛЬНЫЕ hunk-и из файла, остальное оставить в working tree. Распространённая ловушка: после git add file.txt вы ещё раз отредактировали file.txt — в index лежит старое содержимое, в working tree новое; git status покажет файл и в "Changes to be committed", и в "Changes not staged for commit". Решение — повторный git add. git reset HEAD <file> убирает файл из staging, не трогая working tree. git restore --staged <file> — современная альтернатива (Git 2.23+). git diff показывает working vs index, git diff --staged — index vs HEAD.',
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
                'answer' => 'Reflog — локальный журнал движений HEAD и ссылок (branches, tags) на твоей машине: git хранит, куда указывал HEAD после каждого checkout, commit, reset, rebase, merge. Доступ через git reflog (для HEAD) или git reflog <ref>. Главное применение — восстановление «потерянных» коммитов: после неудачного git reset --hard, --amend, rebase или удаления ветки коммиты ещё живы в objects и доступны через их SHA; reflog даёт список этих SHA с описанием операции. Восстановление: git checkout HEAD@{2} или git branch recovered <sha>. Особенности: reflog локальный (не пушится), хранится в .git/logs/, по умолчанию записи о достижимых коммитах живут 90 дней, недостижимые — 30 дней (gc.reflogExpire / gc.reflogExpireUnreachable), после чего git gc может их физически удалить. На свежеклонированном репо или после aggressive gc reflog бесполезен — поэтому критичные ветки лучше пушить заранее.',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое fast-forward merge и когда нужен --no-ff?',
                'answer' => 'Fast-forward — простейший случай merge: если target-ветка (main) является ПРЯМЫМ предком source-ветки (feature) — то есть с момента ответвления в main не добавлялось коммитов — git просто двигает указатель main на последний коммит feature. Никакого нового merge-коммита не создаётся, история остаётся линейной, видимой как «просто продолжение main». git merge feature по умолчанию делает FF, если может. Когда полезно --no-ff (no fast-forward) — git создаёт ЯВНЫЙ merge-commit, даже если FF возможен: 1) Хочется видеть в git log --graph границы фичи: «здесь была отдельная ветка, эти 5 коммитов — одна фича». 2) Удобство отката всей фичи — один git revert -m 1 <merge-sha> откатывает её целиком, без FF откатывать придётся 5 отдельных коммитов. 3) GitHub/GitLab merge buttons обычно делают --no-ff по умолчанию. Когда хочется FF: 1) Trunk-based development с одним мейн-веткой и быстрыми мерджами. 2) После rebase feature на свежий main — линейная история без лишних merge-commit-ов. Принудить FF: git merge --ff-only feature — если FF невозможен, merge просто упадёт, никаких сюрпризных merge-commit-ов. Принудить no-FF: git merge --no-ff feature.',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'В чём разница между git reset, git revert и git restore?',
                'answer' => 'Три операции с разной семантикой и областью действия. git reset — двигает указатель branch назад в истории (переписывает HEAD). Три режима: --soft (только двигает HEAD, изменения остаются в index — удобно «склеить» несколько коммитов), --mixed (default; двигает HEAD и сбрасывает index, working tree не трогает — изменения остаются как unstaged), --hard (двигает HEAD, сбрасывает И index И working tree — ОПАСНО, теряются незакоммиченные изменения). reset переписывает историю — НЕЛЬЗЯ для уже опубликованных коммитов. git revert — создаёт НОВЫЙ коммит, который инвертирует изменения целевого коммита. История не переписывается, всё прозрачно для других. Безопасно для опубликованных коммитов: git revert <sha> или git revert HEAD. Для merge-коммита нужен -m 1 (какая ветка main). git restore (Git 2.23+) — современная замена checkout для работы с файлами (не с веткой). git restore <file> — откатить файл к состоянию HEAD (выкинуть локальные правки). git restore --staged <file> — убрать файл из staging. git restore --source=HEAD~2 <file> — взять файл из конкретного коммита, не трогая HEAD. Не двигает указатели, работает на уровне файлов. Правило: на личной ветке reset для переписывания, на main/публичных — только revert. Restore — для отката файлов без затрагивания истории.',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое cherry-pick и когда им пользоваться?',
                'answer' => 'git cherry-pick <sha> — берёт один (или несколько) коммитов из другой ветки и применяет их к текущей. Создаёт НОВЫЙ коммит с тем же содержимым и сообщением, но другим SHA (parent другой). Под капотом — применяет patch коммита и коммитит. Когда нужно: 1) Hot-fix flow: фикс готов в feature-ветке, но релизить можно только небольшой кусок — cherry-pick фикса в release-ветку. 2) Backport — взять баг-фикс из main и применить к старой LTS-ветке, которую уже не мержат с main. 3) Расщепление случайно слитой работы — пара коммитов из feature-A была мержена в feature-B, нужно перенести их в правильное место. Опции: cherry-pick --no-commit (применить изменения в index, не делая коммит автоматически — для редактирования перед фиксацией), cherry-pick -x (добавить в сообщение упоминание исходного SHA — полезно для backport-логов), cherry-pick A..B (диапазон коммитов, A исключая). Подводные камни: 1) Дубль изменений в истории — один и тот же change-set по содержимому, но с двумя разными SHA. Может всплыть конфликтами при последующем merge между ветками. 2) Cherry-pick merge-commit-а требует -m 1 (выбор какой ветки мержить). 3) Если в исходном коммите были relevant-зависимости (предыдущие коммиты, которые меняли тот же файл) — будут конфликты. Альтернатива: rebase --onto для перемещения серии коммитов между ветками.',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как работает git bisect и когда он спасает?',
                'answer' => 'git bisect — бинарный поиск по истории коммитов для нахождения коммита, который ввёл баг. Сценарий: «сегодня сломалось, неделю назад работало, в истории 500 коммитов». Вручную перебирать долго; bisect находит виновника за log2(500) ≈ 9 шагов. Использование: 1) git bisect start. 2) git bisect bad — отметить текущий HEAD как сломанный. 3) git bisect good <sha-неделю-назад> — отметить заведомо работающий коммит. 4) Git автоматически checkout-ает середину диапазона; вы тестируете (запускаете команду, открываете приложение, гоняете тест) и сообщаете git bisect good или git bisect bad. 5) Bisect делит оставшийся диапазон пополам и снова. 6) В конце git bisect показывает виновный коммит. 7) git bisect reset возвращает HEAD к исходному. Автоматизация: git bisect run <script> — git сам гоняет ваш скрипт на каждом шаге, exit 0 = good, exit 1 = bad, exit 125 = skip (коммит нельзя протестировать — например, не собирается). Пример: git bisect run ./test.sh — найдёт виновника без вашего участия. Подводные камни: 1) Тест должен быть детерминированным — flaky тесты ломают bisect. 2) Если диапазон содержит merge-коммиты от ветки, которая никогда не собиралась — нужно skip. 3) Регрессия может быть в зависимостях, не в коде — bisect укажет на коммит с composer.lock или package-lock.json.',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Зачем нужен git stash и какие у него гайдлайны?',
                'answer' => 'git stash — временное «спрятать» незакоммиченные изменения, чтобы получить чистый working tree, и потом вернуть. Сценарий: вы пишете фичу, прибегает срочный bug-fix на другой ветке, коммитить полу-работу не хочется. git stash убирает изменения, git checkout main, фикс, потом обратно на свою ветку и git stash pop. Под капотом stash создаёт скрытые коммиты в refs/stash/. Команды: git stash (или git stash push) — спрятать tracked-файлы. -u — включить untracked. -a — включить ignored. -m "message" — с пометкой. git stash list — посмотреть стопку (LIFO). git stash pop — применить последний и удалить из стэка. git stash apply stash@{2} — применить конкретный, не удаляя. git stash drop — удалить конкретный. git stash show -p — посмотреть содержимое. Подводные камни: 1) stash pop при конфликте оставляет stash в списке — нужно вручную drop после разрешения, иначе накапливается. 2) Untracked-файлы по умолчанию не stash-ятся — забыли -u и потеряли (на самом деле они на месте, но при checkout могут перекрыться). 3) Stash локален — не пушится, не передаётся. Падение машины = потеря stash. Гайдлайн: stash для МИНУТНЫХ переключений, не как замена ветки. Если работа на час+ — сделайте отдельную ветку с WIP-коммитом (git commit -m "WIP"), позже git reset HEAD~1 вернёт изменения в working tree без потери истории. WIP-коммиты в личной ветке безопаснее stash.',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое detached HEAD и как из него выйти?',
                'answer' => 'HEAD в git — это указатель на текущую позицию. Обычно HEAD → ветка (например, main) → коммит. «Detached HEAD» — состояние, когда HEAD указывает НАПРЯМУЮ на коммит, минуя ветку. Возникает при: git checkout <sha>, git checkout v1.2.0 (тег), git checkout HEAD~5. Опасность: новые коммиты, сделанные в detached HEAD, не привязаны ни к какой ветке. Если переключиться на любую другую ветку, эти коммиты станут «висячими» (unreachable) и попадут под git gc через 30 дней. Безболезненно: 1) Если ничего не коммитили — просто git checkout main или git switch main. 2) Если поработали и нужно сохранить — git switch -c new-branch (Git 2.23+) или git checkout -b new-branch — создать ветку прямо здесь, и она будет указывать на новые коммиты. Подсказки от git: при входе в detached HEAD git сам пишет инструкцию «If you want to create a new branch... use git switch -c new-branch». Зачем вообще нужен detached HEAD: 1) Проинспектировать старое состояние — посмотреть, как код выглядел в v1.0.0. 2) Прогнать тест на старом коммите для bisect. 3) Билд из конкретного тега в CI/CD (Jenkins, GitHub Actions делают checkout по SHA — это detached HEAD, и это нормально для одноразового билда без коммитов). Если случайно потеряли коммиты — git reflog покажет SHA, git branch recovered <sha> вернёт.',
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
                'answer' => 'Конфликт возникает, когда два коммита изменили один и тот же участок файла по-разному — git не может автоматически решить, чья версия правильная. Файл-конфликт помечается в индексе как unmerged, в файле появляются маркеры: <<<<<<< HEAD — ваша версия (current branch). ======= — разделитель. >>>>>>> feature — версия другой стороны. Разрешение вручную: 1) Открыть файл, понять смысл обеих версий. 2) Заменить весь блок (от <<< до >>>) на нужный результат — может быть «оставить нашу», «оставить их», «комбинация обеих». 3) git add <file> — пометить как разрешённый. 4) git commit (для merge) или git rebase --continue (для rebase). git status показывает «both modified: X.php» — это unmerged. Полезные команды: git diff во время конфликта показывает разницу с обеих сторон. git checkout --ours <file> или --theirs <file> — взять целиком одну версию (внимание: для rebase «ours» и «theirs» инвертированы относительно merge!). git mergetool — открывает diff-tool (vimdiff, meld, kdiff3) для визуального разрешения. Профилактика конфликтов: 1) Часто синхронизироваться с main (pull --rebase ежедневно). 2) Маленькие feature-ветки — короткая жизнь = меньше шансов на конфликт. 3) Договорённости в команде о форматировании (Prettier/Pint) — устраняет конфликты по пробелам/кавычкам. 4) git rerere (REuse REcorded REsolution) — git config --global rerere.enabled true — запоминает, как вы разрешили конфликт, и применяет автоматически при повторе той же ситуации (полезно для долгих rebase).',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое git hooks и какие самые полезные на практике?',
                'answer' => 'Git hooks — скрипты в .git/hooks/, которые git автоматически запускает на определённых событиях. Это обычные shell/bash/python/php-скрипты с правом на исполнение и стандартными именами. Локальные hooks (на машине разработчика): 1) pre-commit — запускается перед созданием коммита; падение блокирует commit. Идеально для линтеров и форматтеров: запустить pint/prettier/eslint, отказать в коммите при ошибках. 2) commit-msg — валидация сообщения коммита: соответствие шаблону Conventional Commits, наличие issue-id, длина первой строки. 3) pre-push — финальная проверка перед push: прогнать тесты, проверить, что не пушим в main напрямую. 4) prepare-commit-msg — автоматически вставить шаблон в редактор сообщения (например, номер ветки). Серверные hooks (на git-server, обычно self-hosted GitLab/Gitea/Bitbucket): 1) pre-receive — последний шанс отказать push (политики, защита веток). 2) post-receive — триггер CI, нотификации, deploy. На практике в team-проектах: чтобы hooks не лежали только локально, используют менеджеры — Husky (Node), pre-commit (Python, кросс-язык), Lefthook (Go) — они хранят hook-конфиги в репозитории и устанавливают в .git/hooks по composer install / npm install. Подводные камни: 1) Hooks локальные — кто-то отключит и сломает прод. Должна быть дублирующая проверка в CI. 2) --no-verify обходит все локальные hooks — для emergency удобно, но не должно входить в норму. 3) Тяжёлые hooks (вся test-suite на pre-commit) — раздражают; в pre-commit только быстрые проверки (линтер, format), полный test — в pre-push или CI.',
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
                'answer' => 'Исторически git checkout делал ВСЁ: переключение веток, восстановление файлов, создание ветки, detached checkout, разрешение конфликтов — перегруженная команда. С Git 2.23 (2019) разделили на две: 1) git switch — для работы с ветками. git switch main — переключиться. git switch -c new-feature — создать и переключиться. git switch -c feature origin/feature — создать локальную, отслеживающую remote. git switch - — на предыдущую ветку. git switch --detach <sha> — явный detached checkout. 2) git restore — для работы с файлами. git restore <file> — откатить файл в working tree к HEAD. git restore --staged <file> — убрать из staging (бывший git reset HEAD <file>). git restore --source=HEAD~3 <file> — взять файл из конкретного коммита. git restore --staged --worktree <file> — и из stage, и из working tree. Старый git checkout продолжает работать (для совместимости), но: 1) Менее понятный синтаксис: git checkout <branch> и git checkout <file> — git угадывает по контексту, опасно если ветка и файл одинаково называются. 2) git checkout -- <file> — известный способ откатить файл, тоже работает. Рекомендация: на новых проектах использовать switch/restore — нагляднее, меньше шансов случайно сделать не то. В CI/скриптах для совместимости со старым git могут оставлять checkout. Версии git до 2.23 не знают switch/restore — учитывайте, если деплоите в старые окружения (Debian stable, CentOS 7).',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое force push и какие у него безопасные варианты?',
                'answer' => 'git push --force (или -f) — перезаписывает remote-ветку текущим состоянием локальной, ИГНОРИРУЯ, что на remote есть коммиты, отсутствующие у вас. Нужен после операций, переписывающих историю: rebase, amend, reset назад, filter-branch. Опасность: 1) Если другой человек запушил коммиты, которых вы не видели — вы их СОТРЁТЕ. На remote их больше нет, у того человека они в локальной копии — следующий pull даст ужас. 2) В main/master force-push катастрофа — вся команда теряет общий контекст. БЕЗОПАСНЫЙ вариант — git push --force-with-lease: перезаписывает только если remote-указатель совпадает с тем, что вы видели последний раз (origin/feature). Если кто-то запушил между вашим fetch и push — операция отказывается, вы это видите и решаете осознанно. Это must-have alias: git config --global alias.fp "push --force-with-lease". Ещё безопаснее — git push --force-with-lease=branch:expected_sha — точно указать ожидаемый SHA. Защита через GitHub/GitLab branch protection: 1) Запретить force-push на main/release. 2) Require pull request reviews. 3) Disable direct push. Когда force нормален: 1) Своя feature-ветка перед merge: rebase на main, force-push, всё ок (если в команде договорились). 2) После amend сообщения коммита, который ещё не мержили. 3) Личные ветки разработки. Когда никогда: main, любые ветки, на которые подписаны другие коммиты команды; release-теги (release/v1.2.0).',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое git tag и в чём разница между lightweight и annotated тегом?',
                'answer' => 'Tag — именованный указатель на конкретный коммит, обычно для маркировки релизов: v1.0.0, v2.1.3. В отличие от ветки, tag НЕ ДВИГАЕТСЯ — он навсегда привязан к одному коммиту. Два типа: 1) Lightweight tag — просто файл .git/refs/tags/<name> со SHA коммита. Создание: git tag v1.0.0. Никаких метаданных, ничего нельзя подписать. По сути bookmarks. 2) Annotated tag — полноценный объект в .git/objects: содержит указатель на коммит, имя автора тега, дату создания, сообщение, опционально GPG-подпись. Создание: git tag -a v1.0.0 -m "Release v1.0.0" или git tag -s v1.0.0 -m "..." с подписью. Annotated рекомендуется ВСЕГДА для публичных релизов: 1) Содержит автора и дату — аудит «кто и когда зарелизил». 2) Можно подписать GPG-ключом — proof, что релиз действительно сделан вами (требование для security-критичных проектов, Linux kernel так делает). 3) Появляется в git describe (находит ближайший annotated tag) — без annotated команда не работает. Команды: git tag — список тегов. git tag -l "v1.*" — фильтр. git show v1.0.0 — содержимое тега + commit. git push origin v1.0.0 — пуш одного тега. git push --tags — все теги. Внимание: обычный git push НЕ пушит теги. Удаление: git tag -d v1.0.0 (локально), git push origin --delete v1.0.0 (с remote). Двигать тег не рекомендуется — у клонировавших останется старый SHA. SemVer: v<MAJOR>.<MINOR>.<PATCH> — стандарт версионирования релизов.',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Какие workflow в git популярны и в чём их различия (git-flow, github-flow, trunk-based)?',
                'answer' => 'Git-flow (Vincent Driessen, 2010) — иерархия из 5 типов веток: 1) master — production-ready, тегируется релизами. 2) develop — интеграционная, всё новое сливается сюда. 3) feature/* — от develop, обратно в develop. 4) release/* — от develop при подготовке релиза, мерж в master И develop. 5) hotfix/* — от master, мерж в master и develop. Подходит для проектов с явными релизами (продукты с версиями, mobile apps, библиотеки). Минусы: тяжеловесен, много merge-commit-ов, развитая мерж-логика, медленный для веба с CD. GitHub Flow — упрощённый: только main + короткие feature-ветки. Workflow: ветка от main → коммиты → push → PR → review → merge в main → deploy. Никакого develop, никаких release-веток. Хорош для веб-приложений с continuous deployment. Минус: hotfix сложнее, если в main уже неоттестированные изменения. Trunk-based development — все коммитят в main (или короткоживущие feature-ветки, < 1 день). Релизы — это либо tag-и на main, либо release-ветки только для патчей старых версий. Использует feature flags для скрытия незавершённых фич. Самый «agile»-подход, требует мощного CI и хорошей культуры. Используется в Google, Facebook, Meta. Подходит для high-velocity команд. GitLab Flow — компромисс: main + environment-ветки (staging, production). Деплой = merge main → staging → production. Удобно для контроля релизов. Выбор зависит от: 1) Тип продукта (SaaS vs библиотека vs mobile). 2) Размер команды и зрелость CI/CD. 3) Частота релизов (раз в год vs много раз в день). Для типового веб-стартапа — GitHub Flow или trunk-based.',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Чем git log полезен в продвинутых сценариях — фильтры, поиск, граф?',
                'answer' => 'git log умеет гораздо больше, чем «показать историю». Полезные опции: 1) git log --oneline — компактно. 2) git log --graph --all --oneline — ASCII-граф со всеми ветками. 3) git log --author="Vasya" — только коммиты конкретного автора. 4) git log --since="2 weeks ago" --until="yesterday" — диапазон дат. 5) git log --grep="JIRA-123" — поиск по тексту сообщения коммита. 6) git log -S"oldFunction" — pickaxe search: коммиты, которые добавили/удалили строку "oldFunction" в diff (мощно для «когда удалили эту переменную»). 7) git log -G"regex" — то же, но регуляркой. 8) git log -- path/to/file — только коммиты, затронувшие файл (после -- — separator). 9) git log -p -- file — с полным diff каждого коммита. 10) git log --follow -- file — следить за файлом через переименования (без --follow rename теряется). 11) git log main..feature — коммиты, которые есть в feature, но нет в main («что добавит этот PR»). 12) git log feature..main — обратное («что нового в main с момента отвлетвления»). 13) git log --merges / --no-merges — только/без merge-commit-ов. 14) git log --pretty=format:"%h %an %s" — кастомный формат. 15) git log --stat — summary изменений по файлам. git blame и git show: git blame file.php — кто менял каждую строку (для понимания: «кого спросить про этот код»). git blame -L 50,60 file.php — только строки 50-60. git show <sha> — полная info о коммите + diff.',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое shallow clone (--depth) и когда он полезен?',
                'answer' => 'git clone --depth=1 <url> — клонирует только последний коммит каждой ветки, без всей истории. Размер репозитория на диске драматически меньше: Linux kernel full clone ~3 GB, shallow clone ~300 MB. Зачем: 1) CI/CD pipelines — большинству билдов не нужна история, нужны только файлы текущего коммита. GitHub Actions делает shallow clone по умолчанию (fetch-depth=1). Экономия минут на больших монорепо. 2) Docker images — копирование кода в образ. --depth=1 урежет размер. 3) Read-only deployments — раскатывание на сервера. Ограничения shallow clone: 1) Нельзя сделать git push (история неполная, серверу не от чего разрулить ff). 2) git log даст только клонированные коммиты. 3) git blame работает, но обрывается на границе shallow. 4) git fetch --unshallow «доскачивает» всю историю — превращает shallow в полный клон. 5) Подгрузка на конкретную глубину: git fetch --depth=100 — получить 100 последних коммитов. Promoting shallow → full: git fetch --unshallow. Partial clone (Git 2.19+) — более гибкая альтернатива: --filter=blob:none клонирует историю commit/tree объектов, но НЕ файлы (blob-ы) — они подкачиваются on-demand при checkout. Удобно для огромных монорепо, где история нужна (для blame, log), а содержимое всех файлов — нет. Используется Google в Android, Microsoft в Windows-репозитории.',
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
                'answer' => 'git blame <file> для каждой строки файла показывает: SHA коммита, который последним её изменил, автора и дату. Используется НЕ для поиска виновного (отсюда обманчивое название), а для понимания «когда и зачем эта строка появилась» — git blame ведёт к коммиту → message → PR → задача → контекст. Полезные опции: 1) git blame -L 50,100 file — только строки 50-100, быстрее на больших файлах. 2) git blame -w file — игнорировать изменения в whitespace (формат-коммит после Pint/Prettier перебивал бы blame; -w смотрит «через» него). 3) git blame -C file — детектировать переносы строк ВНУТРИ файла (если код переехал из конца в начало). 4) git blame -CC и -CCC — детектировать копирование из ДРУГИХ файлов (полезно после refactor с extract method). 5) git blame --since="2 years ago" file — игнорировать слишком старые изменения. 6) git blame <sha> file — blame на исторической версии. Подвох: 1) Reformatting-коммит «перебивает» все строки своим SHA — поэтому ставят .git-blame-ignore-revs с SHA-таких коммитов: git config blame.ignoreRevsFile .git-blame-ignore-revs (Git 2.23+) — blame будет «прозрачно» проскакивать через эти коммиты. GitHub автоматически использует этот файл. 2) Blame на удалённых строках не работает — нужно git log -S"text". 3) В IDE (PhpStorm, VSCode) есть inline-blame, который показывает автора прямо у строки — обычно удобнее, чем командная строка. Применение в реальной работе: 90% случаев — «откуда взялось это странное поведение, кто и почему ввёл этот if», 10% — поиск кого спросить про древний код.',
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
