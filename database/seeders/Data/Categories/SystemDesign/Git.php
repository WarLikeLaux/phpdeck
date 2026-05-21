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
                'answer' => 'У git **три зоны состояния файла**:

| Зона | Что это | Где живёт |
| --- | --- | --- |
| **Working directory** | то, что ты редактируешь | сами файлы на диске |
| **Staging area (index)** | «что попадёт в следующий коммит» | `.git/index` |
| **Repository** | зафиксированные снимки | `.git/objects` |

**Что делают команды:**

- `git add file` — копирует содержимое из working directory в **index**
- `git commit` — берёт состояние из **index** и создаёт коммит, working directory не трогает

**Зачем промежуточный шаг:** можно **атомарно собрать коммит из выборочных изменений**. `git add -p` интерактивно добавляет отдельные **hunk-и** из файла, остальное остаётся в working tree — два смысловых изменения уезжают в два коммита.

**Классическая ловушка:** `git add file` → ещё раз отредактировал тот же файл. В **index лежит старая версия**, в working tree — новая. `git status` покажет файл **и в `Changes to be committed`, и в `Changes not staged for commit`** одновременно. Лечится повторным `git add`.

**Полезный набор:**

- `git restore --staged <file>` (или старое `git reset HEAD <file>`) — убрать из staging, **working tree не тронуть**
- `git diff` — working tree **vs** index
- `git diff --staged` — index **vs** HEAD (что реально уйдёт в коммит)',
                'code_example' => '# Собрать коммит из выборочных кусков
$ git add -p app/User.php
@@ -10,3 +10,5 @@ class User
+    protected $fillable = ["email"];
Stage this hunk [y,n,q,a,d,e,?]? y

# Глянуть что РЕАЛЬНО уйдёт в коммит
$ git diff --staged

# Убрать файл из staging, не теряя правок
$ git restore --staged config/app.php

# Ловушка: правил файл после git add
$ git add user.php
$ vim user.php          # ещё правка
$ git status
  Changes to be committed:    modified: user.php   ← старая версия
  Changes not staged:         modified: user.php   ← новая версия
$ git add user.php       # перезалить актуальное',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'В чём разница между git merge и git rebase?',
                'answer' => 'Оба объединяют ветки, но **по-разному переписывают граф истории**.

| | `git merge` | `git rebase` |
| --- | --- | --- |
| **История** | ветвится, есть merge-коммит | **линейная**, merge-коммитов нет |
| **SHA коммитов** | сохраняются | **переписываются** (новый parent → новый SHA) |
| **Сохраняет факт параллельной работы** | да | нет, выглядит как «всё писалось подряд» |
| **Конфликты** | один раз в финальном коммите | по каждому коммиту отдельно |
| **Безопасен на public-ветке** | да | **нет**, требует force-push |

**Когда что выбирать:**

- **`rebase`** — синхронизировать свою feature-ветку с `main` перед PR, чтобы история выглядела чистой и review проходил легче
- **`merge`** — интеграция feature в `main`, особенно с **`--no-ff`** (явный merge-коммит показывает в `git log --graph` границы фичи и даёт `git revert -m 1 <sha>` одним движением)
- **squash-merge** / **`rebase -i`** — линейная история **с одним коммитом на фичу**

**Главное «нельзя»:** **rebase публичной истории**. После `git push` коммиты у других в локальных копиях — `rebase` + force-push ломает их checkout-ы. Force-push разрешён **только в свою ветку и только `--force-with-lease`**.

**Разрешение конфликтов в rebase:**

- `git rebase --continue` — после `git add` идём дальше
- `git rebase --skip` — пропустить текущий коммит
- `git rebase --abort` — откатить весь rebase целиком',
                'code_example' => '# Feature ветка: подтянуть свежий main rebase-ом
git switch feature-x
git fetch origin
git rebase origin/main      # ваши коммиты лягут поверх свежего main

# Конфликт на 2-м коммите из 5
# CONFLICT (content): Merge conflict in app/User.php
$ vim app/User.php          # разрулить маркеры <<<<<<<
$ git add app/User.php
$ git rebase --continue

# Если всё пошло не так
$ git rebase --abort

# Интеграция feature в main явным merge-коммитом
$ git switch main
$ git merge --no-ff feature-x -m "Merge feature-x"

# Безопасный force-push после rebase
$ git push --force-with-lease origin feature-x',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое git reflog и зачем он нужен?',
                'answer' => '**Reflog** — **локальный журнал движений HEAD** и ссылок. Git записывает, куда указывал HEAD после **каждого `checkout`, `commit`, `reset`, `rebase`, `merge`** — это страховочная сетка, восстанавливающая «потерянные» коммиты.

**Главное применение** — спасение после:

- `git reset --hard` — снёс последние коммиты
- `git commit --amend` — затёр старый коммит новым
- неудачный `rebase` — переписал не туда
- `git branch -D feature` — удалил ветку

Коммиты после этих операций становятся **unreachable**, но **физически живы** в `.git/objects` пока их не удалит `git gc`. Reflog даёт их SHA с описанием операции (`HEAD@{2}`, `HEAD@{1.hour.ago}`).

**Восстановление:**

- `git reset --hard HEAD@{1}` — вернуть HEAD на позицию до операции
- `git branch recovered <sha>` — сохранить SHA в новую ветку

**Особенности:**

- **локальный** — не пушится, не передаётся, лежит в `.git/logs/`
- по умолчанию записи о достижимых коммитах живут **90 дней**, о недостижимых — **30** (`gc.reflogExpire` / `gc.reflogExpireUnreachable`)
- на **свежеклонированном репо reflog пустой** — критичные ветки лучше пушить заранее
- `ORIG_HEAD` git сохраняет специально перед `merge`/`rebase`/`reset` — удобный якорь',
                'code_example' => '$ git reflog
7a2c3f1 HEAD@{0}: reset: moving to HEAD~3   ← снесли 3 коммита
4b1d8a2 HEAD@{1}: commit: Add payment
9c5f4e3 HEAD@{2}: commit: Fix typo
2e8a1d7 HEAD@{3}: pull: Fast-forward

# Откатить reset
$ git reset --hard HEAD@{1}

# Восстановить удалённую ветку
$ git reflog | grep feature-checkout
4b1d8a2 HEAD@{5}: checkout: moving from feature-checkout to main
$ git branch feature-checkout 4b1d8a2

# Откатить весь rebase
$ git reset --hard ORIG_HEAD',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое fast-forward merge и когда нужен --no-ff?',
                'answer' => '**Fast-forward (FF)** — простейший случай merge: если `main` — **прямой предок** `feature` (с момента ответвления в `main` не было новых коммитов), git просто **сдвигает указатель `main` на последний коммит `feature`**. Никакого merge-коммита, история остаётся **линейной**.

