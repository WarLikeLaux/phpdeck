<?php

namespace Database\Seeders\Data\Categories\Yii2;

class DataProvider
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Yii2',
                'question' => 'Что такое DataProvider в Yii2 и какие классы реализуют этот интерфейс?',
                'answer' => '**DataProvider** — это унифицированный **поставщик данных** для виджетов вроде `GridView`, `ListView`, `LinkPager`.

- Реализует интерфейс `yii\\data\\DataProviderInterface`: умеет отдавать **порцию записей**, **сортировку** и **пагинацию**.
- Готовые реализации:
  - **`ActiveDataProvider`** — данные из `ActiveQuery`/`Query` (самое частое).
  - **`ArrayDataProvider`** — данные уже в виде PHP-массива.
  - **`SqlDataProvider`** — сырой SQL.
- Главное преимущество: **одна и та же абстракция** работает с любыми источниками — виджеты просто рендерят то, что им дали.',
                'code_example' => 'use yii\\data\\ActiveDataProvider;

$dataProvider = new ActiveDataProvider([
    \'query\' => User::find()->where([\'status\' => 1]),
    \'pagination\' => [\'pageSize\' => 20],
    \'sort\' => [\'defaultOrder\' => [\'created_at\' => SORT_DESC]],
]);

// В контроллере
return $this->render(\'index\', [\'dataProvider\' => $dataProvider]);',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.data_provider',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как настроить пагинацию у ActiveDataProvider в Yii2?',
                'answer' => 'Пагинация настраивается через свойство **`pagination`** — массив или объект `yii\\data\\Pagination`.

- **`pageSize`** — размер страницы (по умолчанию 20).
- **`pageSizeLimit`** — `[min, max]` для защиты от слишком больших страниц через `?per-page=999999`.
- **`pageParam`** — имя GET-параметра страницы (по умолчанию `page`).
- **`pageSizeParam`** — имя параметра размера (по умолчанию `per-page`).
- Чтобы **отключить** пагинацию: `pagination => false` — провайдер вернёт все строки.
- Под капотом провайдер делает 2 SQL: основной с `LIMIT/OFFSET` и `COUNT(*)` для общего числа.',
                'code_example' => 'use yii\\data\\ActiveDataProvider;

$dataProvider = new ActiveDataProvider([
    \'query\' => User::find(),
    \'pagination\' => [
        \'pageSize\' => 20,
        \'pageSizeLimit\' => [1, 100],
    ],
]);

// Отключить пагинацию (для экспорта)
$exportProvider = new ActiveDataProvider([
    \'query\' => User::find(),
    \'pagination\' => false,
]);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.data_provider',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как настроить сортировку (sort) в ActiveDataProvider в Yii2?',
                'answer' => 'Сортировка настраивается через свойство **`sort`** — массив с опциями или объект `yii\\data\\Sort`.

- **`attributes`** — список **допустимых полей**, по которым можно сортировать. Защищает от `?sort=password_hash`.
- **`defaultOrder`** — сортировка по умолчанию, если пользователь ничего не выбрал.
- Можно задать **виртуальный атрибут** через массив с `asc`/`desc`-выражениями для SQL.
- В URL `?sort=email` — по возрастанию, `?sort=-email` — по убыванию.
- `sort => false` — полностью отключить сортировку.',
                'code_example' => 'use yii\\data\\ActiveDataProvider;
use yii\\db\\Expression;

$dataProvider = new ActiveDataProvider([
    \'query\' => User::find(),
    \'sort\' => [
        \'attributes\' => [
            \'id\',
            \'email\',
            \'created_at\',
            // виртуальный атрибут: сортировка по длине email
            \'email_length\' => [
                \'asc\' => [new Expression(\'LENGTH(email)\') => SORT_ASC],
                \'desc\' => [new Expression(\'LENGTH(email)\') => SORT_DESC],
                \'label\' => \'Длина email\',
            ],
        ],
        \'defaultOrder\' => [\'created_at\' => SORT_DESC],
    ],
]);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.data_provider',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что возвращают getModels(), getTotalCount(), getKeys() у DataProvider в Yii2?',
                'answer' => 'Это три «терминальных» метода DataProvider — для **ручного** перебора, тестов, кастомных представлений.

