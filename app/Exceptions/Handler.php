<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
        'questura_password',
        'questura_wskey',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    protected function reportThrowable(Throwable $e): void
    {
        if ($operation = $this->questuraOperation($e)) {
            $this->reportedExceptionMap[$e] = true;
            // Prima di report(), context() e della serializzazione Laravel:
            // nessun messaggio, binding SQL, argomento o previous al logger.
            Log::error('Errore nel ciclo Questura.', [
                'error_code' => 'QUESTURA_WORKFLOW_ERROR',
                'operation' => $operation,
                'exception_class' => get_class($e),
            ]);
            return;
        }

        parent::reportThrowable($e);
    }

    public function render($request, Throwable $e)
    {
        $prepared = $this->prepareException($e);
        if ($this->questuraOperation($e)
            && !$e instanceof \Illuminate\Validation\ValidationException
            && !$e instanceof \Illuminate\Auth\AuthenticationException
            && (!$prepared instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface
                || $prepared->getStatusCode() >= 500)) {
            // Anche con debug attivo non esporre SQL, trace o oggetti personali.
            $message = 'Errore nel ciclo Questura. Verificare lo storico prima di ripetere l’operazione.';
            $status = $prepared instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface ? $prepared->getStatusCode() : 500;
            return $request->expectsJson()
                ? response()->json(['message' => $message, 'error_code' => 'QUESTURA_WORKFLOW_ERROR'], $status)
                : response($message, $status, ['Content-Type' => 'text/plain; charset=UTF-8']);
        }

        return parent::render($request, $e);
    }

    public function renderForConsole($output, Throwable $e)
    {
        if ($this->questuraOperation($e)) {
            $output->writeln('Errore nel ciclo Questura [QUESTURA_WORKFLOW_ERROR].');
            return;
        }

        parent::renderForConsole($output, $e);
    }

    private function questuraOperation(Throwable $e): ?string
    {
        // Classi/fasi ammesse; non leggere argomenti, messaggi, SQL o context().
        $workflows = [
            \App\Http\Controllers\QuesturaExportController::class => [
                'runWsAction', 'storeTransmission', 'storeExport', 'acquireReceipt',
                'acquireManualReceipt', 'finalizeTransmission', 'downloadArchivedReceipt',
            ],
            \App\Services\QuesturaRetentionService::class => [
                'archiveReceipt', 'finalizeDay', 'finalizeTransmission', 'finalizeManual',
                'minimizeCopies', 'verifyReceipt', 'auditArchives', 'expiredReceipts',
            ],
            \App\Services\QuesturaWebService::class => ['verify', 'send', 'receipt'],
            \App\Services\QuesturaTxtExportService::class => [],
        ];
        $inWorkflow = false;
        foreach ($e->getTrace() as $frame) {
            foreach ($workflows as $class => $operations) {
                if (isset($frame['class']) && is_a($frame['class'], $class, true)) {
                    $inWorkflow = true;
                    if (in_array($frame['function'], $operations, true)) {
                        return $frame['function'];
                    }
                }
            }
        }
        // Include errori di middleware/provider durante una route Questura.
        if ($this->container->bound('request')
            && str_starts_with((string) $this->container->make('request')->route()?->getName(), 'questura.')) {
            return 'questura_http';
        }

        return $inWorkflow ? 'questura_workflow' : null;
    }
}