`git merge` по умолчанию делает FF, когда может.

**`--no-ff` — заставить создать merge-commit, даже если FF возможен:**

- В `git log --graph` **видны границы фичи**: «эти 5 коммитов — одна фича»
- Откат всей фичи одной командой: **`git revert -m 1 <merge-sha>`** (без `--no-ff` пришлось бы revert-ить 5 отдельных коммитов)
- GitHub/GitLab merge buttons обычно делают `--no-ff`

**Когда хочется FF:**

- **trunk-based** с короткими ветками и быстрыми merge
- После **rebase feature на свежий main** — линейная история без merge-коммитов
- Личные фичи из 1–2 коммитов, где merge-коммит — лишний шум

**Управляющие флаги:**

- `--ff-only` — **упасть, если FF невозможен** (защита от внезапных merge-коммитов в `main`)
- `--no-ff` — всегда создавать merge-commit
- `git config --global merge.ff only` — поведение по умолчанию',
                'code_example' => '# FF возможен: main двигается на feature
$ git switch main
$ git merge feature
Updating 2e8a1d7..4b1d8a2
Fast-forward
 app/User.php | 5 +++++

# С --no-ff: явный merge-коммит даже если FF возможен
$ git merge --no-ff feature -m "Merge feature: add email verification"
Merge made by the "ort" strategy.

# git log --graph покажет границы фичи
$ git log --graph --oneline
*   d3f4a5b Merge feature: add email verification
|\
| * 4b1d8a2 Add email verification
| * 9c5f4e3 Refactor User model
|/
* 2e8a1d7 Previous main commit

# Откат всей фичи одной командой
$ git revert -m 1 d3f4a5b

# Защититься от сюрпризов в main
$ git config --global merge.ff only',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'В чём разница между git reset, git revert и git restore?',
                'answer' => 'Три команды с **разной семантикой** — путаница опасна.

**`git reset` — двигает указатель ветки назад, переписывает HEAD.** Три режима:

| Режим | Двигает HEAD | Сбрасывает index | Сбрасывает working tree | Когда |
| --- | --- | --- | --- | --- |
| `--soft` | да | нет | нет | склеить N коммитов в один |
| `--mixed` *(default)* | да | да | нет | пересобрать коммит заново |
| `--hard` | да | да | **да** | **выбросить всё** (опасно!) |

Reset **переписывает историю** — **нельзя на уже опубликованных** (запушенных) коммитах.

**`git revert` — создаёт НОВЫЙ коммит, инвертирующий целевой.** История не переписывается, безопасно на **public-ветках** (`main`). Для merge-коммита нужен `-m 1` (родительская ветка-«основная»).

**`git restore` (Git 2.23+) — работа с файлами, не с веткой:**

- `git restore <file>` — откатить файл к HEAD
- `git restore --staged <file>` — убрать из staging (бывший `git reset HEAD <file>`)
- `git restore --source=HEAD~3 <file>` — взять файл из конкретного коммита

**Правило выбора:**

- **личная ветка** → `reset` (переписать историю свободно)
- **public-ветка** → только `revert` (никаких force-push)
- **отдельные файлы** → `restore` (не трогает историю)',
                'code_example' => '# reset: склеить 3 последних коммита в один
$ git reset --soft HEAD~3
$ git commit -m "Add full feature X"

# reset --hard: выбросить последние 2 коммита совсем
$ git reset --hard HEAD~2
# ⚠️ Незакоммиченные изменения тоже исчезнут — спасёт reflog

# revert: безопасный откат на public-ветке
$ git revert 4b1d8a2
$ git revert -m 1 d3f4a5b   # для merge-коммита

# restore: откатить один файл
$ git restore app/User.php
$ git restore --staged config/app.php       # убрать из staging
$ git restore --source=HEAD~5 routes/web.php  # взять старую версию',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое cherry-pick и когда им пользоваться?',
                'answer' => '**`git cherry-pick <sha>`** берёт **один (или несколько) коммитов из другой ветки** и применяет к текущей — создаёт **новый коммит с тем же содержимым и сообщением, но другим SHA** (parent другой).

**Типовые сценарии:**

- **Hot-fix flow** — фикс готов в `feature`, но в релиз нужно только его → cherry-pick в `release`
- **Backport** — баг-фикс из `main` применить к старой LTS-ветке
- Вытащить пару коммитов из «не той» ветки в «правильную»

**Полезные опции:**

- `--no-commit` (`-n`) — применить в index, дать поправить и закоммитить вручную
- `-x` — добавить в сообщение `(cherry picked from commit <sha>)` — **must для backport-логов**
- `A..B` — **диапазон** (A исключая, B включая)

**Подводные камни:**

- Получаются **два коммита с одинаковым содержимым и разными SHA** — при последующем merge между ветками легко словить конфликт
- Cherry-pick merge-коммита требует **`-m 1`** (какую ветку считать «основной»)
- Если у исходного коммита есть **скрытые зависимости от предыдущих** — будут конфликты
- **Альтернатива для серии связанных коммитов** — `git rebase --onto <new-base> <upstream> <branch>`',
                'code_example' => '# Backport фикса из main в LTS-ветку
$ git switch release-1.x
$ git cherry-pick -x 4b1d8a2
[release-1.x e8c4f1d] Fix SQL injection in search
(cherry picked from commit 4b1d8a2)

# Диапазон: 5 коммитов из feature в release
$ git cherry-pick feature~5..feature

# Cherry-pick merge-коммита
$ git cherry-pick -m 1 d3f4a5b

# Конфликт: разрулил и продолжил
$ git cherry-pick --continue
$ git cherry-pick --abort        # или сдался
$ git cherry-pick --skip         # пропустить текущий коммит

# Применить без авто-коммита (поправить и закоммитить руками)
$ git cherry-pick -n 4b1d8a2
$ git commit -m "Backport: fix XSS"',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как работает git bisect и когда он спасает?',
                'answer' => '**`git bisect`** — **бинарный поиск по истории** для нахождения коммита, который ввёл баг.

**Сценарий:** «сегодня сломалось, неделю назад работало, между ними 500 коммитов». Перебирать руками — целый день, bisect найдёт виновника за **log₂(500) ≈ 9 шагов**.

**Алгоритм:**

1. `git bisect start`
2. `git bisect bad` — текущий HEAD сломан
3. `git bisect good <sha-неделю-назад>` — там работало
4. Git **checkout-ит середину диапазона**, вы тестируете и говорите `good` / `bad`
5. Он делит оставшийся диапазон, повторяет
6. В конце выдаёт **виновный коммит** с описанием
7. `git bisect reset` — вернуть HEAD на исходную ветку

