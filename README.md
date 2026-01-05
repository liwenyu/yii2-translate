# Yii2 Translate - 货币替换消息源组件

一个用于 Yii2 框架的国际化消息源组件，可以自动替换翻译结果中的货币单位并填充货币符号。

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D7.0-blue.svg)](https://php.net)
[![Yii2 Version](https://img.shields.io/badge/yii2-%3E%3D2.0.0-blue.svg)](https://www.yiiframework.com/)

## 功能特性

- ✅ 自动将翻译结果中的货币单位（如"元"、"yuan"等）替换为 `{currency}` 变量
- ✅ 自动将 `Yii::$app->params['currency_symbol']` 的值填充到 `{currency}` 中
- ✅ 支持中英文各种形式的货币单位（包括单独的货币单位 value）
- ✅ 支持翻译文件中单独的货币单位 value（如 `'currency' => '元'` 会被正确替换）
- ✅ 使用正则表达式确保只替换独立的货币单位，不会误替换其他文本（如"元素"中的"元"不会被替换）
- ✅ 可配置货币单位列表、货币符号键名等
- ✅ 无需修改应用程序代码，只需在配置文件中替换 class 即可

## 安装

使用 Composer 安装：

```bash
composer require liwenyu/yii2-translate
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
                    'class' => 'liwenyu\translate\MessageSource',
                    'basePath' => '@app/messages',
                    'forceTranslation' => true,
                    'fileMap' => [
                        'app' => 'app.php',
                    ],
                    // 可选：自定义需要替换的货币单位
                    // 如果不配置，使用默认值（包含更多货币单位，见下方说明）
                    'currencyUnits' => [
                        '元',      // 中文：元
                        'yuan',    // 英文：yuan
                        'yuans',   // 英文复数：yuans
                        'Yuan',    // 首字母大写
                        'Yuans',   // 首字母大写复数
                        'dollar',  // 美元（英文）
                        'dollars', // 美元复数
                        'Dollar',  // 首字母大写
                        'Dollars', // 首字母大写复数
                        '$',       // 美元符号
                        'USD',     // 美元
                        'RMB',     // 人民币
                        'CNY',     // 中国元
                        '¥',       // 人民币符号
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

#### 示例 1：翻译消息中包含货币单位

假设翻译文件中有一条消息：

```php
// messages/zh-CN/app.php
return [
    'balance_insufficient' => '余额不足，当前余额为 {balance} 元',
];
```

经过组件处理后，如果 `Yii::$app->params['currency_symbol']` 为 `'$'`，则最终输出：

```
"余额不足，当前余额为 {balance} $"
```

#### 示例 2：翻译文件中单独的货币单位 value

如果翻译文件中 value 就是单独的货币单位：

```php
// messages/zh-CN/app.php
return [
    'currency' => '元',
];
```

当调用 `Yii::t('app', 'currency')` 时，组件会自动将 `'元'` 替换为配置的货币符号（如 `'￥'`），最终输出：

```
"￥"
```

#### 示例 3：各种场景

- `'100元'` → `'100￥'`（数字后的货币单位）
- `'余额500元'` → `'余额500￥'`（数字后的货币单位）
- `'元'` → `'￥'`（单独的货币单位）
- `'元素'` → `'元素'`（不会误替换，因为"元"后面不是标点或结尾）
- `'100 yuan'` → `'100 ￥'`（英文货币单位）

## 配置选项

| 选项                    | 类型   | 默认值                                     | 说明                                     |
| ----------------------- | ------ | ------------------------------------------ | ---------------------------------------- |
| `currencyUnits`         | array  | 包含多种货币单位（见代码注释）             | 需要替换的货币单位列表                   |
| `enableCurrencyReplace` | bool   | `true`                                     | 是否启用货币单位替换                     |
| `currencySymbolKey`     | string | `'currency_symbol'`                        | params 中货币符号的键名                  |
| `defaultCurrencySymbol` | string | `'￥'`                                     | 默认货币符号（当 params 中不存在时使用） |

**默认 `currencyUnits` 列表**：
- 中文：`'元'`
- 英文：`'yuan'`, `'yuans'`, `'Yuan'`, `'Yuans'`
- 美元：`'dollar'`, `'dollars'`, `'Dollar'`, `'Dollars'`, `'$'`, `'USD'`
- 人民币：`'RMB'`, `'CNY'`, `'¥'`

## 注意事项

1. 组件使用正则表达式进行替换，对于中文使用特定的匹配规则（支持字符串开头的货币单位），对于英文使用单词边界 `\b`，确保不会误替换其他文本
2. **支持单独的货币单位**：如果翻译文件中的 value 就是单独的货币单位（如 `'currency' => '元'`），会被正确替换为货币符号
3. **精确匹配**：组件会精确匹配独立的货币单位，例如：
   - ✅ `'100元'` → `'100￥'`（会被替换）
   - ✅ `'元'` → `'￥'`（会被替换）
   - ❌ `'元素'` → `'元素'`（不会被替换，因为"元"后面是"素"）
4. 如果 `Yii::$app->params['currency_symbol']` 不存在，将使用 `defaultCurrencySymbol` 的值
5. 可以通过设置 `enableCurrencyReplace` 为 `false` 来禁用货币替换功能
6. 组件继承自 `yii\i18n\PhpMessageSource`，支持所有父类的功能和配置选项

## 常见问题

### Q: 如何添加自定义的货币单位？

A: 在配置中的 `currencyUnits` 数组中添加即可：

```php
'currencyUnits' => [
    '元',
    'yuan',
    'RMB',
    'CNY',
    'dollar',  // 添加美元
    'dollars', // 添加美元复数
],
```

### Q: 如何禁用货币替换功能？

A: 设置 `enableCurrencyReplace` 为 `false`：

```php
'enableCurrencyReplace' => false,
```

### Q: 支持哪些货币符号？

A: 支持任何字符串，包括 `￥`、`$`、`€`、`£`、`¥` 等，只需在 `params` 中配置即可。

### Q: 翻译文件中的 value 就是单独的货币单位（如 `'currency' => '元'`）会被替换吗？

A: 是的！从 1.1.1 版本开始，组件支持替换翻译文件中单独的货币单位 value。例如：

```php
// messages/zh-CN/app.php
return [
    'currency' => '元',
];
```

当调用 `Yii::t('app', 'currency')` 时，会自动将 `'元'` 替换为配置的货币符号（如 `'￥'`）。

## 许可证

MIT License

## 作者

- liwenyu
- Email: liwenyu66@126.com

## 更新日志

### 1.1.1 (2026-01-05)

- 🐛 **修复**：修复翻译文件中单独的货币单位 value 无法被替换的问题
  - 修复正则表达式，支持匹配字符串开头的货币单位（如单独的 `'元'`）
  - 现在翻译文件中 `'currency' => '元'` 会被正确替换为货币符号
- ✨ **增强**：扩展默认货币单位列表，包含更多常见货币单位（美元、人民币等）
- ✅ **测试**：添加测试用例验证翻译文件中单独的货币单位场景

### 1.0.0 (2025-12-03)

- 初始版本发布
- 支持货币单位自动替换
- 支持货币符号自动填充
- 支持中英文各种形式的货币单位
- 可配置货币单位列表和货币符号

## 贡献

欢迎提交 Issue 和 Pull Request！

## 相关链接

- [GitHub 仓库](https://github.com/liwenyu/yii2-translate)
- [Yii2 官方文档](https://www.yiiframework.com/doc/guide/2.0/en)
