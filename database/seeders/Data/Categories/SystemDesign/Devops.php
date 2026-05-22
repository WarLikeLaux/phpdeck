<?php

namespace Database\Seeders\Data\Categories\SystemDesign;

class Devops
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое деплой простыми словами?',
                'answer' => '**Деплой** (deploy, развёртывание) — это **процесс выкатки новой версии приложения на сервер**, чтобы её увидели пользователи.

Аналогия: ты доделал фичу локально на ноуте — теперь надо положить её на боевой сервер так, чтобы сайт продолжал работать.

**Минимальный деплой Laravel-приложения**:

1. скачать новый код — `git pull`
2. поставить зависимости — `composer install --no-dev`
3. накатить миграции БД — `php artisan migrate`
4. очистить и прогреть кеши — `php artisan config:cache`, `route:cache`
5. перезапустить процессы — `php-fpm`, `php artisan queue:restart`

На современных проектах это автоматизировано через **CI/CD**: пушнул в `main` → пайплайн сам собрал, прогнал тесты и раскатал.

Стратегии выкатки:

- **rolling** — по одной машине, постепенно
- **blue-green** — две среды, переключение трафика одним кликом
- **canary** — выкатка сначала на маленькую часть пользователей',
                'code_example' => '# Простой shell-деплой Laravel
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan queue:restart
sudo systemctl reload php8.3-fpm',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое окружения (dev/stage/prod) и зачем они нужны?',
                'answer' => 'Это **разделение приложения на изолированные среды** с одинаковым кодом, но разными данными, ключами и настройками.

- **dev** (development) — локальная машина разработчика или общий dev-сервер. Можно ломать что угодно.
- **stage** (staging) — максимально похожая на прод копия. На ней **QA и разработчики проверяют релизы** перед выкаткой: миграции, интеграции с внешними API, нагрузку.
- **prod** (production) — **боевое окружение**, которым пользуются клиенты. Реальные деньги и реальные данные.

Зачем разделение:

- ловить ошибки **до пользователей**
- не сломать боевую БД при экспериментах
- проверять миграции на безопасной копии
- отлаживаться без риска

В **Laravel** переключение через переменную окружения **`APP_ENV`** в файле `.env`. Она доступна как `app()->environment()` и определяет, какие конфиги, ключи и базы используются.',
                'code_example' => '# .env на dev
APP_ENV=local
APP_DEBUG=true
DB_DATABASE=shop_local

# .env на stage
APP_ENV=staging
APP_DEBUG=true
DB_DATABASE=shop_stage

# .env на prod
APP_ENV=production
APP_DEBUG=false
DB_DATABASE=shop_prod',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Docker простыми словами?',
                'answer' => '**Docker** — технология **контейнеризации**. Контейнер — упакованное приложение **со всеми зависимостями**, которое работает одинаково везде: на ноуте разработчика, в CI, на стейдже, на проде.

Аналогия: коробка с приложением и всем, что ему нужно для жизни (PHP, расширения, библиотеки, конфиги).

**Три ключевые сущности:**

- **Image** (образ) — шаблон, статичный артефакт (как класс в ООП)
- **Container** — запущенный экземпляр образа (как объект)
- **Dockerfile** — рецепт сборки образа

**Чем отличается от VM:** не виртуализирует ОС — использует **ядро хоста** через `namespaces` и `cgroups`. Поэтому образ весит **мегабайты**, стартует **за секунду**.

**Зачем нужен:**

- **«у меня работает»** уходит как явление — везде одинаковая среда
- легко изолировать сервисы (PHP/MySQL/Redis в разных контейнерах)
- одинаковые артефакты в CI/CD: что собрали — то и катим в прод

**Базовые команды:** `docker build -t myapp .`, `docker run -p 8000:8000 myapp`, `docker ps`, `docker logs <id>`, `docker exec -it <id> bash`.

Снизу — минимальный `Dockerfile` для Laravel.',
                'code_example' => 'FROM php:8.3-fpm

WORKDIR /var/www

# 1) Сначала зависимости — этот слой кэшируется, пока не меняется composer.lock
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --optimize-autoloader

# 2) Потом код — частые правки не ломают кэш зависимостей
COPY . .

EXPOSE 9000
CMD ["php-fpm"]',
                'code_language' => 'bash',
                'difficulty' => 2,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Чем Docker отличается от виртуальной машины (VM)?',
                'answer' => '**Главное различие — что виртуализируется:**

- **VM** виртуализирует **железо** через гипервизор (KVM, Xen, VMware, Hyper-V) — запускает **полную гостевую ОС со своим ядром**
- **Docker** контейнер использует **ядро хоста** и изолирован через **`namespaces`** (изоляция процессов, сети, mount, PID) и **`cgroups`** (лимиты CPU / памяти / I/O)

Аналогия: **VM — отдельная квартира**, **Docker — комната в квартире хозяина**.

**Сравнение:**

| | VM | Docker container |
| --- | --- | --- |
| **Гостевая ОС** | да (своё ядро) | нет (ядро хоста) |
| **Размер** | гигабайты | **мегабайты** |
| **Старт** | минуты | **секунды** |
| **Overhead** | заметный | минимальный |
| **Изоляция** | сильная (security boundary) | слабее (kernel sharing) |
| **Плотность** | десятки на хост | **сотни-тысячи на хост** |
| **Кросс-OS** | да (Linux на macOS, Windows на Linux) | нет (Linux ядро для Linux-контейнеров) |

**Когда что:**

- **VM** — multi-tenant, разные ОС, **сильная security изоляция** (банки, hosting)
- **Docker** — микросервисы, CI/CD, dev-окружение, density

**Часто комбинируют:** **VM с Docker внутри** (k8s nodes — это VM, в каждой запускаются контейнеры). На macOS / Windows Docker сам работает внутри легковесной Linux-VM (Docker Desktop, Colima), потому что нативного Linux-ядра у хоста нет.',
                'difficulty' => 3,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Kubernetes простыми словами?',
                'answer' => '**Kubernetes (k8s)** — **оркестратор контейнеров**. Сам Docker умеет запускать контейнеры, но не умеет: «следить за здоровьем», «балансировать», «перевыкатывать». **k8s — слой сверху**, который этим занимается.

Аналогия: **дирижёр оркестра** — управляет десятками/тысячами Docker-контейнеров, решает **где их запускать, перезапускает упавшие, балансирует нагрузку, масштабирует под нагрузку**.

**Что даёт k8s:**

- **Self-healing** — упавший pod пересоздаётся, не отвечающий health-check удаляется из балансировки
- **Декларативность** — описываешь желаемое состояние в YAML, controller-loop приводит к нему
- **Service discovery** — сервисы находят друг друга по DNS-имени
- **Load balancing** — встроенный round-robin между подами
- **Rolling updates / rollback** — выкатка без простоя, `kubectl rollout undo`
- **Auto-scaling** — горизонтальное (HPA) и вертикальное (VPA)

**Основные сущности:**

| Объект | Что делает |
| --- | --- |
| **Pod** | минимальная единица — один или несколько контейнеров |
| **Deployment** | управляет ReplicaSet — «хочу N подов с таким образом» |
| **Service** | стабильный сетевой адрес для группы подов |
| **Ingress** | L7 маршрутизация HTTP/HTTPS снаружи в Service |
| **ConfigMap / Secret** | конфигурация и секреты для подов |
| **PersistentVolume** | постоянное хранилище |
| **Namespace** | логическое разделение ресурсов |

**Альтернативы:** Nomad (HashiCorp), Docker Swarm (умер), ECS / Fargate (AWS), Cloud Run (GCP).',
                'difficulty' => 3,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Pod, Deployment, Service в Kubernetes?',
                'answer' => 'Три **базовые сущности** k8s, на которых строится всё остальное.

**`Pod`** — **наименьшая единица k8s**:

- Обычно **один контейнер**, иногда несколько связанных в одном сетевом namespace (**app + sidecar** для логирования или service mesh)
- Контейнеры в поде делят сеть (`localhost`) и тома
- **Эфемерный**: упал — создаётся **новый с новым IP**
- Сам по себе pod не пересоздаётся, нужен контроллер выше

**`Deployment`** — **декларативное описание** «хочу N реплик такого пода с такой стратегией обновления»:

- Управляет **`ReplicaSet`** → тот создаёт и пересоздаёт поды
- Держит **actual ≈ desired state**
- Умеет rolling update, rollback (`kubectl rollout undo`)

**`Service`** — **стабильная точка входа** к набору подов через **label selector**:

- У подов меняются IP, у Service **постоянный ClusterIP / DNS-имя**
- Встроенный L4 балансировщик между подами

**Типы Service:**

| Type | Доступ | Когда |
| --- | --- | --- |
| `ClusterIP` *(default)* | только внутри кластера | межсервисный трафик |
| `NodePort` | через порт на **любой ноде** | dev/staging без LB |
| `LoadBalancer` | внешний LB от облака | публичные API |
| `ExternalName` | DNS CNAME | алиас на внешний сервис |

**Связка:** `Pod` — единица запуска, `Deployment` — оркестратор реплик, `Service` — **стабильный адрес перед ними**.',
                'code_example' => 'apiVersion: apps/v1
kind: Deployment
metadata: { name: api }
spec:
  replicas: 3
  selector: { matchLabels: { app: api } }
  template:
    metadata: { labels: { app: api } }
    spec:
      containers:
      - name: api
        image: myapp:1.2
        ports: [{ containerPort: 8000 }]',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое blue-green deployment?',
                'answer' => '**Blue-green** — стратегия **деплоя без простоя через две идентичные среды**:

- **blue** — текущая прод-версия, обслуживает 100% трафика
- **green** — новая версия, развёрнута параллельно, трафика пока не получает

**Поток деплоя:**

1. Деплоим новую версию в `green`
2. Прогоняем **smoke-тесты** и health-checks
3. Переключаем **роутинг с blue на green** одной командой (через **load balancer**, **DNS** или **k8s Service selector**)
4. `blue` остаётся стоять — резерв на rollback
5. Если в `green` проблема → переключаем обратно (**instant rollback**)
6. После наблюдения N часов — `blue` можно гасить или превратить в следующий `green`

**Плюсы:**

- **Instant rollback** одним кликом
- **Нет downtime**
- Smoke-тесты на боевой среде до приёма трафика
- Простой mental model

**Минусы:**

- **Двойные ресурсы** на время деплоя (× стоимость инфраструктуры)
- **Миграции БД сложнее** — схема должна одновременно работать с обеими версиями (паттерн **Expand and Contract**)
- Stateful компоненты (sessions, in-memory cache) теряются при переключении
- Не ловит проблемы, проявляющиеся **только под полной нагрузкой** (для этого — canary)