**Автоматизация:** **`git bisect run ./test.sh`** — git сам гоняет скрипт:

- exit `0` → good
- exit `1` → bad
- exit `125` → **skip** (этот коммит не собирается)

**Подводные камни:**

- **Тест должен быть детерминированным** — flaky-тесты ломают bisect
- Merge-коммиты от ветки, которая не собиралась → `git bisect skip`
- Регрессия может быть **в зависимостях** — тогда bisect укажет на коммит с `composer.lock`
- `git bisect log` запоминает шаги, `git bisect replay` повторяет (удобно делиться с коллегой)',
                'code_example' => '# Ручной поиск
$ git bisect start
$ git bisect bad                    # текущий HEAD сломан
$ git bisect good v1.5.0            # релиз 2 недели назад работал
Bisecting: 256 revisions left to test
[abc1234] Refactor user service

$ ./run-tests.sh
$ git bisect bad                    # сломан и этот
Bisecting: 128 revisions left to test
...
[4b1d8a2] Add caching layer is the first bad commit

$ git bisect reset                  # вернуть HEAD

# Автоматический: 9 шагов без участия человека
$ git bisect start HEAD v1.5.0
$ git bisect run ./tests/regression.sh
running ./tests/regression.sh
4b1d8a2 is the first bad commit',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Зачем нужен git stash и какие у него гайдлайны?',
                'answer' => '**`git stash`** временно **прячет незакоммиченные изменения** и даёт чистый working tree.

**Классический сценарий:** пишешь фичу, прилетел срочный bug-fix, **коммитить полуработу не хочется**. `git stash` → переключился → пофиксил → `git stash pop` → продолжил.

**Как устроено:** под капотом — **скрытые коммиты в `refs/stash`** в виде стека (LIFO).

**Полезные опции:**

- `-u` (`--include-untracked`) — **включить untracked-файлы** (по умолчанию НЕ берутся!)
- `-a` — даже `.gitignore`-файлы
- `-m "msg"` — описание для `git stash list`
- `--keep-index` — спрятать только то, что **НЕ в staging**
- `-p` — интерактивно по hunk-ам

**Навигация по стеку:**

- `git stash list` — все спрятанные изменения
- `git stash apply stash@{2}` — применить, оставить в стеке
- `git stash pop` — применить + удалить из стека
- `git stash drop stash@{2}` — удалить без применения
- `git stash branch new-feature stash@{1}` — спасти stash в новую ветку

**Подводные камни:**

- **Untracked-файлы по умолчанию НЕ стэшатся** — забыл `-u`, `git checkout` их перекроет
- **Stash локален** — не пушится, при потере диска уйдёт
- При конфликте на `pop` stash **остаётся в списке** и накапливается
- Stash без описания — через неделю не помнишь, что внутри

**Гайдлайн:** для минутных переключений stash удобен. **Для работы на час+ лучше WIP-коммит в отдельную ветку** — пушится, переживает перезагрузку, виден в `git log`.',
                'code_example' => '# Спрятать всё + untracked + описание
$ git stash push -u -m "WIP: refactoring auth"

# Посмотреть стек
$ git stash list
stash@{0}: On feature-x: WIP: refactoring auth
stash@{1}: On main: emergency fix attempt

# Применить и удалить
$ git stash pop

# Конфликт — stash остался в стеке
$ git stash pop
CONFLICT (content): Merge conflict in app/User.php
# разрулил → git add → git stash drop

# Спасти stash в ветку (если переключение затянулось)
$ git stash branch wip-auth stash@{1}',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое detached HEAD и как из него выйти?',
                'answer' => '**Обычно** цепочка: **HEAD → ветка → коммит**.

**Detached HEAD** — состояние, когда **HEAD указывает прямо на коммит, минуя ветку**.

**Возникает при:**

- `git checkout <sha>` — на конкретный коммит
- `git checkout v1.2.0` — на тег
- `git checkout HEAD~5` — на N коммитов назад
- В CI после `git checkout $COMMIT_SHA` — норма

**Опасность:** **новые коммиты в detached HEAD ни к какой ветке не привязаны**. При переключении на другую ветку они становятся **unreachable** → через 30 дней их удалит `git gc`.

**Как выйти безболезненно:**

- **Ничего не коммитили** → `git switch main` и забыть
- **Поработали и нужно сохранить** → `git switch -c new-branch` (создаст ветку прямо на этих коммитах)

**Зачем detached HEAD вообще нужен:**

- Посмотреть, как код выглядел в `v1.0.0`
- Прогнать тест на старом коммите (`git bisect`)
- Билд из конкретного SHA в CI (Jenkins / GitHub Actions делают `checkout` по SHA — это **нормальный workflow одноразовой сборки**)

**Если коммиты случайно потеряли:** `git reflog` покажет SHA, `git branch recovered <sha>` вернёт.',
                'code_example' => '# Хочу глянуть код на старой версии
$ git checkout v1.0.0
Note: switching to "v1.0.0".
You are in "detached HEAD" state. ...

# Безопасно вернуться
$ git switch main

# ⚠️ Поработал в detached HEAD — спасти в ветку
$ git switch -c hotfix-from-v1.0.0
Switched to a new branch "hotfix-from-v1.0.0"

# Случайно ушёл и потерял коммит?
$ git reflog
e8c4f1d HEAD@{0}: checkout: moving to main
4b1d8a2 HEAD@{1}: commit: WIP experiment   ← пропал
$ git branch recovered 4b1d8a2',
                'code_language' => 'bash',
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
                'answer' => 'Главное отличие — **`pull` трогает рабочую ветку**, `fetch` нет.

**`git fetch`:**

- скачивает новые коммиты/ветки/теги с remote
- обновляет только **remote-tracking-ветки** (`origin/main`, `origin/feature`)
- **твоя локальная ветка и working tree не меняются**
- безопасно: можно `git log main..origin/main` глянуть и решить, что делать

**`git pull`** = `git fetch` + **автоматический merge** (или `rebase`, если настроено `pull.rebase=true`) в текущую ветку.

**Подвох:** `pull` по умолчанию делает **merge**, и если ваши локальные коммиты разошлись с remote — создаст лишний merge-commit «Merge branch \'main\' of...» прямо в истории.

**Безопасные привычки:**

- `git pull --ff-only` для `main` — откажется, если нужен реальный merge
- `git pull --rebase` для feature-веток — держит **линейную историю**
- `git config --global pull.ff only` — сделать `--ff-only` поведением по умолчанию
- сначала `fetch`, потом смотреть `git log main..origin/main`, потом уже решать',
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
                'answer' => '**Конфликт** возникает, когда **два коммита изменили один и тот же участок файла по-разному** — git не может выбрать сам.

