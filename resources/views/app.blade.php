<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ ($appearance ?? 'system') == 'dark' ? 'dark' : '' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- SEO Meta Tags --}}
        <meta name="description" content="Demanda 3D - Sistema completo para gerenciamento de produção e vendas de impressões 3D. Controle de clientes, pedidos, insumos e métricas do seu negócio.">
        <meta name="keywords" content="impressão 3D, gerenciamento, produção, vendas, filamentos, pedidos, clientes, dashboard">
        <meta name="author" content="Demanda 3D">
        <meta name="robots" content="index, follow">
        <meta name="language" content="Portuguese">
        <meta name="revisit-after" content="7 days">

        {{-- Open Graph / Social Media --}}
        <meta property="og:title" content="Demanda 3D - Gerenciamento de Impressão 3D">
        <meta property="og:description" content="Sistema completo para gerenciar sua produção de impressões 3D. Controle clientes, pedidos, insumos e métricas em um só lugar.">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ config('app.url') }}">
        <meta property="og:image" content="{{ config('app.url') }}/logo.jpg">
        <meta property="og:locale" content="pt_BR">
        <meta property="og:site_name" content="Demanda 3D">

        {{-- Twitter Card --}}
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="Demanda 3D - Gerenciamento de Impressão 3D">
        <meta name="twitter:description" content="Sistema completo para gerenciar sua produção de impressões 3D.">
        <meta name="twitter:image" content="{{ config('app.url') }}/logo.jpg">

        {{-- Schema.org JSON-LD --}}
        @verbatim
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "WebApplication",
            "name": "Demanda 3D",
            "description": "Sistema de gerenciamento de produção e vendas de impressões 3D",
            "url": "{{ config('app.url') }}",
            "applicationCategory": "BusinessApplication",
            "operatingSystem": "Web",
            "browserRequirements": "Requires JavaScript",
            "offers": {
                "@type": "Offer",
                "price": "0",
                "priceCurrency": "BRL"
            },
            "author": {
                "@type": "Organization",
                "name": "Demanda 3D",
                "url": "{{ config('app.url') }}"
            }
        }
        </script>

        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Organization",
            "name": "Demanda 3D",
            "description": "Sistema de gerenciamento de produção e vendas de impressões 3D",
            "url": "{{ config('app.url') }}",
            "logo": "{{ config('app.url') }}/logo.jpg",
            "contactPoint": {
                "@type": "ContactPoint",
                "contactType": "technical support",
                "email": "suporte@demanda3d.com.br"
            }
        }
        </script>
        @endverbatim

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <link rel="icon" href="/logo.jpg" sizes="any">
        <link rel="apple-touch-icon" href="/logo.jpg">
        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        <x-inertia::head>
            <title>{{ config('app.name', 'Demanda 3D') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
