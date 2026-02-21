<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Dog Kennel') }}</title>
        <meta name="description" content="Professional dog kennel management. Book boarding, daycare, and grooming services for your dog with ease.">
        <meta name="keywords" content="dog kennel, dog boarding, dog daycare, dog grooming, pet care, dog sitting">
        <meta name="author" content="{{ config('app.name', 'Dog Kennel') }}">
        <meta name="robots" content="index, follow">
        <link rel="canonical" href="{{ url()->current() }}">

        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:title" content="{{ config('app.name', 'Dog Kennel') }}">
        <meta property="og:description" content="Professional dog kennel management. Book boarding, daycare, and grooming services for your dog with ease.">
        <meta property="og:image" content="{{ asset('images/homepage.png') }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:image:alt" content="{{ config('app.name', 'Dog Kennel') }}">
        <meta property="og:site_name" content="{{ config('app.name', 'Dog Kennel') }}">
        <meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:url" content="{{ url()->current() }}">
        <meta name="twitter:title" content="{{ config('app.name', 'Dog Kennel') }}">
        <meta name="twitter:description" content="Professional dog kennel management. Book boarding, daycare, and grooming services for your dog with ease.">
        <meta name="twitter:image" content="{{ asset('images/homepage.png') }}">
        <meta name="twitter:image:alt" content="{{ config('app.name', 'Dog Kennel') }}">

        <!-- Additional SEO -->
        <meta name="theme-color" content="#6366f1">
        <meta name="msapplication-TileColor" content="#6366f1">
        <meta name="application-name" content="{{ config('app.name', 'Dog Kennel') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <!-- Scripts -->
        <script type="text/javascript">
            (function(c,l,a,r,i,t,y){
                c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
                t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
                y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
            })(window, document, "clarity", "script", "rt7tzo2scn");
        </script>
        @routes
        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
