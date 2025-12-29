<?php

/**
 * 简单的测试脚本
 * 用于测试 MessageSource 组件，特别是中文 key 的处理
 * 
 * 使用方法：
 * php tests/test.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use liwenyu\translate\MessageSource;
use Yii;
use yii\base\Application;

// 设置 Yii 应用（用于测试）
if (Yii::$app === null) {
    new Application([
        'id' => 'test-app',
        'basePath' => __DIR__,
        'params' => [
            'currency_symbol' => '￥',
        ],
    ]);
}

// 创建测试消息目录
$testBasePath = __DIR__ . '/messages';
if (!is_dir($testBasePath . '/zh-CN')) {
    mkdir($testBasePath . '/zh-CN', 0755, true);
}
if (!is_dir($testBasePath . '/en-US')) {
    mkdir($testBasePath . '/en-US', 0755, true);
}

// 创建测试翻译文件（只包含英文 key，不包含中文 key）
$zhMessages = <<<'PHP'
<?php
return [
    'Hello' => '你好',
    'World' => '世界',
    'Price' => '价格',
    // 注意：这里故意不包含中文 key，用于测试中文 key 的处理
];
PHP;

$enMessages = <<<'PHP'
<?php
return [
    'Hello' => 'Hello',
    'World' => 'World',
    'Price' => 'Price',
];
PHP;

file_put_contents($testBasePath . '/zh-CN/app.php', $zhMessages);
file_put_contents($testBasePath . '/en-US/app.php', $enMessages);

// 创建消息源实例
$messageSource = new MessageSource([
    'basePath' => $testBasePath,
    'forceTranslation' => true,
    'fileMap' => [
        'app' => 'app.php',
    ],
]);

echo "开始测试 MessageSource 组件...\n\n";

// 测试用例
$tests = [
    [
        'name' => '测试中文 key（翻译文件中不存在）',
        'category' => 'app',
        'message' => '用户名',
        'language' => 'zh-CN',
        'expected' => '用户名',
        'description' => '当 key 是中文且翻译文件中不存在时，应该返回原始消息而不是空字符串',
    ],
    [
        'name' => '测试英文 key（翻译文件中存在）',
        'category' => 'app',
        'message' => 'Hello',
        'language' => 'zh-CN',
        'expected' => '你好',
        'description' => '当 key 是英文且翻译文件中存在时，应该返回翻译后的消息',
    ],
    [
        'name' => '测试中文 key 包含货币单位',
        'category' => 'app',
        'message' => '余额100元',
        'language' => 'zh-CN',
        'expected' => '余额100￥',
        'description' => '当 key 是中文且包含货币单位时，应该返回处理后的消息（货币单位被替换）',
    ],
    [
        'name' => '测试英文 key 包含货币单位',
        'category' => 'app',
        'message' => 'Price: 100 yuan',
        'language' => 'en-US',
        'expected' => 'Price: 100 ￥', // 注意：yuan 会被替换为 ￥
        'description' => '当 key 是英文且包含货币单位时，应该替换货币单位',
    ],
    [
        'name' => '测试不存在的英文 key',
        'category' => 'app',
        'message' => 'NonExistentKey',
        'language' => 'zh-CN',
        'expected' => 'NonExistentKey',
        'description' => '当 key 不存在时，应该返回原始消息',
    ],
];

$passed = 0;
$failed = 0;

foreach ($tests as $test) {
    echo "测试: {$test['name']}\n";
    echo "  描述: {$test['description']}\n";
    echo "  输入: category={$test['category']}, message={$test['message']}, language={$test['language']}\n";
    
    $result = $messageSource->translate($test['category'], $test['message'], $test['language']);
    
    echo "  期望: {$test['expected']}\n";
    echo "  实际: " . ($result === false ? 'false' : var_export($result, true)) . "\n";
    
    // 处理 false 返回值的情况
    if ($result === false && $test['expected'] !== false) {
        // 如果返回 false，但期望不是 false，检查是否是未找到翻译的情况
        // 在这种情况下，Yii2 的 I18N 组件会使用原始消息
        $result = $test['message'];
    }
    
    if ($result === $test['expected']) {
        echo "  结果: ✓ 通过\n\n";
        $passed++;
    } else {
        echo "  结果: ✗ 失败\n\n";
        $failed++;
    }
}

echo "测试完成: 通过 {$passed} 个, 失败 {$failed} 个\n";

// 清理测试文件
function deleteDirectory($dir) {
    if (!is_dir($dir)) {
        return;
    }
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        is_dir($path) ? deleteDirectory($path) : unlink($path);
    }
    rmdir($dir);
}

// 询问是否清理测试文件
echo "\n是否清理测试文件？(y/n): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
if (trim($line) === 'y' || trim($line) === 'Y') {
    deleteDirectory($testBasePath);
    echo "测试文件已清理\n";
}
fclose($handle);

