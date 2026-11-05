<?php

namespace App\Http\Controllers;

use App\Services\NotificheService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificheController extends Controller
{
    public function __construct(private readonly NotificheService $notificheService)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $status = trim((string) $request->query('stato', ''));
        $search = trim((string) $request->query('q', ''));
        $page = max((int) $request->query('page', 1), 1);
        $result = $this->notificheService->paginateForUser($user, $status, $search, $page, 20);

        return view('notifiche.index', [
            'notifiche' => $result['items'],
            'stato' => $status,
            'search' => $search,
            'contatori' => $result['contatori'],
        ]);
    }
}
