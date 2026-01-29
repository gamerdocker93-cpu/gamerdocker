<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    @php
        // ============================================================
        // BLINDAGEM CONTRA NULL / STRING VAZIA / CHAVE INEXISTENTE
        // ============================================================
        $setting = \Helper::getSetting();
        $setting = is_array($setting) ? $setting : [];

        $custom = \Helper::getCustom();
        $custom = is_array($custom) ? $custom : [];

        /**
         * Pega valor de array com fallback.
         * - Se não existir, retorna $default
         * - Se vier null ou string vazia, retorna $default
         */
        $get = function(array $arr, string $key, $default = '') {
            if (!array_key_exists($key, $arr)) return $default;
            $val = $arr[$key];
            if ($val === null) return $default;
            if (is_string($val) && trim($val) === '') return $default;
            return $val;
        };

        // Algumas cores base seguras para não quebrar o CSS
        $fallbackSidebar      = '#111827';
        $fallbackSidebarDark  = '#0b1220';
        $fallbackBgBase       = '#0b0f19';
        $fallbackPrimary      = '#3b82f6';
    @endphp

    @if(!empty($setting['software_favicon']))
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('/storage/' . $setting['software_favicon']) }}">
    @endif

    <link rel="stylesheet" href="{{ asset('assets/css/fontawesome.min.css') }}">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700&family=Roboto+Condensed:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;1,100&display=swap"
        rel="stylesheet">

    <title>{{ env('APP_NAME', 'Laravel') }}</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        body {
            font-family: {{ $get($custom, 'font_family_default', "'Roboto Condensed', sans-serif") }};
        }

        :root {
            --ci-primary-color: {{ $get($custom, 'primary_color', $fallbackPrimary) }};
            --ci-primary-opacity-color: {{ $get($custom, 'primary_opacity_color', 'rgba(59,130,246,.5)') }};
            --ci-secundary-color: {{ $get($custom, 'secundary_color', '#111827') }};
            --ci-gray-dark: {{ $get($custom, 'gray_dark_color', '#111827') }};
            --ci-gray-light: {{ $get($custom, 'gray_light_color', '#9ca3af') }};
            --ci-gray-medium: {{ $get($custom, 'gray_medium_color', '#6b7280') }};
            --ci-gray-over: {{ $get($custom, 'gray_over_color', '#374151') }};
            --title-color: {{ $get($custom, 'title_color', '#ffffff') }};
            --text-color: {{ $get($custom, 'text_color', '#d1d5db') }};
            --sub-text-color: {{ $get($custom, 'sub_text_color', '#9ca3af') }};
            --placeholder-color: {{ $get($custom, 'placeholder_color', '#6b7280') }};
            --background-color: {{ $get($custom, 'background_color', '#0b0f19') }};

            --standard-color: #1C1E22;
            --shadow-color: #111415;
            --page-shadow: linear-gradient(to right, #111415, rgba(17, 20, 21, 0));
            --autofill-color: #f5f6f7;
            --yellow-color: #FFBF39;
            --yellow-dark-color: #d7a026;

            --border-radius: {{ $get($custom, 'border_radius', '12px') }};

            --tw-border-spacing-x: 0;
            --tw-border-spacing-y: 0;
            --tw-translate-x: 0;
            --tw-translate-y: 0;
            --tw-rotate: 0;
            --tw-skew-x: 0;
            --tw-skew-y: 0;
            --tw-scale-x: 1;
            --tw-scale-y: 1;
            --tw-scroll-snap-strictness: proximity;
            --tw-ring-offset-width: 0px;
            --tw-ring-offset-color: #fff;
            --tw-ring-color: rgba(59,130,246,.5);
            --tw-ring-offset-shadow: 0 0 #0000;
            --tw-ring-shadow: 0 0 #0000;
            --tw-shadow: 0 0 #0000;
            --tw-shadow-colored: 0 0 #0000;

            --input-primary: {{ $get($custom, 'input_primary', '#111827') }};
            --input-primary-dark: {{ $get($custom, 'input_primary_dark', '#0b1220') }};

            --carousel-banners: {{ $get($custom, 'carousel_banners', '#111827') }};
            --carousel-banners-dark: {{ $get($custom, 'carousel_banners_dark', '#0b1220') }};

            --sidebar-color: {{ $get($custom, 'sidebar_color', $fallbackSidebar) }} !important;
            --sidebar-color-dark: {{ $get($custom, 'sidebar_color_dark', $fallbackSidebarDark) }} !important;

            /* Corrigido: faltava ":" nos seus originais antigos */
            --navtop-color: {{ $get($custom, 'navtop_color', $get($custom, 'sidebar_color', $fallbackSidebar)) }};
            --navtop-color-dark: {{ $get($custom, 'navtop_color_dark', $get($custom, 'sidebar_color_dark', $fallbackSidebarDark)) }};

            --side-menu: {{ $get($custom, 'side_menu', '#ffffff') }};
            --side-menu-dark: {{ $get($custom, 'side_menu_dark', '#ffffff') }};

            --footer-color: {{ $get($custom, 'footer_color', '#111827') }};
            --footer-color-dark: {{ $get($custom, 'footer_color_dark', '#0b1220') }};

            --card-color: {{ $get($custom, 'card_color', '#111827') }};
            --card-color-dark: {{ $get($custom, 'card_color_dark', '#0b1220') }};
        }

        .navtop-color {
            background-color: {{ $get($custom, 'sidebar_color', $fallbackSidebar) }} !important;
        }

        :is(.dark .navtop-color) {
            background-color: {{ $get($custom, 'sidebar_color_dark', $fallbackSidebarDark) }} !important;
        }

        .bg-base {
            background-color: {{ $get($custom, 'background_base', $fallbackBgBase) }};
        }

        :is(.dark .bg-base) {
            background-color: {{ $get($custom, 'background_base_dark', $fallbackBgBase) }};
        }
    </style>

    @if(!empty($custom['custom_css']))
        <style>
            {!! $custom['custom_css'] !!}
        </style>
    @endif

    @if(!empty($custom['custom_header']))
        {!! $custom['custom_header'] !!}
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body color-theme="dark" class="bg-base text-gray-800 dark:text-gray-300">
<div id="viperpro"></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.0.0/datepicker.min.js"></script>
<script>
    window.Livewire?.on('copiado', (texto) => {
        navigator.clipboard.writeText(texto).then(() => {
            Livewire.emit('copiado');
        });
    });

    window._token = '{{ csrf_token() }}';

    if (localStorage.getItem('color-theme') === 'light') {
        document.documentElement.classList.remove('dark');
        document.documentElement.classList.add('light');
    } else {
        document.documentElement.classList.remove('light');
        document.documentElement.classList.add('dark');
    }
</script>

@if(!empty($custom['custom_js']))
    <script>
        {!! $custom['custom_js'] !!}
    </script>
@endif

@if(!empty($custom['custom_body']))
    {!! $custom['custom_body'] !!}
@endif
</body>
</html>