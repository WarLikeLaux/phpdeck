<?php

namespace Database\Seeders\Data\Categories\SystemDesign;

class Devops
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example: ?string, code_language: ?string, difficulty: int, topic: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Docker простыми словами?',
                'answer' => 'Docker - технология контейнеризации. Контейнер - это упакованное приложение со всеми зависимостями, которое работает одинаково везде (локально, на стейдже, на проде). Простыми словами: коробка с приложением и всем что ему нужно для жизни. В отличие от VM не виртуализирует ОС - использует ядро хоста, поэтому быстрый и лёгкий. Образ (image) - шаблон, контейнер - запущенный экземпляр. Dockerfile описывает как собрать образ.',
                'code_example' => 'FROM php:8.3-fpm
WORKDIR /var/www
COPY . .
RUN composer install --no-dev --optimize-autoloader
EXPOSE 9000
CMD ["php-fpm"]',
                'code_language' => 'bash',
                'difficulty' => 2,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Чем Docker отличается от виртуальной машины (VM)?',
                'answer' => 'VM виртуализирует железо - запускает полную гостевую ОС со своим ядром. Docker контейнер использует ядро хоста и изолирован через namespaces/cgroups. Простыми словами: VM - целая отдельная квартира, Docker - комната в квартире хозяина. VM весит гигабайты, стартует минуты. Контейнер весит мегабайты, стартует секунды. VM безопаснее изолирован, контейнер быстрее и легче. Часто используют вместе: VM с Docker внутри.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 3,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Kubernetes простыми словами?',
                'answer' => 'Kubernetes (k8s) - оркестратор контейнеров. Простыми словами: дирижёр оркестра - управляет десятками/тысячами Docker-контейнеров, решает где их запускать, перезапускает упавшие, балансирует нагрузку, масштабирует под нагрузку. Сам Docker не умеет это, k8s сверху. Основные сущности: Pod (группа контейнеров), Deployment (как развернуть), Service (стабильный адрес для подов), Ingress (маршрутизация HTTP).',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 3,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Pod, Deployment, Service в Kubernetes?',
                'answer' => 'Pod - наименьшая единица k8s, обычно один контейнер (иногда несколько связанных, например app+sidecar). Эфемерный: упал - создаётся новый. Deployment - декларация "хочу N реплик такого пода с такой стратегией обновления". Сам управляет ReplicaSet и подами. Service - стабильная точка входа к набору подов через label selector. У подов меняются IP, у service постоянный. Типы: ClusterIP (внутри), NodePort, LoadBalancer (внешний).',
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
                'answer' => 'Blue-green - стратегия деплоя без простоя. Имеешь две идентичные среды: blue (текущая прод) и green (новая версия). Деплоишь в green, прогоняешь тесты, переключаешь трафик с blue на green одной командой (через load balancer или DNS). Если проблема - моментально переключаешь обратно. Плюсы: instant rollback, нет downtime. Минусы: двойные ресурсы, миграции БД сложнее (схема должна работать с обеими версиями).',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 3,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое canary deployment?',
                'answer' => 'Canary deploy - постепенный rollout: новая версия сначала получает 1% трафика, потом 5%, 25%, 100%. Простыми словами: канарейка в шахте - если что-то не так, потеряем малость. Метрики (ошибки, latency) на каждом этапе сравниваются с baseline - если ухудшение, автоматический rollback. Плюсы: маленький blast radius при ошибках. Минусы: нужна инфраструктура для маршрутизации (Istio, Linkerd, Argo Rollouts) и хороший observability.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 4,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое rolling deployment?',
                'answer' => 'Rolling deploy - обновление подов по одному: убрали один старый, подняли один новый, проверили health, повторили. По умолчанию в Kubernetes Deployment. Простыми словами: меняем колёса на машине по одному, не останавливая её. Параметры: maxUnavailable (сколько может быть offline), maxSurge (сколько лишних можно поднять). Минус: во время деплоя одновременно работают обе версии - нужна обратная совместимость БД и API.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 3,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое feature flags и зачем нужны?',
                'answer' => 'Feature flags (toggles) - условные блоки в коде, включающие/выключающие фичи без редеплоя. Простыми словами: рубильник на новую фичу - можно включить только для тестовых юзеров, потом 10%, потом всем. Плюсы: trunk-based development, A/B тесты, kill switch при проблеме, разделение деплоя и релиза. Минусы: код засоряется if-ами, надо чистить старые флаги. Инструменты: LaunchDarkly, Unleash, GrowthBook, или своя БД-таблица.',
                'code_example' => '<?php
