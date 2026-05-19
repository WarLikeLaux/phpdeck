<?php

namespace Database\Seeders\Data\Categories\Networking;

class Tls
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Сети',
                'question' => 'Как работает TLS-handshake (на примере TLS 1.3)?',
                'answer' => 'TLS 1.3 handshake занимает 1 RTT (TLS 1.2 — 2 RTT, заметная разница для мобильных). Шаги: 1) ClientHello — клиент шлёт: список поддерживаемых cipher suites (в 1.3 их всего 5: TLS_AES_128_GCM_SHA256, TLS_AES_256_GCM_SHA384, TLS_CHACHA20_POLY1305_SHA256, TLS_AES_128_CCM_SHA256, TLS_AES_128_CCM_8_SHA256), поддерживаемые группы для key exchange (X25519, P-256), список SNI (какой домен запрашиваешь — критично для multi-site хостинга), список ALPN (HTTP/1.1, h2, h3 — что готов говорить), random nonce, и КЛЮЧЕВАЯ ОПТИМИЗАЦИЯ 1.3 — сразу шлёт keyshare с ECDHE-public-key для предположенной группы. 2) ServerHello — сервер выбирает cipher suite, группу, отдаёт свой ECDHE-public-key. Этого достаточно, чтобы обе стороны вычислили общий ключ (Diffie-Hellman). Дальше всё уже шифруется. 3) Server отдаёт: Certificate (X.509, цепочку до промежуточного CA — без корня), CertificateVerify (подпись handshake-данных приватным ключом), Finished. 4) Клиент валидирует цепочку до доверенного root CA в своём хранилище, проверяет имя в сертификате против hostname (SNI), проверяет срок действия и OCSP (revocation). Шлёт Finished. 5) Application data поехало. 0-RTT (early data) — для повторных подключений: клиент шлёт application data в первом же пакете, используя ключ из предыдущей сессии. Минус — replay attack (атакующий может повторить тот же запрос); поэтому 0-RTT разрешают только для безопасных идемпотентных запросов (GET).',
                'difficulty' => 5,
                'topic' => 'networking.tls',
            ],
            [
                'category' => 'Сети',
                'question' => 'Что такое цепочка сертификатов, intermediate CA и почему один сертификат сам по себе не работает?',
                'answer' => 'Доверие в TLS строится на иерархии: root CA → intermediate CA → leaf certificate (сертификат сервера). Браузеры и ОС хранят список доверенных root CA — это «корни доверия». Они выдаются раз в десятилетия, тщательно охраняются (offline HSM, церемонии подписи). Issuing CA не использует root напрямую — root подписывает intermediate CA (живущие активно), а уже intermediate подписывает leaf-сертификаты конечных сервисов. Зачем такая прокладка: 1) Безопасность — компрометация intermediate (которые часто работают онлайн) не равна компрометации root (offline). При взломе intermediate его отзывают, выпускают новый — без перевыпуска всех браузеров. 2) Делегирование — Let\'s Encrypt оперирует своими intermediate, но конечное доверие идёт через ISRG Root X1 / X2. Когда сервер отдаёт сертификат при handshake, он ДОЛЖЕН отдать leaf + все intermediates (chain bundle), но НЕ root (его уже знает клиент). Если сервер забыл intermediate — современные браузеры могут попытаться достроить через AIA (Authority Information Access) URL внутри сертификата, но не все клиенты так умеют, особенно мобильные SDK и старые Android. Симптом: «works on my machine», ломается на каких-то клиентах. Тест: openssl s_client -connect host:443 -showcerts; SSL Labs (ssllabs.com/ssltest). После замены сертификата всегда проверять chain.',
                'difficulty' => 4,
                'topic' => 'networking.tls',
            ],
            [
                'category' => 'Сети',
                'question' => 'Что такое SNI и зачем он нужен? Что такое ESNI/ECH?',
                'answer' => 'SNI (Server Name Indication) — расширение TLS, где клиент в ClientHello указывает, какой ИМЕННО домен он хочет (server_name=example.com). До SNI на одном IP мог жить только один TLS-сайт: сервер не знал, какой сертификат отдать, потому что Host: header идёт после handshake, уже зашифрованный. С SNI сервер видит запрашиваемый домен ДО handshake и выбирает правильный сертификат — на одном IP может жить тысячи HTTPS-сайтов. Поэтому виртуальный хостинг HTTPS стал возможен. Минус SNI: имя домена идёт ОТКРЫТЫМ ТЕКСТОМ в ClientHello — провайдер видит, к какому сайту ты идёшь, даже если сам трафик зашифрован. Решение — ESNI (Encrypted SNI, экспериментально, отменено) → ECH (Encrypted Client Hello, RFC draft, стандартизация в процессе). ECH прячет SNI и часть ClientHello внутри второго (зашифрованного) ClientHello — внешний ClientHello идёт на «прикрытие» (например, общий cdn.cloudflare.com), внутренний раскрывает реальный домен только на сервере с приватным ключом. Поддерживается Firefox (с 2023), частично Chrome (в Origin Trial), Cloudflare. Активно сопротивляются регуляторы: в РФ и Иране ECH блокируется на провайдерском уровне. Для приложений это прозрачно — браузер сам решает, использовать ли ECH.',
                'difficulty' => 5,
                'topic' => 'networking.tls',
            ],
            [
                'category' => 'Сети',
                'question' => 'Что такое mTLS (mutual TLS) и где его применяют?',
                'answer' => 'Обычный TLS аутентифицирует только сервер: клиент проверяет, что говорит с настоящим example.com (через сертификат). Клиент остаётся анонимным до уровня приложения (логин/пароль, токен). mTLS добавляет аутентификацию КЛИЕНТА: у клиента тоже есть сертификат, выписанный доверенным CA (часто внутренним для организации). Во время handshake сервер шлёт CertificateRequest, клиент отвечает Certificate + CertificateVerify (подпись handshake-данных своим приватным ключом). Сервер валидирует цепочку до своего trust store. Если сертификат отсутствует или невалиден — TCP-коннект рвётся, до уровня HTTP даже не доходит. Где применяют: 1) Service mesh — внутри Kubernetes-кластеров (Istio, Linkerd) каждый pod имеет свой сертификат, выданный mesh-CA (SPIFFE/SPIRE), общение между сервисами идёт через mTLS без явного auth — это и есть Zero Trust в инфраструктуре. 2) Backend-to-backend API без user context (server-to-server) — банковские интеграции, B2B-партнёры. 3) IoT — устройства не имеют человеческого логина, аутентифицируются устройственным сертификатом. 4) VPN (OpenVPN, WireGuard используют похожую идею). 5) Admin-доступ к критическим панелям. Преимущества vs API-key: 1) Ротация автоматическая через CA. 2) Нет «утечки токена» — приватный ключ на клиенте никогда не передаётся. 3) Аудит — каждое соединение привязано к конкретной identity. Минусы: сложнее инфраструктура (CA, ротация, revocation), не работает «из коробки» в браузерах (можно, но UX плохой).',
                'difficulty' => 4,
                'topic' => 'networking.tls',
            ],
            [
                'category' => 'Сети',
                'question' => 'Что такое Perfect Forward Secrecy и почему все cipher suites теперь её используют?',
                'answer' => 'Perfect Forward Secrecy (PFS) — свойство протокола: компрометация долгосрочного приватного ключа сервера НЕ позволяет расшифровать ранее перехваченный трафик. Без PFS (старые cipher suites с RSA key exchange): клиент шифровал premaster secret приватным ключом сервера. Атакующий, записавший трафик и потом получивший приватный ключ (взлом, изъятие, court order), может его расшифровать. Случилось известно с Heartbleed (2014) — годы трафика стали ретроактивно уязвимыми. С PFS (ECDHE — Ephemeral Diffie-Hellman на эллиптических кривых): на каждое соединение генерируется НОВАЯ пара ephemeral-ключей, которая используется ТОЛЬКО для этой сессии и сразу выбрасывается. Долгосрочный приватный ключ сервера используется только для ПОДПИСИ ephemeral-параметров — это доказывает идентичность, но не участвует в шифровании контента. Скомпрометировав долгосрочный ключ, атакующий может выдать себя за сервер в БУДУЩЕМ, но НЕ может расшифровать прошлый трафик — ephemeral-ключи нигде не хранились. В TLS 1.3 все cipher suites — PFS, RSA key exchange удалён как класс. В TLS 1.2 нужно явно настраивать (предпочтение ECDHE_RSA_, ECDHE_ECDSA_). Стандарт «безопасной» настройки SSL — Mozilla SSL Config Generator (ssl-config.mozilla.org), который дёргают для nginx/Apache.',
                'difficulty' => 5,
                'topic' => 'networking.tls',
            ],
            [
                'category' => 'Сети',
                'question' => 'Что такое TLS/SSL простыми словами?',
                'answer' => 'Криптографический протокол, который оборачивает обычное TCP-соединение и даёт три гарантии: 1) Шифрование — никто посередине не прочитает трафик. 2) Целостность — подмена байтов на лету будет замечена. 3) Аутентификация сервера — сертификат подтверждает, что example.com это правда example.com. SSL — старое название версий 1.0-3.0, давно устарели и опасны. TLS — современное имя, актуальные версии — 1.2 и 1.3. Лежит между TCP и протоколом приложения: HTTP+TLS = HTTPS, SMTP+TLS = SMTPS, IMAP+TLS = IMAPS. Названия SSL и TLS в речи часто смешивают, но в проде везде уже TLS.',
                'difficulty' => 2,
                'topic' => 'networking.tls',
            ],
            [
                'category' => 'Сети',
                'question' => 'Что такое сертификат TLS простыми словами?',
                'answer' => 'Цифровой документ, подтверждающий, что example.com действительно принадлежит конкретной организации. Содержит имя домена, публичный ключ и подпись доверенного центра сертификации (CA). Браузер при подключении проверяет: подпись валидна → сертификат не истёк → доменное имя совпадает → доверяем.',
                'difficulty' => 2,
                'topic' => 'networking.tls',
            ],
            [
                'category' => 'Сети',
                'question' => 'Что такое центр сертификации (CA) простыми словами?',
                'answer' => 'Организация, которой доверяют браузеры и ОС. CA подписывает сертификаты сайтов, гарантируя их подлинность. Список доверенных CA встроен в браузер/ОС. Известные: Let\'s Encrypt (бесплатно), DigiCert, Sectigo, GlobalSign. Без подписи известного CA браузер покажет «Untrusted certificate».',
                'difficulty' => 2,
                'topic' => 'networking.tls',
            ],
            [
                'category' => 'Сети',
                'question' => 'Что такое TLS-handshake простыми словами?',
                'answer' => 'Короткий обмен сообщениями в начале HTTPS-соединения, в котором клиент и сервер: 1) договариваются, какой алгоритм шифрования использовать, 2) сервер показывает свой сертификат, 3) обмениваются ключами, 4) согласуют общий симметричный ключ для шифрования трафика. После handshake вся переписка идёт зашифрованной. В TLS 1.3 укладывается в 1 RTT (один раунд туда-обратно). После — обычный HTTP внутри.',
                'difficulty' => 2,
                'topic' => 'networking.tls',
            ],
            [
                'category' => 'Сети',
                'question' => 'Что такое Let\'s Encrypt простыми словами?',
                'answer' => 'Бесплатный публичный центр сертификации, выпускающий TLS-сертификаты автоматически по протоколу ACME. Сертификат живёт 90 дней — обновляется автоматически. Используется массово: WordPress-хостинги, мелкие сервисы, домашние pet-проекты. Инструменты: certbot, acme.sh, встроено в Caddy и Traefik. Благодаря Let\'s Encrypt HTTPS стал бесплатной нормой, а не платной опцией.',
                'code_example' => "sudo certbot --nginx -d example.com -d www.example.com",
                'code_language' => 'bash',
                'difficulty' => 2,
                'topic' => 'networking.tls',
            ],
            [
                'category' => 'Сети',
                'question' => 'Как проверить TLS-сертификат сайта руками и на что смотреть?',
                'answer' => 'Базовый инструмент — openssl s_client: открывает TLS-соединение и показывает, что прислал сервер. Обязательно передавать -servername (флаг SNI) — без него на multi-site хостинге вернётся не тот сертификат, или вообще default. Что смотреть в выводе: 1) Subject / SAN (Subject Alternative Name) — список доменов, на которые сертификат валиден; современные браузеры игнорируют CN и смотрят только SAN. 2) Issuer — кто подписал (обычно intermediate CA, например «Let\'s Encrypt R3»). 3) Validity (notBefore / notAfter) — сроки. 4) Verify return code: 0 (ok) — цепочка валидна, ненулевые коды значат проблему (21 = unable to verify the first certificate — забыт intermediate, 10 = expired, 18 = self-signed). 5) Цепочка (-showcerts) — leaf + все intermediates до root. Браузер: значок замка → «Details» → можно увидеть то же самое в GUI. Онлайн: ssllabs.com/ssltest даёт grade A/B/C/F с разбором cipher suites, поддержки версий TLS, OCSP-stapling, HSTS. Типовые ошибки: certificate verify failed — нет intermediate в bundle или просрочен; hostname mismatch — нет нужного SAN; unable to get local issuer — устарел системный CA-store на старом сервере.',
                'code_example' => "openssl s_client -connect example.com:443 -servername example.com < /dev/null\n# Verify return code: 0 (ok)  ← цепочка валидна\nopenssl s_client -connect example.com:443 -servername example.com < /dev/null 2>/dev/null \\\n  | openssl x509 -noout -subject -issuer -dates -ext subjectAltName\n# subject= /CN=example.com\n# issuer=  /C=US/O=Let's Encrypt/CN=R3\n# notBefore=... notAfter=...\n# X509v3 Subject Alternative Name: DNS:example.com, DNS:www.example.com",
                'code_language' => 'bash',
                'difficulty' => 3,
                'topic' => 'networking.tls',
            ],
        ];
    }
}
