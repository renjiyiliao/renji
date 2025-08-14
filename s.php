<?php
/**
 * API 内容展示框架
 * 修复了常见错误并优化了代码结构
 * 适用于 Git 存储库部署
 */

// 开启错误报告（开发环境使用）
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 定义常量
define('API_URL', 'https://XXX.com/api');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API 内容框架</title>
    <style>
        .frame-container {
            border: 2px solid #ccc;
            border-radius: 5px;
            padding: 10px;
            margin: 20px auto;
            max-width: 90%;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .frame-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        
        .content-frame {
            width: 100%;
            height: 500px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        
        .loading {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        
        .error {
            color: red;
            padding: 20px;
            text-align: center;
        }
        
        .browser-tip {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        
        .browser-tip h3 {
            color: #d9534f;
            margin-top: 0;
        }
        
        .browser-tip ol {
            text-align: left;
            display: inline-block;
            margin: 15px auto;
            padding-left: 20px;
        }
        
        .browser-tip li {
            margin-bottom: 8px;
        }
        
        .browser-tip .btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #337ab7;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="frame-container">
        <div class="frame-title">内容展示框架</div>
        
        <?php
        /**
         * 检测是否在微信或QQ内置浏览器中打开
         * @return string|false 返回 'wechat', 'qq' 或 false
         */
        function isInWeChatOrQQ() {
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            if (preg_match('/MicroMessenger/i', $userAgent)) {
                return 'wechat';
            }
            if (preg_match('/QQ\//i', $userAgent)) {
                return 'qq';
            }
            return false;
        }
        
        /**
         * 获取API内容URL
         * @return string|null 返回URL或null
         */
        function getContentUrl() {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5, // 5秒超时
                    'ignore_errors' => true
                ]
            ]);
            
            try {
                $response = file_get_contents(API_URL, false, $context);
                if ($response === false) {
                    throw new Exception("API请求失败");
                }
                
                $data = json_decode($response, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception("JSON解析错误: " . json_last_error_msg());
                }
                
                if (!isset($data['code']) || $data['code'] != 0 || empty($data['data'])) {
                    $message = $data['message'] ?? '未知错误';
                    throw new Exception("API返回错误: " . $message);
                }
                
                return $data['data'];
            } catch (Exception $e) {
                error_log("API请求错误: " . $e->getMessage());
                return null;
            }
        }
        
        // 主逻辑
        try {
            $browser = isInWeChatOrQQ();
            
            if ($browser) {
                // 在微信或QQ中打开，显示提示信息
                echo '<div class="browser-tip">';
                echo '<h3>提示</h3>';
                echo '<p>检测到您在' . ($browser === 'wechat' ? '微信' : 'QQ') . '中打开，部分内容可能无法正常显示。</p>';
                echo '<p>请按照以下步骤操作：</p>';
                echo '<ol>';
                echo '<li>点击右上角/左上角的 <strong>...</strong> 或 <strong>⋮</strong> 按钮</li>';
                echo '<li>选择 <strong>"在浏览器中打开"</strong> 选项</li>';
                echo '<li>在系统浏览器中查看完整内容</li>';
                echo '</ol>';
                
                $currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                echo '<a href="'.htmlspecialchars($currentUrl, ENT_QUOTES, 'UTF-8').'" class="btn">点击尝试在浏览器中打开</a>';
                
                echo '</div>';
            } else {
                // 不在微信或QQ中打开，正常显示内容
                $contentUrl = getContentUrl();
                
                if ($contentUrl) {
                    if (filter_var($contentUrl, FILTER_VALIDATE_URL)) {
                        echo '<iframe class="content-frame" src="' . htmlspecialchars($contentUrl, ENT_QUOTES, 'UTF-8') . '" frameborder="0" allowfullscreen></iframe>';
                    } else {
                        throw new Exception("获取的内容URL无效");
                    }
                } else {
                    throw new Exception("无法获取内容URL");
                }
            }
        } catch (Exception $e) {
            echo '<div class="error">错误: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</div>';
        }
        ?>
    </div>
</body>
</html>
