<?php

namespace plugin\example;

use app\exception\ApiException;
use app\sign\contract\SignPluginInterface;
use app\sign\dto\AccountProfile;
use app\sign\dto\HealthResult;
use app\sign\dto\PluginMetadata;
use app\sign\dto\SignContext;
use app\sign\dto\SignRecord;
use app\sign\dto\SignResult;

/** 不访问任何外部服务，只用于验证插件商店的打包、安装和执行流程。 */
final class ExamplePlugin implements SignPluginInterface
{
    public function metadata(): PluginMetadata
    {
        return new PluginMetadata('example', '示例插件', '1.0.0', '演示插件商店发布流程的示例签到', ['token']);
    }

    public function credentialRules(): array
    {
        return ['token' => ['required', 'string']];
    }

    public function validateAccount(array $credentials): AccountProfile
    {
        $token = trim((string)($credentials['token'] ?? ''));
        if ($token === '') {
            throw new ApiException('EXAMPLE_TOKEN_REQUIRED', '请填写示例 Token', 422);
        }
        return new AccountProfile(substr(hash('sha256', $token), 0, 16), '示例账号');
    }

    public function supportedActions(): array
    {
        return ['daily_sign'];
    }

    public function execute(SignContext $context): SignResult
    {
        $dryRun = (bool)($context->settings['dry_run'] ?? true);
        $record = new SignRecord(
            'example',
            $context->action,
            'success',
            message: $dryRun ? '预演模式：未执行真实签到' : '示例签到完成',
        );
        $context->report($record);
        return new SignResult('success', $record->message ?? '', [$record]);
    }

    public function healthCheck(): HealthResult
    {
        return new HealthResult(true, '示例插件可用');
    }
}