Файл помечается как `unmerged`, в нём появляются **маркеры**:

```
<<<<<<< HEAD       — твоя версия (текущая ветка)
ваш код
=======            — разделитель
>>>>>>> feature    — версия с другой стороны
```

**Разрешение:**

1. Открыть файл, заменить **весь блок от `<<<` до `>>>`** на нужный результат
2. `git add <file>` — пометить как разрешённое
3. `git commit` (для merge) или `git rebase --continue` (для rebase)

**Полезные инструменты:**

- `git checkout --ours <file>` / `--theirs <file>` — взять **целиком одну сторону** (⚠️ в `rebase` `ours`/`theirs` **инвертированы** относительно `merge`)
- `git mergetool` — открыть визуальный diff-tool (VSCode, Meld, kdiff3)
- `git status` показывает **«both modified»** на конфликтных файлах
- `git diff --name-only --diff-filter=U` — список конфликтных файлов

**Профилактика конфликтов:**

- **Часто синкаться с `main`** — daily `pull --rebase`
- **Маленькие короткоживущие feature-ветки** — день-два, не неделя
- **Общий форматтер** (Pint / Prettier) убирает конфликты по пробелам и кавычкам
- **`git config --global rerere.enabled true`** — **reuse recorded resolution**: git запомнит твоё решение и применит при повторении (бесценно в длинном rebase)',
                'code_example' => '$ git merge feature
Auto-merging app/User.php
CONFLICT (content): Merge conflict in app/User.php

$ cat app/User.php
<<<<<<< HEAD
    protected $fillable = ["name", "email"];
=======
    protected $fillable = ["name", "phone"];
>>>>>>> feature

# Открыли, оставили оба:
    protected $fillable = ["name", "email", "phone"];

$ git add app/User.php
$ git commit                 # merge готов

# В rebase
$ git rebase --continue

# Взять целиком одну сторону
$ git checkout --theirs app/User.php
$ git add app/User.php

# Запомнить разрешения для будущего
$ git config --global rerere.enabled true',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое git hooks и какие самые полезные на практике?',
                'answer' => '**Git hooks** — обычные **исполняемые скрипты в `.git/hooks/`** со стандартными именами; git автоматически запускает их на событиях. Падение хука с ненулевым exit code **блокирует операцию**.

**Локальные (на машине разработчика):**

| Hook | Когда срабатывает | Типичное применение |
| --- | --- | --- |
| `pre-commit` | перед `git commit` | **Pint / Prettier / ESLint / PHPStan на staged-файлах** |
| `commit-msg` | после ввода сообщения | **Conventional Commits**, issue-id, длина |
| `prepare-commit-msg` | до открытия редактора | автоподстановка шаблона (номер ветки в сообщение) |
| `pre-push` | перед `git push` | **запустить тесты**, запретить push в `main` |
| `post-checkout` | после `checkout` | `composer install` при смене ветки с другим `composer.lock` |

**Серверные** (self-hosted GitLab / Gitea):

- `pre-receive` — **последний шанс отказать push** (валидация подписей, политики)
- `post-receive` — триггер CI/deploy

**Менеджеры хуков** (чтобы конфиг жил в репе и ставился у всех):

- **Husky** — Node-проекты
- **pre-commit** — Python, кросс-язык
- **Lefthook** — Go, параллельный запуск, быстрый
- **CaptainHook** — PHP

**Подводные камни:**

- Хуки **локальные** — кто-то отключит → **CI обязан дублировать проверки**
- `--no-verify` обходит всё локально (для emergency, не для нормы)
- **Тяжёлые проверки в `pre-commit` раздражают** — там только быстрое (lint/format), полный тест в `pre-push` или CI
- Hook видит **весь индекс, а не только staged** — для линта на staged-файлах нужен `lint-staged` или Lefthook',
                'code_example' => '#!/bin/sh
# .git/hooks/pre-commit (через Husky: .husky/pre-commit)

# Падаем, если есть unresolved merge-маркеры
if git diff --cached --check; then :; else exit 1; fi

# Pint на staged-файлах
FILES=$(git diff --cached --name-only --diff-filter=ACM | grep -E "\\.php$")
if [ -n "$FILES" ]; then
    vendor/bin/pint $FILES || exit 1
    git add $FILES
fi

# .husky/pre-push — тесты перед push
#!/bin/sh
php artisan test --parallel || exit 1

# Лимит в commit-msg
#!/bin/sh
# .husky/commit-msg
grep -qE "^(feat|fix|docs|refactor|test|chore)(\\(.+\\))?: " "$1" || {
    echo "Use Conventional Commits: feat: / fix: / ..."
    exit 1
}',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое .gitignore, как работают правила и что делать с уже отслеживаемым файлом?',
                'answer' => '**`.gitignore`** — файл со списком **паттернов**, которые git **игнорирует** при `git add` и `git status`.

**Главный подвох:** правила действуют **только на untracked-файлы**. Если файл уже **tracked** (был закоммичен раньше), добавление его в `.gitignore` **ничего не изменит** — git продолжит видеть изменения.

**Синтаксис паттернов** (как glob):

- `node_modules/` — папка с таким именем **в любом месте**
- `*.log` — все файлы с расширением
- `/build` — **только в корне** (с ведущим слешем)
- `!important.log` — **исключение** из игнора
- `#` — комментарий

**Глобальный личный игнор** (IDE-мусор, который не должен попадать ни в один репозиторий):

```
git config --global core.excludesFile ~/.gitignore_global
```

**Уже закоммитили лишний файл** (например, `.env`):

1. `git rm --cached .env` — убрать из git, оставить на диске
2. `git commit -m "stop tracking .env"`
3. добавить в `.gitignore`

**Утёк секрет?** `git rm --cached` мало — он остаётся в **истории**. Нужно переписать историю (`git filter-repo` или `BFG`) и **обязательно ротировать секрет** — считай, что он уже скомпрометирован.',
                'code_example' => '# .gitignore для Laravel