**vs canary:** blue-green переключает **100% разом**, canary — **постепенно по %**. Blue-green проще, canary безопаснее для рискованных изменений.',
                'difficulty' => 3,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое canary deployment?',
                'answer' => '**Canary deploy** — **постепенный rollout**: новая версия получает **сначала 1% трафика**, потом 5%, 25%, 100%.

Аналогия: **канарейка в шахте** — если что-то не так, потеряем малость, а не всё разом.

**Как работает (типичный pipeline):**

1. Деплоим новую версию рядом со старой (например, 1 pod vs 99)
2. Маршрутизатор отправляет **1% трафика** в canary
3. **Сравниваем метрики** с baseline (старой версией):
   - error rate
   - p95 / p99 latency
   - бизнес-KPI (заказы / минуту)
4. Если метрики ровные → увеличиваем долю (5% → 25% → 50% → 100%)
5. Если ухудшение → **автоматический rollback**, трафик возвращается в старую версию

**Плюсы:**

- **Маленький blast radius** при ошибках — затронут только N% пользователей
- Ловит проблемы, которые видны **только под реальной нагрузкой**
- Можно совмещать с **A/B тестами**

**Минусы:**

- **Сложная инфраструктура маршрутизации:** Istio, Linkerd, Traefik, **Argo Rollouts**, Flagger
- **Хороший observability обязателен** — без метрик canary бесполезен
- **Сессии и stickiness** требуют внимания (пользователь, попавший в canary, не должен потом видеть старую версию — отсюда session affinity)
- Долгий по времени (часы вместо минут blue-green)

**vs blue-green:** blue-green — мгновенно 100%, canary — **процент за процентом**. Canary безопаснее для рискованных изменений и больших систем.',
                'difficulty' => 3,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое rolling deployment?',
                'answer' => '**Rolling deploy** — **обновление подов по одному**: подняли новый → проверили health → убрали старый → повторили. **Стратегия по умолчанию в Kubernetes Deployment.**

Аналогия: **меняем колёса на машине по одному**, не останавливая её.

**Ключевые параметры (`spec.strategy.rollingUpdate`):**

- **`maxUnavailable`** — сколько подов может быть **offline** во время выкатки (`25%` или абсолют)
- **`maxSurge`** — сколько **лишних** можно поднять сверх `replicas` (`25%` или абсолют)

При `replicas: 10`, `maxUnavailable: 1`, `maxSurge: 2` — в любой момент работает **9–12 подов**.

**Контроль скорости:**

- **Readiness probe** — k8s ждёт, пока новый под не станет ready, прежде чем убрать старый
- `minReadySeconds` — подождать N секунд после ready (страховка от false-positive)
- `progressDeadlineSeconds` — таймаут всей выкатки

**Плюсы:**

- **Без двойных ресурсов** (в отличие от blue-green)
- Встроено в k8s, не нужна доп. инфраструктура
- `kubectl rollout undo` — мгновенный откат

**Минусы:**

- **Во время деплоя одновременно работают обе версии** → нужна **обратная совместимость БД и API**:
  - Не удалять колонки и роуты сразу — паттерн **Expand-and-Contract**
  - JSON-схемы должны быть аддитивными
- **Откат не мгновенный** — снова rolling, только в обратную сторону
- Не ловит проблемы, видимые только при 100% (для этого — canary)',
                'code_example' => '# k8s Deployment — rolling по умолчанию
apiVersion: apps/v1
kind: Deployment
metadata: { name: api }
spec:
  replicas: 10
  strategy:
    type: RollingUpdate
    rollingUpdate:
      maxUnavailable: 1   # минимум 9 подов работают
      maxSurge: 2         # максимум 12 подов
  minReadySeconds: 10
  progressDeadlineSeconds: 600
  template:
    spec:
      containers:
      - name: api
        image: myapp:1.2
        readinessProbe:
          httpGet: { path: /healthz, port: 8000 }
          initialDelaySeconds: 5

# Команды
kubectl rollout status deployment/api
kubectl rollout undo deployment/api        # откатить
kubectl rollout history deployment/api',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое feature flags и зачем нужны?',
                'answer' => '**Feature flags (toggles)** — **условные блоки в коде**, включающие/выключающие фичи **без редеплоя**.

Аналогия: **рубильник** на новую фичу — можно включить только для тестовых юзеров, потом 10%, потом всем.

**Главная идея — разделение деплоя и релиза:**

- **Деплой** — код уехал на прод (флаг OFF)
- **Релиз** — фичу видят пользователи (флаг ON)

**Зачем:**

- **Trunk-based development** — мерджишь незавершённую фичу в `main` под выключенным флагом
- **Постепенная раскатка** — 1% → 10% → 100% по user-id / региону / плану
- **A/B тесты** — два варианта одновременно, метрики решают
- **Kill switch** — мгновенно выключить сломавшуюся фичу без редеплоя
- **Entitlements** — фича доступна по плану, ролям, beta-программе

**Минусы:**

- **Код засоряется `if`-ами** — растёт сложность
- **Технический долг** — старые флаги нужно **активно чистить** после полного rollout
- Тестировать нужно **обе ветки** (включён/выключен)
- Долго живущие флаги усложняют отладку

**Инструменты:**

- **LaunchDarkly** — коммерческий лидер
- **Unleash**, **GrowthBook**, **Flagsmith** — open-source
- **Laravel Pennant** — встроено в Laravel 10+
- Своя БД-таблица — для простых случаев',
                'code_example' => '<?php
// Laravel Pennant
use Laravel\\Pennant\\Feature;

// Определение
Feature::define("new-checkout", fn (User $user) =>
    $user->isInBetaProgram() || lottery(0.1)  // 10% юзеров
);

// Использование в контроллере
public function checkout(Request $request)
{
    if (Feature::for($request->user())->active("new-checkout")) {
        return view("checkout.v2");
    }
    return view("checkout.v1");
}

// В Blade
@feature("new-checkout")
    <x-checkout-v2 />
@else
    <x-checkout-v1 />
@endfeature

// Kill switch при инциденте — без редеплоя
Feature::deactivate("new-checkout");',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое 12-factor app?',
                'answer' => '**12-factor app** — методология **Heroku для cloud-native приложений** (2011). Запоминается не списком из 12, а **четырьмя группами**.

**1. Код и зависимости:**

- **Один codebase в git**, много deploy — никаких «прод-только» правок
- **Явные зависимости** (`composer.json`, `package.json`, lock-файлы)
- **Конфиг в env-переменных** — то же приложение работает на dev и prod, разница только в `.env`
- **Backing services** (БД, Redis, S3) — **подключаемые ресурсы по URL**, заменяемые без правки кода

**2. Сборка и запуск:**

- Чёткое разделение **build / release / run** (артефакт собрали → склеили с конфигом → запускаем)
- **Stateless-процессы** — никакого состояния в памяти между запросами (session/cache → внешнее хранилище)
- **Port binding** — приложение само поднимает порт, **не через Apache `mod_php`**

**3. Эксплуатация:**

- **Масштабирование через процессы** — горизонтально, добавлением воркеров
- **Disposability** — **быстрый старт** и **graceful shutdown** на `SIGTERM`
- **Dev/prod parity** — одинаковые версии PHP, БД, Redis везде; никакого SQLite-на-dev и MySQL-на-prod

**4. Поддержка:**

- **Логи как stdout-поток** — приложение не пишет в файл, лог собирается оркестратором
- **Admin tasks как one-off процессы** — `php artisan migrate`, `tinker`, не через UI

**Итог:** **stateless, конфиг снаружи, всё одинаково на dev и prod** → приложение легко крутится в **k8s, Docker, Heroku, Fly.io, Railway**.

**Laravel из коробки 12-factor-friendly:** `.env`, `php artisan`, лог в `stderr` через `daily`/`stderr` driver, миграции командой, сессии/кэш во внешний store.',
                'difficulty' => 3,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое CI/CD простыми словами?',
                'answer' => '**CI** (Continuous Integration) — каждый коммит **автоматически собирается** и **проходит тесты**. Простыми словами: что бы ты ни запушил — сразу проверка качества (линтер, `phpunit`, статический анализ).

**CD** — две версии:

- **Continuous Delivery** — после успешного CI артефакт **готов к деплою**, но катит человек (кнопка «Deploy»)
- **Continuous Deployment** — деплоится **автоматически** после зелёного CI, без кнопок

**Типовой pipeline** (на каждый push):

1. `composer install`
2. `php artisan test` / `vendor/bin/pest`
3. `vendor/bin/phpstan analyse`
4. сборка фронта (`npm run build`)
5. сборка Docker-образа, push в registry
6. деплой на staging → smoke-тесты → деплой на prod

**Зачем:**

- баги ловятся **сразу**, а не за день до релиза
- релизы маленькие и частые — меньше риск
- история «что и когда катилось» хранится в pipeline-логах

**Инструменты:** `GitHub Actions`, `GitLab CI`, `Jenkins`, `CircleCI`, `Drone`, `Buildkite`. Конфиг хранится в репе (`.github/workflows/*.yml`, `.gitlab-ci.yml`) — pipeline тоже под версионным контролем.',
                'difficulty' => 2,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Infrastructure as Code (IaC)?',
                'answer' => '**IaC** — **описание инфраструктуры в коде** вместо ручной настройки в UI.

Аналогия: вместо **кликов в AWS-консоли** пишешь файл, который их сделает за тебя — и его можно **ревьюить, версионировать, переиспользовать**.

**Два подхода:**

| | Декларативный | Императивный |
| --- | --- | --- |
| **Что описываешь** | желаемое состояние | последовательность шагов |
| **Кто считает diff** | сам инструмент | ты |
| **Идемпотентность** | встроенная | сам обеспечиваешь |
| **Инструменты** | **Terraform**, **OpenTofu**, **Pulumi**, AWS CloudFormation, k8s YAML | **Ansible**, Chef, Puppet, bash |

**Плюсы:**

- **Воспроизводимость** — поднять копию окружения = `terraform apply`
- **History через git** — кто и когда что менял, видно в `git blame`
- **Code review для инфраструктуры** — изменения проходят PR
- **Drift detection** — `terraform plan` показывает расхождение между кодом и реальностью
- **Disaster recovery** — после потери региона восстановили из кода

**Подводные камни:**

- **State-файл** в Terraform — критичен, хранится в S3 + DynamoDB lock или Terraform Cloud
- **Секреты в state** — лежат в plain-text, нужен encryption-at-rest и ограниченный доступ
- **Drift** — ручные правки через UI ломают idempotency
- **Долгие apply** — изменение одной строчки может задеть кучу ресурсов

**Часто комбинируют:** **Terraform** поднимает голую инфру (VPC, инстансы, k8s-кластер), **Ansible** настраивает софт на VM, **Helm** деплоит приложения в k8s.',
                'code_example' => '# Terraform — декларативно
resource "aws_instance" "api" {
  ami           = "ami-0c55b159"
  instance_type = "t3.medium"
  tags = { Name = "api-server" }
}

