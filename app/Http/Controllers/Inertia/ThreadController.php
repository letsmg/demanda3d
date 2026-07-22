<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessageRequest;
use App\Models\Thread;
use App\Services\ThreadService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ThreadController extends Controller
{
    public function __construct(
        private ThreadService $threadService,
    ) {}

    /**
     * Dashboard de conversas (dúvidas).
     *
     * - Sellers: veem apenas threads do seu tenant
     * - Admins: veem threads de todos os tenants
     */
    public function index(Request $request): Response
    {
        $threads = $this->threadService->listForUser(
            auth()->user(),
            $request->get('per_page', 15),
        );

        return Inertia::render('Threads/Index', [
            'threads' => $threads,
        ]);
    }

    /**
     * Exibe uma thread com suas mensagens.
     */
    public function show(Thread $thread): Response
    {
        $thread = $this->threadService->findForUser($thread->id, auth()->user());

        return Inertia::render('Threads/Show', [
            'thread' => $thread,
        ]);
    }

    /**
     * Envia uma mensagem na thread.
     */
    public function storeMessage(StoreMessageRequest $request, Thread $thread)
    {
        try {
            $this->threadService->sendMessage(
                thread: $thread,
                content: $request->validated('content'),
                senderType: 'staff',
                senderId: auth()->id(),
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['content' => $e->getMessage()]);
        }

        return back()->with('success', 'Mensagem enviada com sucesso.');
    }

    /**
     * Fecha uma thread de dúvidas.
     */
    public function close(Thread $thread)
    {
        $this->threadService->closeThread($thread);

        return back()->with('success', 'Conversa fechada.');
    }

    /**
     * Reabre uma thread fechada.
     */
    public function reopen(Thread $thread)
    {
        $this->threadService->reopenThread($thread);

        return back()->with('success', 'Conversa reaberta.');
    }
}