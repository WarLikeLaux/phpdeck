<div align="center">

# phpdeck

![hero](docs/hero.png)

**Тренажёр карточек для подготовки к собеседованиям по PHP-стеку.**
Семь режимов, SRS, аналитика — без зубрёжки в открытую.

[![Laravel](https://img.shields.io/badge/Laravel-13.7-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?logo=php&logoColor=white)](https://www.php.net)
[![React](https://img.shields.io/badge/React-19.2-61DAFB?logo=react&logoColor=black)](https://react.dev)
[![TypeScript](https://img.shields.io/badge/TypeScript-5.7-3178C6?logo=typescript&logoColor=white)](https://www.typescriptlang.org)
[![Inertia](https://img.shields.io/badge/Inertia-3.0-9553E9?logo=inertia&logoColor=white)](https://inertiajs.com)
[![Tailwind](https://img.shields.io/badge/Tailwind-4-06B6D4?logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![Tests](https://img.shields.io/badge/tests-103%20passing-22c55e?logo=pest&logoColor=white)](#)

</div>

---

## Зачем это

Готовиться к PHP-собесам по «прочитай 100 вопросов и ответов» неэффективно — глаза скользят, мозг не закрепляет. **phpdeck** заставляет тебя реально вытаскивать ответ: то по памяти, то выбором, то заполнением пропусков, то сборкой кода из блоков. Карточка считается выученной, только когда ты ответил правильно в **трёх разных режимах** — это убивает иллюзию «я это знаю», которая возникает когда видишь ответ глазами.

В колоде 1093 вопроса по реальным middle/senior собеседованиям. Прогресс свой — много пользователей не мешают друг другу.

## Что внутри

| Раздел | Что делает |
|---|---|
| **`/learn`** | Знакомство: вопрос и ответ сразу. Кнопка «Изучил» переводит в обкатку. |
| **`/study`** | 7 режимов чередуются автоматически. Карточка выучена после 3 разных режимов подряд. |
| **`/review`** | Закрытый формат: только вопрос, ответ по клику. Spaced repetition по интервалам 1/3/5/7 дней. |
| **`/stats`** | Стрик дней, активность за 14 дней, точность по категориям, слабые топики. |
| **`/troubled`** | Топ карточек с худшим error-rate за 30 дней — добиваешь сначала их. |

## Семь режимов проверки

| Режим | Когда включается | Что делать |
|---|---|---|
| Открыть ответ | всегда | Самооценка «знал / не знал» после раскрытия |
| Правда / Ложь | есть сосед в `topic`/категории | Ответ настоящий или подставленный? |
| Выбор варианта | есть 3+ соседей | 1 правильный + 3 дистрактора |
| Заполни пропуски | задан `cloze_text` | Inputs прямо в шаблоне `{{…}}`, опечатки прощаются |
| Точный ввод | задан `short_answer` | Один input с допуском по Левенштейну |
| Собрать из блоков | `assemble_chunks` ≥ 2 | Цепочка из правильных блоков и 2 дистракторов |
| Найди пары | 4+ due-карточек с `short_answer` | 4 термина ↔ 4 коротких ответа |

Соседями считаются карточки с тем же `topic`, иначе — с той же категорией. Дистракторы и пары всегда из реальных карточек, не из синтетических.

## Колода

1093 карточки по 101 топику, по реальным middle/senior собеседованиям:

| Категория | Карточек | Топиков |
|---|---|---|
| PHP | 314 | 27 |
| Laravel | 239 | 27 |
| Архитектура систем | 212 | 9 |
| Базы данных | 193 | 17 |
| ООП | 135 | 22 |

Карточки добавляются **строго через сидеры** — UI только учит. Это сделано осознанно: контент рецензируется через PR-ы, а не накручивается на лету. Структура: `database/seeders/Data/Categories/{Php,Oop,Laravel,Database,SystemDesign}/<Topic>.php`.

## Установка

```bash
make install     # composer + npm + .env + key + sqlite + миграции
make seed        # 1093 карточки
make dev         # server + queue + logs + vite одной командой
```

Откроется на `http://localhost:8000`. Зарегистрируйся — у каждого пользователя свой прогресс.

## Шорткаты

- `/learn` — `1` пропустить, `2` изучил
- `/review` — `Space` показать ответ → `1` забыл, `2` повторить, `3` помню
- `/study` (Открыть ответ) — `Space` → `1`/`2`/`3`
- `/study` (типизированные режимы) — `1` повторить, `Enter` дальше

Шорткаты не срабатывают, пока фокус в `<input>`/`<textarea>`/`contenteditable`.

---

<details>
<summary><b>Алгоритм заучивания (как становится «выучено»)</b></summary>

```
unstudied (studied=false)
   │  /learn → markStudied()
   ▼
studied (studied=true, is_learned=false)
   │  /study, 3 правильных в РАЗНЫХ режимах (correct_modes)
   ▼
learned (is_learned=true, srs_step=0, next_review_at=now()+1д)
   │  spaced repetition: SRS_INTERVALS_DAYS = [1, 3, 5, 7]
   ▼
mastered (srs_step≥4, next_review_at=null) — больше не всплывает
```

- `LEARN_THRESHOLD = 3` — нужно столько разных режимов
- При правильном ответе режим добавляется в `correct_modes` (только уникальные)
- Когда уникальных режимов хватает — карточка `is_learned=true`, заводится SRS
- Каждый правильный ответ продвигает SRS: `+1 → +3 → +5 → +7 → mastered`
- Любая ошибка обнуляет всё: `correct_streak=0`, `correct_modes=[]`, `is_learned=false`, `srs_step=0`, `next_review_at=null`. На `/review` кнопка «Забыл» делает то же самое.

Прогресс хранится per-user в `flashcard_user_progress` — твоя статистика никак не пересекается со статистикой другого пользователя на той же колоде.

</details>

<details>
<summary><b>Лог событий и аналитика</b></summary>

Каждое действие пишется в `flashcard_events` (`user_id`, `flashcard_id`, `kind`, `mode`, `occurred_at`). Виды: `studied`, `skipped`, `study_correct`, `study_incorrect`, `matching_correct`, `matching_incorrect`, `review_remember`, `review_forgot`.

Из лога считаются:
- **`/stats`** — стрик подряд дней с активностью, события за сегодня, learned, due now, 14-дневный график (studied/correct/incorrect/remembered/forgot per day), точность по категориям за 30 дней, слабые топики (top-5 по `error_rate` среди топиков с ≥3 событиями).
- **`/troubled`** — карточки с минимум 3 событиями за 30 дней, отсортированные по `error_rate` ↓, до 50 шт.

Прогресс хранится отдельно в `flashcard_user_progress` — лог нужен **только** для аналитики, не для расчёта SRS.

</details>

<details>
<summary><b>Маршруты</b></summary>

Все маршруты под `auth` middleware.

```
GET    /flashcards                   список + пагинация (?page, ?q, ?status, ?category)
POST   /flashcards/reset             сброс прогресса текущего пользователя

GET    /learn                        unstudied-карточка (Q+A одновременно)
POST   /learn/{id}/studied           отметить как изученную
POST   /learn/{id}/skip              пролистать без отметки

GET    /study                        случайный режим под случайную due-карточку
POST   /study/{id}/answer            результат correct/incorrect (+ mode)
POST   /study/{id}/skip              пропустить без зачёта
POST   /study/matching               пакетная проверка пар

GET    /review                       выученная карточка из session-выборки
POST   /review/{id}/remember         «Помню» — отметить + продвинуть SRS
POST   /review/{id}/forgot           «Забыл» — отметить + откатить прогресс
POST   /review/{id}/skip             «Повторить» — пропустить без зачёта
POST   /review/reset                 сбросить session-список просмотренных

GET    /stats                        KPI, 14-дневный график, точность по категориям, слабые топики
GET    /troubled                     топ-50 карточек с высокой долей ошибок за 30 дней
```

</details>

<details>
<summary><b>Стек целиком</b></summary>

Laravel 13 · Inertia 3 · React 19 · TypeScript 5.7 · Tailwind 4 · shadcn/ui (Radix) · Pest 4 · SQLite · prism-react-renderer · Vite 8 · SSR · Wayfinder

</details>

---

## Команды

```bash
make test        # pest, 103 теста
make lint        # pint + eslint --fix + prettier
make lint-check  # без правок (pint + eslint + prettier + tsc)
make ci          # полный пайплайн
```
