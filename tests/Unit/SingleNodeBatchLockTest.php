<?php

namespace Tests\Unit;

use App\Support\Componenti\SingleNodeBatchLock;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class SingleNodeBatchLockTest extends TestCase
{
    public function test_execute_runs_and_releases_on_success(): void
    {
        $acquireCalls = 0;
        $releaseCalls = 0;

        $lock = new SingleNodeBatchLock(
            function () use (&$acquireCalls): bool {
                $acquireCalls++;

                return true;
            },
            function () use (&$releaseCalls): void {
                $releaseCalls++;
            }
        );

        $result = $lock->execute(fn () => 'ok');

        $this->assertSame('ok', $result);
        $this->assertSame(1, $acquireCalls);
        $this->assertSame(1, $releaseCalls);
    }

    public function test_execute_releases_even_when_critical_section_throws(): void
    {
        $releaseCalls = 0;

        $lock = new SingleNodeBatchLock(
            fn () => true,
            function () use (&$releaseCalls): void {
                $releaseCalls++;
            }
        );

        try {
            $lock->execute(function (): void {
                throw new RuntimeException('boom');
            });
            $this->fail('Expected RuntimeException not thrown.');
        } catch (RuntimeException $exception) {
            $this->assertSame('boom', $exception->getMessage());
        }

        $this->assertSame(1, $releaseCalls);
    }

    public function test_execute_fails_when_lock_not_acquired_without_releasing(): void
    {
        $releaseCalls = 0;

        $lock = new SingleNodeBatchLock(
            fn () => false,
            function () use (&$releaseCalls): void {
                $releaseCalls++;
            }
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(SingleNodeBatchLock::LOCK_NOT_ACQUIRED);

        try {
            $lock->execute(fn () => 'never');
        } finally {
            $this->assertSame(0, $releaseCalls);
        }
    }
}
