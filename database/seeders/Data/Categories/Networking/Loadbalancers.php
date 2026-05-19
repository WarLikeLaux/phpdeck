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
                'answer' => 'L4 (transport layer) — балансирует TCP/UDP без разбора содержимого. Видит только адреса и порты. Каждое соединение целиком уходит к одному backend и держится до закрытия. Примеры: AWS NLB, HAProxy в TCP mode, nginx stream module, LVS, Cilium. Скорость — экстремально высокая (миллионы PPS, минимальная latency, легко работает на железе или DPDK), не разбирает payload — не нужно завершать TLS на балансировщике. Минусы: не видит HTTP-семантику — не может балансировать по path / cookie / header, не может ретраить отдельный запрос внутри keepalive-коннекта. L7 (application layer) — терминирует TCP, парсит протокол (обычно HTTP/HTTP2/gRPC), распределяет КАЖДЫЙ запрос. Примеры: AWS ALB, nginx, HAProxy в HTTP mode, Envoy, Traefik. Плюсы: маршрутизация по host/path (api.example.com → service A, /admin → service B), per-request retry, sticky sessions через cookie, request modification (rewrite, set-headers), TLS termination, gzip/brotli, rate limit по user-id из JWT. Минусы: ниже throughput из-за парсинга, больше задержка, требует приватный ключ TLS на LB. На практике: фронтальный L4 NLB → внутренний L7 (nginx/Envoy) → backend. Это даёт суперскорость на L4 (для DDoS-сопротивления и низкой задержки на handshake) + умную маршрутизацию на L7.',
                'difficulty' => 3,
                'topic' => 'networking.loadbalancers',
            ],
            [
                'category' => 'Сети',
                'question' => 'Какие основные алгоритмы балансировки и когда какой выбрать?',
                'answer' => 'Round Robin (RR) — поочерёдное распределение между backend-ами. Простой, без состояния, подходит для одинаковых stateless-сервисов. Минус — не учитывает реальную нагрузку конкретного backend (один может быть нагружен тяжёлым запросом). Weighted Round Robin — каждому backend задан вес, чаще достаются более сильные машины. Используется для canary (новой версии даём вес 5%) и при гетерогенном железе. Least Connections — выбирает backend с минимумом активных соединений. Хорош когда запросы имеют сильно разную длительность (например, WebSocket долгие vs HTTP короткие смешаны). Требует трекать active count per backend — лёгкое состояние. Least Time / Least Response Time — выбирает по среднему latency. Активно адаптируется к деградации одного из backend. Используется HAProxy, Envoy. IP Hash / Source Hash — хешируется src_ip клиента, всегда попадает на один и тот же backend. Базовая sticky session БЕЗ cookie — но ломается за NAT (все клиенты за одним IP попадают на один backend) и при изменении количества backend (все хеши перераспределяются). Consistent Hashing — улучшение: при добавлении/удалении backend перетасовывается ~1/N ключей. Используется в Memcached, Cassandra, для распределённого кэша. Random / Random with two choices — случайный выбор; «two choices» (Power of Two) выбирает двух случайных и берёт менее загруженного — почти оптимально с минимальным состоянием. Рекомендация Envoy для общего случая. На практике для веб-приложений: round_robin с health checks — 95% случаев.',
                'difficulty' => 3,
                'topic' => 'networking.loadbalancers',
            ],
            [
                'category' => 'Сети',
                'question' => 'Что такое sticky sessions и какие у них плюсы и минусы?',
                'answer' => 'Sticky session (session affinity) — балансировщик отправляет все запросы одного клиента на ОДИН и тот же backend, обычно по cookie или IP. Реализации: 1) Application-managed — сервер при первом запросе ставит cookie вроде JSESSIONID; LB читает её и помнит сопоставление. 2) LB-managed — балансировщик сам ставит свою cookie (например, AWSALB), значение содержит идентификатор target. 3) IP hash — без cookie, по src_ip (ломается за NAT/корпоративными прокси). Зачем: 1) Приложение хранит сессию в локальной памяти/диске backend — нельзя обработать запрос на другом сервере. 2) WebSocket / long-polling требуют попадания на тот же сервер. 3) Кэширование на уровне процесса — heavy объект уже в памяти конкретного worker. Минусы: 1) Hot spots — VIP-клиенты на одном backend, остальные простаивают. 2) Restart backend = все его сессии теряются (или мигрируют не сразу). 3) Усложняет горизонтальное масштабирование: при добавлении пода нагрузка не перераспределяется автоматически — только новые клиенты. 4) Усложняет canary — sticky клиент остаётся на старой версии до релогина. Лучшая практика — НЕ полагаться на sticky: хранить сессию во внешнем хранилище (Redis, Memcached, signed cookies) → любой backend обрабатывает любой запрос → нагрузка распределяется равномерно. Sticky оправдан только для WebSocket/SSE или legacy-приложений без shared state.',
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
                'answer' => 'Сервер-распределитель: принимает входящие запросы и раскидывает их между несколькими бэкенд-серверами. Зачем: 1) Масштабирование — один сервер не справляется, поставили десять. 2) Отказоустойчивость — если один сервер упал, балансировщик перенаправит трафик на остальные. Примеры: nginx, HAProxy, AWS ALB.',
                'difficulty' => 1,
                'topic' => 'networking.loadbalancers',
            ],
            [
                'category' => 'Сети',
                'question' => 'Что такое sticky session простыми словами?',
                'answer' => 'Правило балансировщика: «все запросы одного пользователя — на ОДИН и тот же бэкенд». Нужно, когда сессия хранится в памяти сервера (а не в Redis). Обычно по cookie или IP. Минус: при падении сервера все его пользователи теряют сессию.',
                'difficulty' => 2,
                'topic' => 'networking.loadbalancers',
            ],
            [
                'category' => 'Сети',
                'question' => 'Чем edge-сервер CDN отличается от origin-сервера?',
                'answer' => 'Origin — это «настоящий» сервер приложения, где живут оригиналы файлов и крутится бэкенд. Edge — это сервер CDN, географически близкий к пользователю, который держит у себя кэш статики (картинки, JS, CSS, видео). Когда клиент запрашивает /style.css, DNS направляет его в ближайший edge; если файл там уже есть — отдаётся сразу (cache hit), если нет — edge сам сходит на origin, заберёт файл, закэширует и отдаст клиенту (cache miss). Так origin разгружается, а пользователь получает контент с минимальной задержкой. Известные CDN: Cloudflare, AWS CloudFront, Fastly, Akamai, Bunny.net.',
                'difficulty' => 2,
                'topic' => 'networking.loadbalancers',
            ],
            [
                'category' => 'Сети',
                'question' => 'Что такое reverse proxy простыми словами?',
                'answer' => 'Сервер, который стоит ПЕРЕД вашим бэкендом и принимает запросы от клиентов вместо него. Клиент думает, что общается напрямую с приложением, а реально — с прокси, который дальше передаёт запрос в app-сервер. Зачем: 1) TLS-терминация (HTTPS на прокси, между прокси и app — HTTP), 2) отдача статики, 3) кэш, 4) gzip/brotli, 5) rate limit, 6) балансировка между несколькими бэкендами. Самые частые: nginx, Caddy, Traefik, HAProxy.',
                'difficulty' => 2,
                'topic' => 'networking.loadbalancers',
            ],
            [
                'category' => 'Сети',
                'question' => 'В чём разница между forward proxy и reverse proxy?',
                'answer' => 'Forward proxy стоит на стороне КЛИЕНТОВ: они шлют через него исходящие запросы наружу. Применения: корпоративный прокси, который фильтрует доступ сотрудников в интернет; VPN; Tor. Сервер не знает, что клиент идёт через прокси. Reverse proxy стоит на стороне СЕРВЕРА: принимает входящие запросы от любых клиентов и передаёт в свой бэкенд. Применения: nginx перед PHP-FPM, балансировщик перед кластером. Клиент не знает, что отвечает не «настоящий» сервер, а прокси.',
                'difficulty' => 3,
                'topic' => 'networking.loadbalancers',
            ],
        ];
    }
}
