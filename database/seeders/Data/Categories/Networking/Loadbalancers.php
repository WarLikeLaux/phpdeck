<?php

namespace Database\Seeders\Data\Categories\Networking;

class Loadbalancers
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Сети',
                'question' => 'В чём разница между L4 и L7 балансировщиками нагрузки?',
                'answer' => 'Разница в **уровне OSI**, на котором балансировщик принимает решение.

**`L4` (transport layer)** — балансирует `TCP`/`UDP` без разбора содержимого. Видит только адреса и порты. **Каждое соединение целиком** уходит к одному backend и держится до закрытия.

- **Примеры:** AWS `NLB`, HAProxy в TCP-mode, nginx `stream` module, LVS, Cilium.
- **Плюсы:** экстремальная скорость (миллионы PPS), минимальная latency, не нужно терминировать `TLS` на балансировщике.
- **Минусы:** не видит HTTP-семантику — нельзя балансировать по `path`/`cookie`/`header`, нельзя ретраить отдельный запрос внутри keepalive-коннекта.

**`L7` (application layer)** — терминирует TCP, парсит протокол (`HTTP`/`HTTP/2`/`gRPC`), распределяет **каждый запрос**.

- **Примеры:** AWS `ALB`, nginx, HAProxy в HTTP-mode, Envoy, Traefik.
- **Плюсы:**
    - Маршрутизация по host/path: `api.example.com` → service A, `/admin` → service B.
    - Per-request retry, sticky sessions через cookie.
    - Request modification (rewrite, set-headers), `TLS` termination, `gzip`/`brotli`.
    - Rate limit по user-id из JWT.
- **Минусы:** ниже throughput из-за парсинга, больше задержка, нужен приватный ключ `TLS` на LB.