resource "aws_db_instance" "postgres" {
  identifier        = "shop-prod"
  engine            = "postgres"
  engine_version    = "16.1"
  instance_class    = "db.t3.medium"
  allocated_storage = 100
  multi_az          = true
}

# Применить
$ terraform plan      # показать что изменится
$ terraform apply     # применить
$ terraform destroy   # снести всё',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как обеспечить Graceful Shutdown для PHP-воркеров и Kubernetes-подов?',
                'answer' => '**Graceful shutdown** — корректное завершение процесса при сигнале остановки: дождаться текущей работы, **не принимать новую**, освободить ресурсы. Без него при деплое теряются in-flight `Job`-ы, обрываются HTTP-запросы, в БД остаётся «висящий» state.

**Механика Linux:**
- `SIGTERM` (15) — нужно успеть завершиться за **grace period**.
- Не успел — приходит `SIGKILL` (9), который **не перехватывается**.
- Kubernetes по умолчанию даёт `terminationGracePeriodSeconds=30` после `SIGTERM`, затем `SIGKILL`.

**Что делать в PHP:**

1. **CLI-воркер:** `pcntl_async_signals(true)` + `pcntl_signal(SIGTERM, ...)` + флаг `shouldStop`, проверяемый в основном цикле **между задачами**.
2. **Очереди (Laravel):** `queue:work` сам ловит `SIGTERM`/`SIGINT` и завершается после текущего `Job`. Настрой `--timeout` и `terminationGracePeriodSeconds > --timeout`.
3. **HTTP (php-fpm):** graceful через `kill -USR2` (мастер форкает новых воркеров, старые дорабатывают). `SIGUSR1` — переоткрыть лог-файлы (для logrotate). `SIGQUIT` — graceful shutdown без замены.
4. **Octane / Swoole / RoadRunner:** встроенная поддержка graceful reload.

**Kubernetes-обвязка:**
- **`preStop` hook** (`sleep 10`) — даёт сервис-мешу / LB убрать pod из endpoints **до** остановки.
- **Readiness probe** возвращает `unready` при получении `SIGTERM`.
- **`terminationGracePeriodSeconds`** = максимум твоей задачи + buffer.

**Подводный камень:** в Kubernetes `SIGTERM` приходит **раньше**, чем pod удалён из endpoints — всегда нужен `preStop sleep` либо корректная readiness-проверка.',
                'code_example' => '<?php
// 1. Свой воркер с обработкой SIGTERM
pcntl_async_signals(true);
$shouldStop = false;
pcntl_signal(SIGTERM, function () use (&$shouldStop) { $shouldStop = true; });
pcntl_signal(SIGINT,  function () use (&$shouldStop) { $shouldStop = true; });

while (! $shouldStop) {
    $job = $this->queue->pop();
    if ($job) {
        $job->handle(); // дорабатываем до конца, не прерываем
    } else {
        sleep(1);
    }
}
$this->cleanup(); // close DB, flush metrics

// 2. Laravel queue:work уже умеет; supervisor:
// stopsignal=TERM
// stopwaitsecs=120

// 3. Kubernetes Deployment
// spec:
//   template:
//     spec:
//       terminationGracePeriodSeconds: 120
//       containers:
//       - name: worker
//         lifecycle:
//           preStop:
//             exec:
//               command: ["sh","-c","sleep 10"] # дать LB убрать из endpoints
//         readinessProbe:
//           httpGet: { path: /healthz, port: 8000 }
//         # SIGTERM -> воркер дожимает, SIGKILL через 120s

// 4. php-fpm graceful reload
// kill -USR2 $(cat /var/run/php-fpm.pid) # перезагрузка с дописыванием текущих',
                'code_language' => 'php',
                'difficulty' => 5,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как сделать миграцию БД (rename column, drop column) без downtime в blue-green деплое?',
                'answer' => '**Проблема:** прямой `RENAME` / `DROP` / `ALTER TYPE` во время blue-green или rolling-деплоя ломает прод — в момент миграции **одновременно работают две версии кода**: старая (поды, которые ещё не перекатились) и новая. Если старый код ждёт колонку `email`, а её только что удалили — старые поды падают.

**Решение — паттерн `Expand and Contract` (Parallel Change), 5 шагов:**

1. **EXPAND.** Создай **новую колонку** (добавление — безопасная операция в современных БД, кроме `DEFAULT` в PG до 11 — там переписывается вся таблица). Старая жива, новая пустая. Деплой миграции, прод не трогаем.
2. **DUAL WRITE.** Деплой кода, который пишет **в обе колонки** при каждом `UPDATE`/`INSERT`. Читает пока из старой — чтобы старые и новые поды видели одинаковые данные во время rolling rollout.
3. **BACKFILL.** Миграция данных: копируем существующие записи из старой колонки в новую через `chunkById`, чтобы не залочить таблицу. После — новая колонка имеет полные актуальные данные.
4. **SWITCH READS.** Деплой кода, который **читает из новой**, но всё ещё пишет в обе. Если что-то сломалось — откатываемся, старая колонка цела.
5. **STOP DUAL WRITE + DROP.** Деплой кода, который пишет и читает **только из новой**. Когда уверены, что старая нигде не используется — отдельным релизом `DROP COLUMN`.

**Применимо ко всем «разрушительным» изменениям:** rename column, change type, split/merge таблиц, удаление таблицы.

**Правила:**
- Каждый шаг — **отдельный деплой**. Между ними часы или дни, особенно перед `DROP`.
- В Laravel: пишите миграции в обе стороны (`up`/`down`); **не объединяйте** expand и contract в одном файле миграций.

**PostgreSQL-нюансы для больших таблиц:**
- `ALTER TABLE` без `DEFAULT` обычно мгновенен (в PG 11+).
- `CREATE INDEX CONCURRENTLY` — не блокирует записи.
- `DROP COLUMN` мгновенен, но физическое место освободится только после `VACUUM FULL`.',
                'code_example' => '<?php
// Сценарий: переименовать users.username → users.handle

// Релиз 1 (EXPAND): миграция добавляет новую колонку
Schema::table("users", function (Blueprint $table) {
    $table->string("handle")->nullable()->index();
});

// Релиз 2 (DUAL WRITE): код пишет в обе, читает из старой
class User extends Model
{
    protected static function booted(): void
    {
        static::saving(function (User $u) {
            if ($u->isDirty("username")) {
                $u->handle = $u->username; // зеркалим
            }
        });
    }

    public function getDisplayName(): string
    {
        return $this->username; // читаем из старой
    }
}

// Релиз 3 (BACKFILL): команда заполняет историю
// php artisan app:backfill-handle
User::whereNull("handle")->chunkById(1000, function ($users) {
    foreach ($users as $u) {
        $u->update(["handle" => $u->username]);
    }
});

// Релиз 4 (SWITCH READS): читаем из новой, всё ещё пишем в обе
public function getDisplayName(): string
{
    return $this->handle; // ← переключили
}

// Релиз 5 (CONTRACT): убираем dual-write
class User extends Model
{
    // saving-хук удалён, работаем только с handle
}

// Релиз 6 (CLEANUP): миграция DROP старой колонки
Schema::table("users", function (Blueprint $table) {
    $table->dropColumn("username");
});

// Подводный камень PG: ALTER TABLE ... ADD COLUMN ... NOT NULL DEFAULT \'x\'
// в PG < 11 переписывает всю таблицу (часы на больших таблицах + access exclusive lock).
// В PG 11+ NOT NULL DEFAULT - метаданные, мгновенно.
// Безопасно везде: ADD COLUMN nullable → backfill → ALTER COLUMN SET NOT NULL.',
                'code_language' => 'php',
                'difficulty' => 5,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'В чём разница между ARG и ENV в Dockerfile?',
                'answer' => 'Обе директивы задают переменные, но **в разное время и с разной видимостью**.

| | `ARG` | `ENV` |
| --- | --- | --- |
| **Время жизни** | **build-time** только | **runtime** контейнера |
| **Видны процессу внутри?** | нет | **да** (`getenv()`) |
| **Как передать снаружи** | `docker build --build-arg X=v` | `docker run -e X=v` |
| **Попадает в image** | нет (только в `docker history`) | **да**, в каждый слой |
| **Скоуп** | от объявления до конца stage | от объявления до конца stage |

**Типичные применения:**

**`ARG`** — версия базового образа, флаги сборки, target-окружение:

```dockerfile
ARG PHP_VERSION=8.3
FROM php:${PHP_VERSION}-fpm
ARG APP_ENV=production
RUN if [ "$APP_ENV" = "production" ]; then composer install --no-dev; fi
```

**`ENV`** — runtime-конфиг для процесса:

```dockerfile
ENV APP_ENV=production
ENV PHP_INI_DIR=/usr/local/etc/php
```

**Главный нюанс безопасности — никогда не передавайте секреты через ARG и ENV:**

- **`ARG`**: остаётся в **истории слоёв** — `docker history myimage` покажет
- **`ENV`**: утекает в **логи**, в **`docker inspect`** и виден любому процессу контейнера
- **Решение** — **BuildKit secrets** (mount во время `RUN`, не сохраняется в слое) или **runtime-инъекция** через k8s Secret / Docker Swarm secret',
                'code_example' => '# BuildKit secret для composer auth
# syntax=docker/dockerfile:1.4
FROM composer:2 as deps
WORKDIR /app
COPY composer.json composer.lock ./
RUN --mount=type=secret,id=composer_auth,target=/root/.composer/auth.json \\
    composer install --no-dev

# Сборка с секретом — он НЕ попадает в image
$ docker build --secret id=composer_auth,src=$HOME/.composer/auth.json -t app .

# ARG с дефолтом
ARG PHP_VERSION=8.3
FROM php:${PHP_VERSION}-fpm

# Runtime ENV — попадёт в getenv()
ENV APP_ENV=production
ENV PHP_INI_DIR=/usr/local/etc/php

# ⚠️ НЕ ТАК:
ARG DB_PASSWORD     # увидят в docker history
ENV DB_PASSWORD=    # увидят в docker inspect',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Docker volumes и чем named volume отличается от bind mount?',
                'answer' => '**Volume** — механизм, при котором данные **хранятся вне union-файловой системы контейнера и переживают его удаление**. Контейнер эфемерный, volume — нет.

**Три типа:**

| | Named volume | Bind mount | Anonymous volume |
| --- | --- | --- | --- |
| **Кто управляет** | Docker | вы (путь на хосте) | Docker |
| **Где лежит** | `/var/lib/docker/volumes/<name>` | произвольный путь хоста | `/var/lib/docker/volumes/<sha>` |
| **Имя** | задано (`mydata`) | путь хоста | случайный hash |
| **Переносимость** | хорошая | привязан к структуре хоста | нет |
| **Типичный use case** | **prod-данные** (БД, uploads) | **dev** (mount кода для hot-reload) | временный кэш |

