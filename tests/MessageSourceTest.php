<?php

/**
 * MessageSource 测试用例
 * 
 * 测试翻译组件，特别是中文 key 的处理
 * 
 * @package yii2-translate
 * @author liwenyu
 * @email liwenyu66@126.com
 * @since 2025-12-03
 */

namespace liwenyu\translate\tests;

use liwenyu\translate\MessageSource;
use Yii;
use yii\base\Application;

/**
 * MessageSource 测试类
 */
class MessageSourceTest
{
    /**
     * 测试目录
     */
    private $testBasePath;

    /**
     * 测试消息源实例
     */
    private $messageSource;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->testBasePath = __DIR__ . '/messages';
        $this->setupYii();
        $this->setupMessageSource();
        $this->createTestMessages();
    }

    /**
     * 设置 Yii 应用（用于测试）
     */
    private function setupYii()
    {
        if (Yii::$app === null) {
            new Application([
                'id' => 'test-app',
                'basePath' => __DIR__,
                'params' => [
                    'currency_symbol' => '￥',
                ],
            ]);
        }
    }

    /**
     * 设置消息源
     */
    private function setupMessageSource()
    {
        $this->messageSource = new MessageSource([
            'basePath' => $this->testBasePath,
            'forceTranslation' => true,
            'fileMap' => [
                'app' => 'app.php',
            ],
        ]);
    }

    /**
     * 创建测试用的翻译文件
     */
    private function createTestMessages()
    {
        // 创建目录
        if (!is_dir($this->testBasePath . '/zh-CN')) {
            mkdir($this->testBasePath . '/zh-CN', 0755, true);
        }
        if (!is_dir($this->testBasePath . '/en-US')) {
            mkdir($this->testBasePath . '/en-US', 0755, true);
        }

        // 创建中文翻译文件（包含一些英文 key，但不包含中文 key）
        $zhMessages = <<<'PHP'
<?php
return [
    'Hello' => '你好',
    'World' => '世界',
    'Price' => '价格',
    'currency' => '元',  // 测试：翻译文件中 value 就是单独的货币单位
    // 注意：这里故意不包含中文 key，用于测试中文 key 的处理
];
PHP;

        // 创建英文翻译文件
        $enMessages = <<<'PHP'
<?php
return [
    'Hello' => 'Hello',
    'World' => 'World',
    'Price' => 'Price',
];
PHP;

        file_put_contents($this->testBasePath . '/zh-CN/app.php', $zhMessages);
        file_put_contents($this->testBasePath . '/en-US/app.php', $enMessages);
    }

    /**
     * 运行所有测试
     */
    public function runAllTests()
    {
        echo "开始运行 MessageSource 测试...\n\n";

        $tests = [
            'testChineseKey' => '测试中文 key 翻译',
            'testEnglishKey' => '测试英文 key 翻译',
            'testChineseKeyWithCurrency' => '测试中文 key 包含货币单位',
            'testEmptyTranslation' => '测试空翻译处理',
            'testCurrencyReplacement' => '测试货币单位替换',
            'testCurrencyValueInTranslationFile' => '测试翻译文件中 value 为单独的货币单位',
            'testCurrencyWithHtmlTag' => '测试HTML标签前的货币单位替换',
        ];

        $passed = 0;
        $failed = 0;

        foreach ($tests as $method => $description) {
            echo "测试: {$description}... ";
            try {
                $result = $this->$method();
                if ($result) {
                    echo "✓ 通过\n";
                    $passed++;
                } else {
                    echo "✗ 失败\n";
                    $failed++;
                }
            } catch (\Exception $e) {
                echo "✗ 异常: " . $e->getMessage() . "\n";
                $failed++;
            }
        }

        echo "\n测试完成: 通过 {$passed} 个, 失败 {$failed} 个\n";
    }

    /**
     * 测试中文 key 翻译
     * 这是修复的核心测试：当 key 是中文且翻译文件中没有对应条目时，应该返回原始消息而不是空字符串
     */
    public function testChineseKey()
    {
        // 测试中文 key（翻译文件中不存在）
        $result = $this->messageSource->translate('app', '用户名', 'zh-CN');
        
        // 应该返回原始消息，而不是空字符串
        // 注意：translate 方法可能返回 false，如果返回 false 则说明未找到翻译
        // 但我们的修复应该确保返回原始消息
        if ($result === false) {
            return false;
        }
        
        return $result === '用户名';
    }

    /**
     * 测试英文 key 翻译
     */
    public function testEnglishKey()
    {
        // 测试英文 key（翻译文件中存在）
        $result = $this->messageSource->translate('app', 'Hello', 'zh-CN');
        
        // 应该返回翻译后的消息
        if ($result === false) {
            return false;
        }
        
        return $result === '你好';
    }

    /**
     * 测试中文 key 包含货币单位
     */
    public function testChineseKeyWithCurrency()
    {
        // 测试中文 key 包含货币单位（翻译文件中不存在）
        $result = $this->messageSource->translate('app', '余额100元', 'zh-CN');
        
        // 应该返回处理后的消息（货币单位被替换）
        if ($result === false) {
            return false;
        }
        
        return $result === '余额100￥';
    }

    /**
     * 测试空翻译处理
     */
    public function testEmptyTranslation()
    {
        // 测试空字符串 key
        $result1 = $this->messageSource->translate('app', '', 'zh-CN');
        
        // 测试不存在的 key（英文）
        $result2 = $this->messageSource->translate('app', 'NonExistentKey', 'zh-CN');
        
        // 空字符串应该返回空字符串或 false
        // 不存在的 key 应该返回 false 或原始消息
        return ($result1 === '' || $result1 === false) && ($result2 === false || $result2 === 'NonExistentKey');
    }

    /**
     * 测试货币单位替换
     */
    public function testCurrencyReplacement()
    {
        // 测试包含货币单位的消息（使用存在的 key）
        $result1 = $this->messageSource->translate('app', 'Price: 100 yuan', 'en-US');
        $result2 = $this->messageSource->translate('app', '价格：100元', 'zh-CN');
        
        // 应该替换货币单位
        if ($result1 === false || $result2 === false) {
            return false;
        }
        
        return strpos($result1, '￥') !== false && strpos($result2, '￥') !== false;
    }

    /**
     * 测试翻译文件中 value 为单独的货币单位
     * 这是本次修复的核心测试：当翻译文件中 'currency' => '元' 时，返回的 '元' 应该被替换为货币符号
     */
    public function testCurrencyValueInTranslationFile()
    {
        // 测试翻译文件中 value 就是单独的货币单位
        $result = $this->messageSource->translate('app', 'currency', 'zh-CN');
        
        // 应该返回货币符号，而不是 '元'
        if ($result === false) {
            return false;
        }
        
        // 期望结果：'元' 被替换为 '￥'
        return $result === '￥';
    }

    /**
     * 测试HTML标签前的货币单位替换
     * 测试场景：:{user_gift}元< 这种情况应该被正确替换
     */
    public function testCurrencyWithHtmlTag()
    {
        // 测试包含HTML标签的场景
        $result1 = $this->messageSource->translate('app', ':{user_gift}元<', 'zh-CN');
        $result2 = $this->messageSource->translate('app', ':{user_gift}元>', 'zh-CN');
        $result3 = $this->messageSource->translate('app', '100元<', 'zh-CN');
        
        // 应该替换货币单位
        if ($result1 === false || $result2 === false || $result3 === false) {
            return false;
        }
        
        // 期望结果：货币单位被替换为货币符号
        return strpos($result1, '￥') !== false 
            && strpos($result2, '￥') !== false 
            && strpos($result3, '￥') !== false;
    }

    /**
     * 清理测试文件
     */
    public function cleanup()
    {
        if (is_dir($this->testBasePath)) {
            $this->deleteDirectory($this->testBasePath);
        }
    }

    /**
     * 递归删除目录
     */
    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}

// 如果直接运行此文件，执行测试
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    // 引入必要的文件
    require_once __DIR__ . '/../vendor/autoload.php';
    
    $test = new MessageSourceTest();
    $test->runAllTests();
    $test->cleanup();
}

