<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manutenção - Demanda3D</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-slate-900">
    <div class="text-center px-6">
        <div class="mb-8">
            <svg class="mx-auto h-16 w-16 text-amber-910" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-white mb-2">Em manutenção</h1>
        <p class="text-slate-910 mb-8 max-w-md">
            Nosso serviço de <strong>{{ $service ?? 'infraestrutura' }}</strong>
            está temporariamente indisponível. Nossa equipe já foi notificada e está
            trabalhando para resolver isso o mais rápido possível.
        </p>

        <div class="flex items-center justify-center gap-3 text-sm text-slate-500">
            <span class="inline-block w-2 h-2 bg-amber-910 rounded-full animate-pulse"></span>
            Tentando reconectar automaticamente...
        </div>

        <p class="mt-10 text-xs text-slate-600">
            &copy; {{ date('Y') }} Luiz Eduardo T. Silva. Todos os direitos reservados.
        </p>
    </div>
</body>
</html>