**Когда что:**

- **Named volume** для production-данных:
  - БД (MySQL, Postgres data dir)
  - **Загруженные пользователями файлы**
  - Долгоживущий кэш
- **Bind mount** для разработки:
  - `./src:/app/src` — код перечитывается без ребилда
  - **Привязывает контейнер к структуре хоста** — даёт прямой доступ к ФС хозяина
- **Anonymous volume** Docker создаёт сам без имени — **трудно переиспользовать**, обычно нежелателен

**Подводные камни:**

- **Bind mount затирает то, что было в контейнере** по этому пути (включая `node_modules` из image — отсюда `node_modules` volume поверх bind mount)
- **Права доступа** — UID процесса в контейнере должен совпадать с владельцем на хосте (особенно болезненно на Linux + non-root user)
- **macOS / Windows bind mount медленный** — VM-граница; для скорости используют `:delegated` или `:cached` (deprecated в новом Docker Desktop, теперь mutagen / VirtioFS)
- **Backup volume**: `docker run --rm -v mydata:/data -v $(pwd):/backup alpine tar czf /backup/data.tar.gz -C /data .`',
                'code_example' => '# Named volume
docker volume create mysql-data
docker run -d \\
  -v mysql-data:/var/lib/mysql \\
  -e MYSQL_ROOT_PASSWORD=secret \\
  mysql:8

# Bind mount для разработки
docker run -v $(pwd):/var/www/html php:8.3-fpm

# Inspect и backup
docker volume ls
docker volume inspect mysql-data
docker run --rm \\
  -v mysql-data:/data \\
  -v $(pwd):/backup \\
  alpine tar czf /backup/mysql.tar.gz -C /data .

# docker-compose
# services:
#   db:
#     image: mysql:8
#     volumes:
#       - db_data:/var/lib/mysql      # named
#   app:
#     volumes:
#       - ./src:/var/www              # bind для dev
# volumes:
#   db_data:',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Зачем нужен multi-stage build в Dockerfile?',
                'answer' => '**Multi-stage** позволяет описать в одном `Dockerfile` **несколько `FROM`-этапов** и копировать артефакты из одного в другой через **`COPY --from`**.

**Типовая схема для PHP-приложения:**

1. **Stage `composer`** — образ с composer ставит prod-зависимости
2. **Stage `node`** — образ с node собирает фронт (vite build)
3. **Stage `runtime`** — slim-образ `php-fpm-alpine`, в который через `COPY --from=...` переносится **только готовое**:
   - `vendor/` из composer-stage
   - `public/build/` из node-stage
   - исходники приложения

**Что даёт:**

- **Финальный образ не содержит composer, npm, dev-инструментов, исходников тестов** — **в разы меньше** (200 МБ вместо 1 ГБ)
- **Меньше attack surface** — нет лишних бинарей для эксплойтов
- **Быстрее тянется на ноды** k8s при rolling update
- **Один Dockerfile** вместо двух (build + runtime) — проще поддерживать
- **Параллельная сборка** stages через BuildKit, если они не зависят

**Полезные приёмы:**

- `--target <stage>` в `docker build` — собрать только до нужного stage (например, отдельный `dev` stage с xdebug)
- `FROM ... AS deps` — именованные stage для читаемости
- В `COPY --from` можно указать **внешний image** (`COPY --from=composer:2 /usr/bin/composer /usr/bin/`)

**Де-факто стандарт для production-образов.**',
                'code_example' => '# Stage 1: composer dependencies
FROM composer:2 AS deps
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --optimize-autoloader

# Stage 2: frontend build
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json vite.config.js ./
RUN npm ci
COPY resources/ resources/
RUN npm run build

# Stage 3: runtime (тонкий образ)
FROM php:8.3-fpm-alpine AS runtime
WORKDIR /var/www
COPY --from=deps /app/vendor /var/www/vendor
COPY --from=frontend /app/public/build /var/www/public/build
COPY . .
RUN php artisan optimize
USER 1000:1000
EXPOSE 9000
CMD ["php-fpm"]

# Опциональный dev-stage с xdebug
FROM runtime AS dev
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Сборка
docker build --target runtime -t app:prod .
docker build --target dev -t app:dev .',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как порядок инструкций в Dockerfile влияет на размер слоёв и скорость сборки?',
                'answer' => 'Каждая инструкция **`RUN`, `COPY`, `ADD`** создаёт **отдельный слой**, который кешируется по контрольной сумме входов.

**Главное правило:** **слои инвалидируются последовательно** — если изменился слой N, **все следующие пересобираются заново**.

**Правила оптимизации:**

**1. От редко меняющегося к часто меняющемуся:**

```dockerfile
FROM php:8.3-fpm-alpine

# 1) Системные пакеты — редко меняются
RUN apk add --no-cache git zip libpng-dev

# 2) Зависимости композера — меняются при правке composer.lock
COPY composer.json composer.lock ./
RUN composer install --no-dev

# 3) Код — меняется на каждый коммит
COPY . .
```

Правка одного PHP-файла **не запускает** установку пакетов или composer.

**2. Объединять связанные `RUN` в один слой:**

- `apt-get update && apt-get install -y X && rm -rf /var/lib/apt/lists/*` — **одной командой**
- Иначе кеш пакетов навсегда лежит в промежуточном слое и **раздувает образ**

**3. Минимизировать число слоёв** — каждый имеет метаданные и overhead, но не до фанатизма (читаемость важнее).

**4. `.dockerignore`** до `COPY .` — иначе случайные изменения в `.git/` или `node_modules/` ломают cache.

**5. Использовать BuildKit cache mounts** — `RUN --mount=type=cache,target=/root/.composer/cache composer install` — composer cache переживает rebuild.

**Метрики качества:**

- `docker history myimage --no-trunc` — какой слой сколько весит
- `dive myimage` — TUI-инспектор слоёв с подсветкой избыточного
- При оптимальной разбивке **повторная сборка после правки одного файла занимает секунды**',
                'code_example' => '# ❌ ПЛОХО — каждый билд переустанавливает всё
FROM php:8.3-fpm-alpine
COPY . /var/www
RUN apk add --no-cache git
RUN composer install
RUN apt-get update && apt-get install -y curl   # ⚠️ apt-кеш в слое

# ✅ ХОРОШО — слои упорядочены по частоте изменений
FROM php:8.3-fpm-alpine

# 1. Системные зависимости — установка + cleanup в одном слое
RUN apk add --no-cache \\
        git zip libpng-dev libpq-dev \\
    && docker-php-ext-install pdo_pgsql gd

# 2. Composer-зависимости отдельным слоем
WORKDIR /var/www
COPY composer.json composer.lock ./
RUN --mount=type=cache,target=/root/.composer/cache \\
    composer install --no-dev --no-scripts --no-autoloader

# 3. Код в конце
COPY . .
RUN composer dump-autoload --optimize

# Посмотреть размер слоёв
docker history myapp --no-trunc
dive myapp',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что делает .dockerignore и почему его стоит писать сразу?',
                'answer' => 'При `docker build` клиент **упаковывает контекст** (текущую директорию) и шлёт демону. Без `.dockerignore` туда попадает **всё подряд** — `.git`, `vendor`, `node_modules`, `.env`, логи, дампы.

**Чем это плохо:**

- **медленный билд** — десятки/сотни мегабайт зря грузятся
- **раздувает слои** — `COPY . .` тянет мусор в образ
- **утечка секретов** — `.env` с прод-паролями попадает в образ, который потом кладут в публичный registry
- **ломает кэш** — `.git` меняется на каждый коммит, инвалидирует слои

**Как работает:** `.dockerignore` — это **аналог `.gitignore`**, исключает пути из контекста **до того**, как `Dockerfile` их увидит.

**Что обычно кладут:**

- `.git`, `.github`
- `node_modules`, `vendor` (если ставятся внутри билда)
- `tests`, `storage/logs`, `storage/framework/cache`
- `.env`, `.env.*` (важно для безопасности)
- `*.md`, документация, IDE-файлы (`.idea`, `.vscode`)',
                'code_example' => '# .dockerignore
