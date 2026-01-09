<?php

/**
 * Yii2 货币替换消息源组件
 * 
 * 功能：
 * 1. 将翻译结果中的货币单位（如"元"、"yuan"等）替换为 {currency} 变量
 * 2. 自动将 Yii::$app->params['currency_symbol'] 的值填充到 {currency} 中
 * 
 * 使用方式：
 * 在配置文件中将 i18n 组件的 class 替换为本类即可
 * 
 * @package yii2-translate
 * @author liwenyu
 * @email liwenyu66@126.com
 * @since 2025-12-03
 */

namespace liwenyu\translate;

use yii\i18n\PhpMessageSource;
use Yii;

class MessageSource extends PhpMessageSource
{
    /**
     * 需要替换为 {currency} 的货币单位列表
     * 支持中英文的各种形式
     * 
     * @var array
     */
    public $currencyUnits = [
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
    ];

    /**
     * 是否启用货币单位替换
     * 
     * @var bool
     */
    public $enableCurrencyReplace = true;

    /**
     * params 中货币符号的键名
     * 
     * @var string
     */
    public $currencySymbolKey = 'currency_symbol';

    /**
     * 默认货币符号（当 params 中不存在时使用）
     * 
     * @var string
     */
    public $defaultCurrencySymbol = '￥';

    /**
     * 重写翻译方法，在返回结果前替换货币单位并填充货币符号
     * 
     * @param string $category 翻译分类
     * @param string $message 原始消息
     * @param string $language 语言
     * @return string 翻译后的消息（已替换货币单位并填充货币符号）
     */
    protected function translateMessage($category, $message, $language)
    {
        // 调用父类方法获取翻译结果
        $translated = parent::translateMessage($category, $message, $language);

        // 如果翻译结果为 false（未找到翻译）或空字符串，但原始消息不为空
        // 说明可能是中文 key 未找到翻译，此时应该使用原始消息进行处理
        if (($translated === false || $translated === '') && $message !== '') {
            $translated = $message;
        }

        // 如果翻译结果仍然是 false 或空，直接返回
        if ($translated === false || $translated === '') {
            return $translated;
        }

        // 如果启用货币单位替换，则进行处理
        if ($this->enableCurrencyReplace) {
            // 步骤1：将货币单位替换为 {currency} 变量
            $translated = $this->replaceCurrencyUnits($translated);

            // 步骤2：将 {currency} 变量替换为实际的货币符号
            $translated = $this->fillCurrencySymbol($translated);
        }

        return $translated;
    }

    /**
     * 将翻译结果中的货币单位替换为 {currency} 变量
     * 
     * 使用正则表达式确保只替换独立的货币单位，不会误替换其他文本
     * 
     * @param string $text 待替换的文本
     * @return string 替换后的文本
     */
    protected function replaceCurrencyUnits($text)
    {
        if (empty($this->currencyUnits)) {
            return $text;
        }

        // 对每个货币单位进行替换
        foreach ($this->currencyUnits as $unit) {
            // 转义特殊字符
            $escapedUnit = preg_quote($unit, '/');

            // 判断是否为中文字符（简单判断：检查是否包含中文字符）
            $isChinese = preg_match('/[\x{4e00}-\x{9fa5}]/u', $unit);

            if ($isChinese) {
                // 对于中文，匹配前面是数字、空格、}、] 或字符串开头，后面是标点、空格、换行、HTML标签或字符串结尾
                // 这样可以避免替换"元素"中的"元"，但能匹配"100元"、"余额500元"、单独的"元"以及":{user_gift}元<"等
                $pattern = '/(?<=^|[\d\s}\]\]])' . $escapedUnit . '(?=[\s，。！？、；：,\.!?;:\n\r<>]|$)/u';
            } else {
                // 对于英文，使用单词边界 \b
                // 这样可以避免替换"yuanbao"中的"yuan"，但能匹配"100 yuan"、"price yuan"等
                $pattern = '/\b' . $escapedUnit . '\b/u';
            }

            $text = preg_replace($pattern, '{currency}', $text);
        }

        return $text;
    }

    /**
     * 将 {currency} 变量替换为实际的货币符号
     * 
     * @param string $text 待替换的文本
     * @return string 替换后的文本
     */
    protected function fillCurrencySymbol($text)
    {
        // 从 params 中获取货币符号
        $currencySymbol = $this->getCurrencySymbol();

        // 将 {currency} 替换为实际的货币符号
        $text = str_replace('{currency}', $currencySymbol, $text);

        return $text;
    }

    /**
     * 获取货币符号
     * 从 Yii::$app->params 中读取，如果不存在则返回默认值
     * 
     * @return string 货币符号
     */
    protected function getCurrencySymbol()
    {
        // 尝试从 params 中获取
        if (isset(Yii::$app->params[$this->currencySymbolKey])) {
            return Yii::$app->params[$this->currencySymbolKey];
        }

        // 如果 params 中不存在，返回默认值
        return $this->defaultCurrencySymbol;
    }
}