- **`getModels()`** — массив моделей **текущей страницы**. Триггерит SQL-запрос (с `LIMIT/OFFSET`).
- **`getTotalCount()`** — общее число записей **без учёта пагинации**. Триггерит `SELECT COUNT(*)`.
- **`getKeys()`** — массив **ключей** для каждой модели текущей страницы. По умолчанию это PK; можно настроить через `key` (имя поля или callable).
- Виджеты `GridView`/`ListView` дёргают именно эти методы — никакой магии.',
                'code_example' => '$dataProvider = new \\yii\\data\\ActiveDataProvider([
    \'query\' => User::find()->where([\'status\' => 1]),
    \'pagination\' => [\'pageSize\' => 20],
    \'key\' => \'email\', // ключи будут email\'ами
]);

$users = $dataProvider->getModels();   // 20 моделей
$total = $dataProvider->getTotalCount(); // например 1543
$keys  = $dataProvider->getKeys();     // [\'a@b.c\', \'c@d.e\', ...]

foreach ($users as $i => $user) {
    echo $keys[$i], \' -> \', $user->email, "\\n";
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.data_provider',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое SearchModel паттерн в Yii2 и как его генерирует Gii?',
                'answer' => '**SearchModel** — это отдельная **подмодель** для формы поиска/фильтрации в `GridView`. Gii генерирует её при создании CRUD.

- Наследуется от основной модели (например, `UserSearch extends User`), но с **другим набором rules** — все поля `safe` для фильтра.
- Метод **`search($params)`** возвращает `ActiveDataProvider`, в котором уже применены условия из формы.
- Передаётся в `GridView::widget([\'filterModel\' => $searchModel, ...])` — над каждой колонкой появится поле ввода.
- Удобно отделить **домен** (валидация, бизнес-правила) от **поиска** (свободные комбинации фильтров).',
                'code_example' => 'class UserSearch extends User
{
    public function rules()
    {
        return [
            [[\'id\', \'status\'], \'integer\'],
            [[\'email\', \'username\'], \'safe\'],
        ];
    }

    public function search($params)
    {
        $query = User::find();
        $dataProvider = new \\yii\\data\\ActiveDataProvider([
            \'query\' => $query,
            \'pagination\' => [\'pageSize\' => 20],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            \'id\' => $this->id,
            \'status\' => $this->status,
        ]);
        $query->andFilterWhere([\'like\', \'email\', $this->email])
              ->andFilterWhere([\'like\', \'username\', $this->username]);

        return $dataProvider;
    }
}',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.data_provider',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как использовать GridView::widget с DataProvider в Yii2?',
                'answer' => '**`GridView::widget()`** — виджет таблицы со столбцами, сортировкой, пагинацией, фильтрами.

- Принимает массив опций — главное: **`dataProvider`** и **`columns`**.
- Если `columns` не указать — выведутся все атрибуты модели.
- **`filterModel`** — модель формы поиска (обычно SearchModel), включает поля фильтрации в шапке.
- **`rowOptions`** — callback для атрибутов `<tr>` (классы, data-*).
- Под капотом сам рендерит сортировочные ссылки в `<th>` (если `sort` настроен).',
                'code_example' => 'use yii\\grid\\GridView;
use yii\\helpers\\Html;

echo GridView::widget([
    \'dataProvider\' => $dataProvider,
    \'filterModel\' => $searchModel,
    \'columns\' => [
        [\'class\' => \'yii\\\\grid\\\\SerialColumn\'],
        \'id\',
        \'email:email\',
        \'username\',
        [
            \'attribute\' => \'status\',
            \'value\' => fn($m) => $m->status === 1 ? \'Active\' : \'Disabled\',
        ],
        \'created_at:datetime\',
        [\'class\' => \'yii\\\\grid\\\\ActionColumn\'],
    ],
]);',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'yii2.data_provider',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Какие типы колонок есть в GridView Yii2 (DataColumn, ActionColumn, SerialColumn, CheckboxColumn)?',
                'answer' => 'У `GridView` есть несколько готовых **классов колонок**, каждый со своей задачей.

| Класс | Назначение |
|---|---|
| `DataColumn` (по умолчанию) | Атрибут модели. Поддерживает `value`, `format`, `filter` |
| `SerialColumn` | Порядковый номер строки (с учётом пагинации) |
| `ActionColumn` | Кнопки view/update/delete с настраиваемыми ссылками |
| `CheckboxColumn` | Чекбокс для bulk-операций (`<input type="checkbox">`) |
| `RadioButtonColumn` | Радиокнопка для выбора одной строки |

- Краткая форма `\'email:email\'` — это `DataColumn` с `format=\'email\'`.
- В каждой колонке можно переопределить `header`, `value`, `contentOptions`, `headerOptions`.',
                'code_example' => 'echo \\yii\\grid\\GridView::widget([
    \'dataProvider\' => $dataProvider,
    \'columns\' => [
        [\'class\' => \'yii\\\\grid\\\\SerialColumn\'],
        [\'class\' => \'yii\\\\grid\\\\CheckboxColumn\'],
        \'id\',
        \'email:email\',
        [
            \'attribute\' => \'status\',
            \'value\' => fn($m) => $m->statusLabel(),
            \'filter\' => [1 => \'Active\', 0 => \'Disabled\'],
        ],
        [
            \'class\' => \'yii\\\\grid\\\\ActionColumn\',
            \'template\' => \'{view} {update} {delete}\',
        ],
    ],
]);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.data_provider',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как работает filterModel в GridView Yii2 (фильтрация по колонкам)?',
                'answer' => 'Если в `GridView::widget()` передать **`filterModel`** — в первой строке таблицы под `<th>` появятся **поля ввода**.

- Yii рендерит фильтр на основе типа атрибута и валидации в модели.
- При submit (или AJAX) данные летят в URL как `?UserSearch[email]=foo&UserSearch[status]=1`.
- В контроллере вызывается **`$searchModel->search(Yii::$app->request->queryParams)`** — он возвращает уже **отфильтрованный** `DataProvider`.
- В Yii2 для безопасного применения фильтров используется **`andFilterWhere()`** — он **игнорирует пустые** значения, чтобы пустое поле не превращалось в `WHERE col = \'\'`.',
                'code_example' => '// Action в контроллере
public function actionIndex()
{
    $searchModel = new UserSearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    return $this->render(\'index\', [
        \'searchModel\' => $searchModel,
        \'dataProvider\' => $dataProvider,
    ]);
}

// В представлении
echo \\yii\\grid\\GridView::widget([
    \'dataProvider\' => $dataProvider,
    \'filterModel\' => $searchModel,
    \'columns\' => [\'id\', \'email\', \'status\'],
]);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.data_provider',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Что такое ListView::widget в Yii2 и чем он отличается от GridView?',
                'answer' => '**`ListView`** — рендер коллекции через **шаблон-вьюху** для каждой модели, в отличие от табличного `GridView`.

- Принимает **`dataProvider`** и **`itemView`** — путь к partial-вьюхе (или callable).
- В шаблоне доступны: `$model`, `$key`, `$index`, `$widget`.
- Подходит для **карточек товаров**, **списка постов блога**, **ленты новостей** — когда таблица не нужна.
- Делит общую логику с `GridView` (`sorter`, `pager`, `summary`).
- Опции `itemOptions`, `viewParams`, `separator` позволяют тонко настроить вывод.',
                'code_example' => 'echo \\yii\\widgets\\ListView::widget([
    \'dataProvider\' => $dataProvider,
    \'itemView\' => \'_post_card\',
    \'itemOptions\' => [\'class\' => \'col-md-4\'],
    \'separator\' => \'\',
    \'viewParams\' => [\'showAuthor\' => true],
]);

// views/post/_post_card.php
/** @var app\\models\\Post $model */
?>
<div class="card">
    <h3><?= \\yii\\helpers\\Html::encode($model->title) ?></h3>
    <p><?= \\yii\\helpers\\Html::encode($model->excerpt) ?></p>
</div>',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.data_provider',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Как использовать LinkPager отдельно от GridView/ListView в Yii2?',
                'answer' => '**`LinkPager::widget()`** — самостоятельный пагинатор. Принимает объект `yii\\data\\Pagination`.

- Полезно, когда вы рендерите список **вручную** (например, в кастомной вёрстке), но всё ещё хотите готовые ссылки страниц.
- Получить объект `Pagination` из любого `DataProvider`: `$dataProvider->getPagination()`.
- Можно создать `Pagination` вручную, передав `totalCount` — тогда LIMIT/OFFSET извлекать из `$pagination->offset` / `$pagination->limit`.
- Опции: `firstPageLabel`, `lastPageLabel`, `nextPageLabel`, `prevPageLabel`, `maxButtonCount`.',
                'code_example' => 'use yii\\data\\Pagination;
use yii\\widgets\\LinkPager;

$query = User::find()->where([\'status\' => 1]);
$totalCount = $query->count();

$pagination = new Pagination([
    \'totalCount\' => $totalCount,
    \'pageSize\' => 20,
]);

$users = $query->offset($pagination->offset)
    ->limit($pagination->limit)
    ->all();

// В представлении
foreach ($users as $user) {
    echo $user->email, \'<br>\';
}

echo LinkPager::widget([\'pagination\' => $pagination]);',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'yii2.data_provider',
            ],
            [
                'category' => 'Yii2',
                'question' => 'Чем ArrayDataProvider и SqlDataProvider отличаются от ActiveDataProvider в Yii2?',
                'answer' => 'Три провайдера для **разных источников данных**, но с одинаковым интерфейсом.

| Провайдер | Источник | Особенности |
|---|---|---|
| **`ActiveDataProvider`** | `ActiveQuery` / `Query` | Сам делает `COUNT(*)` и `LIMIT/OFFSET`; работает с моделями |
| **`ArrayDataProvider`** | Массив PHP (`allModels`) | Сортирует и пагинирует **в памяти** — все данные уже должны быть |
| **`SqlDataProvider`** | Сырой SQL (`sql`, `params`) | Нужно **самому** указать `totalCount` (отдельный SQL для COUNT) |

- `ArrayDataProvider` — для небольших коллекций (API-ответы внешнего сервиса, агрегаты).
- `SqlDataProvider` — для сложных запросов, которые не описать через Query Builder (рекурсивные CTE, оконные функции).
- `ActiveDataProvider` — самый частый выбор.',
                'code_example' => 'use yii\\data\\ArrayDataProvider;
use yii\\data\\SqlDataProvider;

// 1) ArrayDataProvider — из внешнего API
$items = json_decode(file_get_contents(\'https://api.example.com/products\'), true);

$arrayProvider = new ArrayDataProvider([
    \'allModels\' => $items,
    \'sort\' => [\'attributes\' => [\'price\', \'name\']],
    \'pagination\' => [\'pageSize\' => 20],
]);

// 2) SqlDataProvider — сложный SQL
$count = Yii::$app->db->createCommand(\'SELECT COUNT(*) FROM user WHERE status=1\')->queryScalar();

$sqlProvider = new SqlDataProvider([
    \'sql\' => \'SELECT * FROM user WHERE status = :s\',
    \'params\' => [\':s\' => 1],
    \'totalCount\' => $count,
    \'pagination\' => [\'pageSize\' => 20],
]);',
                'code_language' => 'php',
                'difficulty' => 4,
                'topic' => 'yii2.data_provider',
            ],
        ];
    }
}
