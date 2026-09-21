<script>
    tailwind.config = { darkMode: 'class' };
    (function () {
        var theme = @json($theme ?? 'system');
        if (theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    })();
</script>
<style>
    html:not(.dark) body { background-color: #f3f4f6; color: #111827; }
    html:not(.dark) .bg-gray-900 { background-color: #f3f4f6 !important; }
    html:not(.dark) .bg-gray-800 { background-color: #ffffff !important; }
    html:not(.dark) .bg-gray-700 { background-color: #e5e7eb !important; color: #111827; }
    html:not(.dark) .text-white { color: #111827 !important; }
    html:not(.dark) .text-gray-300,
    html:not(.dark) .text-gray-400,
    html:not(.dark) .text-gray-500 { color: #4b5563 !important; }
    html:not(.dark) .border-gray-700,
    html:not(.dark) .border-gray-600 { border-color: #e5e7eb !important; }
</style>