/node_modules
/vendor
/public/build
/public/hot
/storage/*.key
.env
.env.*
!.env.example
*.log
.idea/
.vscode/
.DS_Store

# Убрать уже tracked .env из git, оставить на диске
git rm --cached .env
git commit -m "stop tracking .env"

# Удалить из истории и ротировать
git filter-repo --path .env --invert-paths
git push --force-with-lease origin main',
                'code_language' => 'bash',
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
                'answer' => 'Исторически **`checkout` делал всё подряд** — переключал ветки, восстанавливал файлы, создавал ветку, делал detached HEAD. Перегруженная команда, **которую легко применить не к тому**.

**С Git 2.23 (2019)** её разделили на две по смыслу:

**`git switch` — только для работы с ветками:**

- `git switch main` — переключиться на ветку
- `git switch -c new-feature` — создать и переключиться
- `git switch -` — на предыдущую ветку
- `git switch --detach <sha>` — явный detached checkout

**`git restore` — только для файлов:**

- `git restore <file>` — откатить в working tree к HEAD
- `git restore --staged <file>` — убрать из staging (бывший `git reset HEAD <file>`)
- `git restore --source=HEAD~3 <file>` — взять файл из конкретного коммита

**Старый `checkout`** продолжает работать для совместимости, но **опасен**: `git checkout <name>` угадывает «ветка или файл» по контексту — если ветка и файл одноимённые, легко сделать не то.

**Рекомендация:**

- **Новые проекты** → `switch` / `restore`, ошибиться сложнее
- **CI и старые окружения** (Debian stable, CentOS 7) — оставляют `checkout`, там git до 2.23 ничего другого не знает',
                'code_example' => '# Ветки — switch
git switch main
git switch -c hotfix-payment       # создать и переключиться
git switch -                       # назад

# Файлы — restore
git restore app/User.php           # откатить к HEAD
git restore --staged config/app.php # убрать из staging
git restore --source=v1.5.0 routes/web.php  # из тега

# Старый стиль (всё ещё работает)
git checkout main
git checkout -b hotfix-payment
git checkout HEAD -- app/User.php
git checkout v1.5.0 -- routes/web.php',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое force push и какие у него безопасные варианты?',
                'answer' => '**`git push --force`** **перезаписывает remote-ветку локальной**, игнорируя коммиты на remote, которых у вас нет.

**Когда нужен:** после операций, переписывающих историю:

- `git rebase`
- `git commit --amend`
- `git reset` назад
- `git filter-repo` / BFG

**Опасность:** если коллега запушил коммиты, которых вы не видели — **вы их сотрёте**, и его pull превратится в катастрофу. На `main` / `release` force-push — **катастрофа для всей команды**.

**Безопасный вариант — `git push --force-with-lease`:** операция проходит **только если remote-указатель совпадает с тем, что вы видели в последний `fetch`** (`origin/feature`). Если кто-то запушил между вашим fetch и push — **отказывает**, и вы это замечаете.

**Must-have alias:**

```bash
git config --global alias.fp "push --force-with-lease"
```

**Ещё безопаснее (Git 2.30+) — `--force-if-includes`**: дополнительно проверяет, что вы видели последние remote-изменения локально (защита от «refresh fetch без интеграции»).

**Когда force нормален:**

- **своя личная feature-ветка** после rebase / amend
- по договорённости в команде

**Когда никогда:**

- `main`, `release`-ветки
- любая ветка, на которой работают другие

**На уровне платформы:** включить **branch protection** на `main` — запретить force-push, требовать PR-review и status checks.',
                'code_example' => '# После rebase своей feature
$ git rebase origin/main
$ git push --force-with-lease origin feature-x
# Если кто-то запушил между fetch и push:
! [rejected] feature-x -> feature-x (stale info)

# Удобный alias
$ git config --global alias.fp "push --force-with-lease --force-if-includes"
$ git fp origin feature-x

# Снести amend
$ git commit --amend -m "Better message"
$ git push --force-with-lease

# ❌ НИКОГДА:
$ git push --force origin main',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое git tag и в чём разница между lightweight и annotated тегом?',
                'answer' => '**Tag** — именованный указатель на конкретный коммит, обычно для маркировки **релизов** (`v1.0.0`). В отличие от ветки, **tag не двигается** — он навсегда привязан к одному коммиту.

**Два типа:**

| | Lightweight | Annotated |
| --- | --- | --- |
| **Создание** | `git tag v1.0.0` | `git tag -a v1.0.0 -m "Release"` |
| **Метаданные** | нет | автор, дата, сообщение |
| **GPG-подпись** | нельзя | `git tag -s` |
| **Хранение** | файл в `.git/refs/tags/` со SHA | полноценный объект в `.git/objects/` |
| **Применение** | закладка для себя | **публичные релизы** |

**Для публичных релизов всегда annotated**:

- Аудит «кто и когда зарелизил»
- GPG / SSH-подпись
- **`git describe`** ищет именно annotated tag (`v1.0.0-5-gabc1234` = «5 коммитов после v1.0.0»)

**Полезные команды:**

- `git tag -l "v1.*"` — фильтр по шаблону
- `git show v1.0.0` — содержимое тега и коммит
- `git push origin v1.0.0` — пуш **одного** тега
- `git push --tags` — пуш **всех тегов** (обычный `push` теги НЕ пушит!)
- `git tag -d v1.0.0` — удалить локально
- `git push origin --delete v1.0.0` — удалить на remote

**Двигать тег не рекомендуется** — у клонировавших остаётся старый SHA, recompute не происходит без `git fetch --tags --force`.

**Стандарт версионирования** — **SemVer**: `v<MAJOR>.<MINOR>.<PATCH>` (например `v2.3.1`).',
                'code_example' => '# Annotated релиз
$ git tag -a v1.0.0 -m "First stable release"
$ git push origin v1.0.0

# С подписью
$ git tag -s v1.1.0 -m "Signed release"

# Версию билда из git
$ git describe --tags
v1.0.0-3-gabc1234         # 3 коммита после v1.0.0

# Все теги
$ git tag -l "v1.*"
v1.0.0
v1.1.0
v1.2.0

# Снести по ошибке
$ git tag -d v1.2.0
$ git push origin --delete v1.2.0',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Какие workflow в git популярны и в чём их различия (git-flow, github-flow, trunk-based)?',
                'answer' => 'Четыре основных подхода — выбор зависит от **частоты релизов и зрелости CI/CD**.

**Git-flow** (Vincent Driessen, 2010) — тяжеловесная иерархия из **5 типов веток**:

- `master` — production
- `develop` — интеграционная
- `feature/*` — новые фичи
- `release/*` — подготовка релиза
- `hotfix/*` — срочные фиксы в прод

Подходит проектам **с явными релизами** (mobile, библиотеки, продукты с версиями). **Медленный и шумный для веба с CD**.

**GitHub Flow** — упрощение: **только `main` + короткие feature-ветки** → PR → review → merge → deploy. Никаких `develop` и `release`. **Стандарт для веб-приложений с continuous deployment.**

**Trunk-based development** — все коммитят **в `main`** (или ветки < 1 дня), незавершённые фичи прячут за **feature flags**. Требует сильного CI и культуры; используется в **Google, Meta** — даёт максимальную скорость.

**GitLab Flow** — компромисс: `main` + **environment-ветки** (`staging`, `production`), деплой = merge `main` → `staging` → `production`.

**Сравнение:**

| | Git-flow | GitHub Flow | Trunk-based | GitLab Flow |
| --- | --- | --- | --- | --- |
| **Long-lived ветки** | master + develop | только main | только main | main + envs |
| **Feature ветки** | feature/* | short-lived | < 1 дня или нет | short-lived |
| **Релизные ветки** | release/* | нет | нет | environment-ветки |
| **Feature flags** | по желанию | по желанию | **обязательно** | по желанию |
| **Идеальная частота релизов** | раз в спринт | несколько раз в день | continuous | по environment |

**Выбор:**

- Типовой **веб-стартап** → **GitHub Flow** или **trunk-based**
- **Mobile / библиотеки** с версиями → **git-flow**
- Enterprise с явными окружениями → **GitLab Flow**',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Чем git log полезен в продвинутых сценариях — фильтры, поиск, граф?',
                'answer' => '`git log` умеет **гораздо больше**, чем «показать историю».

**Базовая навигация:**

- `--oneline` — компактный вывод (SHA + сообщение)
- `--graph --all --oneline` — **ASCII-граф со всеми ветками**
- `--stat` — summary по изменённым файлам

**Фильтры:**

- `--author="Vasya"` — по автору
- `--since="2 weeks ago"`, `--until="2025-01-01"` — по дате
- `--grep="JIRA-123"` — поиск по тексту сообщения
- `--merges` / `--no-merges` — только merge или без них

**По файлам:**

- `git log -- path/to/file` — история одного файла (после `--` separator)
- `--follow` — отслеживать через **переименования**
- `-p` — полный diff каждого коммита

**Pickaxe search (мощно):**

- `git log -S"oldFunction"` — коммиты, **добавившие или удалившие** эту строку (когда исчезла эта переменная?)
- `git log -G"regex"` — regex-версия

**Диапазоны:**

- `git log main..feature` — что **добавит** PR (есть в feature, нет в main)
- `git log feature..main` — что **прилетело в main** с момента ответвления
- `git log feature...main` — симметричная разница

**Парные команды:**

- `git blame file` — построчное авторство (`-w` игнорит whitespace, `-L 50,60` — диапазон)
- `git show <sha>` — полные изменения одного коммита',
                'code_example' => '# Граф со всеми ветками
$ git log --graph --all --oneline --decorate

# Когда удалили loadUser()
$ git log -S"loadUser" --oneline -p
4b1d8a2 Refactor: rename loadUser → fetchUser

# Что нового в feature по сравнению с main
$ git log main..feature --oneline

# Все коммиты Vasya за 2 недели по файлу
$ git log --author=Vasya --since="2 weeks ago" -- app/User.php

# Найти JIRA-задачу
$ git log --grep="JIRA-1234" --all

# История файла через переименование
$ git log --follow -- app/Models/User.php

# Pretty формат для отчётов
$ git log --pretty=format:"%h %ad %an %s" --date=short',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое shallow clone (--depth) и когда он полезен?',
                'answer' => '**`git clone --depth=1 <url>`** клонирует **только последний коммит каждой ветки**, без всей истории — размер на диске драматически меньше.

**Пример экономии:** Linux kernel — **3 GB full vs ~300 MB shallow**.

**Где применяют:**

- **CI/CD pipelines** — билду нужны только файлы текущего коммита; GitHub Actions `actions/checkout` по умолчанию `fetch-depth: 1`
- **Docker-образы** — копирование кода в image
- **Read-only деплои**

**Ограничения:**

- **`git push` обычно невозможен** (история неполная)
- `git log` / `git blame` ограничены клонированной глубиной
- `git merge-base`, `git describe` могут не работать как ожидается

**Доскачать историю:**

- `git fetch --unshallow` — всё
- `git fetch --depth=100` — конкретную глубину

**Альтернатива для огромных монорепо — partial clone (Git 2.19+):**

```bash
git clone --filter=blob:none <url>     # без blob-ов файлов
git clone --filter=tree:0 <url>        # без tree-объектов
```

Клонируется история (commits + trees), но **blob-ы подкачиваются on-demand** при checkout. Используется в **Google (Android)** и **Microsoft (Windows-репозиторий)** — даёт быстрый `blame` и `log` без полной выкачки контента.

**Комбинация:** `--filter=blob:none --no-checkout --sparse` — основа для **sparse-checkout** в гигантских монорепо.',
                'code_example' => '# Быстрый CI checkout
git clone --depth=1 --single-branch --branch=main https://github.com/laravel/laravel.git

# GitHub Actions
# - uses: actions/checkout@v4
#   with:
#     fetch-depth: 1     # default

# Доскачать историю на месте
git fetch --unshallow
# или конкретную глубину
git fetch --depth=100

# Partial clone — для монорепо
git clone --filter=blob:none https://github.com/torvalds/linux.git
cd linux
git log              # быстро, blob-ы не качаются
git checkout v6.0    # blob-ы нужных файлов подкачаются on-demand',
                'code_language' => 'bash',
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
                'answer' => '**`git blame <file>`** для каждой строки показывает **SHA коммита, автора и дату последнего изменения** — но используется **не для поиска виновного**, а для **понимания контекста**: «откуда взялась эта строка → message коммита → PR → задача».

**Главные опции:**

- `-L 50,100` — **только диапазон строк** (быстрее)
- **`-w`** — **игнорировать whitespace** (форматирующий коммит от Pint / Prettier иначе перебивает весь blame)
- **`-C`** / **`-CC`** — детектировать **перемещение и копирование** кода внутри файла и между файлами
- `<sha> file` — blame на **исторической версии** файла
- `--since="3 months ago"` — только последние изменения

**Главный подвох:** **разовый reformatting-коммит** «перебивает» blame для всего файла — каждая строка теперь от того, кто запустил Pint.

**Решение — `.git-blame-ignore-revs`:**

```
# .git-blame-ignore-revs (коммитится в репо)
# Re-format with Pint
a1b2c3d4e5f6...

# Switch to PSR-12
1234567890ab...
```

```bash
git config blame.ignoreRevsFile .git-blame-ignore-revs
```

GitHub читает этот файл **автоматом**.

**Когда blame не работает:**

- **Удалённые строки** — там `git log -S"text"` (pickaxe)
- **Через переименование файла** — `--follow` для `git log`, для blame используют `-C -C -C`

**В IDE** (PhpStorm, VSCode + GitLens) **inline-blame** обычно удобнее командной строки.',
                'code_example' => '# Базовый blame
$ git blame app/User.php
4b1d8a2 (Vasya 2024-01-10) class User extends Model
9c5f4e3 (Petya 2024-01-15)     protected $fillable = [
9c5f4e3 (Petya 2024-01-15)         "name", "email",

# Только нужные строки + игнор пробелов
$ git blame -L 50,80 -w app/User.php

# Через переименование и перемещение
$ git blame -C -C app/Models/User.php

# Игнорировать "косметические" коммиты
$ git config blame.ignoreRevsFile .git-blame-ignore-revs

# Когда строка УДАЛЕНА — blame бесполезен
$ git log -S"oldFunction" --oneline -p',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое git простыми словами?',
                'answer' => '**Git** — это **распределённая система контроля версий (VCS)**, которая сохраняет «снимки» состояния проекта (**коммиты**) и позволяет работать с историей изменений.

Что git даёт на практике:

- видеть кто, когда и что менял — `git log`, `git blame`
- откатываться к любой прошлой версии
- работать параллельно в нескольких ветках (фича, багфикс)
- сливать изменения коллег без потери своих

**«Распределённая»** значит, что у каждого разработчика на машине **полная копия истории** — можно коммитить и смотреть прошлое без интернета. **GitHub/GitLab** — просто общий удалённый репозиторий для синхронизации между людьми.

Создал git **Линус Торвальдс в 2005** году для разработки ядра Linux.',
                'code_example' => '# Типовой ежедневный цикл
git status                  # что изменилось
git add .                   # подготовить к коммиту
git commit -m "Add feature" # сохранить снимок
git push                    # выложить на GitHub',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что делает git init?',
                'answer' => '`git init` создаёт **пустой git-репозиторий** в текущей папке. Появляется скрытая папка `.git`, в которой git хранит всю историю, объекты коммитов, ветки, конфиг — это и есть «репозиторий».

Ключевое:

- делается **один раз** при старте нового проекта
- после `init` папка проекта стала git-репозиторием, но **коммитов в ней пока нет** — нужны `git add` и `git commit`
- альтернатива — `git clone`, который сразу скачивает существующий репозиторий и тоже создаёт `.git`',
                'code_example' => '$ mkdir my-project && cd my-project
$ git init
Initialized empty Git repository in /home/user/my-project/.git/

$ ls -la
.git/  (теперь это git-репозиторий)',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что делает git clone?',
                'answer' => '`git clone <url>` **скачивает удалённый репозиторий целиком** на локальную машину. Создаёт папку с именем репозитория, кладёт туда весь код и полную историю коммитов и веток, и автоматически настраивает **remote с именем `origin`**, указывающий на этот URL.

После `clone` сразу можно делать `git pull` / `git push` без дополнительной настройки.

Два способа подключения:

- **HTTPS** — `git clone https://github.com/user/repo.git`, при пуше спросит токен
- **SSH** — `git clone git@github.com:user/repo.git`, аутентификация по SSH-ключу

Часто хочется сменить имя папки — `git clone <url> my-folder`.',
                'code_example' => '$ git clone https://github.com/laravel/laravel.git
Cloning into "laravel"...

$ cd laravel
$ git remote -v
origin  https://github.com/laravel/laravel.git (fetch)
origin  https://github.com/laravel/laravel.git (push)',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что делает git add?',
                'answer' => '`git add` помещает изменения в **«область подготовки»** (**staging area**, она же **index**) — промежуточное место между рабочей папкой и историей. **Только то, что в staging, попадёт в следующий `git commit`**.

Зачем нужен этот промежуточный этап: можно собрать коммит **выборочно**, а не из всех изменений сразу — например, разделить два смысловых изменения на два разных коммита.

Варианты:

- `git add file.php` — конкретный файл
- `git add .` — всё изменённое в текущей папке и ниже
- `git add -A` — всё изменённое во всём репозитории, включая удалённые файлы
- `git add -p` — интерактивно по кускам (**patch mode**), удобно когда в одном файле смешаны два разных изменения',
                'code_example' => '$ git status
modified:   app/User.php
modified:   routes/web.php

$ git add app/User.php       # только один файл
$ git add .                  # всё в текущей папке
$ git add -p                 # выбрать куски интерактивно',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что делает git commit?',
                'answer' => '`git commit` **фиксирует подготовленные через `git add` изменения в историю** как новый коммит с описанием.

Каждый коммит получает **уникальный SHA-хэш** (например `7a2c3f1...`) и ссылается на **родительский коммит** — так образуется цепочка истории.

До `commit` изменения живут только в рабочей папке и staging — после `commit` они **сохранены в `.git/`** и их можно вернуть из истории.

Полезные флаги:

- `-m "msg"` — короткое описание одной строкой; без `-m` откроется редактор для длинного сообщения
- `-am` — сразу `add` + `commit` для уже отслеживаемых файлов (новые файлы так не подхватит)
- `--amend` — изменить **последний** коммит (добавить файл или поправить сообщение)

На проектах формат сообщений часто стандартизуют — **Conventional Commits**: `feat:`, `fix:`, `refactor:`.',
                'code_example' => '$ git add app/User.php
$ git commit -m "Add email verification to User"
[main 7a2c3f1] Add email verification to User
 1 file changed, 5 insertions(+)

# Поправить сообщение последнего коммита
$ git commit --amend -m "Better message"',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что делает git push?',
                'answer' => '`git push` **отправляет твои локальные коммиты на удалённый репозиторий** (`origin`), чтобы их увидели коллеги и CI/CD. Пока не сделал `push` — коммиты живут только у тебя на машине.

Ключевые моменты:

- **Первый push новой ветки** делается с `-u` (или `--set-upstream`): `git push -u origin feature-x`. Это запоминает связь локальной ветки с удалённой, дальше можно делать просто `git push` без аргументов.
- Если на удалённой ветке появились коммиты, которых нет у тебя — push будет отвергнут (**non-fast-forward**), сначала надо сделать `git pull`.
- Опасный вариант — `git push --force`, переписывает удалённую историю. На общих ветках использовать **только** с `--force-with-lease`.',
                'code_example' => '# Первый push новой ветки
git push -u origin feature-login

# Обычный push после -u
git push

# Если pull обновил историю и push отвергнут
git pull --rebase
git push',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что делает git pull?',
                'answer' => '`git pull` **скачивает изменения с удалённого репозитория и сразу сливает их в твою текущую локальную ветку**. По сути это **две команды в одной**:

`git pull` = `git fetch` (скачать) + `git merge` (слить)

Используется в начале рабочего дня или перед началом новой задачи, чтобы получить свежие коммиты коллег.

Варианты:

- по умолчанию делает **merge-коммит**, если истории разошлись
- `git pull --rebase` — переписывает свои локальные коммиты поверх удалённых, **без merge-коммита** (история чище), многие команды ставят это дефолтом
- `git pull --ff-only` — только **fast-forward**, откажется делать что-то странное и просто перемотает указатель вперёд; безопасный вариант для `main`',
                'code_example' => '# Обычный pull
git pull

# Rebase вместо merge — линейная история
git pull --rebase

# Только fast-forward, без merge-коммитов
git pull --ff-only',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что показывает git status?',
                'answer' => '`git status` показывает **текущее состояние рабочей папки** относительно последнего коммита и удалённой ветки.

Что видно:

- на какой **ветке** ты сейчас (например, `On branch main`)
- отстаёт ли или опережает ли она `origin/main`
- какие файлы **в staging** (готовы к коммиту, секция `Changes to be committed`)
- какие **изменены, но не добавлены** (`Changes not staged for commit`)
- какие новые без отслеживания (`Untracked files`)

Это **самая часто запускаемая команда** в git — её дёргают перед каждым `add` и `commit`, чтобы убедиться, что попадёт в коммит именно то, что нужно. Краткий вариант — `git status -s` (короткие коды `M`/`A`/`D`/`??` в начале строк).',
                'code_example' => '$ git status
On branch feature-login
Your branch is ahead of "origin/feature-login" by 1 commit.

Changes to be committed:
  modified:   app/Http/Controllers/AuthController.php

Changes not staged for commit:
  modified:   routes/web.php

Untracked files:
  app/Services/TokenService.php',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что делает git diff?',
                'answer' => '`git diff` показывает **построчную разницу между двумя версиями файлов** в унифицированном формате: **красное со знаком минус** — было, **зелёное со знаком плюс** — стало.

Что с чем сравнивает в зависимости от аргументов:

- `git diff` (без аргументов) — рабочая папка vs **staging**, то есть «что я ещё не успел добавить в индекс»
- `git diff --staged` (или `--cached`) — **staging vs последний коммит**, то есть «что попадёт в коммит»
- `git diff HEAD` — все изменения с последнего коммита целиком
- `git diff main feature-x` — разница между двумя **ветками**
- `git diff <sha1> <sha2>` — между двумя любыми коммитами

Полезная привычка: перед каждым `git commit` делать `git diff --staged`, чтобы убедиться, что коммитишь именно то.',
                'code_example' => '$ git diff
diff --git a/app/User.php b/app/User.php
@@ -10,3 +10,5 @@ class User
-    protected $fillable = ["name"];
+    protected $fillable = ["name", "email"];

# Разница staging vs последний коммит
git diff --staged

# Разница между ветками
git diff main..feature-login',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что показывает git log?',
                'answer' => '`git log` показывает **историю коммитов текущей ветки** от свежих к старым: SHA-хэш, автор, дата, сообщение. По умолчанию открывается в постраничном просмотрщике `less` (стрелки и `Page Up`/`Page Down` — листать, `q` — выход).

Полезные варианты:

- `git log --oneline` — каждый коммит одной строкой (SHA + сообщение)
- `git log --graph --all --oneline` — со всеми ветками и графиком слияний
- `git log -10` — только последние 10 коммитов
- `git log --author="Vasya"` — коммиты конкретного человека
- `git log -- path/to/file` — история одного файла
- `git log -S"functionName"` — коммиты, где **появилась или исчезла строка**

Не путать с `git reflog` — это история действий с `HEAD` (включая `reset`, `checkout`), помогает откатить «потерянные» коммиты.',
                'code_example' => '$ git log --oneline -5
7a2c3f1 Add email verification
4b1d8a2 Fix typo in login
9c5f4e3 Add password reset
2e8a1d7 Initial commit

# История с графиком и ветками
git log --graph --oneline --all',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое ветка (branch) в git простыми словами?',
                'answer' => '**Ветка** — это **независимая линия разработки**. Создаёшь ветку (например `feature-login`) — пишешь в ней код и коммитишь, **не трогая основную `main`**. Когда фича готова и проверена — сливаешь ветку обратно в `main` через **merge** или **Pull Request**.

По сути ветка — это всего лишь **подвижный указатель на коммит**, который сам сдвигается вперёд при каждом новом коммите. Поэтому ветки в git **очень дешёвые** — создаются мгновенно, не копируют файлы.

Стандартный flow в команде:

1. `main` всегда стабильна
2. каждая задача делается в своей **feature-ветке**
3. ревью через **Pull Request**
4. после merge ветка удаляется

Полезные команды:

- `git branch` — список локальных веток (звёздочка — текущая)
- `git branch -d feature-x` — удалить локальную ветку',
                'code_example' => '$ git branch
* main
  feature-login
  bugfix-cart

# Создать новую ветку
git switch -c feature-checkout

# Удалить локальную ветку после merge
git branch -d feature-login',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как создать и переключиться на новую ветку?',
                'answer' => '**Современный способ** (git 2.23+):

- `git switch -c feature-x` — **создать** новую ветку от текущей позиции и **сразу переключиться** на неё. Флаг `-c` = `create`.
- `git switch feature-x` — переключиться на **существующую** ветку.

**Старый способ** (всё ещё работает):

- `git checkout -b feature-x` — то же, что `switch -c`
- `git checkout feature-x` — то же, что `switch`

Посмотреть список веток:

- `git branch` — локальные
- `git branch -a` — все, включая удалённые (`remotes/origin/...`)

Команды `switch` и `restore` разделили функции старого `checkout` (который умел и ветки переключать, и файлы восстанавливать) — теперь **`switch` отвечает только за ветки**, `restore` — за файлы. Ошибиться сложнее.',
                'code_example' => '# Создать ветку и переключиться
git switch -c feature-checkout

# Переключиться на существующую
git switch main

# Старый способ (всё ещё работает)
git checkout -b feature-checkout
git checkout main',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'system_design.git',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое remote простыми словами?',
                'answer' => '**Remote** — это **удалённый репозиторий**, с которым связан твой локальный (обычно на **GitHub**, **GitLab** или **Bitbucket**). По сути это просто **именованная ссылка на URL** чужого репозитория.

Стандартное имя первого remote — **`origin`**. Оно создаётся автоматически при `git clone` и указывает на тот URL, откуда ты клонировал.

Можно добавить **несколько remote**: например `upstream` — оригинальный репозиторий, который ты форкнул, а `origin` — твой форк.

Полезные команды:

- `git remote -v` — посмотреть список remote и их URL
- `git remote add upstream <url>` — добавить второй remote
- `git remote remove <name>` — удалить
- `git push origin main` — отправить ветку `main` в remote `origin`
- `git fetch upstream` — скачать изменения из `upstream`',
                'code_example' => '$ git remote -v
origin    git@github.com:me/laravel.git (fetch)
origin    git@github.com:me/laravel.git (push)

# Добавить upstream (оригинал форка)
$ git remote add upstream https://github.com/laravel/laravel.git

# Подтянуть свежие коммиты из оригинала
$ git fetch upstream
$ git merge upstream/main',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'system_design.git',
            ],
        ];
    }
}
