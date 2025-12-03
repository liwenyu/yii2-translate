# Yii2 Translate - 货币替换消息源组件

一个用于 Yii2 框架的国际化消息源组件，可以自动替换翻译结果中的货币单位并填充货币符号。

## 功能特性

- ✅ 自动将翻译结果中的货币单位（如"元"、"yuan"等）替换为 `{currency}` 变量
- ✅ 自动将 `Yii::$app->params['currency_symbol']` 的值填充到 `{currency}` 中
- ✅ 支持中英文各种形式的货币单位
- ✅ 使用正则表达式确保只替换独立的货币单位，不会误替换其他文本
- ✅ 可配置货币单位列表、货币符号键名等
- ✅ 无需修改应用程序代码，只需在配置文件中替换 class 即可

## 安装

使用 Composer 安装：

```bash
composer require liwenyu/translate
```

## 使用方法

### 基本配置

在 `common/config/main.php`（或相应的配置文件中）配置 i18n 组件：

```php
return [
    'components' => [
        'i18n' => [
            'translations' => [
                'app' => [
                    // 关键：将 class 替换为自定义类
                    'class' => 'liwenyu\translate\MessageSource',
                    'basePath' => '@app/messages',
                    'forceTranslation' => true,
                    'fileMap' => [
                        'app' => 'app.php',
                    ],
                ],
            ],
        ],
    ],
];
```

### 高级配置

可以自定义货币单位列表、货币符号键名等：

```php
return [
    'components' => [
        'i18n' => [
            'translations' => [
                'app' => [
                    'class' => 'liwenyu\translate\CurrencyMessageSource',
                    'basePath' => '@app/messages',
                    'forceTranslation' => true,
                    'fileMap' => [
                        'app' => 'app.php',
                    ],
                    // 可选：自定义需要替换的货币单位
                    // 如果不配置，使用默认值：['元', 'yuan', 'yuans', 'Yuan', 'Yuans']
                    'currencyUnits' => [
                        '元',      // 中文：元
                        'yuan',    // 英文：yuan
                        'yuans',   // 英文复数：yuans
                        'Yuan',    // 首字母大写
                        'Yuans',   // 首字母大写复数
                        'RMB',     // 人民币
                        'CNY',     // 中国元
                    ],
                    // 可选：是否启用货币单位替换（默认 true）
                    'enableCurrencyReplace' => true,
                    // 可选：params 中货币符号的键名（默认 'currency_symbol'）
                    'currencySymbolKey' => 'currency_symbol',
                    // 可选：默认货币符号（当 params 中不存在时使用，默认 '￥'）
                    'defaultCurrencySymbol' => '￥',
                ],
            ],
        ],
    ],
];
```

### 配置货币符号

在 `common/config/params.php`（或相应的配置文件中）配置货币符号：

```php
return [
    'currency_symbol' => '￥',  // 或 '$', '€', '£' 等
];
```

## 工作原理

1. **货币单位替换**：组件会扫描翻译结果，将配置的货币单位（如"元"、"yuan"等）替换为 `{currency}` 变量
2. **货币符号填充**：将 `{currency}` 变量替换为实际的货币符号（从 `Yii::$app->params['currency_symbol']` 读取）

### 示例

假设翻译文件中有一条消息：

```
"余额不足，当前余额为 {balance} 元"
```

经过组件处理后，如果 `Yii::$app->params['currency_symbol']` 为 `'$'`，则最终输出：

```
"余额不足，当前余额为 {balance} $"
```

## 配置选项

| 选项                    | 类型   | 默认值                                     | 说明                                     |
| ----------------------- | ------ | ------------------------------------------ | ---------------------------------------- |
| `currencyUnits`         | array  | `['元', 'yuan', 'yuans', 'Yuan', 'Yuans']` | 需要替换的货币单位列表                   |
| `enableCurrencyReplace` | bool   | `true`                                     | 是否启用货币单位替换                     |
| `currencySymbolKey`     | string | `'currency_symbol'`                        | params 中货币符号的键名                  |
| `defaultCurrencySymbol` | string | `'￥'`                                     | 默认货币符号（当 params 中不存在时使用） |

## 注意事项

1. 组件使用正则表达式进行替换，对于中文使用特定的匹配规则，对于英文使用单词边界 `\b`，确保不会误替换其他文本
2. 如果 `Yii::$app->params['currency_symbol']` 不存在，将使用 `defaultCurrencySymbol` 的值
3. 可以通过设置 `enableCurrencyReplace` 为 `false` 来禁用货币替换功能

## 许可证

MIT License

## 作者

- liwenyu
- Email: liwenyu66@126.com

## 更新日志

### 1.0.0 (2025-12-03)

- 初始版本发布
- 支持货币单位自动替换
- 支持货币符号自动填充