**Типичный продакшен:** фронтальный `L4 NLB` → внутренний `L7` (nginx/Envoy) → backend. Это сочетает суперскорость L4 (DDoS-сопротивление и низкая задержка на handshake) с умной маршрутизацией L7.',
                'difficulty' => 3,
                'topic' => 'networking.loadbalancers',
            ],
            [
                'category' => 'Сети',
                'question' => 'Какие основные алгоритмы балансировки и когда какой выбрать?',
                'answer' => '**Round Robin (`RR`)** — поочерёдное распределение. Простой, без состояния, подходит для одинаковых stateless-сервисов. Минус — не учитывает реальную нагрузку backend.

**Weighted Round Robin** — у каждого backend задан **вес**. Используется для **canary** (новой версии даём вес 5%) и при гетерогенном железе.

**Least Connections** — backend с **минимумом активных соединений**. Хорош, когда запросы сильно разной длительности (WebSocket долгие vs HTTP короткие смешаны). Требует трекать active count — лёгкое состояние.

**Least Time / Least Response Time** — по среднему **latency**. Активно адаптируется к деградации одного backend. Реализовано в HAProxy, Envoy.

**IP Hash / Source Hash** — хеш `src_ip` клиента, всегда **тот же backend**. Базовый sticky без cookie. Минусы:

- За **NAT** все клиенты падают на один backend.
- При изменении количества backend **все хеши перераспределяются**.

**Consistent Hashing** — при добавлении/удалении backend перетасовывается **~1/N** ключей вместо всех. Используется в Memcached, Cassandra, распределённых кэшах.

**Random / Power of Two Choices** — случайный выбор. **«Two choices»** выбирает двух случайных и берёт **менее загруженного** — почти оптимально с минимальным состоянием. Рекомендация **Envoy** для общего случая.

**Практика:** для веб-приложений в **95% случаев** — `round_robin` + health checks. Sticky/hash включают только под конкретную необходимость (WebSocket, локальный кэш).',
                'difficulty' => 3,
                'topic' => 'networking.loadbalancers',
            ],
            [
                'category' => 'Сети',
                'question' => 'Что такое sticky sessions и какие у них плюсы и минусы?',
                'answer' => '**Sticky session** (session affinity) — балансировщик отправляет все запросы одного клиента на **один и тот же backend**.

**Реализации:**

- **Application-managed** — приложение ставит cookie (`JSESSIONID`, `laravel_session`), LB запоминает сопоставление.
- **LB-managed** — балансировщик сам ставит cookie (например, `AWSALB`) с идентификатором target.
- **IP hash** — без cookie, по `src_ip` (ломается за NAT/корпоративными прокси).

**Когда нужен:**

- Приложение хранит **сессию в памяти/на диске backend** — другой сервер не знает контекст.
- **WebSocket / long-polling** — это одно физическое соединение, оно живёт на одном backend.
- Прогретый локальный кэш конкретного worker (heavy объект уже в памяти).

**Минусы:**

- **Hot spots** — VIP-клиенты осели на одном backend, остальные простаивают.
- **Restart backend** = все его сессии теряются (или мигрируют не сразу).
- Усложняет **горизонтальное масштабирование** — при добавлении пода нагрузка не перераспределяется (новые клиенты только).
- Усложняет **canary** — sticky-клиент остаётся на старой версии до релогина.

**Лучшая практика — НЕ полагаться на sticky:**

- Хранить сессию во внешнем хранилище: `Redis`, `Memcached`, signed cookies.
- Любой backend обрабатывает любой запрос → нагрузка равномерная.

Sticky оправдан только для **WebSocket/SSE** или legacy-приложений без shared state.',
                'difficulty' => 3,
                'topic' => 'networking.loadbalancers',
            ],
            [
                'category' => 'Сети',
                'question' => 'Что такое заголовки X-Forwarded-For, X-Real-IP и почему их не стоит слепо доверять?',
                'answer' => 'Когда между клиентом и сервером стоят прокси/балансировщики, TCP src_ip в приложении — это IP последнего прокси, не реальный клиент. Чтобы прокинуть оригинальный IP, прокси добавляет специальные заголовки: 1) X-Forwarded-For (XFF) — список IP через запятую: «client_ip, proxy1_ip, proxy2_ip». Каждый прокси на пути ДОБАВЛЯЕТ к нему свой known src_ip. Самый левый — оригинальный клиент. 2) X-Real-IP — обычно только один IP (тот, что прокси считает клиентским). 3) Forwarded (RFC 7239, новый стандарт) — структурированный «Forwarded: for=192.0.2.43;proto=https;by=10.0.0.1». Поддерживается хуже legacy XFF. Главная проблема: заголовки можно ПОДДЕЛАТЬ. Любой клиент может прислать X-Forwarded-For: 1.2.3.4 в обычном HTTP-запросе — и наивный код запишет это в логи/rate-limit/audit как «реальный IP». Правила безопасности: 1) Доверяйте XFF только если перед вами есть ИЗВЕСТНЫЙ доверенный прокси (CDN, LB) — конфигурируйте список trusted_proxies (в Laravel — App\\Http\\Middleware\\TrustProxies, в Symfony — Request::setTrustedProxies, в nginx — set_real_ip_from + real_ip_header). 2) Доверенный прокси ДОЛЖЕН перезаписывать или валидировать XFF (Cloudflare добавляет CF-Connecting-IP, который нельзя подделать снизу). 3) Берите крайний правый IP из XFF, начиная с известного untrusted (Mozilla\'s docs: «leftmost is from least trusted source»; для определения «настоящего клиента» — справа налево скиппайте свои прокси). 4) Никогда не используйте сырой XFF для бизнес-логики без trust chain.',
                'difficulty' => 4,
                'topic' => 'networking.loadbalancers',
            ],
            [
                'category' => 'Сети',
                'question' => 'Что такое CDN и как он реально работает (с anycast, edge cache, origin)?',
                'answer' => 'CDN (Content Delivery Network) — географически распределённая сеть edge-серверов, которая отдаёт контент пользователю с ближайшей точки. Архитектура: 1) Origin — ваш сервер с настоящими данными (S3 bucket, EC2, on-prem). 2) Edge / PoP (Point of Presence) — точки CDN в разных городах (Cloudflare 300+ PoP, AWS CloudFront 600+ edge locations). 3) Маршрутизация клиентов: anycast IP — один и тот же IP анонсируется из всех PoP одновременно через BGP, и пакет клиента автоматически попадает в ближайший по BGP-метрикам PoP. Альтернатива — GeoDNS, где DNS отдаёт разный IP в зависимости от IP резолвера. Anycast надёжнее (если PoP падает, BGP переключает на следующий). Что делает edge: 1) Кэширует ответы origin по Cache-Control / правилам CDN (HTML, JS, CSS, картинки, видео). 2) Терминирует TLS близко к пользователю — TCP/TLS handshake идут до edge (1-5ms), а не до origin (100ms+). Это драматически снижает latency, особенно для HTTPS. 3) Сжатие, image optimization, минификация на лету. 4) WAF, DDoS-защита, rate limit. 5) Edge-compute (Cloudflare Workers, Lambda@Edge, Vercel Edge) — JS/WASM код выполняется в каждом PoP перед origin. 6) Кэш-инвалидация — purge by URL/tag/zone, обычно мгновенный. На примере запроса /style.css: клиент → ближайший edge (anycast) → проверка local cache. Hit → отдаёт сразу. Miss → запрос на origin (часто через приватную backbone CDN), кэширует, отдаёт клиенту. Все следующие запросы из этого региона — hit. Главные провайдеры: Cloudflare, AWS CloudFront, Fastly, Akamai, Bunny.net.',
                'difficulty' => 4,
                'topic' => 'networking.loadbalancers',
            ],
            [
                'category' => 'Сети',
                'question' => 'Что такое балансировщик нагрузки простыми словами?',
                'answer' => '**Балансировщик нагрузки** — сервер-распределитель, который стоит перед группой бэкендов: принимает входящие запросы и раскидывает их между несколькими серверами. Как администратор в очереди — направляет посетителей к свободной кассе.

**Зачем нужен:**

1. **Масштабирование** — один сервер не справляется, поставили десять, балансировщик размазывает нагрузку.
2. **Отказоустойчивость** — если один сервер упал, балансировщик через **health-check** перестаёт слать ему трафик и распределяет остальной по живым.
3. **Релиз без даунтайма** — выводят серверы по очереди, обновляют, возвращают.

**Примеры:** `nginx`, `HAProxy`, AWS ALB/NLB, Cloudflare Load Balancer, Kubernetes Service.',
                'difficulty' => 1,
                'topic' => 'networking.loadbalancers',
            ],
            [
                'category' => 'Сети',
                'question' => 'Что такое sticky session простыми словами?',
                'answer' => '**Sticky session** (session affinity) — правило балансировщика: **«все запросы одного пользователя — на ОДИН и тот же бэкенд»**.

**Как реализуется:**

- **Cookie** — балансировщик ставит свою (`AWSALB`) или читает сессионную приложения (`PHPSESSID`, `laravel_session`).
- **Хеш `src_ip`** — попадание на бэкенд по IP клиента (ломается за NAT).

**Зачем:**

- In-memory сессии на сервере.
- **WebSocket**/**SSE** — это одно физическое соединение, оно живёт на одном бэкенде.
- Разогретый локальный кэш конкретного клиента.

**Минусы:**

- Бэкенд упал — его пользователи теряют сессию.
- **Hot spots** — нагрузка размазывается неравномерно.
- Усложняется горизонтальное масштабирование и canary-деплои.

**Современная практика:** **не полагаться на sticky**. Хранить сессию в общем `Redis`/БД или в signed-cookie — тогда любой запрос отдаётся любому бэкенду.',
                'difficulty' => 2,
                'topic' => 'networking.loadbalancers',
            ],
            [
                'category' => 'Сети',
                'question' => 'Чем edge-сервер CDN отличается от origin-сервера?',
                'answer' => '- **Origin** — «настоящий» сервер приложения: оригиналы файлов и работающий бэкенд (S3, EC2, ваш сервер).
- **Edge** (PoP) — сервер CDN, **географически близкий к пользователю**. Держит у себя кэш статики: картинки, JS, CSS, видео.

**Как работает запрос `/style.css`:**

1. DNS/anycast направляет клиента в **ближайший edge** (Cloudflare, AWS CloudFront, Fastly).
2. **Cache hit** — файл уже в edge, отдаётся сразу с задержкой 5-30 мс.
3. **Cache miss** — edge сам ходит на origin, забирает файл, кэширует и отдаёт клиенту. Следующий клиент из того же региона получит уже hit.

**Что это даёт:**

- Origin **разгружается** — большая часть трафика обслуживается edge-ами.
- Пользователь получает контент с минимальной задержкой.
- Edge ещё и **терминирует TLS** близко к клиенту, что экономит RTT на handshake.

Популярные CDN: **Cloudflare**, **AWS CloudFront**, **Fastly**, **Akamai**, **Bunny.net**.',
                'difficulty' => 2,
                'topic' => 'networking.loadbalancers',
            ],
            [
                'category' => 'Сети',
                'question' => 'Что такое reverse proxy простыми словами?',
                'answer' => '**Reverse proxy** — сервер, который стоит **перед** вашим бэкендом и принимает запросы от клиентов вместо него. Клиент думает, что общается напрямую с приложением, а реально — с прокси, который пересылает запрос дальше в app-сервер.

**Зачем нужен:**

- **TLS-терминация** — HTTPS обрабатывается на прокси, между прокси и app идёт обычный HTTP.
- **Отдача статики** — картинки, JS, CSS быстрее раздавать nginx-ом, чем PHP-FPM.
- **Кэш** ответов и `gzip`/`brotli` сжатие.
- **Rate limit** и базовая защита от DDoS.
- **Балансировка** между несколькими бэкендами.
- **Маршрутизация** по path/host: `/api` → service-A, `/admin` → service-B.

**Самые частые:** `nginx`, `Caddy`, `Traefik`, `HAProxy`, `Envoy`.

Не путать с **forward proxy** (стоит на стороне клиента и проксирует исходящий трафик наружу).',
                'difficulty' => 2,
                'topic' => 'networking.loadbalancers',
            ],
            [
                'category' => 'Сети',
                'question' => 'В чём разница между forward proxy и reverse proxy?',
                'answer' => 'Оба прокси гоняют HTTP/TCP между клиентом и сервером, но **стоят с разных сторон** и решают разные задачи.

**Запоминалка:** forward скрывает **клиента** от сервера, reverse скрывает **сервер** от клиента.

| | **Forward proxy** | **Reverse proxy** |
|---|---|---|
| Стоит у | клиента | сервера |
| Прячет | клиента | бэкенд |
| Кто видит IP клиента | прокси видит, сервер — нет | сервер за прокси не видит |
| Конфигурация | клиент явно настраивает (`HTTP_PROXY`, system proxy) | прозрачно для клиента |
| Применения | corp egress-фильтр, Squid-кэш, VPN/Tor, обход геоблоков, MITM-инспекция HTTPS | nginx/Caddy/Traefik перед PHP-FPM, балансировщик, `TLS`-терминация, статика, `gzip`/`brotli`, WAF, маршрутизация `/api` → service-a |

**Софт часто один и тот же** (`nginx`, HAProxy, Envoy) — разница в конфиге и направлении.',
                'code_example' => "# Forward proxy: клиент явно ходит через корпоративный Squid\nexport HTTP_PROXY=http://corp-proxy:3128\ncurl https://example.com\n\n# Reverse proxy: nginx перед app — типовой конфиг\n# server {\n#     listen 443 ssl;\n#     server_name example.com;\n#     location / {\n#         proxy_pass http://app:9000;\n#         proxy_set_header Host \$host;\n#         proxy_set_header X-Real-IP \$remote_addr;\n#         proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;\n#     }\n# }",
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'networking.loadbalancers',
            ],
        ];
    }
}