.git
.github
node_modules
vendor
storage/logs/*
storage/framework/cache/*
.env
.env.*
!.env.example
tests
*.md
.idea
.vscode
.DS_Store',
                'code_language' => 'bash',
                'difficulty' => 2,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Зачем нужен docker-compose и где у него предел применимости?',
                'answer' => '**`docker-compose`** описывает в одном **YAML-файле группу связанных сервисов** (`php-fpm`, `nginx`, `mysql`, `redis`), их сети, тома и зависимости — чтобы поднять всё локально **одной командой `docker compose up`**.

**Хорошо подходит для:**

- **Dev-окружения** — клонировал репо, `docker compose up`, всё работает
- **Интеграционные тесты на CI** — поднять стек, прогнать тесты, погасить
- **Single-host production** для маленьких проектов или PoC

**Предел применимости — production-кластер:**

В compose **нет**:

- **Шедулинга по нескольким хостам** — всё на одной машине
- **Авто-рестарта** при падении ноды (есть только `restart: always` на уровне контейнера)
- **Rolling update / blue-green** — нативной поддержки
- **Health-based load balancing** между репликами
- **Service mesh / mTLS** между сервисами
- **Auto-scaling** под нагрузку
- **Secrets management** уровня k8s Secret + Vault

**Куда расти:** **Kubernetes**, Nomad, AWS ECS / Fargate. Compose — про **single-host оркестрацию**.

**Конвертация compose → k8s:** **Kompose** (`kompose convert`) генерирует базовые k8s-манифесты из compose-файла — стартовая точка, не production-готовое.',
                'code_example' => '# docker-compose.yml для Laravel dev
services:
  app:
    build: .
    volumes:
      - ./:/var/www
    depends_on:
      db: { condition: service_healthy }
      redis: { condition: service_started }

  nginx:
    image: nginx:alpine
    ports: ["8080:80"]
    volumes:
      - ./docker/nginx.conf:/etc/nginx/nginx.conf
      - ./public:/var/www/public

  db:
    image: postgres:16
    environment:
      POSTGRES_PASSWORD: secret
    volumes:
      - db_data:/var/lib/postgresql/data
    healthcheck:
      test: ["CMD", "pg_isready", "-U", "postgres"]
      interval: 5s

  redis:
    image: redis:7-alpine

volumes:
  db_data:

# Команды
docker compose up -d
docker compose logs -f app
docker compose exec app php artisan migrate
docker compose down -v',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Ingress в Kubernetes и чем он отличается от Service типа LoadBalancer?',
                'answer' => 'Два разных способа **впустить трафик снаружи в кластер** Kubernetes.

| | **`Service` type=`LoadBalancer`** | **`Ingress`** |
|---|---|---|
| **Уровень OSI** | **L4** (TCP/UDP) | **L7** (HTTP/HTTPS) |
| **Маршрутизация** | по портам | по **host** и **path** |
| **TLS** | passthrough | **termination** |
| **На один сервис** | **один LB + IP** | **один LB на N сервисов** |
| **Цена в облаке** | $$ × N сервисов | $$ × 1 |
| **Что под капотом** | cloud-provider LB (ELB, GLB) | **`Ingress Controller`** (nginx, Traefik, HAProxy) |

**`Service: LoadBalancer`** — на каждый сервис **отдельный** внешний балансировщик с **публичным IP**:

- На облаке (`AWS ELB`, `GCP Load Balancer`) — **$15-25/мес** за каждый
- 10 микросервисов → 10 LB → **дорого**
- Простой L4 — не понимает HTTP

**`Ingress`** — **слой L7-маршрутизации** поверх кластера:

- **Один внешний LB** + **`Ingress Controller`** внутри
- Controller (nginx / Traefik / HAProxy / Istio) разводит трафик по **`host` и `path`**:
  - `api.example.com/*` → `api-service`
  - `shop.example.com/*` → `shop-service`
  - `api.example.com/v2/*` → `api-v2-service`
- Из коробки: **TLS termination**, rewrite, basic auth, rate limit, **WAF**

**Возможности Ingress поверх L7:**

- **TLS termination** — сертификаты через `cert-manager` + Let\'s Encrypt
- **HTTP/2**, **gRPC**
- **Rewrite** path (`/api/v1/users` → `/users`)
- **Basic auth**, **JWT validation**
- **Rate limiting** per-route
- **Canary** через header / cookie / weight
- **CORS**, headers manipulation

**Когда что выбирать:**

| Сценарий | Решение |
|---|---|
| **HTTP/HTTPS трафик** | **`Ingress`** (стандарт) |
| **gRPC, WebSocket** | Ingress с поддержкой (Traefik, Istio) |
| **TCP/UDP не-HTTP** (Redis, MySQL извне) | `Service: LoadBalancer` |
| **Один сервис без routing** | `Service: LoadBalancer` (если бюджет позволяет) |

**Современная альтернатива** — **`Gateway API`** (GA в Kubernetes 1.30+) — преемник Ingress с лучшим разделением ролей и более выразительным синтаксисом.',
                'difficulty' => 4,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Зачем в Kubernetes нужны liveness и readiness probes и в чём между ними разница?',
                'answer' => 'Две **разные probe** с разной семантикой — путать их **опасно**.

| Probe | Вопрос | При падении | Когда нужна |
|---|---|---|---|
| **`readiness`** | «готов **принимать трафик**?» | под убирается из `endpoints`, **не убивается** | прогрев кэша, ожидание БД, деплой |
| **`liveness`** | «жив ли **процесс**?» | под **перезапускается** | deadlock, утечка, зависание |
| **`startup`** | «закончил **старт**?» | блокирует другие probes | долгие старты (Java, миграции) |

**`readiness probe`:**

- Возвращает `200 OK` когда под **готов обслуживать запросы**
- `kube-proxy` использует её для решения, **отправлять ли трафик**
- При `fail` → под **удаляется из endpoints** `Service`
- **Под живой**, перезапуска нет — ждём, пока станет ready

**Типичные применения readiness:**

- Прогрев OPcache при старте
- Ожидание подключения к БД / Redis
- Загрузка ML-модели в память
- Во время **graceful shutdown** возвращать `503` чтобы LB убрал из ротации

**`liveness probe`:**

- Возвращает `200 OK` когда **процесс жив и работает**
- При `fail N раз подряд` → **kubelet перезапускает контейнер**
- **Last resort** — починка через рестарт

**Типичные применения liveness:**

- Deadlock в треде
- Memory leak (но обычно ловится OOM-killer)
- Стейтовая рассинхронизация, требующая чистого старта
- Зависание на бесконечном GC

**Опасные ошибки:**

| Ошибка | Что произойдёт |
|---|---|
| **`readiness` привязан к БД** | БД упала → **весь сервис** исчез из балансировки → каскад |
| **`liveness` слишком агрессивный** (`failureThreshold=1`, `period=1s`) | медленный GC → постоянные рестарты |
| **Одинаковая логика для liveness и readiness** | теряется смысл разделения |
| **Нет `initialDelaySeconds`** | под убивают до старта приложения |

**Правильные настройки для Laravel:**

```yaml
readinessProbe:
  httpGet: { path: /healthz/ready, port: 8000 }
  initialDelaySeconds: 5
  periodSeconds: 5
  failureThreshold: 3
livenessProbe:
  httpGet: { path: /healthz/live, port: 8000 }
  initialDelaySeconds: 30
  periodSeconds: 30
  failureThreshold: 5
```

**`/healthz/ready`** проверяет БД + Redis; **`/healthz/live`** возвращает 200 без проверок зависимостей.',
                'difficulty' => 4,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Чем ConfigMap отличается от Secret в Kubernetes?',
                'answer' => 'Два **похожих по API** объекта Kubernetes с **разной семантикой и защитой**.

| | **`ConfigMap`** | **`Secret`** |
|---|---|---|
| **Назначение** | несекретная конфигурация | пароли, токены, ключи |
| **Хранение в etcd** | plain text | **`base64`** (это **НЕ шифрование**) |
| **Encryption-at-rest** | нет | **опционально** (`EncryptionConfiguration`) |
| **RBAC** | обычный | **строже** ограничивают |
| **Размер** | до 1 МБ | до 1 МБ |
| **Маунт** | env / volume | env / volume |

**`ConfigMap`** хранит **несекретную конфигурацию**:

- URL-ы внешних API (`API_URL=https://...`)
- **Фичефлаги** (`FEATURE_NEW_UI=true`)
- Имена файлов, пути
- Конфиги (`nginx.conf`, `php.ini`)

**`Secret`** устроен **почти так же**, но для секретов:

- Значения **закодированы в `base64`** в YAML — это **транспортная кодировка**, **не шифрование**
- В **etcd** опционально включается **encryption-at-rest** (KMS / `aescbc`)
- RBAC обычно настраивают **строже** — `get/list secrets` только у нужных ServiceAccount

**Важный миф:** `base64(password)` **в YAML — это не безопасность**. Любой с доступом к манифесту легко декодирует.

**Подходы к секретам:**

| Подход | Когда |
|---|---|
| **Plain Secret в git** | **никогда** в публичных репо |
| **`sealed-secrets`** (bitnami) | шифрование Secret в git через публичный ключ кластера |
| **`SOPS`** (Mozilla) | шифрование YAML с интеграцией age/PGP/KMS |
| **`external-secrets-operator`** | **best practice** — Secret синкается из **Vault / AWS Secrets Manager / GCP Secret Manager** |
| **CSI Secrets Store Driver** | секреты приходят как volume из внешнего provider, не попадают в etcd |

**Best practice для production:**

1. **Никогда** не коммитить plain Secret в git
2. **Vault** / **AWS Secrets Manager** как источник правды
3. **`external-secrets`** синхронизирует в Kubernetes Secret
4. **Encryption-at-rest в etcd** включён
5. **RBAC** ограничивает доступ к Secret по ServiceAccount
6. **Rotation** автоматизирован через `secret-rotation-operator` или Vault dynamic secrets

**Использование в Pod:**

- **env**: `valueFrom: secretKeyRef` или `configMapKeyRef`
- **volume**: маунт как файл (полезно для сертификатов, `.htpasswd`)
- **envFrom**: импортнуть все ключи разом',
                'difficulty' => 4,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Зачем в Kubernetes указывают requests и limits для CPU и памяти?',
                'answer' => 'Два **разных параметра** ресурсов pod-а, которые легко перепутать.

| | **`requests`** | **`limits`** |
|---|---|---|
| **Значение** | **минимум**, гарантированный поду | **потолок** потребления |
| **Использует** | scheduler — для размещения | cgroup — для enforcement |
| **При превышении** | — | **CPU: throttle**, **Memory: OOM-kill** |
| **На сумме** | считается, влезет ли под на ноду | не считается |

**`requests`** — что обещает кластер:

- **Scheduler** считает: `sum(requests) ≤ node capacity` → можно ли разместить
- Гарантированный ресурс — pod **точно получит** этот объём
- Без requests → шедулинг **непредсказуемый** → **noisy neighbours**

**`limits`** — что не дадим превысить:

- **CPU limit** превышен → **throttling** (процесс замедляется, не убивается)
- **Memory limit** превышен → **OOM-killed** (kernel убивает контейнер)
- **Без memory limit** → процесс может **сожрать всю ноду** и положить **другие pod-ы**

**QoS-классы Kubernetes** (определяются по requests/limits):

| Класс | Условие | Кого убивают первым при нехватке памяти |
|---|---|---|
| **`Guaranteed`** | requests == limits для всех ресурсов | **последним** |
| **`Burstable`** | requests < limits | средний приоритет |
| **`BestEffort`** | нет requests и limits | **первым** |

**CPU limit — осторожно:**

- На **интенсивных пиках** (composer dump, прогрев OPcache, JIT компиляция) **throttling ломает SLA**
- p99 latency страдает сильнее, чем средняя
- **Многие команды** убирают CPU limits, оставляя только **requests**
- Спорный момент — есть оба лагеря: «выставлять» vs «не выставлять»

**Memory limit — обязателен:**

- **Без него** утечка памяти → ноду убивает OOM-killer **в случайном порядке**
- **С limit** — убивается **только** виновный pod

**Best practices:**

- **`requests`** — адекватно **реальному p95-потреблению** (профилируйте на стейдже)
- **`memory limit`** — с **запасом 20-30%** над p99
- **`CPU limit`** — спорно, многие убирают для PHP-FPM
- **VPA / HPA** — горизонтальный/вертикальный автоскейлинг по реальным метрикам
- **LimitRange** на namespace — дефолты для команд, забывающих указывать

**Подвох с PHP-FPM:**

- `pm.max_children × memory_limit_per_process` должно влезать в pod memory limit
- Иначе OOM-kill сразу при наплыве запросов',
                'difficulty' => 4,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Helm и какую проблему он решает?',
                'answer' => '**`Helm`** — **пакетный менеджер** для Kubernetes (аналог `apt` / `composer`, но для манифестов).

**Структура `chart` (пакета):**

```
mychart/
├── Chart.yaml          # метаданные пакета
├── values.yaml         # дефолтные параметры
├── templates/
│   ├── deployment.yaml # шаблоны с {{ .Values.X }}
│   ├── service.yaml
│   ├── ingress.yaml
│   └── _helpers.tpl    # переиспользуемые блоки
└── charts/             # вложенные зависимости
```

**Как работает:**

1. `helm install myapp ./mychart -f prod-values.yaml`
2. Helm **подставляет `values`** в шаблоны (Go templates)
3. **Применяет** получившиеся манифесты **атомарно** через k8s API
4. Сохраняет историю в **`Secret`** в namespace
5. **`helm rollback`** возвращает предыдущую версию

**Проблема, которую решает:**

Без Helm на **каждое окружение** копируются почти **идентичные YAML**:

```
deploy/
├── dev/    deployment.yaml service.yaml ingress.yaml ...
├── stage/  deployment.yaml service.yaml ingress.yaml ...
└── prod/   deployment.yaml service.yaml ingress.yaml ...
```

Любая правка → **`sed` по нескольким файлам** → ошибки, дрейф между окружениями.

**С Helm:**

```yaml
# values.yaml — дефолт
replicas: 1
image: myapp:latest

# values-prod.yaml — оверрайды
replicas: 5
image: myapp:1.2.3
resources: { requests: { cpu: 200m, memory: 256Mi } }
```

`helm upgrade --install myapp ./chart -f values-prod.yaml`

**Преимущества:**

- **DRY** — один chart, разные `values`
- **Релизы и rollback** из коробки (`helm history`, `helm rollback`)
- **Зависимости** — chart-А может включать chart-Б (postgres, redis из community charts)
- **`helm repo`** — публичные хабы готовых charts (`bitnami`, `prometheus-community`)
- **Hooks** — pre-install, post-upgrade

**Альтернативы:**

| | **Helm** | **Kustomize** | **`ArgoCD` + Helm** |
|---|---|---|---|
| **Подход** | шаблонизация | **overlay** patches | GitOps + Helm |
| **Кривая обучения** | средняя | низкая | средняя |
| **Готов в k8s из коробки** | нет | **да** (kubectl apply -k) | нет |
| **История релизов** | да | нет | через git |

**Современная практика:** **GitOps через ArgoCD / Flux поверх Helm chart-ов** — chart хранится в git, ArgoCD применяет автоматически при изменении, rollback = `git revert`.',
                'difficulty' => 4,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'В чём разница между Terraform и Ansible?',
                'answer' => 'Два **разных по назначению** инструмента инфраструктуры, которые часто путают.

| | **`Terraform`** (`OpenTofu`) | **`Ansible`** |
|---|---|---|
| **Что делает** | **provisioning** инфраструктуры | **configuration management** |
| **Подход** | **декларативный** | **императивный** (с идемпотентностью) |
| **Транспорт** | **API облаков** | **SSH** |
| **Язык** | **HCL** | **YAML** |
| **State** | хранит **state-файл** | без state, считывает с хоста |
| **Идемпотентность** | через diff с state | через `check_mode` / when |
| **Тип ресурсов** | VPC, EC2, RDS, k8s-кластер | пакеты, файлы, сервисы на хосте |

**`Terraform`** — **provisioning**:

- Описываешь **желаемое состояние** в HCL: `resource "aws_instance" ...`
- `terraform plan` показывает **diff** между state и реальностью
- `terraform apply` приводит к нужному виду через **API облака**
- Хорош для:
  - Создания VPC, сабнетов, security groups
  - Запуска EC2 / RDS / S3
  - Развёртывания k8s-кластера (EKS / GKE)
  - Управления DNS, IAM
- **Главное:** «**что должно существовать**»

**`Ansible`** — **configuration management**:

- Набор **playbook** с задачами: установи пакет, скопируй файл, перезапусти сервис
- Выполняется **императивно сверху вниз** через **SSH**
- Хорош для:
  - Настройки софта на VM (nginx, php-fpm, postgres)
  - Patch management
  - Деплоя приложений на bare-metal
- **Главное:** «**что должно произойти**»

**На практике часто комбинируют:**

```
Terraform поднимает голую инфру:
└── VPC + EC2 instance + RDS

         ↓ (после apply)

Ansible настраивает софт на VM:
├── apt install nginx php-fpm
├── копирует конфиги
├── создаёт пользователей
└── запускает сервисы
```

**Современные тренды:**

- **С приходом Kubernetes и immutable-образов потребность в Ansible уменьшилась**:
  - Софт уже в Docker image
  - Конфиг — через ConfigMap
  - Деплой — через Helm / Argo
  - VM становятся «cattle» — пересоздаются, не патчатся
- **`OpenTofu`** — fork Terraform после смены лицензии HashiCorp
- **`Pulumi`** — альтернатива Terraform на TypeScript/Python/Go (полноценный язык вместо HCL)
- **`crossplane`** — provisioning через k8s CRD (terraform-as-controller)

**Когда что использовать сегодня:**

- **Terraform** — для **облачных ресурсов** и `kubernetes cluster`
- **Ansible** — для **legacy on-premise**, patch management, one-off задач
- **`Helm` / `Argo CD`** — для приложений в k8s (вместо Ansible deploy)',
                'difficulty' => 4,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое GitOps и чем он отличается от классического CI/CD push-деплоя?',
                'answer' => 'Два **разных направления потока** деплоя в k8s.

| | **Classic push-CD** | **`GitOps`** (pull) |
|---|---|---|
| **Кто инициирует** | CI runner | **агент в кластере** |
| **Кому даны creds на k8s** | CI системе | **никому извне** |
| **Источник правды** | артефакты + кнопка Deploy | **git-репо** с манифестами |
| **Rollback** | новый pipeline run | **`git revert`** |
| **Drift detection** | вручную | **из коробки** (sync loop) |
| **Аудит деплоев** | CI логи | **`git log`** |
| **Изменения в кластере вручную** | приживутся до следующего деплоя | **откатятся** агентом |

**Classic push-CD:**

```
git push → CI tests → CI build image → CI: kubectl apply → kuber
                                            ↑
                                  CI runner имеет creds на прод
```

**Проблемы:**

- **У CI-раннера должны быть creds** на прод-кластер — это **широкий attack surface**
- **Drift** — кто-то сделал `kubectl edit` вручную → состояние расходится с git
- **Откат** — отдельный pipeline run, нужно помнить старый тег

**`GitOps` (pull-модель):**

```
git push → CI tests → CI build image → CI обновляет тег в manifest-repo
                                              ↓
                                         git commit
                                              ↓
ArgoCD/Flux в кластере: периодический sync с manifest-repo
                                              ↓
                                         kubectl apply
```

**Агент** (`ArgoCD`, `Flux`) **внутри кластера** **сам** периодически сверяется с git и приводит кластер к **описанному состоянию**.

**Преимущества `GitOps`:**

- **История деплоев = `git log`** с авторами и PR
- **Rollback = `git revert`** — один коммит, всё прозрачно
- **Кластер не пускает CI внутрь периметра** — улучшение security
- **Drift detection** бесплатно — агент видит расхождение
- **Reconciliation** — ручные правки автоматически откатываются
- **Disaster recovery** — кластер восстанавливается из git
- **Multi-cluster** — один git-репо → много кластеров

**Минусы:**

- **Двухрепная схема** — app-repo (код) + **manifest-repo** (yaml)
- **Кривая обучения** — новые инструменты, паттерны
- **Задержка деплоя** — sync loop (но обычно ≤ 1 мин)
- **Image promotion** между окружениями требует автоматизации

**Инструменты:**

- **`ArgoCD`** — UI, application-of-applications, PreSync hooks (популярнее)
- **`Flux`** — CRD-first, без UI, тесная интеграция с Helm/Kustomize
- **`Jenkins X`**, **`Werf`** — менее популярные

**Архитектурный паттерн** — **app-of-apps**: один root-application в ArgoCD ссылается на N приложений, каждое из своей папки manifest-repo. Изменения в app деплоятся через PR в manifest-repo.',
                'difficulty' => 4,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Почему PHP в production обычно ставят за nginx + php-fpm, а не запускают встроенный сервер?',
                'answer' => '**Встроенный `php -S` — однопоточный**, не поддерживает SSL / HTTP/2, **не отдаёт статику параллельно с PHP** и официально **предназначен только для разработки**.

**Production-связка: `nginx` (reverse proxy) + `php-fpm` (пул PHP-процессов)**, общающихся по **FastCGI** через unix-socket или TCP.

**Разделение труда:**

**`nginx` (легковесный, event-driven) делает:**

- Отдаёт **статику** (`*.css`, `*.js`, images) напрямую — никакого PHP
- **gzip / brotli** компрессия
- **TLS termination** (HTTPS, HTTP/2, HTTP/3)
- **Ограничение размера запроса** (`client_max_body_size`)
- **Keep-alive с клиентом**
- **Rate limiting**, basic auth, кэширование
- Проксирует **только PHP-запросы** в `php-fpm` через `location ~ \\.php$`

**`php-fpm` управляет пулом воркеров:**

- `pm = static / dynamic / ondemand` — стратегия пула
- **`pm.max_requests`** — рестарт воркера после N запросов (борьба с утечками памяти)
- `request_terminate_timeout` — убить зависшие запросы
- Изоляция между запросами (`reset_opcache`)

**Альтернативы:**

- **Octane / Swoole / RoadRunner / FrankenPHP** — long-lived процессы PHP, держат фреймворк в памяти между запросами (быстрее, но требует stateless-кода)
- **Apache + mod_php** — старый stack, проще настроить, но nginx + fpm чаще быстрее и легче в k8s
- **Caddy + FrankenPHP** — современная альтернатива, автоматический HTTPS',
                'code_example' => '# nginx сайт-конфиг для Laravel
server {
    listen 80;
    server_name example.com;
    root /var/www/public;
    index index.php;

    # Статика — мимо PHP
    location ~* \\.(css|js|jpg|png|svg|woff2)$ {
        expires 30d;
        access_log off;
    }

    # SPA-fallback
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP — в php-fpm
    location ~ \\.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_read_timeout 60s;
    }

    client_max_body_size 20M;
}',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что настраивают в php-fpm pool: pm static vs dynamic vs ondemand?',
                'answer' => 'Три **стратегии управления пулом** воркеров `php-fpm` — выбор влияет на память, latency и cold-start.

| Стратегия | Кол-во воркеров | Память | Cold-start под пиком | Когда брать |
|---|---|---|---|---|
| **`static`** | **фиксировано** `pm.max_children` | **константа** | **нет** (всегда готовы) | **нагруженный прод** |
| **`dynamic`** | колеблется между min/max spare | **средняя** | **есть** при резких всплесках | средняя нагрузка |
| **`ondemand`** | форкается **под запрос** | **минимум** | **есть на каждом** запросе | shared-хостинг, dev |

**`pm = static`** — фиксированный пул:

- **Держит ровно `pm.max_children` воркеров** всё время
- **Предсказуемое потребление памяти** — `max_children × memory_per_worker`
- **Лучшая latency под пиком** — нет cold-start, всё готово
- **Минус:** «платит память» даже простаивая
- **Выбор для нагруженного прода**, где трафик стабильный

**`pm = dynamic`** — адаптивный:

- Стартует `pm.start_servers` воркеров
- **Держит** между `pm.min_spare_servers` и `pm.max_spare_servers`
- Под нагрузкой форкает до `pm.max_children`
- **Плюс:** экономит память на простаивающем сервере
- **Минус:** при резком всплеске **часть запросов ждёт форка** (cold-start `~50-100ms`)

**`pm = ondemand`** — лениво:

- **Форкает воркер только под запрос** и убивает его
- **Минимум памяти** в покое
- **Максимум cold-start** — каждый запрос ждёт форка
- **Годится для shared-хостинга** или dev-окружений

**Дополнительные параметры (важны для прода):**

| Параметр | Зачем |
|---|---|
| **`pm.max_requests`** | **рестарт воркера** после N запросов — борьба с утечками памяти |
| **`request_terminate_timeout`** | **kill зависших** запросов (`30s`) |
| **`pm.process_idle_timeout`** | в `dynamic` — сколько ждать перед убийством idle-воркера |
| **`emergency_restart_threshold`** | при N сегфолтах за `emergency_restart_interval` — рестарт пула |
| **`slowlog`** + **`request_slowlog_timeout`** | лог медленных запросов |

**Расчёт `pm.max_children` для пода/сервера:**

```
pm.max_children = доступная_память_pod / память_на_воркер

Пример: 4 ГБ контейнер, ~80 МБ на воркер
pm.max_children = 4096 / 80 ≈ 50
```

**Подвох:** `pm.max_children` × `memory_per_worker` **не должно превышать** memory limit pod-а, иначе OOM.

**Best practice для k8s:** **`pm = static`** + **горизонтальный автоскейлинг** через HPA по CPU/RPS — один pod = фиксированный пул, масштабируется добавлением pod-ов.',
                'difficulty' => 4,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Какие настройки OPcache критичны для production-PHP?',
                'answer' => '**`OPcache`** хранит скомпилированный bytecode PHP в shared memory — без него **каждый запрос перекомпилирует** все включённые файлы. Это **обязательная** оптимизация для production.

**Критичные настройки:**

| Параметр | Значение | Зачем |
|---|---|---|
| **`opcache.enable`** | `1` | без этого OPcache **выключен** |
| **`opcache.memory_consumption`** | `128-256 МБ` | размер кэша; меньше → вытеснение |
| **`opcache.max_accelerated_files`** | `10000-50000` | должно быть **больше** реального числа `.php` в проекте |
| **`opcache.validate_timestamps`** | `0` на проде | максимум скорости, но требует reset при деплое |
| **`opcache.interned_strings_buffer`** | `16-32 МБ` | для общих строк (имена классов, методов) |

**Подвохи:**

- **`max_accelerated_files` слишком мало** → часть файлов **постоянно перекомпилируется**, OPcache мигает
- **`memory_consumption` мало** → cache **вытесняется** под нагрузкой, hit rate падает
- **Мониторинг:** `opcache_get_status()` → `cache_full`, `oom_restarts`, `hash_restarts`, `hit_rate`

**`opcache.validate_timestamps=0`** — главный production-tuning:

- **`=1`** — на **каждый запрос** делает `stat()` по файлам с шагом `revalidate_freq` → лишний syscall, заметно режет p99
- **`=0`** — **максимум скорости**, но **правка файла на диске не подхватывается**
- При деплое нужен **`opcache_reset()`** или **рестарт `php-fpm`**

**`opcache.preload`** (PHP 7.4+):

- Загружает **классы фреймворка при старте** php-fpm — убирает их компиляцию из горячего пути
- Сохраняется в SHM **до рестарта**
- Конфиг: `opcache.preload=/var/www/preload.php`
- **Не работает** для классов, использующих attributes-runtime или динамические includes

**`JIT`** (PHP 8.0+):

- **`opcache.jit_buffer_size=128M`** — без этого JIT выключен
- **`opcache.jit=1255`** или **`tracing`** — режим компиляции
- **Эффект:**
  - **CPU-bound** код (математика, парсинг) — **значительное** ускорение
  - **Типичный I/O-bound веб** (БД, Redis) — **скромный** эффект 5-10%
- Сложнее в эксплуатации — могут быть редкие баги в редких сценариях

**Боевой пресет для прода:**

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=32
opcache.max_accelerated_files=50000
opcache.validate_timestamps=0
opcache.save_comments=1          ; нужно для аттрибутов и аннотаций
opcache.enable_file_override=0
opcache.preload=/var/www/preload.php
opcache.preload_user=www-data
opcache.jit_buffer_size=128M
opcache.jit=tracing
```

**Деплой при `validate_timestamps=0`:** см. отдельную карточку про **симлинк-стратегию** и `opcache_reset` через FastCGI.',
                'difficulty' => 4,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Почему в production нельзя оставлять opcache.validate_timestamps=1 и как тогда катить релиз?',
                'answer' => '**`opcache.validate_timestamps=1`** — режим **«проверять файлы на изменения»**:

- На каждый запрос (с шагом **`revalidate_freq`** секунд) делает **`stat()`** по файлу
- Сверяет **`mtime`** с тем, что в кэше
- Если изменился → **рекомпилирует**

**Цена:** **лишний syscall на каждый `include`**:

- В Laravel приложении — **сотни include-ов** на запрос
- Заметно **режет p99** на больших фреймворках (5-15%)
- На SSD меньше, на сетевом storage (NFS) — катастрофа

**На production ставят `=0`** → правка файла **не подхватывается**, пока не сбросить кэш.

**Безопасный деплой при `validate_timestamps=0`:**

**Симлинк-стратегия** (классический способ):

```bash
# Структура
/var/www/
├── releases/
│   ├── 2024-05-22-1430/    # старый
│   └── 2024-05-22-1500/    # новый
└── current → releases/2024-05-22-1500   # симлинк

# Деплой:
# 1. Распаковываем новый код в releases/2024-05-22-1500
# 2. Прогреваем (composer dump-autoload, php artisan optimize)
# 3. Атомарно переключаем симлинк:
ln -sfn /var/www/releases/2024-05-22-1500 /var/www/current.new
mv -Tf /var/www/current.new /var/www/current

# 4. Сбрасываем OPcache:
systemctl reload php-fpm
# или: php artisan opcache:clear (через FastCGI)
# или: cachetool opcache:reset --fcgi=127.0.0.1:9000
```

**Подводные камни:**

- **`realpath_cache`** PHP — после переключения симлинка кэш путей **остаётся** старым → нужен `realpath_cache_clear()` или рестарт fpm
- **Длинно живущие воркеры** (Octane, RoadRunner) — у них **свой** OPcache в каждом процессе, нужен **graceful reload**
- **`opcache_reset`** через HTTP-endpoint опасен — должен быть **только** с localhost / админ-токеном

**В Kubernetes — проще:**

- **Rolling restart pod-ов** = у каждого нового pod **пустой OPcache**, наполняется свежим кодом
- **Образ Docker = immutable** — нет понятия «правка файла»
- Можно держать **`validate_timestamps=0`** без оглядки

**Альтернативы:**

| Подход | Когда |
|---|---|
| **k8s rolling restart** | контейнерный деплой |
| **Симлинк + opcache_reset** | bare-metal / VM |
| **`cachetool` через FastCGI** | если нет SSH |
| **Octane/RoadRunner graceful reload** | long-lived воркеры |
| **`validate_timestamps=1` с `revalidate_freq=60`** | компромисс для нечастых деплоев |',
                'difficulty' => 4,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое self-hosted runner в GitHub Actions / GitLab CI и когда он нужен?',
                'answer' => 'Два **варианта runner-а** для CI-пайплайнов — у каждого свои сильные стороны.

| | **Shared runner** (provider) | **Self-hosted runner** |
|---|---|---|
| **Кто хостит** | GitHub / GitLab | **вы сами** |
| **Цена** | **платно** по минутам | hardware + ops |
| **Доступ в private VPN** | нет | **да** |
| **Тёплый кэш** | **нет** (cold start) | **да** (persistent) |
| **Скорость** | средняя | **зависит от железа** |
| **Security** | изолированно provider-ом | **ваша ответственность** |
| **Custom hardware** | нет | **GPU, ARM, большая RAM** |

**По умолчанию** пайплайны исполняются на **shared-runner** провайдера:

- Удобно — ничего настраивать не надо
- **Платно по минутам** — после free tier $0.008-0.08/мин
- **Без доступа** во внутреннюю сеть компании
- **Cold кэш** на каждом запуске — composer/npm качают по сети

**`Self-hosted runner`** — собственная VM или pod в кластере:

- Регистрируется как **worker** через токен от GitHub/GitLab
- Задачи **едут на ваш runner** (по label или fallback)

**Когда нужен self-hosted:**

- **Доступ к private DB / k8s** через VPN — интеграционные тесты против реальной staging-БД
- **Тёплый кэш** `composer` / `node_modules` между запусками — сборка за **секунды**, а не минуты
- **Тяжёлые билды** дешевле гонять у себя — Docker images, ML-датасеты
- **Кастомное железо** — **GPU** для ML, **ARM** для multi-arch images
- **Compliance** — данные не должны покидать корпоративный периметр

**Минусы:**

- **Сами следите** за обновлениями runner-агента
- **Изоляция задач** — один билд не должен влиять на другой (Docker-in-Docker, ephemeral runners)
- **Безопасность runner-токена** — компрометация = доступ к secrets и репозиториям
- **Hardware и cost** — VM/железо круглосуточно
- **Очистка между запусками** — мусор от прошлых билдов накапливается

**Современные варианты:**

- **`ephemeral runners`** (GitHub Actions Runner Controller, ARC) — pod в k8s **только на время задачи**, потом удаляется
- **`Spot/preemptible` инстансы** — дёшево, перезапуск при выселении
- **`act_runner`** + **`Gitea`** — self-hosted CI «всё своё»
- **`docker-in-docker`** vs **`kubernetes-executor`** в GitLab — разные подходы к изоляции

**Best practice:**

- Использовать **ARC (Actions Runner Controller)** в k8s — ephemeral, изолированные
- **Не давать** self-hosted runner-у secrets от прода — только staging
- **Защита** через `allowed_actions` (whitelist использованных actions)',
                'difficulty' => 4,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Зачем в CI отдельно кешировать composer и npm зависимости?',
                'answer' => '**Чистый `composer install`** на холодном раннере тянет сотни пакетов из packagist и собирает autoload — это **десятки секунд на каждый pipeline**. При 50 PR в день — это часы машинного времени и деньги за CI-минуты.

**Решение** — CI-системы (**GitHub Actions cache**, **GitLab cache**, **CircleCI**) умеют **сохранять директорию между запусками**, ключуя её хешем lock-файла:

- `vendor/` или `~/.composer/cache` — по `composer.lock`
- `node_modules` или `~/.npm` — по `package-lock.json`

**Что происходит:**

- **Lock не изменился** → cache hit → зависимости разворачиваются **за секунды**
- **Lock изменился** → cache miss → пересобираем и **сохраняем новый кеш** под новым ключом

**Главное правило — ключ кеша должен включать lock-файл:**

- Иначе **ничего не обновится** при правке `composer.json`
- Или **кеш будет неконсистентен** (старый vendor с новым lock)

**Дополнительные приёмы:**

- **`restore-keys`** — fallback на «похожий» кеш (тот же `composer.json`, обновился только lock)
- **OS / PHP version в ключе** — Linux/macOS vendor различны, PHP 8.2 / 8.3 — тоже
- **Кешировать `~/.composer/cache`** удобнее `vendor/` — устойчиво к разным версиям PHP
- **Docker layer cache** через **buildx + GHA cache** ускоряет образа',
                'code_example' => '# GitHub Actions
- name: Cache composer
  uses: actions/cache@v4
  with:
    path: ~/.composer/cache
    key: composer-${{ runner.os }}-${{ hashFiles(\'composer.lock\') }}
    restore-keys: |
      composer-${{ runner.os }}-

- name: Install
  run: composer install --no-progress --prefer-dist

- name: Cache npm
  uses: actions/cache@v4
  with:
    path: ~/.npm
    key: npm-${{ hashFiles(\'package-lock.json\') }}

- run: npm ci

# Docker buildx с GHA cache
- uses: docker/build-push-action@v5
  with:
    cache-from: type=gha
    cache-to: type=gha,mode=max

# GitLab CI
# cache:
#   key:
#     files:
#       - composer.lock
#   paths:
#     - vendor/',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Чем canary отличается от blue-green и когда выбирать какой?',
                'answer' => 'Две **стратегии безопасного деплоя**, оптимизированные под разные сценарии.

| | **`blue-green`** | **`canary`** |
|---|---|---|
| **Перевод трафика** | **100% разом** | **постепенно** (1% → 5% → 25% → 100%) |
| **Время выкатки** | **минуты** | **часы-дни** |
| **Ресурсы** | **× 2** на время | **+ 1-N pod** |
| **Rollback** | **мгновенный** | **частичный** автоматический |
| **Ловит проблемы под нагрузкой** | нет (100% сразу) | **да** (постепенно) |
| **Требует мониторинга** | смок-тесты | **продвинутый** (error rate, p95, KPI) |
| **Сложность инфры** | низкая | высокая (`Istio`/`Argo Rollouts`) |

**`blue-green`** — мгновенное переключение:

1. Поднимаем **рядом со старой** версией (`blue`) **полный второй стек** новой (`green`)
2. Прогоняем **smoke-тесты** на `green` (на ней пока **нет трафика**)
3. **Переключаем 100% трафика** разом (LB / DNS / k8s Service selector)
4. `blue` остаётся стоять — **резерв на rollback**
5. Если что — **переключаем назад** одним движением

**Плюсы:**

- **Instant rollback** — секунды
- **Нет downtime**
- Smoke-тесты до приёма трафика
- Простая mental model

**Минусы:**

- **× 2 ресурсов** на время деплоя
- **Не ловит проблемы под реальной нагрузкой** — все или никто
- Sessions, in-memory cache **теряются** при переключении
- Миграции БД сложнее (паттерн **Expand-and-Contract**)

**`canary`** — постепенное:

1. Деплоим новую версию рядом со старой (`1 pod vs 99`)
2. Маршрутизатор отправляет **1% трафика** в canary
3. **Сравниваем метрики** с baseline:
   - `error rate`
   - `p95 / p99 latency`
   - **Бизнес-KPI** (заказы / мин)
4. Если метрики ровные → **увеличиваем долю** (5% → 25% → 50% → 100%)
5. Если ухудшение → **автоматический rollback**, трафик возвращается в старую

**Плюсы:**

- **Маленький blast radius** — затронут N% пользователей
- **Ловит проблемы, видимые только под реальной нагрузкой**
- Можно совмещать с **A/B тестами**
- Меньше ресурсов, чем blue-green

**Минусы:**

- **Сложная инфраструктура маршрутизации** — `Istio`, `Linkerd`, **`Argo Rollouts`**, **`Flagger`**
- **Хороший observability обязателен** — без метрик canary бесполезен
- **Sticky sessions** требуют внимания
- **Долго** — часы вместо минут

**Когда что выбирать:**

| Сценарий | Стратегия |
|---|---|
| **Короткий релиз**, низкий риск | **`blue-green`** |
| **Stateful** компонент, фоновая миграция БД | `blue-green` |
| **Рискованное** изменение (новый алгоритм рекомендаций) | **`canary`** |
| **Большая система**, много пользователей | `canary` |
| **A/B тест** новой фичи | `canary` (по сути то же) |
| **Бэк без рискованных изменений** | **rolling** (k8s default) |

**Современный паттерн:** **`Argo Rollouts` / `Flagger`** + **Istio/Linkerd** — автоматический canary с авто-rollback по метрикам Prometheus.',
                'difficulty' => 4,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое immutable infrastructure и почему она безопаснее mutable?',
                'answer' => 'Два **подхода к управлению серверами** — mutable («живой» сервер) vs immutable («одноразовый» сервер).

| | **Mutable** | **Immutable** |
|---|---|---|
| **Жизнь сервера** | долгая, **патчится** | короткая, **пересоздаётся** |
| **Обновления** | через `ssh`/`ansible` | **новый образ** + замена |
| **Drift** | **накапливается** | **невозможен** |
| **Rollback** | сложный | **тривиальный** — старый образ |
| **Аудит** | логи ansible, history | **git** + CI |
| **Snowflake-серверы** | часто | **исключены** |

**`Mutable infrastructure`** — классика:

- **Сервер живёт долго** (месяцы/годы)
- На него раскатываются обновления через **`ssh`/`ansible`**
- Ставятся пакеты, правятся конфиги, ротируются ключи
- **Проблема — `configuration drift`**:
  - Два «одинаковых» сервера ведут себя **по-разному**
  - Воспроизвести проблему **сложно** — на проде есть, на стейдже нет
  - **Snowflake servers** — каждый уникален, никто не знает, что там накручено
  - Аудит изменений — только в логах ansible, легко потерять историю

**`Immutable infrastructure`** — современный подход:

- **Любое изменение = пересборка** нового образа (`Docker image`, AMI)
- **Замена** старых инстансов на новые
- **Правок на живой машине НЕ делают** (даже `ssh` обычно отключён)
- Откат = **прежний образ** обратно

**Что даёт:**

- **Идентичные окружения** — dev / stage / prod из одного образа
- **Тривиальный rollback** — `kubectl rollout undo` или прежний AMI
- **Отсутствие snowflake-серверов** — все идентичны
- **Аудит через git/CI** — Dockerfile/Packer template + commit-история
- **Disaster recovery** — после потери региона **поднимаем из образов**
- **Воспроизводимость** — баг на проде воспроизводится локально из того же образа

**Цена:**

- **Обязательная автоматизация сборки** — без CI не получится
- **Центральное хранилище образов** — `registry` (`Docker Hub`, ECR, GCR, Harbor) с retention
- **Stateful данные — отдельно** — БД, файлы пользователей **не** в образе; в `volumes` / `PVC` / managed services
- **Размер образа** — нужно держать под контролем (`multi-stage build`, alpine)
- **Дольше деплой** — пересборка vs `apt-get upgrade`

**Где встречается:**

- **Kubernetes** — pods inherently immutable (нужен новый image для изменений)
- **Auto Scaling Groups** в облаках — AMI как источник правды
- **Packer** + **Terraform** — Packer строит AMI, Terraform запускает
- **Serverless** — Lambda/Cloud Functions inherently immutable

**Anti-pattern:** **`kubectl exec` → `apt install`** на проде → теряется immutability, нужно **запретить** на production-кластерах через RBAC.',
                'difficulty' => 4,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Зачем PHP-приложению контейнеры запускать не от root, и что для этого нужно сделать?',
                'answer' => '**По умолчанию контейнер запускается от `UID 0` (root)** — это **слабая security posture**, которую обязательно лечат на production.

**Чем плох root внутри контейнера:**

- **Container escape** — если злоумышленник вырвался из процесса (через **RCE** в приложении) и нашёл уязвимость в **runc/containerd** или **ядре** — получает **root на хосте**
- **Повреждение volumes** — root внутри легко удалит/перепишет примонтированные тома
- **Capabilities** — у root по умолчанию `CAP_NET_RAW`, `CAP_NET_BIND_SERVICE` и др.
- **Соответствие** PCI-DSS / CIS Kubernetes Benchmark — требуется non-root

**Что нужно сделать:**

**1. В Dockerfile** — создать пользователя:

```dockerfile
FROM php:8.3-fpm-alpine

RUN addgroup -g 1000 app && \\
    adduser -u 1000 -G app -D app && \\
    chown -R app:app /var/www

USER app
WORKDIR /var/www
EXPOSE 9000
CMD ["php-fpm"]
```

**2. В Kubernetes** — `securityContext`:

```yaml
spec:
  securityContext:
    runAsNonRoot: true
    runAsUser: 1000
    runAsGroup: 1000
    fsGroup: 1000
  containers:
  - name: app
    securityContext:
      allowPrivilegeEscalation: false
      readOnlyRootFilesystem: true
      capabilities:
        drop: ["ALL"]
```

**Сложности и подводные камни:**

| Проблема | Решение |
|---|---|
| **Порты ниже 1024** | non-root **не может** слушать; fpm слушает `9000`, TLS терминируется на ingress |
| **Права на сокет** `/var/run/php-fpm.sock` | владелец = UID контейнера или общая группа |
| **Логи** | директория должна быть writable для UID; используйте `stderr` (12-factor) |
| **`readOnlyRootFilesystem` + Laravel** | `storage/`, `bootstrap/cache/` нужны writable → **`emptyDir`** volumes |
| **Composer artisan на старте** | должны работать от того же UID, что и runtime |

**Пример Laravel-pod с `readOnlyRootFilesystem`:**

```yaml
volumeMounts:
- name: storage
  mountPath: /var/www/storage
- name: bootstrap-cache
  mountPath: /var/www/bootstrap/cache
- name: tmp
  mountPath: /tmp
volumes:
- name: storage
  emptyDir: {}
- name: bootstrap-cache
  emptyDir: {}
- name: tmp
  emptyDir: {}
```

**Без этих volumes** приложение упадёт при попытке записать **compiled view**, **route cache**, **session file**.

**Дополнительные меры безопасности:**

- **`securityContext.seccompProfile: RuntimeDefault`** — стандартный seccomp-фильтр
- **`PodSecurityStandard: restricted`** — namespace-level policy
- **`drop ALL capabilities`** — оставить только нужные
- **`AppArmor`/`SELinux`** — MAC поверх DAC',
                'difficulty' => 4,
                'topic' => 'system_design.devops',
            ],
        ];
    }
}