if (Feature::active("new-checkout", $user)) {
    return view("checkout.v2");
}
return view("checkout.v1");',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое 12-factor app?',
                'answer' => '12-factor - методология построения SaaS-приложений. Ключевые: 1) Codebase в git, 2) Dependencies явные, 3) Config в env, 4) Backing services как ресурсы, 5) Build/release/run разделены, 6) Stateless процессы, 7) Port binding, 8) Concurrency через процессы, 9) Disposability (быстрый старт/остановка), 10) Dev/prod parity, 11) Logs как stream stdout, 12) Admin tasks как one-off процессы. Идеология современного облачного приложения.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 3,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое CI/CD простыми словами?',
                'answer' => 'CI (Continuous Integration) - каждый коммит автоматически собирается и проходит тесты. Простыми словами: что бы ты ни запушил - сразу проверка качества. CD (Continuous Delivery/Deployment) - после успешного CI код автоматически деплоится в стейдж/прод. Delivery - готов к деплою (нажми кнопку), Deployment - деплоится сам. Зачем: ловим баги рано, релизы маленькие и частые. Инструменты: GitHub Actions, GitLab CI, Jenkins, CircleCI, Drone.',
                'code_example' => null,
                'code_language' => null,
                'difficulty' => 2,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Infrastructure as Code (IaC)?',
                'answer' => 'IaC - описание инфраструктуры в коде вместо ручной настройки в UI. Простыми словами: вместо кликов в AWS-консоли пишешь файл, который их сделает за тебя - и его можно ревьюить, версионировать, переиспользовать. Декларативный (Terraform, CloudFormation) - описываешь желаемое состояние. Императивный (Ansible, Chef) - последовательность шагов. Плюсы: воспроизводимость, history через git, code review для инфраструктуры.',
                'code_example' => 'resource "aws_instance" "api" {
  ami           = "ami-0c55b159"
  instance_type = "t3.medium"
  tags = { Name = "api-server" }
}',
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как обеспечить Graceful Shutdown для PHP-воркеров и Kubernetes-подов?',
                'answer' => 'Graceful shutdown - корректное завершение процесса при получении сигнала остановки: дождаться завершения текущей работы, не принимать новую, освободить ресурсы. Без него при деплое теряются in-flight Job-ы, обрываются HTTP-запросы, остаётся "висящий" state в БД. Механика в Linux: процесс получает SIGTERM (15) - нужно успеть завершиться за grace period; если не успел, через timeout приходит SIGKILL (9), который не перехватывается. Kubernetes по умолчанию даёт terminationGracePeriodSeconds=30 после SIGTERM, потом SIGKILL. Что делать в PHP: 1) В CLI-воркере - pcntl_async_signals(true) + pcntl_signal(SIGTERM, ...) + установить флаг "shouldStop", который проверяется в основном цикле между задачами. 2) Для очередей - Laravel queue:work уже умеет сам ловить SIGTERM/SIGINT и завершается после текущего Job; нужно только настроить правильный --timeout и terminationGracePeriodSeconds > timeout. 3) Для HTTP - php-fpm graceful через kill -USR1/USR2 (master форкает новых воркеров, старые дорабатывают текущие запросы). В Kubernetes: 4) preStop hook на pod (sleep 10) - даёт время сервис-меш / load balancer убрать pod из endpoints до начала остановки, чтобы новые запросы не шли. 5) Readiness probe возвращает unready при получении SIGTERM. 6) terminationGracePeriodSeconds = max время вашей задачи + buffer. Для Octane/Swoole/RoadRunner - встроенная поддержка graceful reload. Подводный камень: в Kubernetes SIGTERM приходит ДО того, как pod удалён из endpoints - всегда нужен preStop sleep либо корректная readiness-проверка.',
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
                'difficulty' => 4,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как сделать миграцию БД (rename column, drop column) без downtime в blue-green деплое?',
                'answer' => 'Прямая миграция RENAME/DROP/изменение типа колонки во время blue-green или rolling-деплоя ломает приложение, потому что в момент миграции одновременно работают ДВЕ версии кода: старая (работающие воркеры/инстансы, ещё не перекатились) и новая. Если старый код ждёт колонку email, а вы её только что удалили - старые поды падают. Решение - паттерн Expand and Contract (Parallel Change), 5 шагов. 1) EXPAND. Создаём НОВУЮ колонку (добавление - всегда безопасная операция в современных БД, кроме случаев с DEFAULT в PG старее 11 - там переписывается вся таблица). Старая колонка живая, новая пустая или с дефолтом. Деплоим миграцию, прод не трогаем. 2) DUAL WRITE. Деплоим код, который пишет В ОБЕ колонки (старую и новую) при каждом UPDATE/INSERT. Читает пока из старой - чтобы старые поды и новые видели одинаковые данные во время rolling rollout. 3) BACKFILL. Запускаем миграцию данных: копируем существующие записи из старой колонки в новую (через chunkById, чтобы не залочить таблицу). После backfill: новая колонка имеет полные актуальные данные. 4) SWITCH READS. Деплоим код, который ЧИТАЕТ из новой колонки, но всё ещё пишет в обе. Если что-то сломалось - откатываемся, старая колонка цела. 5) STOP DUAL WRITE + DROP. Деплоим код, который пишет и читает только из новой. Когда уверены, что нигде не используется старая - отдельным релизом DROP COLUMN (миграция). Применимо ко всем "разрушительным" изменениям: rename column, change type, разделение таблицы, объединение, удаление таблицы. Каждый шаг - отдельный деплой, между ними проходят часы или дни (особенно перед DROP, чтобы убедиться, что ничего не использует старое). В Laravel: пишите миграции в обоих направлениях (up/down), не объединяйте expand и contract в одном файле миграций. PostgreSQL: для больших таблиц следить за блокировками - ALTER TABLE без DEFAULT обычно мгновенен (в 11+), CREATE INDEX CONCURRENTLY (не блокирует записи), DROP COLUMN мгновенен (но физически место освободится только после VACUUM FULL).',
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
                'difficulty' => 4,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'В чём разница между ARG и ENV в Dockerfile?',
                'answer' => 'ARG объявляет build-time переменную: она доступна только во время docker build, в готовом образе и runtime её нет. Передаётся через --build-arg и часто используется для версии базового образа или флагов сборки. ENV задаёт переменную окружения, которая попадает в layer образа и видна процессу внутри контейнера через getenv. Главный нюанс безопасности: секреты не стоит передавать через ARG — они остаются в истории слоёв (docker history покажет), и через ENV — они утекут в логи и docker inspect; для секретов есть BuildKit secrets и runtime-инъекция.',
                'difficulty' => 3,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Docker volumes и чем named volume отличается от bind mount?',
                'answer' => 'Volume — механизм, при котором данные хранятся вне union-файловой системы контейнера и переживают его удаление. Named volume управляется Docker (лежит в /var/lib/docker/volumes), переносим между хостами и нужен для production-данных БД и загруженных пользователями файлов. Bind mount монтирует конкретную директорию хоста в контейнер — удобно в разработке, чтобы код перечитывался без ребилда, но привязывает контейнер к структуре хоста и даёт прямой доступ к ФС хозяина. Anonymous volume Docker создаёт сам без имени и его трудно переиспользовать.',
                'difficulty' => 3,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Зачем нужен multi-stage build в Dockerfile?',
                'answer' => 'Multi-stage позволяет описать в одном Dockerfile несколько FROM-этапов и копировать артефакты из одного в другой через COPY --from. Типовая схема для PHP: первый stage с composer и dev-инструментами устанавливает зависимости и собирает фронт, второй — slim-образ php-fpm-alpine, в который через COPY --from=builder переносится только vendor и собранные ассеты. Финальный образ не содержит composer, npm, исходников тестов и build-tools, весит в разы меньше, имеет меньшую attack surface и быстрее тянется на ноды. Это де-факто стандарт для production-образов.',
                'difficulty' => 3,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Как порядок инструкций в Dockerfile влияет на размер слоёв и скорость сборки?',
                'answer' => 'Каждая инструкция (RUN, COPY, ADD) создаёт отдельный слой, который кешируется по контрольной сумме входов. Слои инвалидируются последовательно: если изменился слой N, все следующие пересобираются заново. Поэтому редко меняющиеся шаги (apt-get install, composer install при стабильном lock) ставят раньше, а COPY исходников — в конце, чтобы правка кода не запускала установку зависимостей. Также apt-get update и install объединяют в один RUN с rm -rf /var/lib/apt/lists/*, иначе кеш пакетов навсегда лежит в промежуточном слое и раздувает образ.',
                'difficulty' => 3,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что делает .dockerignore и почему его стоит писать сразу?',
                'answer' => 'При docker build клиент упаковывает контекст (текущую директорию) и шлёт демону. Без .dockerignore туда попадают .git, vendor, node_modules, .env, логи и дампы — это и замедляет билд, и засоряет слои, и легко затягивает в образ секреты. .dockerignore работает как .gitignore: исключает пути из контекста ещё до того, как они стали доступны Dockerfile-инструкциям. На практике в него заносят .git, node_modules, vendor (если они ставятся внутри билда), tests, storage/logs, .env*, var/cache.',
                'difficulty' => 2,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Зачем нужен docker-compose и где у него предел применимости?',
                'answer' => 'docker-compose описывает в одном YAML-файле группу связанных сервисов (php-fpm, nginx, mysql, redis), их сети, тома и зависимости, чтобы поднять всё локально одной командой docker compose up. Хорошо подходит для dev-окружения и интеграционных тестов на CI. В production его обычно не используют: нет встроенного авто-рестарта по падению ноды, нет шедулинга по нескольким хостам, нет rolling update и health-based load balancing — это всё отдаёт Kubernetes, Nomad или Swarm. Compose — про single-host оркестрацию.',
                'difficulty' => 3,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Ingress в Kubernetes и чем он отличается от Service типа LoadBalancer?',
                'answer' => 'Service типа LoadBalancer создаёт по одному внешнему балансировщику (и публичному IP) на каждый сервис — на облаке это быстро дорого. Ingress — это слой L7-маршрутизации поверх кластера: один внешний LB, за ним Ingress Controller (nginx, Traefik, HAProxy), который по host и path разводит трафик на разные ClusterIP-сервисы. Ingress умеет TLS termination, rewrite, basic auth, rate limit. То есть LoadBalancer работает на L4, Ingress — на L7 и заменяет N балансировщиков одним с правилами маршрутизации.',
                'difficulty' => 4,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Зачем в Kubernetes нужны liveness и readiness probes и в чём между ними разница?',
                'answer' => 'Readiness probe отвечает на вопрос «готов ли под принимать трафик»: если она падает, kube-proxy убирает под из endpoints соответствующего Service, но сам под не убивается — это нужно при прогреве кэша, ожидании БД, во время деплоя. Liveness probe отвечает «жив ли процесс вообще»: если она падает несколько раз подряд, kubelet перезапускает контейнер. Путать их опасно: если readiness привязать к внешней БД, при её недоступности весь сервис исчезнет из балансировки; если liveness слишком агрессивен, медленный GC будет вызывать постоянные рестарты.',
                'difficulty' => 4,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Чем ConfigMap отличается от Secret в Kubernetes?',
                'answer' => 'ConfigMap хранит несекретную конфигурацию (URL-ы, фичефлаги, имена файлов) и пробрасывается в под как переменные окружения или примонтированные файлы. Secret устроен почти так же, но предназначен для паролей, токенов и ключей: значения хранятся в etcd закодированными в base64 (это не шифрование, а транспортная кодировка) и в etcd может быть включён encryption-at-rest. RBAC обычно настраивают строже на Secret, чем на ConfigMap. На практике секреты лучше отдавать через external-secrets из Vault/AWS Secrets Manager, а не коммитить в YAML в гит.',
                'difficulty' => 4,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Зачем в Kubernetes указывают requests и limits для CPU и памяти?',
                'answer' => 'Requests — это минимум, который шедулер гарантирует поду на ноде; на их сумме считается, влезет ли под на узел. Limits — потолок: при превышении CPU контейнер троттлится, при превышении памяти — OOM-killed. Без requests шедулинг становится непредсказуемым и можно получить шумных соседей. Без memory limit процесс способен сожрать всю ноду и положить остальные поды. С CPU limit нужно осторожно: на интенсивных пиках (composer dump, прогрев OPcache) троттлинг ломает SLA. Хорошая практика — задать requests адекватно реальному p95-потреблению, а memory limit — с запасом 20-30%.',
                'difficulty' => 4,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Helm и какую проблему он решает?',
                'answer' => 'Helm — это пакетный менеджер для Kubernetes. Чарт (chart) — это набор шаблонизированных YAML-манифестов с values.yaml, в котором задаются параметры (имя образа, теги, replicas, ресурсы). helm install/upgrade подставляет values в шаблоны и применяет получившиеся манифесты атомарно, ведя историю релизов с возможностью rollback. Без Helm на каждое окружение (dev/stage/prod) копируются почти идентичные YAML, и любая правка означает sed по нескольким файлам. Альтернатива — Kustomize (overlay-подход без шаблонов) или GitOps через ArgoCD/Flux поверх Helm-чартов.',
                'difficulty' => 4,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'В чём разница между Terraform и Ansible?',
                'answer' => 'Terraform — это декларативный provisioning: описываешь желаемое состояние инфраструктуры (VPC, сабнеты, инстансы, RDS, k8s-кластер) в HCL, terraform apply считает diff с реальностью через state-файл и приводит к нужному виду. Хорош для создания и удаления ресурсов в облаке. Ansible — это конфигурационный менеджмент через SSH: набор playbook с задачами (поставить пакет, скопировать файл, перезапустить сервис), выполняется императивно сверху вниз. На практике их часто комбинируют: Terraform поднимает голые VM/сети, Ansible настраивает на них софт. С приходом Kubernetes и immutable-образов потребность в Ansible уменьшилась.',
                'difficulty' => 4,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое GitOps и чем он отличается от классического CI/CD push-деплоя?',
                'answer' => 'В классическом push-CD pipeline после успешных тестов сам по SSH или kubectl apply деплоит в кластер — у CI-раннера должны быть creds на прод. В GitOps git-репозиторий с манифестами объявляется единственным источником истины, а агент в кластере (ArgoCD, Flux) сам периодически сверяется с гитом и приводит кластер к описанному состоянию (pull-модель). Преимущества: история деплоев = git log, rollback = git revert, кластер не пускает CI внутрь периметра, drift detection бесплатно. Минус — двухрепная схема (app-repo и manifest-repo) и кривая обучения.',
                'difficulty' => 4,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Почему PHP в production обычно ставят за nginx + php-fpm, а не запускают встроенный сервер?',
                'answer' => 'Встроенный сервер php -S однопоточный, не поддерживает SSL/HTTP2, не отдаёт статику параллельно с PHP и официально предназначен только для разработки. Production-связка — nginx как reverse proxy + php-fpm как пул PHP-процессов, общающихся по FastCGI через unix-socket или tcp. nginx отдаёт статику, делает gzip, TLS termination, ограничивает размер запросов и держит keep-alive с клиентом, а в php-fpm проксирует только то, что попало под location ~ \\.php$. php-fpm сам управляет пулом воркеров (pm = dynamic/static/ondemand), перезапускает по pm.max_requests и изолирует утечки памяти.',
                'difficulty' => 3,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что настраивают в php-fpm pool: pm static vs dynamic vs ondemand?',
                'answer' => 'pm = static держит фиксированное число pm.max_children воркеров — предсказуемое потребление памяти, лучшая латентность под пиком, выбор для нагруженного прода. pm = dynamic стартует pm.start_servers и держит между pm.min_spare_servers и pm.max_spare_servers — экономит память на простаивающем сервере, но при резком всплеске часть запросов ждёт форка. pm = ondemand форкает воркер только под запрос и убивает его — минимум памяти, максимум cold-start; годится для shared-хостинга. Дополнительно крутят pm.max_requests (рестарт воркера для борьбы с утечками) и request_terminate_timeout.',
                'difficulty' => 4,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Какие настройки OPcache критичны для production-PHP?',
                'answer' => 'opcache.enable=1 и opcache.memory_consumption (128-256 МБ для среднего проекта) — без них кеш либо выключен, либо вытесняется. opcache.max_accelerated_files должен быть больше реального числа .php файлов в проекте, иначе часть будет постоянно перекомпилироваться. opcache.validate_timestamps=0 на проде даёт максимум скорости, но требует opcache_reset/рестарт fpm при деплое — иначе старый код останется в памяти. opcache.preload (PHP 7.4+) загружает классы фреймворка при старте и убирает их компиляцию из горячего пути. Для измерения — opcache.jit и jit_buffer_size, но JIT помогает в основном CPU-bound коду.',
                'difficulty' => 4,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Почему в production нельзя оставлять opcache.validate_timestamps=1 и как тогда катить релиз?',
                'answer' => 'При validate_timestamps=1 OPcache на каждый запрос (с шагом revalidate_freq) делает stat по файлу и сверяет mtime — это лишний syscall на каждый include, заметно режущий p99 на больших фреймворках. На проде ставят 0 и тогда правка файла на диске не подхватывается, пока не сбросить кеш. Безопасный деплой: выкатывать новый код в новую директорию (release_N), переключать симлинк current на неё атомарно и затем дёргать opcache_reset через fastcgi_finish_request или systemctl reload php-fpm. В Kubernetes ту же роль играет rolling-restart подов — у новых OPcache пустой и наполняется свежим кодом.',
                'difficulty' => 4,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое self-hosted runner в GitHub Actions / GitLab CI и когда он нужен?',
                'answer' => 'По умолчанию пайплайны исполняются на shared-runner провайдера — удобно, но платно по минутам, без доступа во внутреннюю сеть и с холодным кешем на каждом запуске. Self-hosted runner — собственная VM или под в кластере, зарегистрированная как worker; задачи едут туда. Это нужно, когда нужен доступ к private DB/k8s через VPN, когда хочется тёплый кеш composer/node_modules между запусками, когда тяжёлые билды дешевле гонять у себя, и когда требуется кастомное железо (GPU). Минусы — самим следить за обновлениями, изоляцией задач и безопасностью runner-токена.',
                'difficulty' => 4,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Зачем в CI отдельно кешировать composer и npm зависимости?',
                'answer' => 'Чистый composer install на холодном раннере тянет сотни пакетов из packagist и собирает autoload — это десятки секунд на каждый pipeline. CI-системы (GitHub Actions cache, GitLab cache) умеют сохранять директорию vendor/ или ~/.composer/cache между запусками с ключом по хешу composer.lock. При неизменном lock зависимости разворачиваются за секунды, при изменении — кеш промахивается и пересобирается. Аналогично с node_modules по package-lock.json. Главное — ключ кеша должен включать lock-файл, иначе либо ничего не обновится, либо кеш будет неконсистентен.',
                'difficulty' => 3,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Чем canary отличается от blue-green и когда выбирать какой?',
                'answer' => 'Blue-green поднимает рядом со старой версией (blue) полный второй стек новой версии (green), прогоняет smoke-тесты и переключает 100% трафика разом — мгновенный rollback переключением назад, но требует двойных ресурсов и не ловит проблемы, проявляющиеся только под реальной нагрузкой. Canary катит новую версию на 1-5% трафика, наблюдает метрики (error rate, p95, бизнес-KPI) и постепенно увеличивает долю. Canary безопаснее для рискованных изменений и больших систем, но требует развитого мониторинга и feature-flag-инфраструктуры. Blue-green удобен для коротких релизов и фоновой миграции БД.',
                'difficulty' => 4,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое immutable infrastructure и почему она безопаснее mutable?',
                'answer' => 'В mutable-подходе сервер живёт долго: на него раскатываются обновления через ssh/ansible, ставятся пакеты, правятся конфиги. Со временем накапливается configuration drift — два «одинаковых» сервера ведут себя по-разному, воспроизвести проблему сложно. Immutable infrastructure означает, что любое изменение = пересборка нового образа (Docker image, AMI) и замена старых инстансов на новые; правок на живой машине не делают. Это даёт идентичные окружения, тривиальный rollback (вернуть прежний образ), отсутствие snowflake-серверов и аудит через git/CI. Цена — обязательная автоматизация сборки и центральное хранилище образов.',
                'difficulty' => 4,
                'topic' => 'system_design.devops',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Зачем PHP-приложению контейнеры запускать не от root, и что для этого нужно сделать?',
                'answer' => 'По умолчанию контейнер запускается от UID 0 — если злоумышленник вырвался из процесса (например, через RCE в приложении) и нашёл уязвимость в runc/ядре, он получает root на хосте. Также root внутри легко повредит примонтированные тома. Правильно: в Dockerfile создать пользователя (RUN adduser -u 1000 app) и USER app перед CMD; в Kubernetes выставить securityContext.runAsNonRoot: true и runAsUser: 1000, плюс readOnlyRootFilesystem: true. Сложности: php-fpm нужно дать права на /var/run/php-fpm.sock и логи, а слушать порты ниже 1024 не-root не сможет — поэтому fpm обычно слушает 9000, а 80/443 терминируются на nginx-ingress.',
                'difficulty' => 4,
                'topic' => 'system_design.devops',
            ],
        ];
    }
}
